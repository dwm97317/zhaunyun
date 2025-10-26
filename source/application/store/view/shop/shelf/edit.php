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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货架名称</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shelf[shelf_name]"
                                           value="<?= $model['shelf_name']?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货架编号</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shelf[shelf_no]"
                                           value="<?= $model['shelf_no']?>" required>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货架行数</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shelf[shelf_column]"
                                           value="<?= $model['shelf_column']?>" required>
                                             <div class="help-block">
                                </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货架列数</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shelf[shelf_row]"
                                           value="<?= $model['shelf_row']?>" required>
                                             <div class="help-block">
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 所属仓库 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="shelf[ware_no]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}"  data-select_type='shelf'>
                                        <option value=""></option>
                                        <?php if (isset($shopList) && !$shopList->isEmpty()): ?>
                                          <?php foreach ($shopList as $item): ?>
                                                <option value="<?= $item['shop_id'] ?>"  <?= $model['ware_no']??'' == $item['shop_id'] ? 'selected' : '' ?>><?= $item['shop_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>货架所在仓库</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 条码类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="shelf[barcode_type]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" data-select_type='shelf'>
                                                <option value="10" <?= $model['barcode_type']??'' == 10? 'selected' : '' ?>>二维码</option>
                                                <option value="20" <?= $model['barcode_type']??'' == 20 ? 'selected' : '' ?>>条形码</option>
                                    </select>
                                    <div class="help-block">
                                        <small>货架所在仓库</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">无主货架 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_nouser]" value="0" data-am-ucheck
                                        <?= $model['is_nouser'] == 0 ? 'checked' : '' ?>>
                                        专属
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_nouser]" value="1" data-am-ucheck
                                            <?= $model['is_nouser'] == 1 ? 'checked' : '' ?>>
                                        无主
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">普敏货架 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_normal]" value="0" data-am-ucheck
                                        <?= $model['is_normal'] == 0 ? 'checked' : '' ?>>
                                        普货
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_normal]" value="1" data-am-ucheck
                                            <?= $model['is_normal'] == 1 ? 'checked' : '' ?>>
                                        敏货
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货位大小 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_big]" value="0" data-am-ucheck
                                        <?= $model['is_big'] == 0 ? 'checked' : '' ?>>
                                        小货
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_big]" value="1" data-am-ucheck
                                            <?= $model['is_big'] == 1 ? 'checked' : '' ?>>
                                        大货
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货架状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[status]" value="1" data-am-ucheck
                                            <?= $model['status'] == 1 ? 'checked' : '' ?>>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[status]" value="2" data-am-ucheck
                                        <?= $model['status'] == 0 ? 'checked' : '' ?>>
                                        禁用
                                    </label>
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
