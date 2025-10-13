<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">汇款账号编辑</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">账号类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank[bank_type]" value="0" data-am-ucheck
                                               checked>
                                        银行账户
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank[bank_type]" value="1" data-am-ucheck>
                                        收款码
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group bank-info-fields">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">开户行 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="bank[bank_name]"
                                           placeholder="请输入开户行" value="">
                                </div>
                            </div>  
                            <div class="am-form-group am-padding-top bank-info-fields">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 开户支行 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="bank[child_bank_name]"
                                           placeholder="请输入开户支行" value="">
                                </div>
                            </div>
                            <div class="am-form-group bank-info-fields">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 银行卡号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="bank[bank_card]"
                                           placeholder="请输入银行卡号" value="">
                                </div>
                            </div>
                            <div class="am-form-group bank-info-fields">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 银行行号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="bank[bank_no]"
                                           placeholder="请输入银行行号" value="">
                                </div>
                            </div>
                            <div class="am-form-group bank-info-fields">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 开户人 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="bank[open_name]"
                                           placeholder="请输入开户人" value="">
                                    
                                </div>
                            </div>
                            <div class="am-form-group qrcode-upload-field" style="display: none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">收款码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank[status]" value="1" data-am-ucheck
                                               checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank[status]" value="0" data-am-ucheck>
                                        禁用
                                    </label>
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


<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/store/js/select.region.js?v=1.2"></script>
<!-- 图片文件列表模板 -->
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_id }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

<script>

    $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'bank[image_id]'
        });

        /**
         * 根据账号类型切换显示字段
         */
        function toggleBankFields() {
            var bankType = $('input[name="bank[bank_type]"]:checked').val();
            
            if (bankType == '0') {
                // 银行账户：显示银行信息，隐藏收款码
                $('.bank-info-fields').show();
                $('.qrcode-upload-field').hide();
            } else if (bankType == '1') {
                // 收款码：隐藏银行信息，显示收款码
                $('.bank-info-fields').hide();
                $('.qrcode-upload-field').show();
            }
        }
        
        // 页面加载时执行一次
        toggleBankFields();
        
        // 监听账号类型变化
        $('input[name="bank[bank_type]"]').on('change', function() {
            toggleBankFields();
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
