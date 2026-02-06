<?php
use app\common\enum\BatchType as BatchTypeEnum;
?>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">批次列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if ($type==0) : ?>
                                    <?php if (checkPrivilege('batch/addbatch')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success"
                                               href="<?= url('batch/addbatch') ?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; endif; ?>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>批次ID</th>
                                <th>批次名称</th>
                                <th>批次类型</th>
                                <th>装箱代码</th>
                                <th>发货单号</th>
                                <th>目标仓库</th>
                                <th>物流模板</th>
                                <th>批次信息(长/宽/高/重/体积)</th>
                                <th>状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['batch_id'] ?></td>
                                    <td class="am-text-middle"><?= $item['batch_name'] ?></td>
                                    <td class="am-text-middle"><?= BatchTypeEnum::data()[$item['batch_type']]['name'] ?></td>
                                    <td class="am-text-middle"><?= $item['batch_no'] ?></td>
                                    <td class="am-text-middle"><?= $item['express_no'] ?></td>
                                    <td class="am-text-middle"><?= $item['shop']['shop_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['template']['template_name']?$item['template']['template_name']:'未选择' ?></td>
                                    <td class="am-text-middle">
                                        <span class="am-badge am-badge-success">长</span>
                                            <?= $item['length'] ?>/<?= $item['width'] ?>/<?= $item['height'] ?><br>
                                            重：<?= $item['weigth'] ?><br>
                                            体积：<?= $item['wegihtvol'] ?>
                                       </td>
                                    <?php $status=[0=>'待发货',1=>'运送中',2=>'已到达'] ?>
                                    <td class="am-text-middle">
                                            <span class="am-badge am-badge-warning">
                                               <?= $status[$item['status']] ?>
                                           </span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('batch/editbatch')): ?>
                                                <a href="<?= url('batch/editbatch', ['batch_id' => $item['batch_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('batch/deletebatch')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['batch_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (checkPrivilege('batch/logistics')): ?>
                                            <a href="javascript:void(0);" class="j-wuliu tpl-table-black-operation-green " data-id="<?= $item['batch_id'] ?>"> 
                                                <i class="iconfont icon-755danzi "></i> 物流更新
                                            </a>
                                            <?php endif; ?>
                                            
                                            <?php if (checkPrivilege('batch/statuschange')): ?>
                                            <a href="javascript:void(0);" class="j-status-change tpl-table-black-operation-blue" data-id="<?= $item['batch_id'] ?>" data-status="<?= $item['status'] ?>"> 
                                                <i class="am-icon-edit"></i> 状态变更
                                            </a>
                                            <?php endif; ?>
                                            
                                            <?php if (checkPrivilege('batch/shipment') && $item['status'] == 0): ?>
                                            <a href="javascript:void(0);" class="j-shipment tpl-table-black-operation-green" data-id="<?= $item['batch_id'] ?>"> 
                                                <i class="am-icon-truck"></i> 发货
                                            </a>
                                            <?php endif; ?>
                                   
                                        </div>
                                        <div style="margin-top:10px;" class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('batch/batchvsinpack')): ?>
                                            <a href="<?= url('batch/batchvsinpack', ['id' => $item['batch_id']]) ?>">
                                            查看批次订单
                                            </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('batch/batchvspack')): ?>
                                            <a  class="tpl-table-black-operation-del" href="<?= url('batch/batchvspack', ['id' => $item['batch_id']]) ?>">
                                            查看批次包裹
                                            </a>
                                            <?php endif; ?>
                                             <a class='tpl-table-black-operation-green j-package' href="javascript:void(0);" data-id="<?= $item['batch_id'] ?>">
                                                <i class="iconfont icon-daochu"></i> 导出包裹明细
                                            </a>
                                         
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="11" class="am-text-center">暂无记录</td>
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
<script id="tpl-wuliu" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3  am-form-label">轨迹模板 </label>
                    <div class="am-u-sm-9 am-u-end">
                         <select name="track_id" id="" data-am-selected="{searchBox: 1,maxHeight:300}">
                             <option value="">选择模板</option>
                         <?php if (isset($tracklist)):
                                foreach ($tracklist as $item): ?>
                                    <option value="<?= $item['track_id'] ?>"><?= $item['track_name'] ?></option>
                                <?php endforeach; endif; ?>
                         </select>
                         <div class="help-block">
                            <small>注：你可以在下方自定义轨迹，或者选择预设好的轨迹</small>
                    </div>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label">
                        输入物流状态
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="logistics_describe" value="">
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label">
                        选择物流时间
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text"  name="created_time" placeholder="请选择起始日期" value="<?php echo date("Y-m-d H:i:s",time()) ?>" id="datetimepicker" class="am-form-field">
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<script id="tpl-status-change" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label">当前状态</label>
                    <div class="am-u-sm-9 am-u-end">
                        <span class="am-badge am-badge-warning" id="current-status-text">待发货</span>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label">选择新状态</label>
                    <div class="am-u-sm-9 am-u-end">
                        <select name="status" id="status-select" data-am-selected="{searchBox: 0,maxHeight:200}">
                            <option value="0">待发货</option>
                            <option value="1">运送中</option>
                            <option value="2">已到达</option>
                        </select>
                        <div class="help-block">
                            <small>请选择要变更到的状态</small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<script id="tpl-shipment" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-u-lg-2 am-form-label">运输方式</label>
                    <div class="am-u-sm-9 am-u-end">
                        <label class="am-radio-inline">
                            <input type="radio" name="transfer" value="1" data-am-ucheck checked onchange="onChangeShipment('c1')">
                            运输商
                        </label>
                        <label class="am-radio-inline">
                            <input type="radio" name="transfer" value="0" data-am-ucheck onchange="onChangeShipment('c2')">
                            自有物流
                        </label>
                    </div>
                </div>
                <div class="am-form-group c" id="c1">
                    <label class="am-u-sm-3 am-u-lg-2 am-form-label">承运商</label>
                    <div class="am-u-sm-9 am-u-end">
                        <select name="tt_number" data-am-selected="{searchBox: 1,maxHeight:300}">
                            <option value="">选择承运商</option>
                            <?php if (isset($track) && !$track->isEmpty()):
                                foreach ($track as $item): ?>
                                    <option value="<?= $item['express_code'] ?>"><?= $item['express_name'] ?>-<?= $item['express_code'] ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                        <div class="help-block">
                            <small>注：选择自有物流17track不可查，请选择正确的物流商，否则国际单号无法查询；</small>
                        </div>
                    </div>
                </div>
                <div class="am-form-group c" id="c2" style="display: none;">
                    <label class="am-u-sm-3 am-u-lg-2 am-form-label">承运商</label>
                    <div class="am-u-sm-9 am-u-end">
                        <select name="t_number" data-am-selected="{searchBox: 1,maxHeight:300}">
                            <option value="">选择承运商</option>
                            <?php if (isset($ditchlist) && !$ditchlist->isEmpty()):
                                foreach ($ditchlist as $item): ?>
                                    <option value="<?= $item['ditch_id'] ?>"><?= $item['ditch_name'] ?>-<?= $item['ditch_no'] ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-u-lg-2 am-form-label">运单号</label>
                    <div class="am-u-sm-3 am-u-end">
                        <input type="text" class="tpl-form-input" name="express_no" placeholder="请输入运单号" required>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script>
    $(function () {
        // 删除元素
        var url = "<?= url('batch/deletebatch') ?>";
        $('.item-delete').delete('batch_id', url, '删除后不可恢复，确定要删除吗？');
        
    });
    
	
	/**
	 * 导出包裹
	 */
	$('.j-invoice').on('click', function() {
		var $tabs, data = $(this).data();
		$.ajax({
			type: 'post',
			url: "<?= url('store/trOrder/batchinvoice') ?>",
			data: {
				id: data.id,
			},
			dataType: "json",
			success: function(res) {
				if (res.code == 1) {
					console.log(res.url.file_name);
					var a = document.createElement('a');
					document.body.appendChild(a);
					a.href = res.url.file_name;
					a.click();
				}
			}
		})
	});
	
		
	/**
	 * 导出包裹明细
	 */
	$('.j-package').on('click', function() {
		var $tabs, data = $(this).data();
		$.ajax({
			type: 'post',
			url: "<?= url('store/trOrder/exportBatchInpackpackage') ?>",
			data: {
			    id: data.id,
			},
			dataType: "json",
			success: function(res) {
				if (res.code == 1) {
					console.log(res.url.file_name);
					var a = document.createElement('a');
					document.body.appendChild(a);
					a.href = res.url.file_name;
					a.click();
				}
			}
		})
	});
	
	
	/**
	 * 导出集运清关文件
	 */
	$('.j-clearance').on('click', function() {
		var $tabs, data = $(this).data();
		$.ajax({
			type: 'post',
			url: "<?= url('store/trOrder/batchclearance') ?>",
			data: {
			    id: data.id,
			},
			dataType: "json",
			success: function(res) {
				if (res.code == 1) {
					console.log(res.url.file_name);
					var a = document.createElement('a');
					document.body.appendChild(a);
					a.href = res.url.file_name;
					a.click();
				}
			}
		})
	});
    
    /**
     * 批量手动更新物流信息
     */
    $('.j-wuliu').on('click', function () {
        var $tabs, data = $(this).data();
        console.log(data);
        $.showModal({
            title: '更新批次订单物流轨迹'
            , area: '460px'
            , content: template('tpl-wuliu', data)
            , uCheck: true
            , success: function ($content) {
            }
            , yes: function ($content) {
                $content.find('form').myAjaxSubmit({
                    url: '<?= url('store/batch/logistics') ?>',
                    data: {
                        batch_id:data.id,
                    }
                });
                return true;
            }
        });
    });
    
    /**
     * 状态变更
     */
    $('.j-status-change').on('click', function () {
        var $tabs, data = $(this).data();
        var statusMap = {0: '待发货', 1: '运送中', 2: '已到达'};
        
        $.showModal({
            title: '批次状态变更'
            , area: '400px'
            , content: template('tpl-status-change', data)
            , uCheck: true
            , success: function ($content) {
                // 设置当前状态显示
                $('#current-status-text').text(statusMap[data.status]);
                // 设置当前状态为选中
                $('#status-select').val(data.status);
            }
            , yes: function ($content) {
                var newStatus = $content.find('#status-select').val();
                if (newStatus == data.status) {
                    $.msg('请选择不同的状态', 'error');
                    return false;
                }
                
                $content.find('form').myAjaxSubmit({
                    url: '<?= url('store/batch/statuschange') ?>',
                    data: {
                        batch_id: data.id,
                        status: newStatus
                    }
                });
                return true;
            }
        });
    });
    
    $('#datetimepicker').datetimepicker({
      format: 'yyyy-mm-dd hh:ii'
    });
    
    $('#datetimepicker').datetimepicker().on('changeDate', function(ev){
        $('#datetimepicker').datetimepicker('hide');
    });
    
    /**
     * 发货功能
     */
    $('.j-shipment').on('click', function () {
        var $tabs, data = $(this).data();
        
        $.showModal({
            title: '批次发货'
            , area: '500px'
            , content: template('tpl-shipment', data)
            , uCheck: true
            , success: function ($content) {
                // 初始化运输方式切换
                onChangeShipment('c1');
            }
            , yes: function ($content) {
                var transfer = $content.find('input[name="transfer"]:checked').val();
                var expressNo = $content.find('input[name="express_no"]').val();
                
                if (!expressNo) {
                    $.msg('请输入运单号', 'error');
                    return false;
                }
                
                var carrierValue = '';
                if (transfer == 1) {
                    carrierValue = $content.find('select[name="tt_number"]').val();
                } else {
                    carrierValue = $content.find('select[name="t_number"]').val();
                }
                
                if (!carrierValue) {
                    $.msg('请选择承运商', 'error');
                    return false;
                }
                
                $content.find('form').myAjaxSubmit({
                    url: '<?= url('store/batch/shipment') ?>',
                    data: {
                        batch_id: data.id,
                        transfer: transfer,
                        carrier_value: carrierValue,
                        express_no: expressNo
                    }
                });
                return true;
            }
        });
    });
    
    /**
     * 发货弹窗运输方式切换
     */
    function onChangeShipment(tab) {
        $('.c').hide();
        $('#' + tab).show();
    }
</script>

