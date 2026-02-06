<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">系统设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 被关注回复 </label>
                                <div class="am-u-sm-9">
                                    <textarea  rows="8" class="tpl-form-input" name="wechat[subscribe]"
                                           value="<?= $values['subscribe'] ?>"><?= $values['subscribe'] ?></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 收到消息回复 </label>
                                <div class="am-u-sm-9">
                                    <textarea class="tpl-form-input" name="wechat[autoreply]"
                                           value=""><?= $values['autoreply'] ?></textarea>
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
