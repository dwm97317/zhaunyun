<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">商品条码列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('setting.barcode/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('setting.barcode/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>条形码</th>
                                <th>品牌名称</th>
                                <th>产品名称</th>
                                <th>产品英文名</th>
                                <th>产品日文名</th>
                                <th>规格</th>
                                <th>价格</th>
                                <th>毛重</th>
                                <th>净重</th>
                                <th>长</th>
                                <th>宽</th>
                                <th>高</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['barcode'] ?></td>
                                        <td class="am-text-middle"><?= $item['brand'] ?></td>
                                        <td class="am-text-middle"><?= $item['goods_name'] ?></td>
                                        <td class="am-text-middle"><?= $item['goods_name_en'] ?></td>
                                        <td class="am-text-middle"><?= $item['goods_name_jp'] ?></td>
                                        <td class="am-text-middle"><?= $item['spec'] ?></td>
                                        <td class="am-text-middle"><?= $item['price'] ?></td>
                                        <td class="am-text-middle"><?= $item['gross_weight'] ?></td>
                                        <td class="am-text-middle"><?= $item['net_weight'] ?></td>
                                        <td class="am-text-middle"><?= $item['depth'] ?></td>
                                        <td class="am-text-middle"><?= $item['width'] ?></td>
                                        <td class="am-text-middle"><?= $item['height'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('setting.barcode/edit')): ?>
                                                    <a href="<?= url('setting.barcode/edit',
                                                        ['sku_id' => $item['sku_id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <!--<?php if (checkPrivilege('setting.barcode/delete')): ?>-->
                                                <!--    <a href="javascript:;"-->
                                                <!--       class="item-delete tpl-table-black-operation-del"-->
                                                <!--       data-id="<?= $item['sku_id'] ?>">-->
                                                <!--        <i class="am-icon-trash"></i> 删除-->
                                                <!--    </a>-->
                                                <!--<?php endif; ?>-->
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        // 删除元素
        var url = "<?= url('setting.barcode/delete') ?>";
        $('.item-delete').delete('express_id', url);
        
        $('#j-copycontry').on('click', function () {
            $.ajax({
                type:"POST",
                url:'<?= url('store/setting.barcode/copy') ?>',
                data:{},
                dataType:"JSON",
                success:function(result){
                    layer.alert(result.msg)
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                    
                }
            })
            
        });
    });
</script>

