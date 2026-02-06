<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">新增底部菜单</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 菜单名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="nav[name]" value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">未选中时的菜单图标</label>
                                <div class="am-u-sm-9 am-u-end">
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
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">选中时的菜单图标 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-filew am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 菜单类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[nav_linktype]" value="1" data-am-ucheck
                                               checked>
                                        小程序内部链接
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[nav_linktype]" value="2" data-am-ucheck>
                                         外部链接
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[nav_linktype]" value="3" data-am-ucheck>
                                         微信客服
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[nav_linktype]" value="4" data-am-ucheck>
                                         手机号
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">菜单地址 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" min="0" class="tpl-form-input" name="nav[nav_link]" value=""
                                           required>
                                    <small>注意：小程序内部链接使用链接库中的<a target="_blank" href="index.php?s=/store/wxapp.page/links">点击打开链接库</a></small>
                                </div>
                            </div>
                            <div class="am-form-group c" id="c1">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 对应语言 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <select name="nav[lang_type]"  data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择语言类型</option>
                                     <?php if (isset($langlist)):
                                            foreach ($langlist as $key =>$item): ?>
                                                <? json_decode($item,true); ?>
                                                <option value="<?= $item['enname'] ?>"><?= $item['name'] ?>-<?= $item['enname'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                     <div class="help-block">
                                        <small>注：请选择对应的语言，如果需要多套语言，请添加多个</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 菜单位置 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[position]" value="left" data-am-ucheck
                                               checked>
                                         左侧
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[position]" value="center" data-am-ucheck>
                                         中间
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[position]" value="right" data-am-ucheck>
                                         右侧
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否显示 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[status]" value="0" data-am-ucheck
                                               checked>
                                        显示
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[status]" value="1" data-am-ucheck>
                                        隐藏
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="nav[sort]" value="100"
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
    
        $(function () {
        // 选择图片
        $('.upload-file').selectImages({
            name: 'nav[icon]'
        });
        $('.upload-filew').selectImages({
            name: 'nav[selectedIcon]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
    });
</script>
