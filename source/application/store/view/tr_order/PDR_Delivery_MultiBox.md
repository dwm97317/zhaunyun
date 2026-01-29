
# PDR: 分箱发货与顺丰子母单自动填入

## 1. 现状与问题 (`Current State & Problem`)
- **现状**:
  - 当前 `delivery` (发货) 页面仅支持输入一个“转运单号” (`t_order_sn`)。
  - 对于多箱订单 (Multi-Box Support)，后端已支持通过 `yoshop_inpack_item` 表存储每个箱子的子单号。
  - `sendtoqudaoshang` (推送到渠道商) 接口虽然在后端保存了顺丰子母单号到数据库，但在前端只返回了母单号，且前端没有展示子单号的地方。
- **问题**:
  - 管理员无法在发货环节直观地看到或修改子箱的运单号。
  - 推送顺丰/中通后，虽然生成了子单号，但前端无法自动展示，用户体验不完整。

## 2. 目标 (`Goals`)
1.  **支持多单号填写**: 在发货页面，如果订单包含多个子箱，动态展示对应数量的单号输入框。
2.  **自动填入子单号**: 当通过 API (如顺丰) 推送成功后，自动将返回的子母单号回填到对应的输入框中。
3.  **数据保存**: 确保提交发货表单时，所有子单号都能正确保存到数据库。

## 3. 设计方案 (`Design Solution`)

### 3.1 用户体验优化 (UX Improvement)
- **核心原则**: 消除冗余，所见即所得。
- **展示逻辑**:
  - **单箱订单**: 保持原有界面，只显示一个“转运单”输入框。
  - **多箱订单**: 
    - **隐藏**原本独立的“转运单”输入框。
    - **显示**分箱列表，其中“箱1”明确标记为“母单/主单”。
    - 用户只需在分箱列表中填写，无需重复填写主单号。

### 3.2 前端改造 (`delivery.php`)

#### 3.2.1 视图渲染逻辑
- 判断 `$detail['packageitems']` 数量。
- **Case A: 多箱 (>0)**:
  - 渲染一个隐藏的 `<input type="hidden" name="delivery[t_order_sn]">` 用于兼容旧后端逻辑。
  - 渲染“分箱信息列表”：
    - **箱1**: 
      - Label: "箱1 (母单)"
      - Input: `name="delivery[sonitem][{id}][t_order_sn]"`
      - Binding: 添加 JS 事件，当箱1输入改变时，自动同步到隐藏的主单号 Input。
    - **其他箱**: 
      - Label: "箱N"
      - Input: `name="delivery[sonitem][{id}][t_order_sn]"`
- **Case B: 单箱/无子箱**:
  - 保持原有布局，显示可见的“转运单”输入框。

#### 3.2.2 JS 逻辑增强
- **同步逻辑**: 
  - `$('.box-1-input').on('input', function() { $('input[name="delivery[t_order_sn]"]').val($(this).val()); });`
- **自动回填**:
  - 适配新的 DOM 结构，通过 `data-box-id` 精准回填。
  - 回填箱1时，触发 `input` 事件以确保同步逻辑执行。

### 3.3 后端逻辑保持 (`TrOrder.php`)
- `deliverySave`: 保持现有逻辑不变。由于前端会同步主单号，后端主表 (`save`) 和子表 (`sonitem` 循环) 的保存逻辑都能正常工作，无需改动后端。

## 4. 实施步骤 (`Implementation Steps`)

1.  **Refactor Backend (`sendtoqudaoshang`)**:
    - 修改顺丰和中通的多箱处理逻辑，收集 `sub_tracking_numbers`。
2.  **Update View (`delivery.php`)**:
    - 添加 PHP 逻辑遍历 `sonitem` 并渲染输入框。
    - 确保输入框已有值（如果数据库里有）能正确预填。
3.  **Update JS (`delivery.php`)**:
    - 修改 `pushToThird` 回调逻辑。
4.  **Refactor Backend (`deliverySave`)**:
    - 确保能保存子单号。

## 5. 验收标准 (`Acceptance Criteria`)
- [ ] 发货页面能根据订单的箱数显示对应数量的单号输入框。
- [ ] 现有的单号（如果以前保存过）能正确加载显示。
- [ ] 点击“推送到第三方”成功后，所有输入框自动填入顺丰/中通返回的单号。
- [ ] 点击“提交”发货后，所有子单号正确保存到数据库。
