<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑渠道公司</div>
                            </div>
                            <div class="am-form-group c" id="c1">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 承运商 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <select onchange="changeexpress(this)"  id="deliveryitem" data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择承运商</option>
                                     <?php if (isset($track)):
                                            foreach ($track as $item): ?>
                                                <option value="<?= $item['key'] ?>"><?= $item['_name'] ?>-<?= $item['_name_zh-cn'] ?>-<?= $item['key'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                     <div class="help-block">
                                        <small>注：请选择对应的渠道公司，并根据使用习惯填写对应的渠道公司名称</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道公司名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[ditch_name]"
                                           value="<?= $model['ditch_name'] ?>" required>
                                    <small>请对照 <a href="<?= url('setting.ditch/company') ?>" target="_blank">渠道公司编码表</a> 填写</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道公司代码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="ditch_no" type="text" class="tpl-form-input" name="express[ditch_no]"
                                           value="<?= $model['ditch_no'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 17track中是否有该渠道 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[type]" value="0" data-am-ucheck <?= $model['type'] == 0 ?'checked' : '' ?>>
                                        有
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[type]" value="1" data-am-ucheck <?= $model['type'] == 1 ? 'checked' : '' ?>>
                                        无
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php $dt = isset($model['ditch_type']) ? (int)$model['ditch_type'] : 1; ?>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[ditch_type]" value="1" data-am-ucheck <?= $dt == 1 ? 'checked' : '' ?> id="ditch_type_1">
                                        专线
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[ditch_type]" value="2" data-am-ucheck <?= $dt == 2 ? 'checked' : '' ?> id="ditch_type_2">
                                        中通
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[ditch_type]" value="3" data-am-ucheck <?= $dt == 3 ? 'checked' : '' ?> id="ditch_type_3">
                                        中通管家
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[ditch_type]" value="4" data-am-ucheck <?= $dt == 4 ? 'checked' : '' ?> id="ditch_type_4">
                                        顺丰快递
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道公司官网 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="website" type="text" class="tpl-form-input" name="express[website]"
                                           value="<?= $model['website'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道公司API地址 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[api_url]"
                                           value="<?= $model['api_url'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道打印地址 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[print_url]"
                                           value="<?= $model['print_url'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 客户号（Key） </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[app_key]"
                                           value="<?= $model['app_key'] ?>" required>
                                    <small>顺丰快递填写 <strong>partnerID</strong>，中通填写 AppKey</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 密钥（Token） </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[app_token]"
                                           value="<?= $model['app_token'] ?>" required>
                                    <small>顺丰快递填写 <strong>appSecret</strong>（用于生成msgDigest签名），中通填写 AppSecret</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="shop_key_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 店铺Key(ShopKey) </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="shop_key" type="text" class="tpl-form-input" name="express[shop_key]"
                                           value="<?= isset($model['shop_key']) ? htmlspecialchars($model['shop_key']) : '' ?>" placeholder="中通管家接口必填">
                                    <small>对应接口文档中的 shopKey</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="customer_code_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 客户编号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="customer_code" type="text" class="tpl-form-input" name="express[customer_code]"
                                           value="<?= isset($model['customer_code']) ? htmlspecialchars($model['customer_code']) : '' ?>" placeholder="中通集团客户编号 / 顺丰月结卡号">
                                    <small>中通集团客户填写客户编号，顺丰快递填写月结卡号</small>>
                                </div>
                            </div>
                            <div class="am-form-group" id="account_id_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 电子面单账号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="account_id" type="text" class="tpl-form-input" name="express[account_id]"
                                           value="<?= isset($model['account_id']) ? htmlspecialchars($model['account_id']) : '' ?>" placeholder="如 KDGJ221000015957">
                                    <small>非集团电子面单时必填；与客户编号二选一</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="account_password_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 电子面单密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="account_password" type="text" class="tpl-form-input" name="express[account_password]"
                                           value="<?= isset($model['account_password']) ? htmlspecialchars($model['account_password']) : '' ?>" placeholder="测试环境可填 ZTO123">
                                    <small>非集团电子面单时填写；测试环境默认 ZTO123</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="sf_express_type_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 快递产品类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php $sfExpressType = isset($model['sf_express_type']) ? (int)$model['sf_express_type'] : 1; ?>
                                    <select class="tpl-form-input" name="express[sf_express_type]" id="sf_express_type">
                                        <optgroup label="常用产品">
                                            <option value="1" <?= $sfExpressType == 1 ? 'selected' : '' ?>>顺丰特快</option>
                                            <option value="2" <?= $sfExpressType == 2 ? 'selected' : '' ?>>顺丰标快</option>
                                            <option value="6" <?= $sfExpressType == 6 ? 'selected' : '' ?>>顺丰即日</option>
                                            <option value="202" <?= $sfExpressType == 202 ? 'selected' : '' ?>>顺丰微小件</option>
                                            <option value="247" <?= $sfExpressType == 247 ? 'selected' : '' ?>>电商标快</option>
                                            <option value="323" <?= $sfExpressType == 323 ? 'selected' : '' ?>>电商微小件</option>
                                        </optgroup>
                                        <optgroup label="时效产品">
                                            <option value="207" <?= $sfExpressType == 207 ? 'selected' : '' ?>>限时次日</option>
                                            <option value="263" <?= $sfExpressType == 263 ? 'selected' : '' ?>>顺丰半日达</option>
                                            <option value="235" <?= $sfExpressType == 235 ? 'selected' : '' ?>>预售当天达</option>
                                            <option value="253" <?= $sfExpressType == 253 ? 'selected' : '' ?>>前置当天达</option>
                                            <option value="303" <?= $sfExpressType == 303 ? 'selected' : '' ?>>专享即日</option>
                                        </optgroup>
                                        <optgroup label="电商产品">
                                            <option value="236" <?= $sfExpressType == 236 ? 'selected' : '' ?>>电商退货</option>
                                            <option value="261" <?= $sfExpressType == 261 ? 'selected' : '' ?>>O2O店配</option>
                                            <option value="262" <?= $sfExpressType == 262 ? 'selected' : '' ?>>前置标快</option>
                                            <option value="265" <?= $sfExpressType == 265 ? 'selected' : '' ?>>预售电标</option>
                                        </optgroup>
                                        <optgroup label="冷运生鲜">
                                            <option value="201" <?= $sfExpressType == 201 ? 'selected' : '' ?>>冷运标快</option>
                                            <option value="325" <?= $sfExpressType == 325 ? 'selected' : '' ?>>温控包裹</option>
                                            <option value="374" <?= $sfExpressType == 374 ? 'selected' : '' ?>>生鲜专递</option>
                                            <option value="381" <?= $sfExpressType == 381 ? 'selected' : '' ?>>大闸蟹专递</option>
                                        </optgroup>
                                        <optgroup label="国际产品">
                                            <option value="10" <?= $sfExpressType == 10 ? 'selected' : '' ?>>国际小包</option>
                                            <option value="23" <?= $sfExpressType == 23 ? 'selected' : '' ?>>顺丰国际特惠(文件)</option>
                                            <option value="24" <?= $sfExpressType == 24 ? 'selected' : '' ?>>顺丰国际特惠(包裹)</option>
                                            <option value="99" <?= $sfExpressType == 99 ? 'selected' : '' ?>>顺丰国际标快(文件)</option>
                                            <option value="100" <?= $sfExpressType == 100 ? 'selected' : '' ?>>顺丰国际标快(包裹)</option>
                                            <option value="308" <?= $sfExpressType == 308 ? 'selected' : '' ?>>国际特快(文件)</option>
                                            <option value="310" <?= $sfExpressType == 310 ? 'selected' : '' ?>>国际特快(包裹)</option>
                                            <option value="29" <?= $sfExpressType == 29 ? 'selected' : '' ?>>国际电商专递-标准</option>
                                            <option value="218" <?= $sfExpressType == 218 ? 'selected' : '' ?>>国际电商专递-CD</option>
                                            <option value="241" <?= $sfExpressType == 241 ? 'selected' : '' ?>>国际电商专递-快速</option>
                                        </optgroup>
                                        <optgroup label="重货大件">
                                            <option value="111" <?= $sfExpressType == 111 ? 'selected' : '' ?>>顺丰干配</option>
                                            <option value="215" <?= $sfExpressType == 215 ? 'selected' : '' ?>>大票直送</option>
                                            <option value="231" <?= $sfExpressType == 231 ? 'selected' : '' ?>>陆运包裹</option>
                                            <option value="255" <?= $sfExpressType == 255 ? 'selected' : '' ?>>顺丰卡航</option>
                                            <option value="266" <?= $sfExpressType == 266 ? 'selected' : '' ?>>顺丰空配(新)</option>
                                            <option value="293" <?= $sfExpressType == 293 ? 'selected' : '' ?>>特快包裹</option>
                                            <option value="299" <?= $sfExpressType == 299 ? 'selected' : '' ?>>标快零担</option>
                                        </optgroup>
                                        <optgroup label="其他产品">
                                            <option value="59" <?= $sfExpressType == 59 ? 'selected' : '' ?>>E顺递</option>
                                            <option value="244" <?= $sfExpressType == 244 ? 'selected' : '' ?>>店到店</option>
                                            <option value="245" <?= $sfExpressType == 245 ? 'selected' : '' ?>>店到门</option>
                                            <option value="246" <?= $sfExpressType == 246 ? 'selected' : '' ?>>门到店</option>
                                            <option value="252" <?= $sfExpressType == 252 ? 'selected' : '' ?>>即时城配</option>
                                            <option value="259" <?= $sfExpressType == 259 ? 'selected' : '' ?>>极速配</option>
                                            <option value="407" <?= $sfExpressType == 407 ? 'selected' : '' ?>>帮我送</option>
                                        </optgroup>
                                    </select>
                                    <small>不同产品类型时效和价格不同，请根据业务需求选择。常用产品：特快、标快、电商标快</small>
                                </div>
                            </div>
                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否启用 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[status]" value="0" data-am-ucheck <?= $model['status'] == 0 ?'checked' : '' ?>>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[status]" value="1" data-am-ucheck <?= $model['status'] == 1 ? 'checked' : '' ?>>
                                        不启用
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="express[sort]"
                                           value="<?= $model['sort'] ?>" required>
                                    <small>数字越小越靠前</small>
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
    function changeexpress(_this){
      var a =  _this.options[_this.selectedIndex].value;
      $("#ditch_no").val(a);
    }

    function toggleCustomerCode() {
        var v = $('input[name="express[ditch_type]"]:checked').val();
        var $g = $('#customer_code_group');
        var $g2 = $('#account_id_group');
        var $g3 = $('#account_password_group');
        var $g4 = $('#shop_key_group');
        var $g5 = $('#sf_express_type_group');
        var $i = $('#customer_code');
        
        // Reset requirements
        $i.prop('required', false);
        
        // Hide all optional groups first
        $g.hide();
        $g2.hide();
        $g3.hide();
        $g4.hide();
        $g5.hide();

        if (v == '2') { // 中通
            $g.show();
            $g2.show();
            $g3.show();
        } else if (v == '3') { // 中通管家
            $g4.show();
        } else if (v == '4') { // 顺丰快递
            $g.show(); // 显示客户编号（月结卡号）
            $g5.show(); // 显示快递产品类型选择器
        }
    }

    $(function () {
        $('input[name="express[ditch_type]"]').on('change', toggleCustomerCode);
        toggleCustomerCode();

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
