<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">预报功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    单个快递单号预报
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_single]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_single'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_single]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_single'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    多个快递单号预报
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_more]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_more'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_more]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_more'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报国家
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_country]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_country'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_country]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_country'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_country_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_country_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报仓库
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_shop]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_shop'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_shop]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_shop'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_shop_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_shop_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报快递公司
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_expressname]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_expressname'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_expressname]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_expressname'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_expressname_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_expressname_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报类目
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_category]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_category'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_category]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_category'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_category_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_category_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写物品价值
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_price]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_price'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_price]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_price'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_price_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_price_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写备注
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_remark]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_remark'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_remark]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_remark'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_remark_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_remark_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要上传图片
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_images]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_images'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_images]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_images'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_images_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_images_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要确认阅读协议
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_xieyi]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_xieyi'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_xieyi]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_xieyi'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_xieyi_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_xieyi_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写物品信息
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_goodslist]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_goodslist'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_goodslist]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_goodslist'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_goodslist_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_goodslist_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户资料功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    自定义身份证别名
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="userclient[userinfo][identification_card]"
                                           value="<?= $values['userinfo']['identification_card'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    自定义身份证照片别名
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="userclient[userinfo][identification_card_image]"
                                           value="<?= $values['userinfo']['identification_card_image'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要上传身份证图片
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_identification_card]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_identification_card'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_identification_card]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_identification_card'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_identification_card_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_identification_card_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写生日
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_birthday]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_birthday'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_birthday]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_birthday'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_birthday_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_birthday_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写微信号
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_wechat]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_wechat'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_wechat]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_wechat'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_wechat_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_wechat_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写邮箱
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_email]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_email'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_email]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_email'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_email_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_email_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写手机号
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_mobile]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_mobile'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_mobile]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_mobile'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_mobile_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_mobile_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户打包功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否在提交打包前需要完善用户资料
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_force]" value="1"
                                               data-am-ucheck  <?= $values['packit']['is_force'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_force]" value="0"
                                               data-am-ucheck <?= $values['packit']['is_force'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>必填项请在上方【用户资料功能设置】中设置</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否在提交打包前可以填写代收款（代收货款）
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_waitreceivedmoney]" value="1"
                                               data-am-ucheck  <?= $values['packit']['is_waitreceivedmoney'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_waitreceivedmoney]" value="0"
                                               data-am-ucheck <?= $values['packit']['is_waitreceivedmoney'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>必填项请在上方【用户资料功能设置】中设置</small>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户登录功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    在小程序或公众号中是否开启账号密码登录方式
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_passwordlogin]" value="1"
                                               data-am-ucheck  <?= $values['loginsetting']['is_passwordlogin'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_passwordlogin]" value="0"
                                               data-am-ucheck <?= $values['loginsetting']['is_passwordlogin'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>必填项请在上方【用户资料功能设置】中设置</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    H5端登录是否展示手机号前缀
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_phone]" value="1"
                                               data-am-ucheck  <?= $values['loginsetting']['is_phone'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_phone]" value="0"
                                               data-am-ucheck <?= $values['loginsetting']['is_phone'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>关闭后，H5用户端则不需要选择手机号前缀，如果开启国际短信，请选择开启</small>
                                    </div>
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
