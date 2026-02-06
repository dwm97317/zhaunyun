<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">新增国家</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="country[title]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">二字码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="country[code]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">三字码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="country[three_code]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">手机号前缀 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="country[prefix]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否热门 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="country[is_hot]" value="1"?>
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="country[is_hot]" value="0"  data-am-ucheck checked >
                                        否
                                    </label>
                                    <div class="help-block">
                                        <small style="color:#ff6666;">关闭状态:热门国家会优先展示</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否为默认发货国家 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="country[is_send]" value="1"?>
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="country[is_send]" value="0"  data-am-ucheck checked >
                                        否
                                    </label>
                                    <div class="help-block">
                                        <small style="color:#ff6666;">默认发货国家只能设置一个，被设置默认的国家在运费估算功能里被默认选择</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否为默认目的地国家 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="country[is_top]" value="1" ?>
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="country[is_top]" value="0"  data-am-ucheck checked>
                                        否
                                    </label>
                                    <div class="help-block">
                                        <small style="color:#ff6666;">默认目的地国家只能设置一个，被设置默认的国家在预报，填写地址等各个环节被默认选择</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否开启 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="country[status]" value="1" data-am-ucheck checked ?>
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="country[status]" value="0">
                                        否
                                    </label>
                                    <div class="help-block">
                                        <small style="color:#ff6666;">关闭状态:前端将不显示该国家</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="country[sort]"
                                           value="" required>
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

<script>
    $(function () {


        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
