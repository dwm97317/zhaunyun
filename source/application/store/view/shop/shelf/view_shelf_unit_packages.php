<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">货位包裹列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 货位信息 -->
                    <div class="am-u-sm-12 am-margin-bottom">
                        <div class="am-panel am-panel-default">
                            <div class="am-panel-bd" style="padding: 15px;">
                                <div class="am-g">
                                    <div class="am-u-sm-3">
                                        <strong>货位ID：</strong><?= $shelfUnit['shelf_unit_id'] ?>
                                    </div>
                                    <div class="am-u-sm-3">
                                        <strong>货位编号：</strong><?= $shelfUnit['shelf_unit_no'] ?>
                                    </div>
                                    <div class="am-u-sm-3">
                                        <strong>所属货架：</strong><?= $shelfUnit['shelf']['shelf_name'] ?? '-' ?>
                                    </div>
                                    <div class="am-u-sm-3">
                                        <strong>包裹数量：</strong><span style="color: #0e90d2; font-size: 16px; font-weight: bold;"><?= count($packageList) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    
                    <!-- 操作按钮和搜索筛选 -->
                    <div class="am-u-sm-12 am-margin-bottom-xs">
                        <div class="am-cf">
                            <!-- 左侧操作按钮 -->
                            <div class="am-fl">
                                <a href="javascript:history.back();" class="am-btn am-btn-default am-btn-sm">
                                    <i class="am-icon-arrow-left"></i> 返回货位列表
                                </a>
                                <button type="button" id="j-batch-delete" class="am-btn am-btn-danger am-btn-sm">
                                    <i class="am-icon-trash"></i> 批量下架
                                </button>
                            </div>
                            <!-- 右侧搜索筛选 -->
                            <div class="am-fl" style="margin-left: 15px;">
                                <form class="am-form-inline" method="get" action="" style="margin-bottom: 0;">
                                    <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                                    <input type="hidden" name="shelf_unit_id" value="<?= $shelfUnit['shelf_unit_id'] ?>">
                                    <div class="am-form-group" style="margin-bottom: 0;">
                                        <?php $filterStatus = $request->get('status'); ?>
                                        <select name="status" class="am-form-field am-input-sm" style="width: 150px;">
                                            <option value="">全部状态</option>
                                            <option value="1" <?= $filterStatus === '1' ? 'selected' : '' ?>>未入库</option>
                                            <option value="2" <?= $filterStatus === '2' ? 'selected' : '' ?>>已入库</option>
                                            <option value="3" <?= $filterStatus === '3' ? 'selected' : '' ?>>已上货架</option>
                                            <option value="4" <?= $filterStatus === '4' ? 'selected' : '' ?>>待打包</option>
                                            <option value="5" <?= $filterStatus === '5' ? 'selected' : '' ?>>待支付</option>
                                            <option value="6" <?= $filterStatus === '6' ? 'selected' : '' ?>>已支付</option>
                                            <option value="7" <?= $filterStatus === '7' ? 'selected' : '' ?>>已加入批次</option>
                                            <option value="8" <?= $filterStatus === '8' ? 'selected' : '' ?>>已打包</option>
                                            <option value="9" <?= $filterStatus === '9' ? 'selected' : '' ?>>已发货</option>
                                            <option value="10" <?= $filterStatus === '10' ? 'selected' : '' ?>>已收货</option>
                                            <option value="11" <?= $filterStatus === '11' ? 'selected' : '' ?>>已完成</option>
                                            <option value="-1" <?= $filterStatus === '-1' ? 'selected' : '' ?>>问题件</option>
                                        </select>
                                    </div>
                                    <div class="am-form-group" style="margin-bottom: 0;">
                                        <button type="submit" class="am-btn am-btn-primary am-btn-sm">
                                            <i class="am-icon-search"></i> 搜索
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


                    <!-- 包裹列表 -->
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black am-table-hover">
                            <thead>
                            <tr>
                                <th width="40"><input id="checkAll" type="checkbox"></th>
                                <th width="60">序号</th>
                                <th>快递单号</th>
                                <th>用户昵称</th>
                                <th>用户标识</th>
                                <th>包裹状态</th>
                                <th>上架时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="package-tbody">
                            <?php 
                                // 包裹状态映射
                                $statusText = [
                                    1 => '未入库',
                                    2 => '已入库',
                                    3 => '已上货架',
                                    4 => '待打包',
                                    5 => '待支付',
                                    6 => '已支付',
                                    7 => '已加入批次',
                                    8 => '已打包',
                                    9 => '已发货',
                                    10 => '已收货',
                                    11 => '已完成',
                                    -1 => '问题件',
                                    '' => '无状态'
                                ];
                                $statusColor = [
                                    1 => '#999',         // 未入库 - 灰色
                                    2 => '#0e90d2',      // 已入库 - 蓝色
                                    3 => '#0e90d2',      // 已上货架 - 蓝色
                                    4 => '#f37b1d',      // 待打包 - 橙色
                                    5 => '#f37b1d',      // 待支付 - 橙色
                                    6 => '#5eb95e',      // 已支付 - 绿色
                                    7 => '#0e90d2',      // 已加入批次 - 蓝色
                                    8 => '#0e90d2',      // 已打包 - 蓝色
                                    9 => '#5eb95e',      // 已发货 - 绿色
                                    10 => '#5eb95e',     // 已收货 - 绿色
                                    11 => '#5eb95e',     // 已完成 - 绿色
                                    -1 => '#dd514c',     // 问题件 - 红色
                                    '' => '#999'         // 无状态 - 灰色
                                ];
                            ?>
                            <?php if (count($packageList) > 0): ?>
                                <?php foreach ($packageList as $index => $item): ?>
                                    <tr>
                                        <td class="am-text-middle">
                                            <?php if (isset($item['package']['id'])): ?>
                                                <input name="checkIds" type="checkbox" value="<?= $item['package']['id'] ?>">
                                            <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle"><?= $index + 1 ?></td>
                                        <td class="am-text-middle">
                                            <strong style="color: #333;"><?= $item['express_num'] ?></strong>
                                        </td>
                                        <td class="am-text-middle">
                                            <?= $item['user']['nickName'] ?? '-' ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <?php if (isset($item['user'])): ?>
                                                <?php if($set['usercode_mode']['is_show']==1) :?>
                                                    <span><?= $item['user']['user_code'] ?? '-' ?></span>
                                                <?php else: ?>
                                                    <span>ID: <?= $item['user']['user_id'] ?? '-' ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <?php 
                                                $status = $item['package']['status'] ?? 0;
                                                $color = $statusColor[$status] ?? '#999';
                                                $text = $statusText[$status] ?? '未知';
                                            ?>
                                            <span style="color: <?= $color ?>; font-weight: bold;">
                                                <?= $text ?>
                                            </span>
                                        </td>
                                        <td class="am-text-middle">
                                           <?= $item['created_time'] ?? '-' ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (isset($item['package']['id'])): ?>
                                                    <a href="javascript:;" 
                                                       class="tpl-table-black-operation-del j-delete-package"
                                                       data-pack-id="<?= $item['package']['id'] ?>"
                                                       data-express-num="<?= $item['express_num'] ?>">
                                                        <i class="am-icon-trash"></i> 删除下架
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="am-text-center" style="padding: 40px 0; color: #999;">
                                        <i class="am-icon-inbox" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
                                        暂无包裹
                                    </td>
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

<script>
    $(function () {
        // 全选/取消全选功能
        var checker = {
            check: [],
            num: 0,
            init: function() {
                this.check = document.getElementById('package-tbody').querySelectorAll('input[name="checkIds"]');
                this.num = this.check.length;
                this.bindEvent();
            },
            bindEvent: function() {
                var that = this;
                // 单个复选框点击事件
                for(var i = 0; i < this.check.length; i++) {
                    this.check[i].onclick = function() {
                        var isFullCheck = that.isFullCheck();
                        document.getElementById('checkAll').checked = isFullCheck;
                    }
                }
                // 全选复选框点击事件
                var allCheck = document.getElementById('checkAll');
                if (allCheck) {
                    allCheck.onclick = function() {
                        that.setFullCheck(this.checked);
                    }
                }
            },
            setFullCheck: function(checked) {
                for (var i = 0; i < this.num; i++) {
                    this.check[i].checked = checked;
                }
            },
            isFullCheck: function() {
                var hasCheck = 0;
                for (var k = 0; k < this.num; k++) {
                    if (this.check[k].checked) {
                        hasCheck++;
                    }
                }
                return hasCheck == this.num && this.num > 0;
            },
            getCheckSelect: function() {
                var selectIds = [];
                this.check = document.getElementById('package-tbody').querySelectorAll('input[name="checkIds"]');
                for (var i = 0; i < this.check.length; i++) {
                    if (this.check[i].checked) {
                        selectIds.push(this.check[i].value);
                    }
                }
                return selectIds;
            }
        };
        
        // 初始化选择器
        if (document.getElementById('package-tbody').querySelectorAll('input[name="checkIds"]').length > 0) {
            checker.init();
        }
        
        // 批量删除
        $('#j-batch-delete').on('click', function() {
            var selectIds = checker.getCheckSelect();
            if (selectIds.length == 0) {
                layer.alert('请先选择要下架的包裹', {icon: 5});
                return;
            }
            
            layer.confirm('确认要将选中的 <strong>' + selectIds.length + '</strong> 个包裹从货位下架吗？', {
                icon: 3,
                title: '批量下架确认',
                btn: ['确认', '取消']
            }, function(index) {
                $.ajax({
                    type: 'POST',
                    url: '<?= url("store/shop.shelf/batchDeletePackagesFromShelf") ?>',
                    data: {  selectIds },
                    dataType: 'json',
                    success: function(res) {
                        if (res.code == 1) {
                            layer.msg('批量下架成功', {icon: 1});
                            // 删除选中的行
                            selectIds.forEach(function(id) {
                                $('input[name="checkIds"][value="' + id + '"]').closest('tr').fadeOut(300, function() {
                                    $(this).remove();
                                    updatePackageCount();
                                    // 如果没有包裹了，显示空状态
                                    if ($('#package-tbody tr').length == 0) {
                                        $('#package-tbody').html('<tr><td colspan="8" class="am-text-center" style="padding: 40px 0; color: #999;"><i class="am-icon-inbox" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>暂无包裹</td></tr>');
                                    }
                                });
                            });
                            // 取消全选
                            document.getElementById('checkAll').checked = false;
                        } else {
                            layer.msg(res.msg || '批量下架失败', {icon: 5});
                        }
                        layer.close(index);
                    },
                    error: function() {
                        layer.msg('网络请求失败，请稍后重试', {icon: 5});
                        layer.close(index);
                    }
                });
            });
        });
        
        // 单个删除包裹
        $(document).on('click', '.j-delete-package', function() {
            var packId = $(this).data('pack-id');
            var expressNum = $(this).data('express-num');
            var $row = $(this).closest('tr');
            
            layer.confirm('确认要将快递单号 <strong>' + expressNum + '</strong> 从货位下架吗？', {
                icon: 3,
                title: '提示',
                btn: ['确认', '取消']
            }, function(index) {
                $.ajax({
                    type: 'POST',
                    url: '<?= url("store/shop.shelf/deletePackageFromShelf") ?>',
                    data: { pack_id: packId },
                    dataType: 'json',
                    success: function(res) {
                        if (res.code == 1) {
                            layer.msg('下架成功', {icon: 1});
                            // 删除该行
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                // 更新包裹数量
                                updatePackageCount();
                                // 如果没有包裹了，显示空状态
                                if ($('#package-tbody tr').length == 0) {
                                    $('#package-tbody').html('<tr><td colspan="7" class="am-text-center" style="padding: 40px 0; color: #999;"><i class="am-icon-inbox" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>暂无包裹</td></tr>');
                                }
                            });
                        } else {
                            layer.msg(res.msg || '删除失败', {icon: 5});
                        }
                        layer.close(index);
                    },
                    error: function() {
                        layer.msg('网络请求失败，请稍后重试', {icon: 5});
                        layer.close(index);
                    }
                });
            });
        });
        
        // 更新包裹数量
        function updatePackageCount() {
            var count = $('#package-tbody tr').length;
            $('.am-panel-bd .am-u-sm-3:last span').text(count);
            // 重新编号
            $('#package-tbody tr').each(function(index) {
                $(this).find('td:eq(1)').text(index + 1);
            });
        }
    });
</script>

