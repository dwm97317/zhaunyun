<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">新增渠道公司</div>
                            </div>
                            <div class="am-form-group c" id="c1">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 承运商 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <select onchange="changeexpress(this)" id="deliveryitem" data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择承运商</option>
                                     <?php if (isset($track)):
                                            foreach ($track as $item): ?>
                                                <option value="<?= $item['key'] ?>"><?= $item['_name'] ?>-<?= $item['_name_zh-cn'] ?>-<?= $item['key'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                     <div class="help-block">
                                        <small>注：请选择对应的渠道公司，并根据使用习惯填写对应的公司名称</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道公司名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="ditch_name" type="text" class="tpl-form-input" name="ditch[ditch_name]" value="" required>
                                    <small>请对照 <a href="<?= url('setting.ditch/company') ?>" target="_blank">渠道公司编码表</a> 填写</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道公司代码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="ditch_no" type="text" class="tpl-form-input" name="ditch[ditch_no]" value="" required>
                                    <small>用于17track查询物流信息，务必填写正确，当在上面找不到时，可以不填。在下方的选择中选"无"</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 17track中是否有该渠道 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="ditch[type]" value="1" data-am-ucheck checked>
                                        有
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="ditch[type]" value="0" data-am-ucheck>
                                        无
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="ditch[ditch_type]" value="1" data-am-ucheck checked id="ditch_type_1">
                                        专线
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="ditch[ditch_type]" value="2" data-am-ucheck id="ditch_type_2">
                                        中通
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="ditch[ditch_type]" value="3" data-am-ucheck id="ditch_type_3">
                                        中通管家
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="ditch[ditch_type]" value="4" data-am-ucheck id="ditch_type_4">
                                        顺丰快递
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="ditch[ditch_type]" value="5" data-am-ucheck id="ditch_type_5">
                                        京东快递
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 渠道公司官网</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="website" type="text" class="tpl-form-input" name="ditch[website]" value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道公司API地址</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="api_url" type="text" class="tpl-form-input" name="ditch[api_url]" value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 渠道打印地址</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="print_url" type="text" class="tpl-form-input" name="ditch[print_url]" value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 客户号（Key）</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="app_key" type="text" class="tpl-form-input" name="ditch[app_key]" value="" required>
                                    <small>顺丰快递填写 <strong>partnerID</strong>，中通填写 AppKey</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 密钥（Token）</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="app_token" type="text" class="tpl-form-input" name="ditch[app_token]" value="" required>
                                    <small>顺丰快递填写 <strong>appSecret</strong>（用于生成msgDigest签名），中通填写 AppSecret</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="shop_key_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 店铺Key(ShopKey) </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="shop_key" type="text" class="tpl-form-input" name="ditch[shop_key]" value="" placeholder="中通管家接口必填">
                                    <small>对应接口文档中的 shopKey</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="customer_code_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 客户编号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="customer_code" type="text" class="tpl-form-input" name="ditch[customer_code]" value="" placeholder="中通集团客户编号 / 顺丰月结卡号">
                                    <small>中通集团客户填写客户编号，顺丰快递填写月结卡号</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="account_id_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 电子面单账号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="account_id" type="text" class="tpl-form-input" name="ditch[account_id]" value="" placeholder="如 KDGJ221000015957">
                                    <small>非集团电子面单时必填；与客户编号二选一</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="account_password_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 电子面单密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="account_password" type="text" class="tpl-form-input" name="ditch[account_password]" value="" placeholder="测试环境可填 ZTO123">
                                    <small>非集团电子面单时填写；测试环境默认 ZTO123</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="sf_express_type_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 快递产品类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select class="tpl-form-input" name="ditch[sf_express_type]" id="sf_express_type">
                                        <optgroup label="常用产品">
                                            <option value="1" selected>顺丰特快</option>
                                            <option value="2">顺丰标快</option>
                                            <option value="6">顺丰即日</option>
                                            <option value="202">顺丰微小件</option>
                                            <option value="247">电商标快</option>
                                            <option value="323">电商微小件</option>
                                        </optgroup>
                                        <optgroup label="时效产品">
                                            <option value="207">限时次日</option>
                                            <option value="263">顺丰半日达</option>
                                            <option value="235">预售当天达</option>
                                            <option value="253">前置当天达</option>
                                            <option value="303">专享即日</option>
                                        </optgroup>
                                        <optgroup label="电商产品">
                                            <option value="236">电商退货</option>
                                            <option value="261">O2O店配</option>
                                            <option value="262">前置标快</option>
                                            <option value="265">预售电标</option>
                                        </optgroup>
                                        <optgroup label="冷运生鲜">
                                            <option value="201">冷运标快</option>
                                            <option value="325">温控包裹</option>
                                            <option value="374">生鲜专递</option>
                                            <option value="381">大闸蟹专递</option>
                                        </optgroup>
                                        <optgroup label="国际产品">
                                            <option value="10">国际小包</option>
                                            <option value="23">顺丰国际特惠(文件)</option>
                                            <option value="24">顺丰国际特惠(包裹)</option>
                                            <option value="99">顺丰国际标快(文件)</option>
                                            <option value="100">顺丰国际标快(包裹)</option>
                                            <option value="308">国际特快(文件)</option>
                                            <option value="310">国际特快(包裹)</option>
                                            <option value="29">国际电商专递-标准</option>
                                            <option value="218">国际电商专递-CD</option>
                                            <option value="241">国际电商专递-快速</option>
                                        </optgroup>
                                        <optgroup label="重货大件">
                                            <option value="111">顺丰干配</option>
                                            <option value="215">大票直送</option>
                                            <option value="231">陆运包裹</option>
                                            <option value="255">顺丰卡航</option>
                                            <option value="266">顺丰空配(新)</option>
                                            <option value="293">特快包裹</option>
                                            <option value="299">标快零担</option>
                                        </optgroup>
                                        <optgroup label="其他产品">
                                            <option value="59">E顺递</option>
                                            <option value="244">店到店</option>
                                            <option value="245">店到门</option>
                                            <option value="246">门到店</option>
                                            <option value="252">即时城配</option>
                                            <option value="259">极速配</option>
                                            <option value="407">帮我送</option>
                                        </optgroup>
                                    </select>
                                    <small>不同产品类型时效和价格不同，请根据业务需求选择。常用产品：特快、标快、电商标快</small>
                                </div>
                            </div>
                            <div class="am-form-group" id="jd_product_code_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 京东主产品 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select class="tpl-form-input" name="ditch[product_json]" id="jd_product_code">
                                        <option value="ed-m-0001">京东标快 (ed-m-0001) - 推荐</option>
                                        <option value="ed-m-0002">京东特快 (ed-m-0002)</option>
                                        <option value="ed-m-0012">特惠包裹 (ed-m-0012)</option>
                                        <option value="ed-m-0059">电商标快 (ed-m-0059)</option>
                                        <option value="ed-m-0019">电商特惠 (ed-m-0019)</option>
                                        <option value="fr-m-0004">特快重货 (fr-m-0004)</option>
                                        <option value="fr-m-0017">特惠专配 (fr-m-0017)</option>
                                        <option value="LL-HD-M">生鲜标快 (LL-HD-M)</option>
                                        <option value="LL-SD-M">生鲜特快 (LL-SD-M)</option>
                                        <option value="ed-m-0005">同城急送 (ed-m-0005)</option>
                                    </select>
                                    <small>请选择京东物流主产品类型。标快/特快适用于大部分B2C/C2C场景。</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否启用 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="ditch[status]" value="1" data-am-ucheck checked>
                                        不启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="ditch[status]" value="0" data-am-ucheck>
                                        启用
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="ditch[sort]" value="100"
                                           required>
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
        var v = $('input[name="ditch[ditch_type]"]:checked').val();
        var $g = $('#customer_code_group');
        var $g2 = $('#account_id_group');
        var $g3 = $('#account_password_group');
        var $g4 = $('#shop_key_group');
        var $g5 = $('#sf_express_type_group');
        var $g6 = $('#jd_product_code_group');
        var $i = $('#customer_code');
        
        // Reset requirements and labels
        $i.prop('required', false);
        $('input[name="ditch[app_key]"]').next('small').html('顺丰填写 partnerID，中通填写 AppKey');
        $('input[name="ditch[app_token]"]').next('small').html('顺丰填写 appSecret，中通填写 AppSecret');
        $('input[name="ditch[print_url]"]').parent().prev('label').text('渠道打印地址');
        
        // Hide all optional groups first
        $g.hide();
        $g2.hide();
        $g3.hide();
        $g4.hide();
        $g5.hide();
        $g6.hide();

        if (v == '2') { // 中通
            $g.show();
            $g2.show();
            $g3.show();
        } else if (v == '3') { // 中通管家
            $g4.show();
        } else if (v == '4') { // 顺丰快递
            $g.show(); // 显示客户编号（月结卡号）
            $g5.show(); // 显示快递产品类型选择器
        } else if (v == '5') { // 京东快递
            $g.show(); // 显示客户编号 (CustomerCode)
            $g6.show(); // 显示京东产品选择器
            
            // Update Labels for JD
            $('input[name="ditch[app_key]"]').next('small').html('填写京东 <strong>AppKey</strong>');
            $('input[name="ditch[app_token]"]').next('small').html('填写京东 <strong>AppSecret</strong>');
            $('input[name="ditch[customer_code]"]').next('small').html('填写京东 <strong>CustomerCode (商家编码)</strong>');
            $('input[name="ditch[print_url]"]').parent().prev('label').text('京东 AccessToken');
        }
        
    }

    $(function () {
        $('input[name="ditch[ditch_type]"]').on('change', toggleCustomerCode);
        toggleCustomerCode();

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
