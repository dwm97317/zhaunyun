<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑导航菜单</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 导航名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="nav[name]"
                                           value="<?= $model['name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 导航副标题(2号模板需要填) </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="nav[desc]"
                                           value="<?= $model['desc'] ?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">导航图标 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= $model['image']['file_path'] ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= $model['image']['file_path'] ?>">
                                                    </a>
                                                    <input type="hidden" name="nav[nav_icon]"
                                                           value="<?= $model['nav_icon'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 导航类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[nav_linktype]" value="1" data-am-ucheck
                                                <?= $model['nav_linktype'] == 1 ? 'checked' : '' ?>>
                                        小程序内部链接
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[nav_linktype]" value="2" data-am-ucheck
                                         <?= $model['nav_linktype'] == 2 ? 'checked' : '' ?>>
                                        外部链接
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[nav_linktype]" value="3" data-am-ucheck
                                         <?= $model['nav_linktype'] == 3 ? 'checked' : '' ?>>
                                        微信客服
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[nav_linktype]" value="4" data-am-ucheck
                                         <?= $model['nav_linktype'] == 4 ? 'checked' : '' ?>>
                                        手机号
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 链接地址 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" min="0" class="tpl-form-input" name="nav[nav_link]" value="<?= $model['nav_link'];?>"
                                           ><small>注意：小程序内部链接使用链接库中的<a target="_blank" href="index.php?s=/store/wxapp.page/links">点击打开链接库</a></small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否显示 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[is_use]" value="0" data-am-ucheck
                                                <?= $model['is_use'] == 0 ? 'checked' : '' ?>>
                                        显示
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="nav[is_use]" value="1" data-am-ucheck
                                         <?= $model['is_use'] == 1 ? 'checked' : '' ?>>
                                        隐藏
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label"> 对应包裹/订单状态</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="nav[tips]" data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                        <option value="0">不对应状态</option>
                                        <option value="10" <?= $model['tips'] == 10 ? 'selected' : '' ?>>所有包裹</option>
                                        <option value="20" <?= $model['tips'] == 20 ? 'selected' : '' ?>>未入库包裹</option>
                                        <option value="30" <?= $model['tips'] == 30 ? 'selected' : '' ?>>已入库包裹</option>
                                        <option value="40" <?= $model['tips'] == 40 ? 'selected' : '' ?>>已发货包裹</option>
                                        <option value="50" <?= $model['tips'] == 50 ? 'selected' : '' ?>>问题件包裹</option>
                                        <option value="60" <?= $model['tips'] == 60 ? 'selected' : '' ?>>已到货包裹</option>
                                        <option value="70" <?= $model['tips'] == 70 ? 'selected' : '' ?>>已签收包裹</option>
                                        <option value="80" <?= $model['tips'] == 80 ? 'selected' : '' ?>>待发货包裹</option>
                                        <option value="90" <?= $model['tips'] == 90 ? 'selected' : '' ?>>待支付包裹</option>
                                        <option value="100" <?= $model['tips'] == 100 ? 'selected' : '' ?>>待打包包裹</option>
                                        <option value="110" <?= $model['tips'] == 110 ? 'selected' : '' ?>>所有订单</option>
                                        <option value="120" <?= $model['tips'] == 120 ? 'selected' : '' ?>>已支付订单</option>
                                        <option value="130" <?= $model['tips'] == 130 ? 'selected' : '' ?>>未支付订单</option>
                                        <option value="140" <?= $model['tips'] == 140 ? 'selected' : '' ?>>待查验订单</option>
                                        <option value="150" <?= $model['tips'] == 150 ? 'selected' : '' ?>>未发货订单</option>
                                        <option value="160" <?= $model['tips'] == 160 ? 'selected' : '' ?>>已发货订单</option>
                                        <option value="170" <?= $model['tips'] == 170 ? 'selected' : '' ?>>已完成订单</option>
                                        <option value="180" <?= $model['tips'] == 180 ? 'selected' : '' ?>>未支付订单</option>
                                    </select>
                                    <div class="help-block">
                                        <small>此内容非必填，在首页需要显示某个状态的包裹数量或某个状态的订单数量时可以选择，其他菜单请勿选</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group c" id="c1">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 对应语言 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <select name="nav[lang_type]" onchange="changeexpress(this)" id="deliveryitem" data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择语言类型</option>
                                     <?php if (isset($langlist)):
                                            foreach ($langlist as $item): ?>
                                                <option value="<?= $item['enname'] ?>" <?= $item['enname'] == $model['lang_type']?"selected":'' ?>><?= $item['name'] ?>-<?= $item['enname'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                     <div class="help-block">
                                        <small>注：请选择对应的语言，如果需要多套语言，请添加多个</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="nav[sort]"
                                           value="<?= $model['sort'] ?>" required>
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
        // 选择图片
        $('.upload-file').selectImages({
            name: 'nav[nav_icon]'
        });
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
    })
</script>
