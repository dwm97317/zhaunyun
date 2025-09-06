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
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-12">
                                <div class="am fl">
                                    <div class="am-form-group am-fl">
                                        <?php $grade_id = $request->get('grade_id'); ?>
                                        <select name="grade_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '请选择会员等级'}">
                                            <option value=""></option>
                                            <?php if(isset($gradeList) && !empty($gradeList)): foreach ($gradeList as $grade): ?>
                                                <option value="<?= $grade['grade_id'] ?>"
                                                    <?= $grade_id == $grade['grade_id'] ? 'selected' : '' ?>><?= $grade['name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $is_expired = $request->get('is_expired'); ?>
                                        <select name="is_expired"
                                                data-am-selected="{btnSize: 'sm', placeholder: '请选择状态'}">
                                            <option value=""></option>
                                            <option value="0" <?= $is_expired === '0' ? 'selected' : '' ?>>未过期</option>
                                            <option value="1" <?= $is_expired === '1' ? 'selected' : '' ?>>已过期</option>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input style="padding:6px 5px;" autocomplete="off" type="text" name="start_date"
                                               class="am-form-field"
                                               value="<?= $request->get('start_date') ?>" placeholder="创建起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input style="padding:6px 5px;" autocomplete="off" type="text" name="end_date"
                                               class="am-form-field"
                                               value="<?= $request->get('end_date') ?>" placeholder="创建截止日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input style="padding:6px 5px;" autocomplete="off" type="text" name="effect_start_date"
                                               class="am-form-field"
                                               value="<?= $request->get('effect_start_date') ?>" placeholder="生效起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input style="padding:6px 5px;" autocomplete="off" type="text" name="effect_end_date"
                                               class="am-form-field"
                                               value="<?= $request->get('effect_end_date') ?>" placeholder="生效截止日期"
                                               data-am-datepicker>
                                    </div>
                                    <?php if($storesetting['usercode_mode']['is_show']==0): ?>
                                    <div class="am-form-group am-fl" style="padding:1px 0px;">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="user_id"
                                                   placeholder="请输入用户ID" value="<?= $request->get('user_id') ?>">
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="am-form-group am-fl" style="padding:1px 0px;">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="user_code"
                                                   placeholder="请输入用户编号" value="<?= $request->get('user_code') ?>">
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="nickName"
                                                   placeholder="请输入会员昵称" value="<?= $request->get('nickName') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
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
                                <th>到期时间</th>
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
                                        <span><?= $item['grade_time']>0?date("Y-m-d H:i:s",$item['grade_time']):'--' ?></span>
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

