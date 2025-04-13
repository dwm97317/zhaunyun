<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf"> 订单详情 </div>
                </div>
                <?php $taker_status = [1=>'待认领',2=>'已认领',3=>'已丢弃',4=>'退件']; ?>
                <?php $status = [1=>'待查验',2=>'待发货',3=>'待发货','4'=>'待发货','5'=>'待发货','6'=>'已发货','7'=>'已到货','8'=>'已完成','-1'=>'问题件']; ?>
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
                                <td>
                                    平台单号：<?= $detail['order_sn'] ?><br>
                                    支付单号：<?= $detail['pay_order'] ?><br>
                                    集运单号：<?= $detail['t_order_sn'] ?><br>
                                    
                                </td>
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
                                            <li class="am-text-right"><?= $detail['weight'] ?> <?= $set['weight_mode']['unit'] ?>(<?= $set['weight_mode']['unit_name'] ?>)</li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                       <p><?= $detail['storage']['shop_name'] ?></p>
                                       <p class="am-link-muted">(仓库id：<?= $detail['storage']['shop_id'] ?>)</p> 
                                </td>
                                <td>
                                       <p><?= $detail['address']?$detail['address']['country']:'暂未选择' ?></p>
                                </td>
                                <td>
                                    <p>支付状态：
                                      <span class="am-badge <?=  $detail['is_pay']==1?"am-badge-success":"am-badge-danger" ?>"> <?=  $detail['is_pay']==1?"已支付":"未支付" ?></span>
                                    </p>
                                    <p>订单状态：
                                      <span class="am-badge am-badge-success"> <?=  $status[$detail['status']] ?></span>
                                    </p>
                                </td>
                                <td>
                                    <p>申请打包：
                                        <span> <?= $detail['created_time'] ?></span>
                                    </p>
                                    <p>更新时间：
                                      <span> <?=  $detail['updated_time'] ?></span>
                                    </p>
                                </td>
                            </tr>
                            <tr><td colspan="7"></td></tr>
                            <tr>
                                <th>收件人</th>
                                <th>电话</th>
                                <th>身份证/通关代码</th>
                                <th>国省市区</th>
                                <th colspan="3">详细地址</th>
                            </tr>
                            <tr>
                                <td><?= $detail['address']['name'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['name'];?>" onclick="copyUrl2(this)">[复制]</span>
                                </td>
                                <td><?= $detail['address']['phone'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['phone'];?>" onclick="copyUrl2(this)">[复制]</span>
                                </td>
                                <td>
                                    <?php if ($set['address_setting']['is_identitycard']==1): ?> 
                                        身份证:<?= $detail['address']['identitycard'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['identitycard'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                    <?php endif ;?>
                                    <?php if ($set['address_setting']['is_clearancecode']==1): ?> 
                                    通关代码:<?= $detail['address']['clearancecode'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['clearancecode'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                    <?php endif ;?>
                                </td>
                                <td>
                                    国家/地区：<?= $detail['address']['country'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['country'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                    <?php if ($set['address_setting']['is_province']==1): ?> 
                                    省/州：<?= $detail['address']['province'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['province'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                    <?php endif ;?>
                                    <?php if ($set['address_setting']['is_city']==1): ?> 
                                    市：<?= $detail['address']['city'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['city'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                    <?php endif ;?>
                                    <!--区：<?= $detail['address']['region']=='0'?'未填':$detail['address']['region']?></br>-->
                                    <?php if ($set['address_setting']['is_street']==1): ?>
                                    街道：<?= $detail['address']['street']=='0'?'未填':$detail['address']['street']?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['street'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                    <?php endif ;?>
                                    <?php if ($set['address_setting']['is_door']==1): ?> 
                                    门牌：<?= $detail['address']['door'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['door'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                     <?php endif ;?>
                                     <?php if ($set['address_setting']['is_code']==1): ?> 
                                    邮编：<?= $detail['address']['code']==''?'未填': $detail['address']['code']?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['code'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                    <?php endif ;?>
                                    <?php if ($set['address_setting']['is_email']==1): ?> 
                                    邮箱：<?= !isset($detail['address']['email'])?'未填':$detail['address']['email'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['email'];?>" onclick="copyUrl2(this)">[复制]</span>
                                    <?php endif ;?></td>
                                </td>
                                <td colspan="3" style="text-align:left;">
                                        <?php if ($set['address_setting']['is_detail']==1): ?> 
                                        详细地址：<?= $detail['address']['detail'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['detail'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        拼接详细地址：<span style="word-break:break-all;"><?= $detail['address']['chineseregion'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $detail['address']['chineseregion'];?>" onclick="copyUrl2(this)">[复制]</span></span></br>
                                        <?php endif ;?>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 子订单信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">子订单/分箱清单</div>
                        <div class="am-fr tpl-table-black-operation">
                            <a id="j-soninpack" href="javascript:void(0)">
                                <i class="am-icon-pencil"></i> 新增子订单
                            </a>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table width="100%" class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>长/宽/高</th>
                                <th>实重</th>
                                <th>体积</th>
                                <th>计费重量</th>
                                <th>发货单号</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php  foreach ($detail['sonitem'] as $key=>$item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $key + 1 ?></td>
                                    <td class="am-text-middle"><?= $item['length']. '/' .$item['width']. '/' .$item['height'] ?></td>
                                    <td class="am-text-middle"><?= $item['weight'] ?></td>
                                    <td class="am-text-middle"><?= $item['volume'] ?></td>
                                    <td class="am-text-middle"><?= $item['cale_weight'] ?></td>
                                    <td class="am-text-middle"><?= $item['t_order_sn'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <a href="javascript:void(0);" class="editinpackitem" data-id="<?= $item['id'] ?>" > <i class="am-icon-pencil"></i> 编辑</a>
                                            <a href="javascript:void(0);" class="item-deletetitem tpl-table-black-operation-del" data-id="<?= $item['id'] ?>" ><i class="am-icon-trash"></i> 删除</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach?>
                            </tbody>
                        </table>
                    </div>
                    
                    
                    <!-- 箱品明细信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">海关申报信息</div>
                        <div class="am-fr tpl-table-black-operation">
                            <a id="j-detailinpack" href="javascript:void(0)">
                                <i class="am-icon-pencil"></i> 新增申报
                            </a>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table width="100%" class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>中文品名</th>
                                <th>英文品名</th>
                                <th>单个商品重量(kg)</th>
                                <th>产品数量</th>
                                <th>总金额USD</th>
                                <th>出口海关编码</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php  foreach ($detail['inpackdetail'] as $key=>$item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $key + 1 ?></td>
                                    <td class="am-text-middle"><?= $item['goods_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['goods_name_en'] ?></td>
                                    <td class="am-text-middle"><?= $item['unit_weight'] ?></td>
                                    <td class="am-text-middle"><?= $item['unit_num'] ?></td>
                                    <td class="am-text-middle"><?= $item['total_free'] ?></td>
                                    <td class="am-text-middle"><?= $item['customs_code'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <a href="javascript:void(0);" class="editinpackdetial" data-id="<?= $item['detail_id'] ?>" > <i class="am-icon-pencil"></i> 编辑</a>
                                            <a href="javascript:void(0);" class="item-deletedetail tpl-table-black-operation-del" data-id="<?= $item['detail_id'] ?>" ><i class="am-icon-trash"></i> 删除</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 打包服务项目 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">打包服务项目</div>
                        <div class="am-fr tpl-table-black-operation">
                            <a id="j-inpack" href="javascript:void(0)">
                                <i class="am-icon-pencil"></i> 新增服务项目
                            </a>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table width="100%" class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th>项目ID</th>
                                <th>项目名称</th>
                                <th>项目类型</th>
                                <th>类目价格</th>
                                <th>数量</th>
                                <th>总价格</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php  foreach ($detail['service'] as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['service']['name'] ?></td>
                                    <td class="am-text-middle"><?= $item['service']['type']==0?'固定金额':'运费百分比' ?></td>
                                    <td class="am-text-middle"><?= $item['service']['type']==0?$item['service']['price']:$item['service']['percentage'] ?></td>
                                    <td class="am-text-middle"><?= $item['service_sum'] ?></td>
                                    <td class="am-text-middle"><?= $item['service']['type']==0?$item['service_sum']*$item['service']['type']==0?$item['service']['price']:$item['service']['percentage']:'' ?></td>
                                    <td class="am-text-middle"><a href="javascript:void(0);" class="item-deletet tpl-table-black-operation-del" data-id="<?= $item['id'] ?>" ><i class="am-icon-trash"></i> 删除</a></td>
                                </tr>
                            <?php endforeach?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 日志信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">物流记录</div>
                        <div class="am-fr tpl-table-black-operation">
                            <a href="<?= url('store/trOrder/logistics', ['id' =>$detail['id']]) ?>">
                                <i class="am-icon-pencil"></i> 物流更新
                            </a>
                        </div>
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
                    
                     <!-- 包裹信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">包裹信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table width="100%" class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th>包裹ID</th>
                                <th>包裹单号</th>
                                <th>快递公司</th>
                                <th>类目名称</th>
                                <th>数量</th>
                                <th>单位重量</th>
                                <th>商品价值</th>
                                <th>备注</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php  foreach ($packageItem as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['order_id'] ?></td>
                                    <td class="am-text-middle"><?= $item['express_num'] ?></td>
                                    <td class="am-text-middle"><?= $item['express_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['class_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['product_num'] ?></td>
                                    <td class="am-text-middle"><?= $item['product_num'] ?></td>
                                    <td class="am-text-middle"><?= $item['all_price'] ?></td>
                                    <td class="am-text-middle"><?= $item['product_num'] ?></td>
                                    <td class="am-text-middle"><a href="javascript:void(0);" class="item-deletetp tpl-table-black-operation-del" data-id="<?= $item['id'] ?>" ><i class="am-icon-trash"></i> 删除</a></td>
                                </tr>
                            <?php endforeach?>
                            </tbody>
                        </table>
                    </div>
                    
                    
                    
                    
                    
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">订单图片</div>
                    </div>
                    
                    <figure style="display:inline-flex;" data-am-widget="figure" class="am am-figure am-figure-default "   data-am-figure="{  pureview: 'true' }">
                            <?php  foreach ($detail['inpackimage'] as $item): ?>
                            <a href="<?= $item['file_path'] ?>"><img style="max-width: 200px;max-height: 200px;" src="<?= $item['file_path'] ?>"/></a>
                            <?php endforeach?>
                    </figure>
                    
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">付款图片</div>
                    </div>
                    
                    <figure style="display:inline-flex;" data-am-widget="figure" class="am am-figure am-figure-default "  data-am-figure="{  pureview: 'true' }">
                            <a href="<?= $detail['certimage']['file_path'] ?>"><img style="max-width: 200px;max-height: 200px;" src="<?= $detail['certimage']['file_path'] ?>"/></a>
                      
                    </figure>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<script id="tpl-inpack" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包装服务
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                      <select name="inpack[service_id]" data-am-selected="{btnSize: 'sm', placeholder: '请选择服务项目'}">
                        <?php foreach ($packageService as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= $item['name'] ?></option>
                            <?php endforeach; ?>
                            </select>
                    </div>
                </div>
            </div>
            <input type="hidden" name="inpack[id]" value="<?= $detail['id'] ?>"/>
            <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 服务项目数量 </label>
                    <div class="am-u-sm-8 am-u-end">
                             <select name="inpack[service_sum]" data-am-selected="{btnSize: 'sm', placeholder: '请选择服务项目'}">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
                    </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-soninpack" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <input type="hidden" name="inpack[inpack_id]" value="<?= $detail['id'] ?>"/>
            <div class="am-form-group">
                <label class="am-u-sm-5 am-u-lg-2 am-form-label">长宽高体积重</label>
                <div class="am-u-sm-10 am-u-end" style="position: relative">
                     <div class="step_mode">
                         <div style="display:flex;">
                             <div class="span">
                                <input type="text" class="vlength" class="tpl-form-input" onblur="getweightvol(0)" style="width:60px;border: 1px solid #c2cad8;" name="inpack[length]" value="" placeholder="长<?= $set['size_mode']['unit'] ?>">
                             </div>
                             <div class="span">
                                <input type="text" class="vwidth" class="tpl-form-input" onblur="getweightvol(0)" style="width:60px;border: 1px solid #c2cad8;" name="inpack[width]" value="" placeholder="宽<?= $set['size_mode']['unit'] ?>">
                             </div>
                             <div class="span">
                                 <input type="text" class="vheight" class="tpl-form-input" onblur="getweightvol(0)" style="width:60px;border: 1px solid #c2cad8;" name="inpack[height]" value="" placeholder="高<?= $set['size_mode']['unit'] ?>">
                             </div>
                             <div class="span">
                                 <select class="wvop" onchange="getweightvol(0)" style="width:60px;border: 1px solid #c2cad8;" >
                                    <option value="5000">5000</option>
                                    <option value="6000">6000</option>
                                    <option value="7000">7000</option>
                                    <option value="8000">8000</option>
                                    <option value="9000">9000</option>
                                    <option value="10000">10000</option>
                                    <option value="139">139</option>
                                    <option value="166">166</option>
                                 </select>
                             </div>
                             <div class="span">
                                 <input id="volume0" class="volume" type="text" class="tpl-form-input" style="width:80px;border: 1px solid #c2cad8;" name="inpack[volume_weight]" value="" placeholder="体积重<?= $set['size_mode']['unit'] ?>">
                             </div>
                             <div class="span">
                                 <input type="text" id="weight" class="tpl-form-input" style="width:60px;border: 1px solid #c2cad8;" name="inpack[weight]" value="" placeholder="重量<?= $set['weight_mode']['unit'] ?>">
                             </div>
                         </div>
                     </div>
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-2 am-form-label">发货单号</label>
                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                    <input type="text" class="tpl-form-input" name="inpack[t_order_sn]"
                           value="" placeholder="请输入发货单号">
                </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-editsoninpack" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <input type="hidden" name="inpack[inpack_id]" value="<?= $detail['id'] ?>"/>
            <div class="am-form-group">
                <label class="am-u-sm-5 am-u-lg-2 am-form-label">长宽高体积重</label>
                <div class="am-u-sm-10 am-u-end" style="position: relative">
                     <div class="step_mode">
                         <div style="display:flex;">
                             <div class="span">
                                <input type="text" class="vlength" class="tpl-form-input" onblur="getweightvol(0)" style="width:60px;border: 1px solid #c2cad8;" name="inpack[length]" value="{{ length }}" placeholder="长<?= $set['size_mode']['unit'] ?>">
                             </div>
                             <div class="span">
                                <input type="text" class="vwidth" class="tpl-form-input" onblur="getweightvol(0)" style="width:60px;border: 1px solid #c2cad8;" name="inpack[width]" value="{{width}}" placeholder="宽<?= $set['size_mode']['unit'] ?>">
                             </div>
                             <div class="span">
                                 <input type="text" class="vheight" class="tpl-form-input" onblur="getweightvol(0)" style="width:60px;border: 1px solid #c2cad8;" name="inpack[height]" value="{{height}}" placeholder="高<?= $set['size_mode']['unit'] ?>">
                             </div>
                             <div class="span">
                                 <select class="wvop" onchange="getweightvol(0)" style="width:60px;border: 1px solid #c2cad8;" >
                                    <option value="5000">5000</option>
                                    <option value="6000">6000</option>
                                    <option value="7000">7000</option>
                                    <option value="8000">8000</option>
                                    <option value="9000">9000</option>
                                    <option value="10000">10000</option>
                                    <option value="139">139</option>
                                    <option value="166">166</option>
                                 </select>
                             </div>
                             <div class="span">
                                 <input id="volume0" class="volume" type="text" class="tpl-form-input" style="width:80px;border: 1px solid #c2cad8;" name="inpack[volume_weight]" value="{{volume_weight}}" placeholder="体积重<?= $set['size_mode']['unit'] ?>">
                             </div>
                             <div class="span">
                                 <input type="text" id="weight" class="tpl-form-input" style="width:60px;border: 1px solid #c2cad8;" name="inpack[weight]" value="{{weight}}" placeholder="重量<?= $set['weight_mode']['unit'] ?>">
                             </div>
                             <div class="span">
                                 <input type="hidden" class="tpl-form-input" style="width:60px;border: 1px solid #c2cad8;" name="inpack[id]" value="{{id}}">
                             </div>
                         </div>
                     </div>
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-2 am-form-label">发货单号</label>
                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                    <input type="text" class="tpl-form-input" name="inpack[t_order_sn]"
                           value="{{t_order_sn}}" placeholder="请输入发货单号">
                </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-detailinpack" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <input type="hidden" name="inpack[inpack_id]" value="<?= $detail['id'] ?>"/>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label">中文品名</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input" name="inpack[goods_name]"
                           value="" placeholder="请输入中文品名" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">英文品名</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input" name="inpack[goods_name_en]"
                           value="" placeholder="请输入英文品名" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label">配货</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input"  name="inpack[distribution]"
                           value="" placeholder="请输入配货" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">单重</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input" name="inpack[unit_weight]"
                           value="" placeholder="请输入单重" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">数量</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input" name="data[unit_num]"
                           value="" placeholder="请输入数量" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">总金额USD</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input"  name="inpack[total_free]"
                           value="" placeholder="请输入配货" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label">出口海关编码</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input"  name="inpack[customs_code]"
                           value="" placeholder="请输入出口海关编码" required> 
                </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-editdetailinpack" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <input type="hidden" name="inpack[inpack_id]" value="<?= $detail['id'] ?>"/>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label">中文品名</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input"  name="inpack[goods_name]"
                           value="{{ goods_name }}" placeholder="请输入中文品名" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">英文品名</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input"  name="inpack[goods_name_en]"
                           value="{{ goods_name_en }}" placeholder="请输入英文品名" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label">配货</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input"  name="inpack[distribution]"
                           value="{{ distribution }}" placeholder="请输入配货" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">单重</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input"  name="inpack[unit_weight]"
                           value="{{ unit_weight }}" placeholder="请输入单重" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">数量</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input"  name="data[unit_num]"
                           value="{{ unit_num }}" placeholder="请输入数量" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">总金额USD</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input"  name="inpack[total_free]"
                           value="{{ total_free }}" placeholder="请输入配货" required> 
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-u-lg-2 am-form-label">出口海关编码</label>
                <div class="am-u-sm-9 am-u-end">
                    <input type="text" class="tpl-form-input"  name="inpack[customs_code]"
                           value="{{ customs_code }}" placeholder="请输入出口海关编码" required> 
                </div>
            </div>
            <input type="hidden" class="tpl-form-input" style="width:60px;border: 1px solid #c2cad8;" name="inpack[id]" value="{{detail_id}}">
        </form>
    </div>
</script>
<script>
    function getweightvol(num){
        console.log(num,6767);
        var num=parseInt(num);
        var length = 0;
        var width = 0;
        var height = 0;
        var wvop = 0;
        if($(".vlength")[num]){
             length = $(".vlength")[num].value;
        }
        if($(".vwidth")[num]){
             width = $(".vwidth")[num].value;
        }
        if($(".vheight")[num]){
            height = $(".vheight")[num].value;
        }
        if($(".wvop")[num]){
            wvop = $(".wvop")[num].value;
        }
        console.log(length,7878);
        console.log(width,7878);
        console.log(height,7878);
        if(length !='' && width !='' && height !=''){
            $("#volume"+num).val(length * width * height / wvop);
        }
        // console.log($("#volume"+num).val(34),7878);
        
    }

 $(function () {
        /**
         * 新增子订单
         */
        $('.editinpackitem').on('click', function () {
            var $tabs, data = $(this).data();
            $.post('store/tr_Order/InpackItemdetail',{id:data.id}, function (result) {
                if(result.code == 1 ){
                    $.showModal({
                        title: '编辑子订单'
                        , area: '800px'
                        , content: template('tpl-editsoninpack', result.data.detail)
                        , uCheck: true
                        , success: function ($content) {
                        }
                        , yes: function ($content) {
                            $content.find('form').myAjaxSubmit({
                                url: '<?= url('store/TrOrder/editInpackItem') ?>',
                                data: {
                                   
                                }
                            });
                        }
                    });
                }else{
                  $.show_error(result.msg);   
                }
            });
        });
        
        
        $('.editinpackdetial').on('click', function () {
            var $tabs, data = $(this).data();
            $.post('store/tr_Order/Inpackdetaildetail',{id:data.id}, function (result) {
                if(result.code == 1 ){
                    $.showModal({
                        title: '编辑申报信息'
                        , area: '800px'
                        , content: template('tpl-editdetailinpack', result.data.detail)
                        , uCheck: true
                        , success: function ($content) {
                        }
                        , yes: function ($content) {
                            $content.find('form').myAjaxSubmit({
                                url: '<?= url('store/TrOrder/editInpackDetail') ?>',
                                data: {
                                   
                                }
                            });
                        }
                    });
                }else{
                  $.show_error(result.msg);   
                }
            });
        });
     
     
     /**
         * 新增子订单
         */
        $('#j-detailinpack').on('click', function () {
            var $tabs, data = $(this).data();
            $.showModal({
                title: '新增申报'
                , area: '800px'
                , content: template('tpl-detailinpack', data)
                , uCheck: true
                , success: function ($content) {
                    $tabs = $content.find('.j-tabs');
                    $tabs.tabs({noSwipe: 1});
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/TrOrder/addInpackDetail') ?>',
                        data: {
                            service_id:data.selectId,
                        }
                    });
                    return true;
                }
            });
        });

         /**
         * 新增子订单
         */
        $('#j-soninpack').on('click', function () {
            var $tabs, data = $(this).data();
            $.showModal({
                title: '新增子订单'
                , area: '800px'
                , content: template('tpl-soninpack', data)
                , uCheck: true
                , success: function ($content) {
                    $tabs = $content.find('.j-tabs');
                    $tabs.tabs({noSwipe: 1});
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/TrOrder/addInpackItem') ?>',
                        data: {
                            service_id:data.selectId,
                        }
                    });
                    return true;
                }
            });
        });
        /**
         * 新增服务项目
         */
        $('#j-inpack').on('click', function () {
            var $tabs, data = $(this).data();
            $.showModal({
                title: '新增服务项目'
                , area: '460px'
                , content: template('tpl-inpack', data)
                , uCheck: true
                , success: function ($content) {
                    $tabs = $content.find('.j-tabs');
                    $tabs.tabs({noSwipe: 1});
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/InpackService/add') ?>',
                        data: {
                            service_id:data.selectId,
                        }
                    });
                    return true;
                }
            });
        });
        // 删除元素
        var url = "<?= url('store/Logistics/delete') ?>";
        $('.item-delete').delete('id', url);
        
        var url = "<?= url('store/PackageItem/delete') ?>";
        $('.item-deletetp').delete('id', url);
        
        var urle = "<?= url('store/InpackService/delete') ?>";
        $('.item-deletet').delete('id', urle);
        
        // 删除元素
        var urll = "<?= url('store/TrOrder/deleteInpackItem') ?>";
        $('.item-deletetitem').delete('id', urll);
        
        var urlll = "<?= url('store/TrOrder/deleteInpackDetail') ?>";
        $('.item-deletedetail').delete('id', urlll);
        
        
 });
</script>