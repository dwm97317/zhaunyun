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
                                <div class="widget-title am-fl">服务器配置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    服务器地址(URL) <span class="tpl-form-line-small-title">(URL)</span>
                                </label>
                                <div class="am-u-sm-9">
                                    
                                           <small>默认主域名+ /index.php?s=/api/wechat/callback&wxapp_id=<?= $model['wxapp_id'] ?></small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">
                                    公众号Token <span class="tpl-form-line-small-title">(Token)</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="wxapp[wechat_token]"
                                           value="<?= $model['wechat_token'] ?>">
                                           <small>登录微信公众平台，设置与开发 - 基本配置 - 开发接口管理，Token</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">
                                    公众号EncodingAESKey <span class="tpl-form-line-small-title">(EncodingAESKey)</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="wxapp[encodingAESKey]"
                                           value="<?= $model['encodingAESKey'] ?>">
                                           <small>登录微信公众平台，设置与开发 - 基本配置 - 开发接口管理，EncodingAESKey</small>
                                </div>
                            </div>
                            
                            
                            
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">微信开放平台</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    开放平台是否申请
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                            <input type="radio" name="wxapp[wx_type]" value="1"
                                                   data-am-ucheck
                                                <?= $model['wx_type'] == '1' ? 'checked' : '' ?>
                                                   required>
                                            已申请
                                        </label>
                                        <label class="am-radio-inline">
                                            <input type="radio" name="wxapp[wx_type]" value="0"
                                                   data-am-ucheck
                                                <?= $model['wx_type'] == '0' ? 'checked' : '' ?>>
                                            未申请
                                        </label>
                                    <div class="help-block">
                                        <small>注意：只要小程序公众号绑定了微信开放平台就需要选择已申请，如果需要接收微信模板消息，则必须申请，注册地址<a target="_blank" href="https://open.weixin.qq.com/">微信开放平台申请入口</a></small>
                                    </div>
                                </div>
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
                                           value="<?= $model['other_url'] ?>" required>
                                           <small>系统默认域名为https://zhuanyun10001.sllowly.cn,10001是动态变化的，根据商家的id自动变化。如果你需要配置自己的域名，请联系客服人员协助处理；</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    备案号 <span class="tpl-form-line-small-title"></span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="wxapp[filing_number]"
                                           value="<?= $model['filing_number'] ?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    版权说明 <span class="tpl-form-line-small-title"></span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="wxapp[copyrighttext]"
                                           value="<?= $model['copyrighttext'] ?>">
                                    <small>可以参考：©2017-2025 湖北思楼文化传媒有限公司</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label form-require">站点logo </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= isset($model['logos'])?$model['logos']['file_path']:"##" ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= isset($model['logos'])?$model['logos']['file_path']:'' ?>">
                                                    </a>
                                                    <input type="hidden" name="wxapp[logo]"
                                                           value="<?= $model['logo'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label form-require">微信公众号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-wechat am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= isset($model['wechatimgs'])?$model['wechatimgs']['file_path']:"##" ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= isset($model['wechatimgs'])?$model['wechatimgs']['file_path']:'' ?>">
                                                    </a>
                                                    <input type="hidden" name="wxapp[wechatimg]"
                                                           value="<?= $model['wechatimg'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                    </div>
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
<script src="/web/static/js/selectize.min.js"></script>
<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
        // 选择图片
        $('.upload-file').selectImages({
            name: 'wxapp[logo]'
        });
        $('.upload-wechat').selectImages({
            name: 'wxapp[wechatimg]'
        });
    });
</script>
