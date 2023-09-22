<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf"> 包裹详情 </div>
                </div>
                <?php $taker_status = [1=>'待认领',2=>'已认领',3=>'已丢弃',4=>'退件']; ?>
                <?php $status = [0=>"问题件",1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成','-1'=>'问题件']; ?>
                <div class="widget__order-detail widget-body am-margin-bottom-lg">
                        <!-- 基本信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">基本信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>订单号</th>
                                <th>买家</th>
                                <th>包裹情况</th>
                                <th>寄送仓库</th>
                                <th>寄送国家</th>
                                <th>包裹状态</th>
                                <th>时间</th>
                            </tr>
                            <tr>
                                <td><?= $detail['order_sn'] ?></td>
                                <td>
                                    <p><?= $detail['user']['nickName'] ?></p>
                                    <p class="am-link-muted">(用户id：<?= $detail['user']['user_id'] ?>)</p>
                                </td>
                                <td class="">
                                    <div class="td__order-price am-text-left">
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">长：</li>
                                            <li class="am-text-right"><?= $detail['length'] ?> <?= $set['size_mode']['unit'] ?></li>
                                        </ul>
                                            <ul class="am-avg-sm-2">
                                                <li class="am-text-right">宽：</li>
                                                <li class="am-text-right"><?= $detail['width'] ?> <?= $set['size_mode']['unit'] ?></li>
                                            </ul>
                                            <ul class="am-avg-sm-2">
                                                <li class="am-text-right">高：</li>
                                                <li class="am-text-right"><?= $detail['height'] ?> <?= $set['size_mode']['unit'] ?></li>
                                            </ul>
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">重量：</li>
                                            <li class="am-text-right"><?= $detail['weight'] ?><?= $set['weight_mode']['unit'] ?> (<?= $set['weight_mode']['unit_name'] ?>)</li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                       <p><?= $detail['storage']['shop_name'] ?></p>
                                       <p class="am-link-muted">(仓库id：<?= $detail['storage']['shop_id'] ?>)</p> 
                                </td>
                                <td>
                                       <p><?= $detail['country']['title']?$detail['country']['title']:'暂未选择' ?></p>
                                       <p class="am-link-muted">(国家id：<?= $detail['country']['id']?$detail['country']['id']:0 ?>)</p> 
                                </td>
                                <td>
                                    <p>认领状态：
                                        <span class="am-badge am-badge-success"> <?= $taker_status[$detail['is_take']] ?></span>
                                    </p>
                                    <p>包裹状态：
                                       <span class="am-badge am-badge-success"> <?= $status[$detail['status']] ?></span>
                                    </p>
                                    <p>支付状态：
                                      <span class="am-badge am-badge-success"> <?=  $detail['is_pay']==1?"已支付":"未支付" ?></span>
                                    </p>
                                </td>
                                <td>
                                    <p>预报时间：
                                        <span> <?= $detail['created_time'] ?></span>
                                    </p>
                                    <p>入库时间：
                                       <span> <?= $detail['entering_warehouse_time'] ?></span>
                                    </p>
                                    <p>更新时间：
                                      <span> <?=  $detail['updated_time'] ?></span>
                                    </p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 包裹信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">包裹信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table width="100%" class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>快递单号</th>
                                <th>快递公司</th>
                                <th>类目名称</th>
                                <th>数量</th>
                                <th>单位重量</th>
                                <th>商品价值</th>
                                <th>备注</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php  foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['express_num'] ?></td>
                                    <td class="am-text-middle"><?= $item['express_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['class_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['product_num'] ?></td>
                                    <td class="am-text-middle"><?= $detail['weight'] ?></td>
                                    <td class="am-text-middle"><?= $item['all_price'] ?></td>
                                    <td class="am-text-middle"><?= $detail['remark'] ?></td>
                                </tr>
                            <?php endforeach?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                    
                    <!-- 日志信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">包裹记录</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table width="100%" class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>状态值</th>
                                <th>状态名</th>
                                <th>内容</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php  foreach ($detail['log'] as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['status'] ?></td>
                                    <td class="am-text-middle"><?= $item['status_cn'] ?></td>
                                    <td class="am-text-middle"><?= $item['logistics_describe'] ?></td>
                                    <td class="am-text-middle"><?= $item['created_time'] ?></td>
                                    <td class="am-text-middle"><a href="javascript:void(0);" class="item-delete tpl-table-black-operation-del" data-id="<?= $item['id'] ?>" ><i class="am-icon-trash"></i> 删除</a></td>
                                </tr>
                            <?php endforeach?>
                            </tbody>
                        </table>
                    </div>
                    

                    
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">入库信息</div>
                    </div>
                       <figure style="display:inline-flex;" data-am-widget="figure" class="am am-figure am-figure-default "   data-am-figure="{  pureview: 'true' }">
                                <?php  foreach ($detail['packageimage'] as $item): ?>
                                <img style="max-width: 200px;max-height: 200px;" src="<?= $item['file_path'] ?>"/>
                                <?php endforeach?>
                       </figure>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
 $(function () {
        // 删除元素
        var url = "<?= url('store/Logistics/delete') ?>";
        $('.item-delete').delete('id', url);
 });
</script>