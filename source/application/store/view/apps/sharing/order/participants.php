<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">
                        参与人员列表
                        <small class="tipssmall">(拼团订单：<?= $sharingOrder['title'] ?? $sharingOrder['order_id'] ?>)</small>
                    </div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>用户信息</th>
                                <th>集运单信息</th>
                                <th>包裹列表</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($participantsList)): ?>
                                <?php $index = 1; foreach ($participantsList as $participant): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $index++ ?></td>
                                    <td class="am-text-middle">
                                        用户ID: <?= $participant['user_id'] ?><br>
                                        <?php if($setcode['is_show']!=0 && !empty($participant['user_code'])): ?>
                                        用户Code: <?= $participant['user_code'] ?><br>
                                        <?php endif; ?>
                                        昵称: <?= $participant['nickName'] ?><br>
                                        手机: <?= $participant['mobile'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if (!empty($participant['inpacks'])): ?>
                                            <?php foreach ($participant['inpacks'] as $inpack): ?>
                                                <div style="margin-bottom: 10px; padding: 5px; border: 1px solid #ddd; border-radius: 3px;">
                                                    订单号: <a href="<?= url('store/trOrder/orderdetail', ['id' => $inpack['id']]) ?>" target="_blank"><?= $inpack['order_sn'] ?></a><br>
                                                    状态: <?= [1=>'待查验',2=>'待支付',3=>'已支付',4=>'已拣货',5=>'已打包',6=>'已发货',7=>'已收货',8=>'已完成',-1=>'问题件'][$inpack['status']] ?? '未知' ?><br>
                                                    重量: <?= $inpack['weight'] ?> Kg<br>
                                                    费用: <?= $inpack['free'] ?><br>
                                                    支付状态: <?= $inpack['is_pay']==1?'已支付':'未支付' ?><br>
                                                    创建时间: <?= $inpack['created_time'] ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            暂无集运单
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if (!empty($participant['packages'])): ?>
                                            <table class="am-table am-table-compact am-table-bordered" style="margin: 0;">
                                                <thead>
                                                <tr>
                                                    <th>快递单号</th>
                                                    <th>重量</th>
                                                    <th>状态</th>
                                                    <th>备注</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($participant['packages'] as $package): ?>
                                                <tr>
                                                    <td><?= $package['express_num'] ?></td>
                                                    <td><?= $package['weight'] ?> Kg</td>
                                                    <td><?= [1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成',-1=>'问题件'][$package['status']] ?? '未知' ?></td>
                                                    <td><?= $package['remark'] ?: '-' ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <div style="margin-top: 5px;">
                                                <strong>包裹总数: <?= count($participant['packages']) ?></strong>
                                            </div>
                                        <?php else: ?>
                                            暂无包裹
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if (!empty($participant['inpacks'])): ?>
                                            <?php foreach ($participant['inpacks'] as $inpack): ?>
                                                <a href="<?= url('store/trOrder/package', ['id' => $inpack['id']]) ?>" class="am-btn am-btn-xs am-btn-primary" target="_blank">
                                                    查看包裹明细
                                                </a><br>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="am-text-center">暂无参与人员</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

