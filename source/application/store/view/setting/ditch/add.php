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
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 密钥（Token）</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="app_token" type="text" class="tpl-form-input" name="ditch[app_token]" value="" required>
                                </div>
                            </div>
                            <div class="am-form-group" id="customer_code_group" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 客户编号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="customer_code" type="text" class="tpl-form-input" name="ditch[customer_code]" value="" placeholder="中通渠道时填写（集团客户）">
                                    <small>集团客户时填写，与电子面单二选一</small>
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
        var $i = $('#customer_code');
        if (v == '2') {
            $g.show();
            $g2.show();
            $g3.show();
            $i.prop('required', false);
        } else {
            $g.hide();
            $g2.hide();
            $g3.hide();
            $i.prop('required', false).val('');
            $('#account_id').val('');
            $('#account_password').val('');
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
