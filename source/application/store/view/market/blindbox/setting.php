<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">盲盒计划设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否开启盲盒计划 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[is_open]" value="1" data-am-ucheck
                                            <?= $values['is_open'] ? 'checked' : '' ?>> 开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[is_open]" value="0" data-am-ucheck
                                            <?= $values['is_open'] ? '' : 'checked' ?>> 关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否开启盲盒分享墙 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[is_wall_open]" value="1" data-am-ucheck
                                            <?= $values['is_wall_open'] ? 'checked' : '' ?>> 开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[is_wall_open]" value="0" data-am-ucheck
                                            <?= $values['is_wall_open'] ? '' : 'checked' ?>> 关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 盲盒计划别名 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="blindbox[blindbox_name]"
                                           value="<?= $values['blindbox_name'] ?>" required>
                                    <div class="help-block">
                                        <small>注：修改盲盒计划名称后，在买家端的所有页面里，看到的都是自定义的名称</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 盲盒计划分享描述 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="blindbox[blindbox_desc]"
                                           value="<?= $values['blindbox_desc'] ?>" required>
                                    <div class="help-block">
                                        <small>注：盲盒计划分享描述文案，在用户分享到微信时能看到</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 抽盲盒按钮别名 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="blindbox[button_cj]"
                                           value="<?= $values['button_cj'] ?>" required>
                                    <div class="help-block">
                                        <small>注：自定义“抽盲盒”按钮的别名</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 我的盲盒按钮别名 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="blindbox[button_my]"
                                           value="<?= $values['button_my'] ?>" required>
                                    <div class="help-block">
                                        <small>注：自定义“我的盲盒”按钮的别名</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label form-require"> 盲盒计划说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <textarea rows="5" name="blindbox[describe]"
                                              placeholder="请输入盲盒计划说明/规则"><?= $values['describe'] ?></textarea>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">下单赠送</div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label form-require"> 下单是否赠送抽盲盒次数 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[is_logistics_gift]" value="1" data-am-ucheck
                                            <?= $values['is_logistics_gift'] ? 'checked' : '' ?>> 开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[is_logistics_gift]" value="0" data-am-ucheck
                                            <?= $values['is_logistics_gift'] ? '' : 'checked' ?>> 关闭
                                    </label>
                                    <div class="help-block">
                                        <small>注：如开启则用户集运订单支付完成后赠送用户抽盲盒次数</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 下单赠送次数 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <div class="am-input-group">
                                        <input type="number" name="blindbox[logistics_gift_ratio]"
                                               class="am-form-field" min="0"
                                               value="<?= $values['logistics_gift_ratio'] ?>" required>
                                    </div>
                                    <div class="help-block">
                                        <small>注：赠送次数默认1次，请勿添加离谱的次数。一般建议设定1次，最多不要超过3次</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">分享墙赠送</div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label form-require"> 发布到分享墙赠送抽盲盒次数 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[is_wall_gift]" value="1" data-am-ucheck
                                            <?= $values['is_wall_gift'] ? 'checked' : '' ?>> 开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[is_wall_gift]" value="0" data-am-ucheck
                                            <?= $values['is_wall_gift'] ? '' : 'checked' ?>> 关闭
                                    </label>
                                    <div class="help-block">
                                        <small>注：如开启则用户发布到盲盒分享墙后赠送用户抽盲盒次数</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 分享墙赠送次数 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <div class="am-input-group">
                                        <input type="number" name="blindbox[wall_gift_ratio]"
                                               class="am-form-field" min="0"
                                               value="<?= $values['wall_gift_ratio'] ?>" required>
                                    </div>
                                    <div class="help-block">
                                        <small>注：赠送次数默认1次，请勿添加离谱的次数。一般建议设定1次，最多不要超过3次</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">拉新赠送</div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label form-require"> 拉新人赠送抽盲盒次数 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[is_shopping_gift]" value="1" data-am-ucheck
                                            <?= $values['is_shopping_gift'] ? 'checked' : '' ?>> 开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[is_shopping_gift]" value="0" data-am-ucheck
                                            <?= $values['is_shopping_gift'] ? '' : 'checked' ?>> 关闭
                                    </label>
                                    <div class="help-block">
                                        <small>注：如开启则用户拉取到新人并完成注册后赠送用户盲盒抽奖次数</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 每拉新用户 </label>
                                <div class="am-u-sm-3 am-u-md-3 am-u-lg-1 am-u-end">
                                    <div class="am-input-group">
                                        <input type="number" name="blindbox[newuser_num_ratio]"
                                               class="am-form-field" min="0"
                                               value="<?= $values['newuser_num_ratio'] ?>" required>
                                    </div>
                                </div>
                                <label class="am-u-sm-2 am-u-lg-1 am-form-label"> 人，则赠送抽盲盒 </label>
                                <div class="am-u-sm-3 am-u-md-3 am-u-lg-1 am-u-end">
                                    <div class="am-input-group">
                                        <input type="number" name="blindbox[newuser_gift_ratio]"
                                               class="am-form-field" min="0"
                                               value="<?= $values['newuser_gift_ratio'] ?>" required>
                                    </div>
                                </div>
                                <span class='am-form-label'>次</span>
                            </div>
                            
                            
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
