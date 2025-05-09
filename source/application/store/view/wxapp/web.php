<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">网页端设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    官网入口
                                </label>
                                <div class="am-u-sm-6 am-u-end" style="display:flex;">
                                    <input type="text" id="url" class="tpl-form-input" name="h5['enter']" value="<?= $model['other_url']; ?>home" readonly required> 
                                    <button style="margin-left:10px;" type="button" class="am-btn am-btn-secondary"><span onclick="toUrl()">访问URL</span></button>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    查询物流URL
                                </label>
                                <div class="am-u-sm-6 am-u-end" style="display:flex;">
                                    <input type="text" id="logurl" class="tpl-form-input" value="<?= $model['other_url']; ?>/index.php?s=/web/track/search&wxappid=<?= $model['wxapp_id']; ?>" readonly required> 
                                    <button style="margin-left:10px;" type="button" class="am-btn am-btn-secondary"><span onclick="tologUrl()">访问URL</span></button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script id="tpl-errors" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        输入管理密码
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                         <input type="text" name="password" placeholder="请勿随意重置URL！！" class="am-field-valid"/>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>
<script>
    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
    });
    
    function toUrl(){
       var url =  $('#url')[0].getAttribute('value');
       window.open(url);
    }
    
    function tologUrl(){
       var url =  $('#logurl')[0].getAttribute('value');
       window.open(url);
    }
    

</script>
