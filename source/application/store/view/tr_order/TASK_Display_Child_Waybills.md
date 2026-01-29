# 任务清单：前端显示子单号功能

## 📋 任务概览

**功能名称**: 前端显示子单号  
**PDR 文档**: `PDR_Display_Child_Waybills.md`  
**创建日期**: 2026-01-30  
**预计工期**: 2-3 小时  
**优先级**: 🔴 高  

---

## 🎯 任务目标

在订单列表页面（`/store/tr_order/all_list`）显示多箱订单的所有子单号，采用**方案 A（直接展开显示）**。

**成功标准**：
- ✅ 单箱订单正常显示主单号
- ✅ 多箱订单显示母单 + 所有子单号
- ✅ 子单号有视觉层级（缩进）
- ✅ 母单有明确标识（Badge）
- ✅ 复制功能正常
- ✅ 物流查询功能正常

---

## 📊 任务拆解

### 🔍 Phase 1: 前置验证（预计 30 分钟）

#### Task 1.1: 验证后端数据加载 ⏱️ 15min
**目标**: 确认 `TrOrder::index()` 是否已加载 `packageitems` 关联数据

**步骤**:
1. 查看 `TrOrder::index()` 方法源码
2. 检查是否有 `->with(['packageitems'])` 或类似代码
3. 如果没有，记录需要添加的位置

**验收标准**:
- [ ] 确认是否已加载 `packageitems`
- [ ] 如未加载，明确需要修改的代码位置

**TDD 验证**:
```php
// 创建 web/verify_packageitems_loading.php
// 查询一个多箱订单，检查 packageitems 是否存在
```

---

#### Task 1.2: 验证数据库数据完整性 ⏱️ 10min
**目标**: 确认现有订单的 `packageitems` 数据是否完整

**步骤**:
1. 使用 MCP MySQL 工具查询
2. 检查是否有多箱订单
3. 检查 `yoshop_inpack_item.t_order_sn` 是否有数据

**验收标准**:
- [ ] 至少找到 1 个多箱订单用于测试
- [ ] 确认子单号已存储在数据库中

**TDD 验证**:
```sql
-- 查询多箱订单
SELECT 
    i.id, 
    i.order_sn, 
    i.t_order_sn,
    COUNT(ii.id) as box_count
FROM yoshop_inpack i
LEFT JOIN yoshop_inpack_item ii ON i.id = ii.inpack_id
WHERE i.t_order_sn IS NOT NULL AND i.t_order_sn != ''
GROUP BY i.id
HAVING box_count > 1
LIMIT 5;
```

---

#### Task 1.3: 验证物流查询功能 ⏱️ 5min
**目标**: 确认 `getlog(this)` 函数是否支持子单号查询

**步骤**:
1. 查看 `index.php` 中 `getlog` 函数定义
2. 检查是否使用 `inpack_id` 还是 `tracking_number`
3. 确认是否需要修改

**验收标准**:
- [ ] 明确 `getlog` 的参数和逻辑
- [ ] 确认是否支持子单号查询

---

### 🔧 Phase 2: 后端开发（预计 30 分钟）

#### Task 2.1: 修改控制器加载关联数据 ⏱️ 20min
**目标**: 确保 `TrOrder::index()` 加载 `packageitems` 数据

**文件**: `source/application/store/controller/TrOrder.php`

**修改内容**:
```php
// 在 index() 方法中，找到类似这样的代码：
$list = $model->where($where)->order(...)->paginate(...);

// 修改为：
$list = $model->with([
    'packageitems' => function($query) {
        $query->where('t_order_sn', '<>', '')
              ->field('id,inpack_id,t_order_sn,weight')
              ->order('id asc');
    }
])->where($where)->order(...)->paginate(...);
```

**验收标准**:
- [ ] 代码修改完成
- [ ] 语法检查通过（`php -l`）
- [ ] 不影响现有功能

**依赖**: Task 1.1 完成

---

#### Task 2.2: TDD 验证数据加载 ⏱️ 10min
**目标**: 验证修改后的控制器能正确加载数据

**TDD 脚本**: `web/verify_index_packageitems.php`

```php
<?php
// 模拟访问 index 方法，检查返回数据中是否包含 packageitems
require_once __DIR__ . '/../source/thinkphp/start.php';

use app\store\model\Inpack;

$model = new Inpack();
$list = $model->with(['packageitems'])->limit(5)->select();

foreach ($list as $item) {
    echo "订单 {$item['order_sn']}: ";
    echo "箱子数 = " . count($item['packageitems']) . "\n";
}
```

**验收标准**:
- [ ] 脚本运行成功
- [ ] 输出显示箱子数量正确

**依赖**: Task 2.1 完成

---

### 🎨 Phase 3: 前端开发（预计 40 分钟）

#### Task 3.1: 修改视图文件 ⏱️ 30min
**目标**: 在 `index.php` 中实现子单号显示逻辑

**文件**: `source/application/store/view/tr_order/index.php`  
**修改位置**: Line 414-418

**修改内容**: 见 `PDR_Display_Child_Waybills.md` 第 3.2.2 节

**关键代码**:
```php
<?php 
$hasMultipleBoxes = !empty($item['packageitems']) && count($item['packageitems']) > 1;
?>

<?php if ($hasMultipleBoxes): ?>
    <!-- 显示母单 + 子单 -->
    国际单号:
    <span style="cursor:pointer" text="<?= $item['t_order_sn'] ?>" onclick="copyUrl2(this)">
        <?= $item['t_order_sn'] ?>
    </span>
    <span class="am-badge am-badge-primary am-radius" style="font-size:10px;">母单</span>
    <a href="javascript:;" onclick="getlog(this)" value="<?= $item['id'] ?>">[物流]</a></br>
    
    <?php foreach ($item['packageitems'] as $index => $box): ?>
        <?php if ($index > 0 && !empty($box['t_order_sn'])): ?>
            <span style="margin-left:20px;color:#999;">└ 子单:</span>
            <span style="cursor:pointer" text="<?= $box['t_order_sn'] ?>" onclick="copyUrl2(this)">
                <?= $box['t_order_sn'] ?>
            </span>
            <a href="javascript:;" onclick="getlog(this)" value="<?= $item['id'] ?>">[物流]</a></br>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else: ?>
    <!-- 单箱：正常显示 -->
    国际单号:
    <span style="cursor:pointer" text="<?= $item['t_order_sn'] ?>" onclick="copyUrl2(this)">
        <?= $item['t_order_sn'] ?>
    </span>
    <a href="javascript:;" onclick="getlog(this)" value="<?= $item['id'] ?>">[物流]</a></br>
<?php endif; ?>
```

**验收标准**:
- [ ] 代码修改完成
- [ ] 语法正确（浏览器不报错）
- [ ] 样式正常（缩进、颜色）

**依赖**: Task 2.2 完成

---

#### Task 3.2: 样式微调 ⏱️ 10min
**目标**: 优化显示效果

**可选优化**:
1. 调整缩进距离（`margin-left`）
2. 调整子单号颜色（`color`）
3. 调整 Badge 大小和颜色

**验收标准**:
- [ ] 视觉层级清晰
- [ ] 与现有样式协调

**依赖**: Task 3.1 完成

---

### ✅ Phase 4: 功能测试（预计 40 分钟）

#### Task 4.1: 单箱订单测试 ⏱️ 5min
**测试场景**: 只有 1 个箱子的订单

**测试步骤**:
1. 访问 `/store/tr_order/all_list`
2. 找到单箱订单
3. 检查显示效果

**预期结果**:
- [ ] 显示主单号
- [ ] 不显示"母单" Badge
- [ ] 不显示子单号
- [ ] 复制功能正常

---

#### Task 4.2: 多箱订单（顺丰）测试 ⏱️ 10min
**测试场景**: 顺丰快递，3 个箱子（1 母 + 2 子）

**测试步骤**:
1. 找到顺丰多箱订单
2. 检查显示效果

**预期结果**:
- [ ] 显示母单号 + "母单" Badge
- [ ] 显示 2 个子单号，有缩进
- [ ] 每个单号都可以复制
- [ ] 每个单号都有 `[物流]` 链接

---

#### Task 4.3: 多箱订单（中通）测试 ⏱️ 10min
**测试场景**: 中通快递，2 个箱子（独立单号）

**测试步骤**:
1. 找到中通多箱订单
2. 检查显示效果

**预期结果**:
- [ ] 显示主单号 + "母单" Badge（或不显示 Badge，取决于实现）
- [ ] 显示第 2 个箱子的单号
- [ ] 复制和物流功能正常

---

#### Task 4.4: 复制功能测试 ⏱️ 5min
**测试步骤**:
1. 点击主单号复制
2. 点击子单号复制
3. 粘贴到文本编辑器验证

**预期结果**:
- [ ] 主单号复制正确
- [ ] 子单号复制正确
- [ ] 不包含额外字符

---

#### Task 4.5: 物流查询功能测试 ⏱️ 10min
**测试步骤**:
1. 点击主单号的 `[物流]` 链接
2. 点击子单号的 `[物流]` 链接
3. 检查是否能正确查询

**预期结果**:
- [ ] 主单号物流查询正常
- [ ] 子单号物流查询正常（或明确不支持）

**注意**: 如果 `getlog` 不支持子单号，需要在 Task 1.3 中确认并修改

---

### 🔒 Phase 5: 兼容性测试（预计 20 分钟）

#### Task 5.1: 旧数据兼容性测试 ⏱️ 10min
**测试场景**: 没有 `packageitems` 数据的旧订单

**测试步骤**:
1. 找到旧订单（推送前的订单）
2. 检查是否报错

**预期结果**:
- [ ] 不报错
- [ ] 正常显示主单号（如果有）
- [ ] 不显示子单号

---

#### Task 5.2: 空数据处理测试 ⏱️ 5min
**测试场景**: `t_order_sn` 为空的订单

**预期结果**:
- [ ] 不显示运单信息
- [ ] 不报错

---

#### Task 5.3: 浏览器兼容性测试 ⏱️ 5min
**测试浏览器**: Chrome, Edge, Firefox

**预期结果**:
- [ ] 样式在各浏览器下正常
- [ ] 功能在各浏览器下正常

---

## 📈 进度跟踪

### 当前状态: 📝 待开始

| Phase | 任务数 | 已完成 | 进度 |
|-------|--------|--------|------|
| Phase 1: 前置验证 | 3 | 0 | 0% |
| Phase 2: 后端开发 | 2 | 0 | 0% |
| Phase 3: 前端开发 | 2 | 0 | 0% |
| Phase 4: 功能测试 | 5 | 0 | 0% |
| Phase 5: 兼容性测试 | 3 | 0 | 0% |
| **总计** | **15** | **0** | **0%** |

---

## 🚨 风险与应对

### 风险 1: `packageitems` 未加载
**影响**: 子单号无法显示  
**概率**: 中  
**应对**: Task 1.1 验证，Task 2.1 修复

### 风险 2: 旧数据无子单号
**影响**: 显示空白或报错  
**概率**: 低  
**应对**: Task 5.1 测试，增加空值判断

### 风险 3: 物流查询不支持子单号
**影响**: 点击 `[物流]` 无法查询  
**概率**: 中  
**应对**: Task 1.3 确认，如需要则修改 `getlog` 函数

---

## 📝 开发规范

### 代码规范
- 遵循项目现有代码风格
- 使用项目已有的 CSS 类（如 `am-badge`）
- 保持代码可读性，添加必要注释

### 测试规范
- 每个 Phase 完成后进行验证
- 使用 TDD 工具验证数据库操作
- 使用浏览器验证前端交互

### 提交规范
- 提交前运行 `php -l` 检查语法
- 提交信息格式：`[功能] 描述`
- 例如：`[前端] 添加子单号显示功能`

---

## ✅ 验收标准

### 功能验收
- [ ] 单箱订单显示正常
- [ ] 多箱订单显示母单 + 子单
- [ ] 子单号有视觉层级
- [ ] 母单有明确标识
- [ ] 复制功能正常
- [ ] 物流查询功能正常

### 性能验收
- [ ] 页面加载时间无明显增加
- [ ] 列表渲染流畅

### 兼容性验收
- [ ] 旧数据不报错
- [ ] 主流浏览器显示正常

---

## 📞 下一步行动

**等待用户审阅 PDR 文档后，按以下顺序执行**：

1. ✅ 用户审阅 `PDR_Display_Child_Waybills.md`
2. ✅ 用户审阅 `TASK_Display_Child_Waybills.md`（本文档）
3. 🚀 开始执行 Phase 1: 前置验证
4. 🚀 开始执行 Phase 2-5

---

**准备好开始了吗？请确认是否开始执行任务！** 🚀
