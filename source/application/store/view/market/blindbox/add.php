<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加盲盒</div>
                            </div>
                            <div class="am-form-group" data-x-switch>
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">盲盒类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[type]" value="10" checked
                                               data-am-ucheck
                                               data-switch-box="switch-coupon_type"
                                               data-switch-item="coupon_type__10">
                                        优惠券
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[type]" value="20"
                                               data-am-ucheck
                                               data-switch-box="switch-coupon_type"
                                               data-switch-item="coupon_type__20">
                                        实物商品
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group switch-coupon_type coupon_type__20 hide">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">盲盒名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="blindbox[goods_name]"
                                           value="" placeholder="请输入盲盒名称" required>
                                    <small>例如：老干妈</small>
                                </div>
                            </div>
                            <div class="am-form-group switch-coupon_type coupon_type__20 hide">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">盲盒价值 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number"  class="tpl-form-input"
                                           name="blindbox[goods_price]"
                                           value="" placeholder="请输入物品的销售价" required>
                                    <small>填写物品的销售价</small>
                                </div>
                            </div>
                            <div class="am-form-group switch-coupon_type coupon_type__20 hide">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">盲盒描述 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="blindbox[goods_desc]"
                                           value="" placeholder="请输入盲盒描述" required>
                                    <small>例如：盲盒名称是老干妈，一种也让人喜爱的美食</small>
                                </div>
                            </div>
                            
                            <div class="am-form-group switch-coupon_type coupon_type__10">
                                <label class="am-u-sm-3 am-u-lg-2  am-form-label form-require"> 优惠券 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="blindbox[coupon_id]"$couponlist
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                        <?php if (!$couponlist->isEmpty()): foreach ($couponlist as $item): ?>
                                            <option value="<?= $item['coupon_id']?>" ><?= $item['name']?></option>
                                        <?php endforeach;endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>请选择系统上可用的优惠券</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">抽中概率 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" max="100" class="tpl-form-input" name="blindbox[probability]"
                                           value="" placeholder="请输入抽中概率" required>
                                    <small>例如：请从1-100之间填写一个数字，系统会根据参与的所有商品的总数字之和计算当前盲盒的抽中概率，如不想被抽中，请设置为0</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否参与抽盲盒 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[status]" value="0" checked
                                               data-am-ucheck>
                                        参与
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="blindbox[status]" value="1"
                                               data-am-ucheck>
                                        不参与
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">库存 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="blindbox[goods_inventory]" value="0"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="blindbox[sort]" value="100"
                                           required>
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
        // swith切换
        var $mySwitch = $('[data-x-switch]');
        $mySwitch.find('[data-switch-item]').click(function () {
            var $mySwitchBox = $('.' + $(this).data('switch-box'));
            $mySwitchBox.hide().filter('.' + $(this).data('switch-item')).show();
        });
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
