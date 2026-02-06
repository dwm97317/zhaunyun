<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">新增商品条码</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 条形码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="barcode" type="text" class="tpl-form-input" name="barcode[barcode]" value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 品牌名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="brand" type="text" class="tpl-form-input" name="barcode[brand]" value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 产品名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="goods_name" type="text" class="tpl-form-input" name="barcode[goods_name]" value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 产品英文名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="goods_name_en" type="text" class="tpl-form-input" name="barcode[goods_name_en]" value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 产品日文名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="goods_name_jp" type="text" class="tpl-form-input" name="barcode[goods_name_jp]" value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 规格 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="spec" type="text" class="tpl-form-input" name="barcode[spec]" value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 价格 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="price" type="text" class="tpl-form-input" name="barcode[price]" value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 毛重 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="gross_weight" type="text" class="tpl-form-input" name="barcode[gross_weight]" value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 净重 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="net_weight" type="text" class="tpl-form-input" name="barcode[net_weight]" value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 长 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="depth" type="text" class="tpl-form-input" name="barcode[depth]" value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 宽 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="width" type="text" class="tpl-form-input" name="barcode[width]" value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 高 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="height" type="text" class="tpl-form-input" name="barcode[height]" value="">
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
