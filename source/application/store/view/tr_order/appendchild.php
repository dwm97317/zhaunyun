<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" action="<?= url('/store/tr_order/add') ?>" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加包裹</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 订单号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <p class="am-form-static"><?= $model['order_sn'] ?></p>
                                </div>
                            </div>
                            <!-- 待打包包裹列表 -->
                            <?php if (!empty($pending_packages)): ?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 待打包包裹 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-panel am-panel-default">
                                        <div class="am-panel-hd">
                                            <h3 class="am-panel-title" style="display: flex; align-items: left; justify-content: flex-start;">
                                                <input type="checkbox" id="select-all-packages" class="am-checkbox"> 
                                                <span>全选/取消全选</span>
                                                
                                            </h3>
                                        </div>
                                        <div class="am-panel-bd">
                                            <div class="am-scrollable-horizontal">
                                                <table class="am-table am-table-striped am-table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th width="50">选择</th>
                                                            <th width="250">快递单号</th>
                                                            <th width="80">重量(kg)</th>
                                                            <th width="200">尺寸(cm)</th>
                                                            <th width="200">入库时间</th>
                                                            <th width="100">用户备注</th>
                                                            <th>系统备注</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($pending_packages as $package): ?>
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" name="selected_packages[]" 
                                                                       value="<?= $package['id'] ?>" 
                                                                       class="am-checkbox package-checkbox"
                                                                       data-express-num="<?= $package['express_num'] ?>">
                                                            </td>
                                                            <td><?= $package['express_num'] ?></td>
                                                            <td><?= $package['weight'] ?></td>
                                                            <td><?= $package['length'] ?>×<?= $package['width'] ?>×<?= $package['height'] ?></td>
                                                            <td><?= date('Y-m-d H:i', strtotime($package['entering_warehouse_time'])) ?></td>
                                                            <td><?= $package['usermark'] ?: '-' ?></td>
                                                            <td><?= $package['remark'] ?: '-' ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="am-text-xs am-text-info">提示：勾选包裹后点击"添加选中包裹"按钮，或手动输入快递单号</small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3">
                                    <button type="button" id="add-selected-packages" class="am-btn am-btn-primary am-btn-sm">
                                        添加选中包裹
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 快递单号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <textarea class="tpl-form-input" name="delivery[express_num]" id="express-num-input" rows="4"
                                              placeholder="请输入快递单号，多个单号可用逗号或换行分隔"></textarea>
                                    <small class="am-text-xs am-text-warning">提示：多个快递单号请用逗号(,)或换行分隔</small>
                                </div>
                            </div>
                          
                            <div class="am-form-group">
                                <input type="hidden" name="delivery[id]" value="<?= $model['id'] ?>"/>
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                            
                            <!-- 隐藏字段用于提交选中的包裹ID -->
                            <div id="selected-packages-container"></div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/store/js/select.region.js?v=1.2"></script>
<script>
    /**
     * 设置坐标
     */
    function setCoordinate(value) {
        var $coordinate = $('#coordinate');
        $coordinate.val(value);
        // 触发验证
        $coordinate.trigger('change');
    }
</script>
<script>
    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        // 全选/取消全选功能
        $('#select-all-packages').on('change', function() {
            $('.package-checkbox').prop('checked', $(this).prop('checked'));
        });

        // 单个复选框变化时更新全选状态
        $('.package-checkbox').on('change', function() {
            var totalCheckboxes = $('.package-checkbox').length;
            var checkedCheckboxes = $('.package-checkbox:checked').length;
            $('#select-all-packages').prop('checked', totalCheckboxes === checkedCheckboxes);
        });

        // 添加选中包裹功能
        $('#add-selected-packages').on('click', function() {
            var selectedPackages = $('.package-checkbox:checked');
            if (selectedPackages.length === 0) {
                alert('请先选择要添加的包裹');
                return;
            }

            var expressNums = [];
            var packageIds = [];
            selectedPackages.each(function() {
                expressNums.push($(this).data('express-num'));
                packageIds.push($(this).val());
            });

            // 将选中的快递单号添加到输入框
            var currentValue = $('#express-num-input').val();
            var newValue = expressNums.join('\n');
            
            if (currentValue) {
                $('#express-num-input').val(currentValue + '\n' + newValue);
            } else {
                $('#express-num-input').val(newValue);
            }

            // 将选中的包裹ID添加到隐藏字段
            updateSelectedPackagesHiddenFields(packageIds);

            // 取消所有选中状态
            $('.package-checkbox').prop('checked', false);
            $('#select-all-packages').prop('checked', false);

            // 显示成功提示
            alert('已添加 ' + selectedPackages.length + ' 个包裹到快递单号输入框');
        });

        // 更新隐藏字段中的选中包裹ID
        function updateSelectedPackagesHiddenFields(packageIds) {
            var container = $('#selected-packages-container');
            container.empty();
            
            packageIds.forEach(function(packageId) {
                container.append('<input type="hidden" name="delivery[selected_packages][]" value="' + packageId + '">');
            });
        }

        // 表单提交前确保选中包裹也被提交
        $('#my-form').on('submit', function() {
            var selectedPackages = $('.package-checkbox:checked');
            if (selectedPackages.length > 0) {
                var packageIds = [];
                selectedPackages.each(function() {
                    packageIds.push($(this).val());
                });
                updateSelectedPackagesHiddenFields(packageIds);
            }
        });

    });
</script>
