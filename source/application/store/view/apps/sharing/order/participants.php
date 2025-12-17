<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">
                        参与人员列表
                        <small class="tipssmall">(拼团订单：<?= $sharingOrder['title'] ?? $sharingOrder['order_id'] ?>)</small>
                    </div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12">
                        <div class="pack-toolbar" style="margin-bottom: 15px; padding: 12px; background: #f8f9fa; border-radius: 4px; border-left: 3px solid #0e90d2;">
                            <button type="button" class="am-btn am-btn-primary am-btn-sm" id="batch-pack-btn" style="display: none;">
                                <i class="am-icon-check-square-o"></i> <span id="pack-btn-text">代客户打包</span>
                            </button>
                            <span class="pack-tip" style="margin-left: 15px; color: #666; font-size: 13px;">
                                <i class="am-icon-info-circle"></i> 提示：请选择同一用户的包裹进行打包
                            </span>
                        </div>
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black">
                            <thead>
                            <tr>
                                <th width="30"><input type="checkbox" id="select-all-packages" /></th>
                                <th>序号</th>
                                <th>用户信息</th>
                                <th>集运单信息</th>
                                <th>包裹列表</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($participantsList)): ?>
                                <?php $index = 1; foreach ($participantsList as $participant): ?>
                                <tr>
                                    <td class="am-text-middle"></td>
                                    <td class="am-text-middle"><?= $index++ ?></td>
                                    <td class="am-text-middle">
                                        <?php if (!empty($participant['user_id'])): ?>
                                        用户ID: <?= $participant['user_id'] ?><br>
                                        <?php endif; ?>
                                        <?php if($setcode['is_show']!=0 && !empty($participant['user_code'])): ?>
                                        用户Code: <?= $participant['user_code'] ?><br>
                                        <?php endif; ?>
                                        昵称: <?= $participant['nickName'] ?><br>
                                        手机: <?= $participant['mobile'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if (!empty($participant['inpacks'])): ?>
                                            <?php foreach ($participant['inpacks'] as $inpack): ?>
                                                <div class="inpack-card" style="margin-bottom: 10px; padding: 10px; border: 1px solid #e0e0e0; border-radius: 4px; background: #fafafa; transition: all 0.3s;">
                                                    <div style="margin-bottom: 6px;">
                                                        <strong>订单号:</strong> 
                                                        <a href="<?= url('store/trOrder/orderdetail', ['id' => $inpack['id']]) ?>" target="_blank" style="color: #0e90d2; text-decoration: none;">
                                                            <?= $inpack['order_sn'] ?>
                                                        </a>
                                                    </div>
                                                    <div style="margin-bottom: 4px;">
                                                        <span class="status-badge status-<?= $inpack['status'] ?>" style="display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 12px; background: #e0e0e0; color: #666;">
                                                            <?= [1=>'待查验',2=>'待支付',3=>'已支付',4=>'已拣货',5=>'已打包',6=>'已发货',7=>'已收货',8=>'已完成',-1=>'问题件'][$inpack['status']] ?? '未知' ?>
                                                        </span>
                                                    </div>
                                                    <div style="font-size: 12px; color: #666; line-height: 1.6;">
                                                        <span style="margin-right: 10px;">重量: <strong><?= $inpack['weight'] ?></strong> Kg</span>
                                                        <span style="margin-right: 10px;">费用: <strong style="color: #f56c6c;"><?= $inpack['free'] ?></strong></span>
                                                        <span style="margin-right: 10px;">支付: <?= $inpack['is_pay']==1?'<span style="color: #67c23a;">已支付</span>':'<span style="color: #e6a23c;">未支付</span>' ?></span>
                                                    </div>
                                                    <div style="font-size: 11px; color: #999; margin-top: 4px;">
                                                        <?= $inpack['created_time'] ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: #999;">暂无集运单</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if (!empty($participant['packages'])): ?>
                                            <div class="package-table-wrapper" style="max-height: 400px; overflow-y: auto;">
                                                <table class="am-table am-table-compact am-table-bordered package-table" style="margin: 0; font-size: 12px;">
                                                    <thead>
                                                    <tr style="background: #f5f5f5;">
                                                        <th width="35" style="text-align: center;">选择</th>
                                                        <th>快递单号</th>
                                                        <th width="80">重量</th>
                                                        <th width="100">状态</th>
                                                        <th>备注</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($participant['packages'] as $package): ?>
                                                    <?php 
                                                        // 判断包裹是否可以打包：状态为2(已入库)、3(已拣货上架)、4(待打包)、7(已分拣下架)且未被打包
                                                        $canPack = in_array($package['status'], [2, 3, 4, 7]) && empty($package['inpack_id']);
                                                        $statusText = [1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成',-1=>'问题件'][$package['status']] ?? '未知';
                                                        $statusClass = 'status-' . $package['status'];
                                                    ?>
                                                    <tr class="package-row <?= $canPack ? 'can-pack' : '' ?>" style="transition: background 0.2s;">
                                                        <td style="text-align: center;">
                                                            <?php if ($canPack): ?>
                                                                <input type="checkbox" class="package-checkbox" 
                                                                       data-package-id="<?= $package['id'] ?>" 
                                                                       data-user-id="<?= $participant['user_id'] ?>"
                                                                       value="<?= $package['id'] ?>" />
                                                            <?php else: ?>
                                                                <span style="color: #ccc;">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td style="font-family: monospace; color: #333;"><?= $package['express_num'] ?></td>
                                                        <td style="text-align: right; color: #666;"><?= $package['weight'] ?> Kg</td>
                                                        <td>
                                                            <span class="package-status-badge <?= $statusClass ?>" style="display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 11px; background: #e0e0e0; color: #666;">
                                                                <?= $statusText ?>
                                                            </span>
                                                        </td>
                                                        <td style="color: #999; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($package['remark']) ?>">
                                                            <?= $package['remark'] ?: '-' ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div style="margin-top: 8px; padding: 6px; background: #f0f0f0; border-radius: 3px; font-size: 12px;">
                                                <i class="am-icon-cube"></i> 包裹总数: <strong><?= count($participant['packages']) ?></strong>
                                                <span style="margin-left: 15px; color: #0e90d2;">
                                                    可打包: <strong id="packable-count-<?= $participant['user_id'] ?>"><?= count(array_filter($participant['packages'], function($p) { return in_array($p['status'], [2, 3, 4, 7]) && empty($p['inpack_id']); })) ?></strong>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #999;">暂无包裹</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if (!empty($participant['inpacks'])): ?>
                                            <?php foreach ($participant['inpacks'] as $inpack): ?>
                                                <a href="<?= url('store/trOrder/package', ['id' => $inpack['id']]) ?>" class="am-btn am-btn-xs am-btn-primary" target="_blank">
                                                    查看包裹明细
                                                </a><br>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="am-text-center">暂无参与人员</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 打包弹窗 -->
<div class="am-modal am-modal-confirm" tabindex="-1" id="pack-modal">
    <div class="am-modal-dialog pack-modal-dialog" style="max-width: 600px;">
        <div class="am-modal-hd" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 15px 20px; border-radius: 4px 4px 0 0;">
            <i class="am-icon-cube"></i> 代客户打包
        </div>
        <div class="am-modal-bd" style="padding: 20px;">
            <form class="am-form" id="pack-form">
                <input type="hidden" name="share_id" value="<?= $shareId ?>" />
                <div class="am-form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="am-icon-truck" style="color: #0e90d2;"></i> 选择线路 <span style="color: #f56c6c;">*</span>
                    </label>
                    <select name="line_id" id="line_id" required class="am-form-field" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">请选择线路</option>
                        <?php if (!empty($sharingOrder['line_id'])): ?>
                            <?php foreach ($lineList as $line): ?>
                                <?php if ($line['id'] == $sharingOrder['line_id']): ?>
                                    <option value="<?= $line['id'] ?>" selected><?= $line['name'] ?> (拼团线路)</option>
                                <?php else: ?>
                                    <option value="<?= $line['id'] ?>"><?= $line['name'] ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($lineList as $line): ?>
                                <option value="<?= $line['id'] ?>"><?= $line['name'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="am-form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="am-icon-map-marker" style="color: #67c23a;"></i> 选择地址 <span style="color: #f56c6c;">*</span>
                    </label>
                    <select name="address_id" id="address_id" required class="am-form-field" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">请选择地址</option>
                    </select>
                    <div id="address-loading" style="display: none; margin-top: 8px; color: #0e90d2;">
                        <i class="am-icon-spinner am-icon-spin"></i> 加载中...
                    </div>
                </div>
                <div class="am-form-group" style="margin-bottom: 0;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="am-icon-comment-o" style="color: #e6a23c;"></i> 备注信息
                    </label>
                    <textarea name="remark" rows="3" placeholder="请输入备注信息（选填）" class="am-form-field" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"></textarea>
                </div>
            </form>
        </div>
        <div class="am-modal-footer" style="padding: 15px 20px; border-top: 1px solid #eee; text-align: right;">
            <span class="am-modal-btn" data-am-modal-cancel style="margin-right: 10px; padding: 8px 20px; background: #f5f5f5; color: #666; border-radius: 4px; cursor: pointer;">取消</span>
            <span class="am-modal-btn" data-am-modal-confirm style="padding: 8px 20px; background: #0e90d2; color: #fff; border-radius: 4px; cursor: pointer;">确定打包</span>
        </div>
    </div>
</div>

<script>
(function() {
    // 全选/取消全选
    $('#select-all-packages').on('change', function() {
        $('.package-checkbox').prop('checked', $(this).prop('checked'));
        updatePackButton();
    });
    
    // 单个复选框变化
    $(document).on('change', '.package-checkbox', function() {
        updatePackButton();
        // 如果取消选中，取消全选
        if (!$(this).prop('checked')) {
            $('#select-all-packages').prop('checked', false);
        }
    });
    
    // 更新打包按钮显示状态
    function updatePackButton() {
        var checkedCount = $('.package-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#batch-pack-btn').show();
            $('#pack-btn-text').html('代客户打包 <span style="background: rgba(255,255,255,0.3); padding: 2px 6px; border-radius: 10px; margin-left: 5px;">' + checkedCount + '</span>');
        } else {
            $('#batch-pack-btn').hide();
        }
    }
    
    // 打包按钮点击
    $('#batch-pack-btn').on('click', function() {
        var selectedPackages = [];
        $('.package-checkbox:checked').each(function() {
            selectedPackages.push($(this).val());
        });
        
        if (selectedPackages.length === 0) {
            layer.msg('请选择要打包的包裹', {icon: 2});
            return;
        }
        
        // 检查是否选择了同一用户的包裹
        var userIds = [];
        $('.package-checkbox:checked').each(function() {
            var userId = $(this).data('user-id');
            if (userIds.indexOf(userId) === -1) {
                userIds.push(userId);
            }
        });
        
        if (userIds.length > 1) {
            layer.msg('请选择同一用户的包裹进行打包', {icon: 2});
            return;
        }
        
        // 加载线路和地址列表
        loadLinesAndAddresses(userIds[0]);
        
        // 显示弹窗
        $('#pack-modal').modal({
            relatedTarget: this,
            onConfirm: function(options) {
                var result = submitPack(selectedPackages);
                if (!result) {
                    // 如果验证失败，阻止弹窗关闭
                    return false;
                }
            },
            onCancel: function() {
                // 取消操作
            }
        });
    });
    
    // 加载地址列表
    function loadLinesAndAddresses(userId) {
        $('#address-loading').show();
        $('#address_id').html('<option value="">加载中...</option>');
        
        $.ajax({
            url: '<?= url("store/apps.sharing.order/getUserAddresses") ?>',
            type: 'GET',
            data: { user_id: userId },
            dataType: 'json',
            success: function(res) {
                $('#address-loading').hide();
                if (res.code === 1 && res.data && res.data.length > 0) {
                    var html = '<option value="">请选择地址</option>';
                    var sharingAddressId = <?= !empty($sharingOrder['address_id']) ? $sharingOrder['address_id'] : 0 ?>;
                    
                    res.data.forEach(function(address) {
                        var selected = (address.address_id == sharingAddressId) ? ' selected' : '';
                        var label = address.name + ' - ' + address.phone;
                        if (address.address_id == sharingAddressId) {
                            label += ' (拼团地址)';
                        }
                        html += '<option value="' + address.address_id + '"' + selected + '>' + label + '</option>';
                    });
                    
                    $('#address_id').html(html);
                } else {
                    $('#address_id').html('<option value="">该用户暂无地址</option>');
                }
            },
            error: function() {
                $('#address-loading').hide();
                $('#address_id').html('<option value="">加载失败</option>');
            }
        });
    }
    
    // 提交打包
    function submitPack(packageIds) {
        var formData = {
            package_ids: packageIds.join(','),
            share_id: $('input[name="share_id"]').val(),
            line_id: $('#line_id').val(),
            address_id: $('#address_id').val(),
            remark: $('textarea[name="remark"]').val()
        };
        
        if (!formData.line_id) {
            layer.msg('请选择线路', {icon: 2});
            return false;
        }
        
        if (!formData.address_id) {
            layer.msg('请选择地址', {icon: 2});
            return false;
        }
        
        layer.load(2);
        
        $.ajax({
            url: '<?= url("store/apps.sharing.order/packForCustomer") ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                layer.closeAll('loading');
                if (res.code === 1) {
                    // 关闭弹窗
                    $('#pack-modal').modal('close');
                    layer.msg(res.msg, {icon: 1}, function() {
                        location.reload();
                    });
                } else {
                    layer.msg(res.msg, {icon: 2});
                }
            },
            error: function() {
                layer.closeAll('loading');
                layer.msg('网络错误，请重试', {icon: 2});
            }
        });
        
        // 返回true表示已处理，允许弹窗关闭（但实际关闭由success回调控制）
        return true;
    }
})();
</script>

<style>
/* 代客户打包样式优化 */
.pack-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

.pack-tip {
    display: inline-flex;
    align-items: center;
}

.pack-tip i {
    margin-right: 5px;
}

/* 集运单卡片样式 */
.inpack-card:hover {
    border-color: #0e90d2;
    box-shadow: 0 2px 8px rgba(14, 144, 210, 0.1);
    transform: translateY(-1px);
}

/* 状态标签颜色 */
.status-badge.status-1 { background: #e6a23c; color: #fff; } /* 待查验 */
.status-badge.status-2 { background: #f56c6c; color: #fff; } /* 待支付 */
.status-badge.status-3 { background: #67c23a; color: #fff; } /* 已支付 */
.status-badge.status-4 { background: #409eff; color: #fff; } /* 已拣货 */
.status-badge.status-5 { background: #909399; color: #fff; } /* 已打包 */
.status-badge.status-6 { background: #0e90d2; color: #fff; } /* 已发货 */
.status-badge.status-7 { background: #67c23a; color: #fff; } /* 已收货 */
.status-badge.status-8 { background: #909399; color: #fff; } /* 已完成 */
.status-badge.status--1 { background: #f56c6c; color: #fff; } /* 问题件 */

/* 包裹表格样式 */
.package-table-wrapper {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
}

.package-table thead th {
    position: sticky;
    top: 0;
    background: #f5f5f5;
    z-index: 10;
}

.package-row.can-pack:hover {
    background: #f0f9ff;
}

.package-row.can-pack td {
    cursor: pointer;
}

/* 包裹状态标签 */
.package-status-badge.status-2 { background: #e1f3d8; color: #67c23a; } /* 已入库 */
.package-status-badge.status-3 { background: #d4edda; color: #28a745; } /* 已拣货上架 */
.package-status-badge.status-4 { background: #fff3cd; color: #856404; } /* 待打包 */
.package-status-badge.status-7 { background: #d1ecf1; color: #0c5460; } /* 已分拣下架 */
.package-status-badge.status-5 { background: #f8d7da; color: #721c24; } /* 待支付 */
.package-status-badge.status-6 { background: #d4edda; color: #155724; } /* 已支付 */
.package-status-badge.status-8 { background: #d1ecf1; color: #0c5460; } /* 已打包 */
.package-status-badge.status-9 { background: #cce5ff; color: #004085; } /* 已发货 */
.package-status-badge.status-10 { background: #d4edda; color: #155724; } /* 已收货 */
.package-status-badge.status-11 { background: #e2e3e5; color: #383d41; } /* 已完成 */
.package-status-badge.status--1 { background: #f8d7da; color: #721c24; } /* 问题件 */

/* 弹窗样式优化 */
.pack-modal-dialog {
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.pack-modal-dialog .am-form-field:focus {
    border-color: #0e90d2;
    outline: none;
    box-shadow: 0 0 0 2px rgba(14, 144, 210, 0.1);
}

.pack-modal-dialog .am-modal-btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    transition: all 0.2s;
}

/* 复选框样式优化 */
.package-checkbox {
    cursor: pointer;
    width: 18px;
    height: 18px;
}

/* 响应式优化 */
@media (max-width: 768px) {
    .pack-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .pack-tip {
        margin-left: 0;
        margin-top: 10px;
    }
    
    .package-table-wrapper {
        font-size: 11px;
    }
}
</style>

