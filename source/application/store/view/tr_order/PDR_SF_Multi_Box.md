# 顺丰多箱（子母件）与多运单项目设计文档 (PDR)

## 1. 业务修正与背景
**业务场景**：
用户在“集运详情页”通过 **“添加箱子”** 功能，为同一个集运单（`Inpack`）添加了多个物理包裹（`InpackItem`）。

**发货需求**：
1.  **顺丰 (SF Express)**：
    *   采用 **子母件 (Mother-Child)** 模式。
    *   第 1 个箱子作为 **母单** (Main Waybill)。
    *   后续箱子作为 **子单** (Sub Waybill)，关联母单号。
    *   实现“一单多箱，统一追踪”。
2.  **非顺丰 (中通/京东等)**：
    *   采用 **独立主单** 模式。
    *   每个箱子都视为一个独立的主运单，生成独立的运单号。

---

## 2. 现有系统数据结构分析

### 2.1 主表 (`yoshop_inpack`)
*   代表“集运订单”。
*   字段 `t_order_sn`: 存储主运单号（对于顺丰即母单号）。

### 2.2 子表 (`yoshop_inpack_item`)
*   代表“物理箱子/包裹”。
*   通过前端“添加箱子”功能生成，`addItem` 方法会根据数量生成多条记录。
*   **关键字段**：
    *   `inpack_id`: 关联主订单。
    *   `length`, `width`, `height`, `weight`: 箱子物理属性。
    *   `t_order_sn`: **已存在**，可用于存储每个箱子的独立运单号（子单号）。

---

## 3. 解决方案设计

### 3.1 核心逻辑流程 (`TrOrder::sendtoqudaoshang`)

**Step 1: 获取箱子信息**
*   在推送前，先查询当前订单下的所有箱子 (`InpackItem`)。
*   `$boxes = InpackItem::where('inpack_id', $order_id)->select();`

**Step 2: 顺丰策略 (SF Logic)**
*   **场景 1：无子箱 (只有一个主订单信息)**
    *   按原有逻辑推送一条主单。
*   **场景 2：有多箱 (Count($boxes) > 0)**
    *   **Loop 循环遍历箱子**：
        *   **第 1 个箱子 (Key 0)**:
            *   **角色**：母单 (`is_mother_child = 1`)。
            *   **操作**：调用 SF 接口。
            *   **保存**：运单号存入 `Inpack.t_order_sn` **和** `InpackItem[0].t_order_sn`。
            *   **记录**：暂存此单号为 `$mother_no`。
        *   **后续箱子 (Key > 0)**:
            *   **角色**：子单 (`is_mother_child = 2`)。
            *   **参数**：`mother_waybill_no = $mother_no`。
            *   **操作**：调用 SF 接口。
            *   **保存**：运单号存入 `InpackItem[i].t_order_sn`。

**Step 3: 其他快递策略 (General Logic)**
*   **Loop 循环遍历箱子**：
    *   **每个箱子**：
        *   视为独立订单 (`is_mother_child` 不传或传 0)。
        *   生成独立的商家订单号 (`order_sn` + `_` + `id`) 防止重复。
        *   **保存**：运单号存入 `InpackItem[i].t_order_sn`。
        *   (可选) 将第 1 个运单号同步更新到 `Inpack.t_order_sn` 以便列表页显示。

### 3.2 接口类库改造 (`Sf.php` / `Zto.php`)

*   **`Sf.php`**:
    *   `createOrder` 方法需接收 `is_mother_child` 和 `mother_waybill_no` 参数。
    *   调整 `cargo_details` (托寄物) 逻辑：母单建议包含完整货物信息（或按比例分配），子单通常作为附件处理（视顺丰具体校验而定，一般母单全申报即可）。

*   **通用接口**：
    *   确保 `createOrder` 支持传入 `weight`, `volumn` 等箱子级参数，覆盖主单默认参数。

### 3.3 数据库字段兼容性
*   目前 `yoshop_inpack_item` 表已具备 `t_order_sn`, `t_name`, `t_number` 字段，**无需修改数据库结构**。

---

## 4. 实施步骤 (Roadmap)

1.  **代码重构**: 修改 `TrOrder.php` 的推送方法，引入对 `InpackItem` 的查询和遍历。
2.  **接口升级**: 更新 `Sf.php` 的下单方法签名。
3.  **UI 适配**:
    *   修改 `delivery.php` (Javascript) 的 `pushToThird` 回调处理。
    *   原本只回填一个 `#t_order_sn`。
    *   现在需要根据返回结果（可能是数组），回填到对应的箱子列表输入框中（如果前端展示了箱子明细）。
    *   或者仅提示“推送成功”，由用户刷新查看详情。

## 5. 风险点
*   **部分失败**：如果 3 个箱子，第 1 个成功（母单），第 2 个失败，第 3 个怎么办？
    *   **建议机制**：遇到错误立即中断，提示用户。用户需手动处理或重试（需处理重试时的幂等性，已成功的不要重推）。
*   **取消订单**：取消操作需要遍历所有子单号进行取消。
