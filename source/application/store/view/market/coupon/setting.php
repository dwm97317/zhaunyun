<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">优惠券设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label form-require"> 新用户注册发放 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="coupon[is_register]" value="1" data-am-ucheck
                                            <?= $values['is_register'] ? 'checked' : '' ?>> 发放
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="coupon[is_register]" value="0" data-am-ucheck
                                            <?= $values['is_register'] ? '' : 'checked' ?>> 不发放
                                    </label>
                                    <div class="help-block">
                                        <small>如设置发放，则新用户注册成功后会自动获得一张优惠券</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">
                                    新用户注册发放优惠券
                                </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select id="selectize-tags-1" name="coupon[register_coupon]"  class="tag-gradient-success">
                                        <?php if ( isset($list)) : foreach ($list as $key =>$item): ?>
                                            <option value="<?= $item['coupon_id'] ?>"  <?= $item['coupon_id'] == $values['register_coupon']?"selected":'' ?>><?= $item['name'] ?></option>
                                        <?php endforeach; endif; ?>

                                    </select>
                                    <small>注：请选择新用户注册时需要赠送的优惠券</small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label form-require"> 下单并评价完成后发放 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="coupon[is_order]" value="1" data-am-ucheck
                                            <?= $values['is_order'] ? 'checked' : '' ?>> 发放
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="coupon[is_order]" value="0" data-am-ucheck
                                            <?= $values['is_order'] ? '' : 'checked' ?>> 不发放
                                    </label>
                                    <div class="help-block">
                                        <small>用户下单并在收货后进行订单评价后获得</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">
                                    下单并评价完成后发放优惠券
                                </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select id="selectize-tags-1" name="coupon[order_coupon]"  class="tag-gradient-success">
                                        <?php if ( isset($list)) : foreach ($list as $key =>$item): ?>
                                            <option value="<?= $item['coupon_id'] ?>"  <?= $item['coupon_id'] == $values['order_coupon']?"selected":'' ?>><?= $item['name'] ?></option>
                                        <?php endforeach; endif; ?>

                                    </select>
                                    <small>注：请选择新用户注册时需要赠送的优惠券</small>
                                </div>
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
