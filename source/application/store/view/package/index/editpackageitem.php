<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑包裹内容</div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 条码 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[barcode]"
                                           value="<?= $model['barcode'] ?>" placeholder="请输入条码">
                                </div>
                            </div>
                            
                          
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">快递公司</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[express_name]" 
                                           value="<?= $model['express_name'] ?>" placeholder="请输入快递公司">
                                </div>
                            </div>
                          
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">分类名称</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[class_name]"
                                           value="<?= $model['class_name'] ?>" placeholder="请输入分类名称">
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">货物名称</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[goods_name]"
                                           value="<?= $model['goods_name'] ?>" placeholder="请输入货物名称">
                                </div>
                            </div>
                         
                           
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">英文品名</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[class_name_en]"
                                           value="<?= $model['class_name_en'] ?>" placeholder="请输入英文品名">
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">日文品名</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[goods_name_jp]"
                                           value="<?= $model['goods_name_jp'] ?>" placeholder="请输入日文品名">
                                </div>
                            </div>
                        
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">品牌</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[brand]"
                                           value="<?= $model['brand'] ?>" placeholder="请输入品牌">
                                </div>
                            </div>
              
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">规格</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[spec]"
                                           value="<?= $model['spec'] ?>" placeholder="请输入规格">
                                </div>
                            </div>
               
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">数量</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[product_num]"
                                           value="<?= $model['product_num'] ?>" placeholder="请输入数量">
                                </div>
                            </div>
                
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">单价</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[one_price]"
                                           value="<?= $model['one_price'] ?>" placeholder="请输入单价">
                                </div>
                            </div>
                 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">总价</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[all_price]"
                                           value="<?= $model['all_price'] ?>" placeholder="请输入详细地址">
                                </div>
                            </div>
             
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">海关编码</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[customs_code]"
                                           value="<?= $model['customs_code'] ?>" placeholder="请输入海关编码">
                                </div>
                            </div>
                
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">净重</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[net_weight]"
                                           value="<?= $model['net_weight'] ?>" placeholder="请输入净重">
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">毛重</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[unit_weight]"
                                           value="<?= $model['unit_weight'] ?>" placeholder="请输入毛重">
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">原产地</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[origin_region]"
                                           value="<?= $model['origin_region'] ?>" placeholder="请输入原产地">
                                </div>
                            </div>
                  
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary"> 提交
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
