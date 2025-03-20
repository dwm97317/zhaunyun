<?php

use app\common\enum\order\PayType as PayTypeEnum;

?>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">会员订单列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>订单ID</th>
                                <th>订单编号</th>
                                <th>会员信息</th>
                                <th>会员等级</th>
                                <th>付款类型</th>
                                <th>支付状态</th>
                                <th>创建时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['order_id'] ?></td>
                                    <td class="am-text-middle"><?= $item['order_no'] ?></td>
                                    <td class="am-text-middle">
                                        <?= $item['user']['nickName'] ;?><br>
                                        <?php if($storesetting['usercode_mode']['is_show']==0) :?>
                                            用户ID:<?= $item['user']['user_id'] ;?>
                                        <?php endif;?>
                                        <?php if($storesetting['usercode_mode']['is_show']==1) :?>
                                            用户编号:<?= $item['user']['user_code'] ;?>
                                        <?php endif;?>
                                        
                                    </td>
                                    <td class="am-text-middle">
                                        <span><?= $item['grade']['name'] ?></span>
                                    </td>
                                    <td class="am-text-middle">
                                        <span><?= PayTypeEnum::data()[$item['pay_type']]['name'] ?></span>
                                    </td>
                                    <td class="am-text-middle">
                                       <span class="am-badge am-badge-<?= $item['is_pay'] ? 'success' : 'warning' ?>">
                                           <?= $item['is_pay'] ? '已支付' : '未支付' ?>
                                       </span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="8" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        // 删除元素
        var url = "<?= url('user.grade/delete') ?>";
        $('.j-delete').delete('grade_id', url);

    });
</script>

