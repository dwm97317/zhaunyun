<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加货架</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">货架名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shelf[shelf_name]"
                                           value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货架编号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shelf[shelf_no]"
                                           value="" required>
                                </div>
                            </div>
                           <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货架层数 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="shelf[shelf_column]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货架列数 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="shelf[shelf_row]"
                                           value="" required>
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
                                                <option value="<?= $item['shop_id'] ?>"  <?= $data['storage_id']??'' == $item['shop_id'] ? 'selected' : '' ?>><?= $item['shop_name'] ?></option>
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
                                                <option value="10">二维码</option>
                                                <option value="20">条形码</option>
                                    </select>
                                    <div class="help-block">
                                        <small>货架所在仓库</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 编号生成模式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="shelf[number_type]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" data-select_type='number_type'>
                                                <option value="10">编号-层数-列数（N-01-01）</option>
                                                <option value="20">编号+数量（如M01,M16）</option>
                                    </select>
                                    <div class="help-block">
                                        <small>编号+数量模式：编号为M，设置3行3列则生成的编号为M01,M02,M03,M04,M05,M06,M07,M08,M09</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否无主货架 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_nouser]" value="0" data-am-ucheck checked>
                                        专属
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_nouser]" value="1" data-am-ucheck
                                               >
                                        无主
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">普敏货架 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_normal]" value="0" data-am-ucheck checked>
                                        普货
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_normal]" value="1" data-am-ucheck
                                               >
                                        敏货
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货位大小 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_big]" value="0" data-am-ucheck checked>
                                        小货
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[is_big]" value="1" data-am-ucheck
                                               >
                                        大货
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">货架状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[status]" value="1" data-am-ucheck
                                               checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shelf[status]" value="2" data-am-ucheck>
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
