<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <div class="am-tabs am-margin-top" data-am-tabs="{noSwipe: 1}">
                            <div class="am-tabs-bd">
                                <div class="am-tab-panel am-active am-margin-top-lg" id="tab1">
                                    <div class="widget-head am-cf">
                                        <div class="widget-title am-fl">请将中文翻译成<?= $language ?></div>
                                    </div>
                                    <?php if (isset($lang)): foreach ($lang as $key =>$value): ?>
                                    <div class="am-form-group">
                                        
                                        <label class="am-u-sm-4 am-form-label form-require">
                                            <?= $key ?>
                                        </label>
                                        <div class="am-u-sm-8">
                                            <input type="text" class="tpl-form-input"
                                                   name="lang[<?= $key ?>]"
                                                   value="<?= $lang[$key]?$lang[$key]:'' ?>"
                                                   required>
                                        </div>
                                        
                                    </div>
                                
                                    <?php endforeach; endif; ?>

                                </div>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                </button>
                            </div>
                        </div>
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
