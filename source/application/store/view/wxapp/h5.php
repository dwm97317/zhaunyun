<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">公众号设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    公众号名称
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="wxapp[app_wxname]"
                                           value="<?= $model['app_wxname'] ?>" required>
                                </div>
                                
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    原始ID
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="wxapp[app_wxrealid]"
                                           value="<?= $model['app_wxrealid'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    公众号AppID <span class="tpl-form-line-small-title">(AppID)</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="wxapp[app_wxappid]"
                                           value="<?= $model['app_wxappid'] ?>" required>
                                           <small>登录微信公众平台，设置与开发 - 基本配置 - 公众号开发信息，记录开发者ID(AppID)</small>
                                </div>
                                
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    公众号AppSecret <span class="tpl-form-line-small-title">(AppSecret)</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="password" class="tpl-form-input" name="wxapp[app_wxsecret]"
                                           value="<?= $model['app_wxsecret'] ?>" required>
                                           <small>登录微信公众平台，设置与开发 - 基本配置 - 公众号开发信息，设置开发者密码(AppSecret)</small>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">微信开放平台</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">
                                    微信开放平台AppID <span class="tpl-form-line-small-title">(AppID)</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="wxapp[app_wxoepnid]"
                                           value="<?= $model['app_wxoepnid'] ?>">
                                           <small>登录微信开放平台，申请移动应用，然后获取AppID，只有需要APP微信授权登录的才需要填写此项</small>
                                </div>
                                
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">
                                    微信开放平台AppSecret <span class="tpl-form-line-small-title">(AppSecret)</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="password" class="tpl-form-input" name="wxapp[app_wxopensecret]"
                                           value="<?= $model['app_wxopensecret'] ?>">
                                           <small>登录微信开放平台，申请移动应用，然后获取AppSecret，只有需要APP微信授权登录的才需要填写此项</small>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">站点设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    H5站点地址 <span class="tpl-form-line-small-title"></span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="wxapp[other_url]"
                                           value="<?= $model['other_url'].'html5' ?>" required>
                                           <small>系统默认域名为https://zhuanyun10001.sllowly.cn,10001是动态变化的，根据商家的id自动变化。如果你需要配置自己的域名，请联系客服人员协助处理；</small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <?php if (checkPrivilege('wxapp.setting/h5')): ?>
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交</button>
                                    <?php endif; ?>
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
