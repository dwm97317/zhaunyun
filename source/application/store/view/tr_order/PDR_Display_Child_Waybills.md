# PDR: 前端显示子单号功能

## 文档信息
- **文档版本**: v1.0
- **创建日期**: 2026-01-29
- **最后更新**: 2026-01-29
- **相关需求**: 在订单列表页面显示多箱订单的子单号信息

---

## 一、需求背景

### 1.1 业务场景
在集运业务中，一个订单可能包含多个物理箱子：
- **顺丰快递**：使用"子母单"模式，第一个箱子为母单，后续箱子为子单
- **其他快递**（如中通）：每个箱子独立生成运单号

### 1.2 当前问题
- 后端已实现多箱推送逻辑（`TrOrder::sendtoqudaoshang`）
- 数据库已存储每个箱子的运单号（`yoshop_inpack_item.t_order_sn`）
- **前端仅显示主单号**（`yoshop_inpack.t_order_sn`），子单号无法查看

### 1.3 用户需求
用户在订单列表页面（`/store/tr_order/all_list`）需要：
1. 查看主单号（母单号）
2. 查看所有子单号
3. 能够复制每个运单号
4. 能够查询每个运单的物流轨迹

---

## 二、解决方案

### 2.1 显示方案：方案 A（直接展开显示）

**显示效果**：
```
承运商: 顺丰速运
国际单号: SF7444701482866 (母单) [物流]
  └ 子单: SF7444701482875 [物流]
  └ 子单: SF7444701482883 [物流]
```

**选择理由**：
1. 信息直观，无需额外操作
2. 符合用户查看习惯
3. 实现简单，维护成本低
4. 适合大多数场景（通常 2-5 个箱子）

---

## 三、技术设计

### 3.1 数据流

#### 数据来源
```
yoshop_inpack (主表)
├─ id: 集运单ID
├─ t_order_sn: 主单号（母单号）
├─ t_name: 承运商名称
└─ packageitems (关联关系)
    └─ yoshop_inpack_item (箱子表)
        ├─ id: 箱子ID
        ├─ inpack_id: 关联集运单ID
        ├─ t_order_sn: 该箱子的运单号
        ├─ weight: 重量
        └─ ...
```

#### 数据查询
在 `TrOrder::all_list()` 或 `TrOrder::index()` 方法中，确保查询时加载 `packageitems` 关联数据：

```php
$list = $model->with(['packageitems' => function($query) {
    $query->where('t_order_sn', '<>', '')->field('id,inpack_id,t_order_sn,weight');
}])->where($where)->order(...)->paginate(...);
```

### 3.2 前端实现

#### 3.2.1 修改文件
**文件路径**: `source/application/store/view/tr_order/index.php`

**修改位置**: Line 414-418（当前显示主单号的位置）

#### 3.2.2 实现逻辑

**原代码**（Line 414-418）：
```php
<?php if (!empty($item['t_order_sn'])): ?> 
承运商:
<span style="cursor:pointer" text="<?= $item['t_name'] ?>" onclick="copyUrl2(this)"><?= $item['t_name'] ?></span></br>
国际单号:
<span style="cursor:pointer" text="<?= $item['t_order_sn'] ?>" onclick="copyUrl2(this)"><?= $item['t_order_sn'] ?></span><a href="javascript:;" onclick="getlog(this)" value="<?= $item['id'] ?>" >[物流]</a></br>
<?php endif ;?>
```

**新代码**（支持子单显示）：
```php
<?php if (!empty($item['t_order_sn'])): ?> 
承运商:
<span style="cursor:pointer" text="<?= $item['t_name'] ?>" onclick="copyUrl2(this)"><?= $item['t_name'] ?></span></br>

<?php 
// 检查是否有多个箱子
$hasMultipleBoxes = !empty($item['packageitems']) && count($item['packageitems']) > 1;
?>

<?php if ($hasMultipleBoxes): ?>
    <!-- 多箱情况：显示母单 + 子单 -->
    国际单号:
    <span style="cursor:pointer" text="<?= $item['t_order_sn'] ?>" onclick="copyUrl2(this)"><?= $item['t_order_sn'] ?></span>
    <span class="am-badge am-badge-primary am-radius" style="font-size:10px;">母单</span>
    <a href="javascript:;" onclick="getlog(this)" value="<?= $item['id'] ?>">[物流]</a></br>
    
    <?php foreach ($item['packageitems'] as $index => $box): ?>
        <?php if ($index > 0 && !empty($box['t_order_sn'])): ?>
            <span style="margin-left:20px;color:#999;">└ 子单:</span>
            <span style="cursor:pointer" text="<?= $box['t_order_sn'] ?>" onclick="copyUrl2(this)"><?= $box['t_order_sn'] ?></span>
            <a href="javascript:;" onclick="getlog(this)" value="<?= $item['id'] ?>">[物流]</a></br>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else: ?>
    <!-- 单箱情况：正常显示 -->
    国际单号:
    <span style="cursor:pointer" text="<?= $item['t_order_sn'] ?>" onclick="copyUrl2(this)"><?= $item['t_order_sn'] ?></span>
    <a href="javascript:;" onclick="getlog(this)" value="<?= $item['id'] ?>">[物流]</a></br>
<?php endif; ?>

<?php endif ;?>
```

### 3.3 样式优化

#### 3.3.1 子单缩进样式
使用 `margin-left` 和特殊字符 `└` 实现视觉层级：
```html
<span style="margin-left:20px;color:#999;">└ 子单:</span>
```

#### 3.3.2 母单标识
使用 Badge 组件标识母单：
```html
<span class="am-badge am-badge-primary am-radius" style="font-size:10px;">母单</span>
```

#### 3.3.3 可选：添加箱子编号
如果需要显示箱子编号（如"箱 #1"、"箱 #2"）：
```php
<span style="margin-left:20px;color:#999;">└ 箱 #<?= $index ?>:</span>
```

---

## 四、实现步骤

### 4.1 后端准备（已完成）
- [x] `TrOrder::sendtoqudaoshang` 已实现多箱推送逻辑
- [x] `yoshop_inpack_item.t_order_sn` 已存储子单号
- [ ] **待验证**：`TrOrder::index()` 或 `all_list()` 是否已加载 `packageitems` 关联

### 4.2 前端开发
1. **修改视图文件** `index.php`（Line 414-418）
2. **测试场景**：
   - 单箱订单：显示正常
   - 多箱订单（顺丰）：显示母单 + 子单
   - 多箱订单（中通）：显示多个独立单号
3. **验证功能**：
   - 点击复制功能正常
   - 点击 `[物流]` 能查询轨迹
   - 样式显示正常（缩进、颜色）

### 4.3 兼容性处理
- **旧数据兼容**：如果 `packageitems` 为空或未加载，回退到显示主单号
- **单箱订单**：保持原有显示方式，不显示"母单"标识
- **空运单号**：如果某个箱子的 `t_order_sn` 为空，跳过不显示

---

## 五、测试用例

### 5.1 测试场景

| 场景 | 箱子数 | 快递公司 | 预期显示 |
|------|--------|----------|----------|
| 单箱订单 | 1 | 顺丰 | 国际单号: SF123456 [物流] |
| 多箱订单（顺丰） | 3 | 顺丰 | 母单 + 2个子单，带缩进 |
| 多箱订单（中通） | 2 | 中通 | 主单 + 1个子单 |
| 未推送订单 | 2 | - | 不显示运单信息 |
| 部分推送失败 | 3 | 顺丰 | 仅显示成功推送的运单号 |

### 5.2 验证点
- [ ] 主单号正确显示
- [ ] 子单号正确显示且有缩进
- [ ] "母单" Badge 仅在多箱时显示
- [ ] 复制功能正常
- [ ] 物流查询功能正常
- [ ] 样式在不同浏览器下正常

---

## 六、后续优化（可选）

### 6.1 短期优化
1. **显示箱子重量**：在子单号后显示重量
   ```
   └ 子单: SF7444701482875 (1.5kg) [物流]
   ```

2. **批量复制**：增加"复制所有单号"按钮
   ```
   [复制所有单号]
   ```

3. **物流状态标识**：用颜色区分已签收/运输中/异常
   ```
   └ 子单: SF7444701482875 ✓已签收 [物流]
   ```

### 6.2 长期优化
1. **折叠显示**：当箱子数 > 5 时，默认折叠显示
2. **详情弹窗**：点击"查看详情"显示所有箱子的完整信息（尺寸、重量、状态）
3. **批量查询物流**：一键查询所有子单的物流轨迹

---

## 七、风险评估

### 7.1 技术风险
| 风险 | 影响 | 概率 | 应对措施 |
|------|------|------|----------|
| `packageitems` 未加载 | 子单号无法显示 | 中 | 在控制器中确保加载关联数据 |
| 旧数据无子单号 | 显示空白 | 低 | 增加空值判断，回退到主单显示 |
| 样式兼容性问题 | 显示错乱 | 低 | 测试主流浏览器 |

### 7.2 业务风险
| 风险 | 影响 | 概率 | 应对措施 |
|------|------|------|----------|
| 箱子过多导致页面过长 | 用户体验差 | 低 | 后续可增加折叠功能 |
| 用户不理解"母单/子单" | 咨询增加 | 中 | 增加提示文案或帮助文档 |

---

## 八、相关文档

- **后端实现文档**: `PDR_SF_Multi_Box.md`
- **数据库设计**: `yoshop_inpack` 和 `yoshop_inpack_item` 表结构
- **测试报告**: `test_sf_multibox.php` 测试结果

---

## 九、变更记录

| 日期 | 版本 | 修改人 | 修改内容 |
|------|------|--------|----------|
| 2026-01-29 | v1.0 | AI | 初始版本，定义显示方案 A |

---

## 十、附录

### 10.1 关键代码片段

#### 控制器查询（需验证）
```php
// TrOrder.php - index() 或 all_list() 方法
$list = $model->with([
    'packageitems' => function($query) {
        $query->where('t_order_sn', '<>', '')
              ->field('id,inpack_id,t_order_sn,weight')
              ->order('id asc');
    }
])->where($where)->order(...)->paginate(...);
```

#### 视图显示逻辑
见 **3.2.2 实现逻辑** 部分

### 10.2 数据示例

**单箱订单**：
```json
{
  "id": 123,
  "t_order_sn": "SF7444701482866",
  "t_name": "顺丰速运",
  "packageitems": [
    {"id": 1, "t_order_sn": "SF7444701482866", "weight": 2.0}
  ]
}
```

**多箱订单**：
```json
{
  "id": 124,
  "t_order_sn": "SF7444701482866",
  "t_name": "顺丰速运",
  "packageitems": [
    {"id": 1, "t_order_sn": "SF7444701482866", "weight": 2.0},
    {"id": 2, "t_order_sn": "SF7444701482875", "weight": 1.5},
    {"id": 3, "t_order_sn": "SF7444701482883", "weight": 1.0}
  ]
}
```

---

**文档结束**
