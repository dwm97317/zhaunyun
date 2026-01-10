<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">充值订单详情</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-margin-top">
                        <button type="button" onclick="goBack()" class="am-btn am-btn-default am-btn-sm">
                            <i class="am-icon-arrow-left"></i> 返回上一页
                        </button>
                    </div>
                    
                    <div class="am-u-sm-12 am-margin-top-lg">
                        <div class="am-panel am-panel-default">
                            <div class="am-panel-hd">订单信息</div>
                            <div class="am-panel-bd">
                                <table class="am-table am-table-bordered am-table-striped">
                                    <tr>
                                        <th width="150">订单ID</th>
                                        <td><?= $detail['order_id'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>订单号</th>
                                        <td><?= $detail['order_no'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>支付金额</th>
                                        <td class="am-text-danger"><strong>¥<?= number_format($detail['pay_price'], 2) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>赠送金额</th>
                                        <td>¥<?= number_format($detail['gift_money'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <th>充值方式</th>
                                        <td>
                                            <span class="am-badge <?= $detail['recharge_type']['value'] == 10 ? 'am-badge-secondary' : 'am-badge-success' ?>">
                                                <?= $detail['recharge_type']['text'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>套餐名称</th>
                                        <td><?= isset($detail['order_plan']['plan_name']) ? $detail['order_plan']['plan_name'] : '--' ?></td>
                                    </tr>
                                    <tr>
                                        <th>支付状态</th>
                                        <td>
                                            <span class="am-badge <?= $detail['pay_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                <?= $detail['pay_status']['text'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>交易号</th>
                                        <td><?= !empty($detail['transaction_id']) ? $detail['transaction_id'] : '--' ?></td>
                                    </tr>
                                    <tr>
                                        <th>付款时间</th>
                                        <td><?= $detail['pay_time']['text'] ?: '--' ?></td>
                                    </tr>
                                    <tr>
                                        <th>创建时间</th>
                                        <td><?= $detail['create_time'] ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($detail['user'])): ?>
                    <div class="am-u-sm-12 am-margin-top-lg">
                        <div class="am-panel am-panel-default">
                            <div class="am-panel-hd">用户信息</div>
                            <div class="am-panel-bd">
                                <table class="am-table am-table-bordered am-table-striped">
                                    <tr>
                                        <th width="150">用户头像</th>
                                        <td>
                                            <img src="<?= $detail['user']['avatarUrl'] ?>" width="72" height="72" alt="">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>用户昵称</th>
                                        <td><?= $detail['user']['nickName'] ?></td>
                                    </tr>
                                    <?php if($set['is_show']==0): ?>
                                    <tr>
                                        <th>用户ID</th>
                                        <td><?= $detail['user']['user_id'] ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($set['is_show']==1): ?>
                                    <tr>
                                        <th>用户编号</th>
                                        <td><?= $detail['user']['user_code'] ?: '--' ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function goBack() {
    // 优先使用浏览器历史记录返回
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // 如果没有历史记录，跳转到充值订单列表页
        window.location.href = '<?= url('setting.payment_flow/index') ?>';
    }
}
</script>

