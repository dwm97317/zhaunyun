<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">新增保险</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 保险名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="insure[insure_name]" value="<?= $model['insure_name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">保险图标 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= $model['image']['file_path'] ?>" title="点击查看大图"
                                                       target="_blank">
                                                        <img src="<?= $model['image']['file_path'] ?>">
                                                    </a>
                                                    <input type="hidden" name="article[image_id]"
                                                           value="<?= $model['image_id'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 保险类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="insure[insure_type]" value="10" data-am-ucheck
                                               <?= $model['insure_type'] == 10 ? 'checked' : '' ?>>
                                        固定金额
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="insure[insure_type]" value="20" data-am-ucheck
                                        <?= $model['insure_type'] == 20 ? 'checked' : '' ?>>
                                        货值比例
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">保费金额/比例 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="insure[insure_price]" value="<?= $model['insure_price'] ?>"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">保险简述 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <textarea class="tpl-form-input" name="insure[description]" ><?= $model['description'] ?></textarea>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">保险详情介绍 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="insure[content]"
                                              type="text/plain"><?= $model['content'] ?></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否显示 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="insure[status]" value="0" data-am-ucheck
                                               <?= $model['status'] == 0 ? 'checked' : '' ?>>
                                        显示
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="insure[status]" value="1" data-am-ucheck
                                        <?= $model['status'] == 1 ? 'checked' : '' ?>>
                                        隐藏
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="insure[sort]" value="<?= $model['sort'] ?>"
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

<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
         // 选择图片
        $('.upload-file').selectImages({
            name: 'insure[image_id]'
        });
        // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 675 + 15,
            initialFrameHeight: 300
        });

    });
</script>
