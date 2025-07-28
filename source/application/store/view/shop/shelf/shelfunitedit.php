<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑货架</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货位名称</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shelf[shelf_unit_no]"
                                           value="<?= $model['shelf_unit_no']?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货架编号</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shelf[shelf_unit_code]"
                                           value="<?= $model['shelf_unit_code']?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 所属货架 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="shelf[shelf_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}"  data-select_type='shelf'>
                                        <option value=""></option>
                                        <?php if (isset($shelfList) && !$shelfList->isEmpty()): ?>
                                          <?php foreach ($shelfList as $item): ?>
                                                <option value="<?= $item['id'] ?>"  <?= $model['shelf_id']??'' == $item['id'] ? 'selected' : '' ?>><?= $item['shelf_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>货位所在仓库</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label"> 选择用户 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择用户
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                           <div class="file-item">
                                               
                                               <a href="<?= $model['user']['avatarUrl'] ?>" title="<?= $model['user']['nickName'] ?>" target="_blank">
                                                 <img src="<?= $model['user']['avatarUrl'] ?>">
                                               </a>
                                               <input type="hidden" name="shelf[user_id]" value="<?= $model['user']['user_id'] ?>">
                                           </div>
                                        </div>
                                        <div class="am-block">
                                         <small>
                                            昵称：<?= $model['user']['nickName'] ?> 
                                            ID：<?= $model['user']['user_id'] ?>
                                            CODE：<?= $model['user']['user_code'] ?>
                                        </small> 
                                         <small>点击选择用户更改包裹所属用户</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--货位类型 10=小 20=中 30=大 40=特殊-->
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 货位规格 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="shelf[shelf_type]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" data-select_type='shelf'>
                                                <option value="10" <?= $model['shelf_type']??'' == 10? 'selected' : '' ?>>小</option>
                                                <option value="20" <?= $model['shelf_type']??'' == 20 ? 'selected' : '' ?>>中</option>
                                                <option value="30" <?= $model['shelf_type']??'' == 30 ? 'selected' : '' ?>>大</option>
                                                <option value="40" <?= $model['shelf_type']??'' == 40 ? 'selected' : '' ?>>特殊</option>
                                    </select>
                                    <div class="help-block">
                                        <small>货架所在仓库</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shelf[sort]"
                                           value="<?= $model['sort']?>" required>
                                             <div class="help-block">
                                </div>
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
<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
        </a>
        <input type="hidden" name="shelf[user_id]" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
</script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>
 // 选择用户
    $('.j-selectUser').click(function () {
        var $userList = $('.user-list');
        $.selectData({
            title: '选择用户',
            uri: 'user/lists',
            dataIndex: 'user_id',
            done: function (data) {
                var user = [data[0]];
                $userList.html(template('tpl-user-item', user));
            }
        });
    });
    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
