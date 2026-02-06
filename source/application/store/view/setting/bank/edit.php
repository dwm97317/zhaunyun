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
                                               <?= $model['bank_type'] == 0 ? 'checked' : '' ?>>
                                        银行账户
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank[bank_type]" value="1" data-am-ucheck
                                        <?= $model['bank_type'] == 1 ? 'checked' : '' ?>>
                                        收款码
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank[bank_type]" value="2" data-am-ucheck
                                        <?= $model['bank_type'] == 2 ? 'checked' : '' ?>>
                                        淘口令
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group bank-info-fields">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">开户行 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="bank[bank_name]"
                                           placeholder="请输入开户行" value="<?= $model['bank_type'] == 2 ? ($model['bank_name'] ?: '淘口令') : $model['bank_name'] ?>">
                                </div>
                            </div>  
                            <div class="am-form-group am-padding-top bank-info-fields">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 开户支行 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="bank[child_bank_name]"
                                           placeholder="请输入开户支行" value="<?= $model['child_bank_name'] ?>">
                                </div>
                            </div>
                            <div class="am-form-group bank-info-fields">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 银行卡号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="bank[bank_card]"
                                           placeholder="请输入银行卡号" value="<?= $model['bank_card'] ?>">
                                </div>
                            </div>
                            <div class="am-form-group bank-info-fields">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 银行行号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="bank[bank_no]"
                                           placeholder="请输入银行行号" value="<?= $model['bank_no'] ?>">
                                </div>
                            </div>
                            <div class="am-form-group bank-info-fields">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 开户人 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="bank[open_name]"
                                           placeholder="请输入开户人" value="<?= $model['open_name'] ?>">
                                    
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
                                                <div class="file-item">
                                                    <a href="<?= isset($model['image'])?$model['image']['file_path']:"##" ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= isset($model['image'])?$model['image']['file_path']:'' ?>">
                                                    </a>
                                                    <input type="hidden" name="bank[image_id]"
                                                           value="<?= $model['image_id'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group taocode-field" style="display: none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">淘口令内容 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <textarea class="tpl-form-input" name="bank[taocode]" rows="4"
                                              placeholder="请粘贴完整的淘口令内容"><?= isset($model['taocode']) ? $model['taocode'] : '' ?></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank[status]" value="1" data-am-ucheck
                                               <?= $model['status'] == 1 ? 'checked' : '' ?>>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank[status]" value="0" data-am-ucheck 
                                        <?= $model['status'] == 0 ? 'checked' : '' ?>>
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
<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/store/js/select.region.js?v=1.2"></script>
<style>
    .taocode-field textarea {
        word-break: break-all;
        white-space: normal;
    }
</style>

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
            var $bankNameInput = $('input[name="bank[bank_name]"]');
            var $taocodeTextarea = $('textarea[name="bank[taocode]"]');
            
            if (bankType == '0') {
                // 银行账户：显示银行信息，隐藏收款码
                $('.bank-info-fields').show();
                $('.qrcode-upload-field').hide();
                $('.taocode-field').hide();
                var backupBankName = $bankNameInput.data('backup-bank-name');
                if (typeof backupBankName !== 'undefined') {
                    $bankNameInput.val(backupBankName);
                    $bankNameInput.removeData('backup-bank-name');
                }
            } else if (bankType == '1') {
                // 收款码：隐藏银行信息，显示收款码
                $('.bank-info-fields').hide();
                $('.qrcode-upload-field').show();
                $('.taocode-field').hide();
                var backupBankName = $bankNameInput.data('backup-bank-name');
                if (typeof backupBankName !== 'undefined') {
                    $bankNameInput.val(backupBankName);
                    $bankNameInput.removeData('backup-bank-name');
                }
            } else if (bankType == '2') {
                // 淘口令：隐藏银行信息和收款码，显示淘口令字段
                if (typeof $bankNameInput.data('backup-bank-name') === 'undefined') {
                    $bankNameInput.data('backup-bank-name', $bankNameInput.val());
                }
                $bankNameInput.val('淘口令');
                if (!$taocodeTextarea.data('decoded')) {
                    var decodedValue = $('<textarea/>').html($taocodeTextarea.val()).text();
                    $taocodeTextarea.val(decodedValue);
                    $taocodeTextarea.data('decoded', true);
                }
                $('.bank-info-fields').hide();
                $('.qrcode-upload-field').hide();
                $('.taocode-field').show();
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
