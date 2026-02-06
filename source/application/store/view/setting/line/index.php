<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">集运线路</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('setting.line/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('setting.line/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <?php if (checkPrivilege('setting.line/batchChangeStatus')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <button type="button" id="j-batch-open" class="am-btn am-btn-success am-radius">批量开启</button>
                                        <button type="button" id="j-batch-close" class="am-btn am-btn-default am-radius">批量关闭</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                    </div>

                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12">
                                <div class="am fl">
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="name"
                                                   placeholder="请输入渠道名称"
                                                   value="<?= $request->get('name') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th width="50px">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" id="checkAll" value="">
                                    </label>
                                </th>
                                <th width='80px'>线路ID</th>
                                <th>路线名称</th>
                                <th>运输形式</th>
                                <th>使用范围</th>
                                <th>支持会员等级</th>
                                <th>限重</th>
                                <th width='50px'>时效</th>
                                <th>关税</th>
                                <th width='600px'>限制条件</th>      
                                <th width='100px'>计费方式</th>
                                <th width='40px'>排序</th>
                                <th>状态</th>
                                <th width='90px'>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                          
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle">
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="item-checkbox" value="<?= $item['id'] ?>">
                                            </label>
                                        </td>
                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                        <td class="am-text-middle"><?= $item['name'] ?></td>
                                        <td class="am-text-middle"><?= $item['lineCategory']['name'] ?></td>
                                        <td class="am-text-middle">
                                            <?php 
                                            $linePosition = isset($item['line_position']) ? $item['line_position'] : 10;
                                            $positionMap = [
                                                10 => '拼邮',
                                                20 => '直邮',
                                                30 => '拼团',
                                                40 => '通用'
                                            ];
                                            echo isset($positionMap[$linePosition]) ? $positionMap[$linePosition] : '拼邮';
                                            ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <?php 
                                            $gradeId = isset($item['grade_id']) ? $item['grade_id'] : null;
                                            if ($gradeId === null || $gradeId == -1) {
                                                echo '所有用户都适用';
                                            } elseif ($gradeId == 0) {
                                                echo '仅普货会员适用';
                                            } elseif (!empty($item['grade']) && isset($item['grade']['name'])) {
                                                echo $item['grade']['name'];
                                            } elseif ($gradeId > 0) {
                                                // 如果关联数据未加载，尝试直接查询
                                                $grade = \app\common\model\user\Grade::get($gradeId);
                                                echo $grade ? $grade['name'] : '会员等级ID: ' . $gradeId;
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td class="am-text-middle"><?= $item['weight_min'] ?></td>
                                        <td class="am-text-middle"><?= $item['limitationofdelivery'] ?></td>
                                        <td class="am-text-middle"><?= $item['tariff'] ?></td>
                                        <td  class="am-text-middle">物品限制:<?= $item['goods_limit'] ?></br>长度限制:<?= $item['length_limit'] ?></br>重量限制:<?= $item['weight_limit'] ?></br></td>
                                        <td class="am-text-middle"><?= $item['free_mode'] ?></td>
                                        <td class="am-text-middle"><?= $item['sort'] ?></td>
                                        <td class="am-text-middle">
                                            <?php 
                                            $isOpen = isset($item['status']) && $item['status'] == 1;
                                            ?>
                                            <label class="toggle-switch <?= $isOpen ? 'active' : '' ?>" data-id="<?= $item['id'] ?>" data-status="<?= $isOpen ? 1 : 0 ?>">
                                                <input type="checkbox" <?= $isOpen ? 'checked' : '' ?>>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                        <td class="am-text-middle"><?= date('Y-m-d',$item['created_time']) ?></td>
                                        <td class="am-text-middle" width="180px">
                                            <div class="tpl-table-black-operation">
                                                 <?php if (checkPrivilege('setting.line/copyline')): ?>
                                                    <a href="javascript:;" class="tpl-table-black-operation-green fuyong" data-id="<?= $item['id'] ?>"> 
                                                        <i class="am-icon-pencil fuyong"></i> 复用
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('setting.line/edit')): ?>
                                                    <a href="<?= url('setting.line/edit',
                                                        ['id' => $item['id']]) ?>"
                                                         class="tpl-table-black-operation-green`">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('setting.line/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['id'] ?>">
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="15" class="am-text-center">暂无记录</td>
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
<style>
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
        cursor: pointer;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #d4d4d4;
        transition: all 0.3s ease;
        border-radius: 26px;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: all 0.3s ease;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    .toggle-switch.active .toggle-slider {
        background-color: #5eb95e;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .toggle-switch.active .toggle-slider:before {
        transform: translateX(24px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }
    .toggle-switch:hover .toggle-slider {
        background-color: #c0c0c0;
    }
    .toggle-switch.active:hover .toggle-slider {
        background-color: #4da84d;
    }
</style>
<script>
        $(function () {
            // 删除元素
            var url = "<?= url('store/setting.line/delete') ?>";
            $('.item-delete').delete('id', url);
            
            
            /**
             * 代用户打包
             */
            $('.fuyong').on('click', function () {
            var $tabs, data = $(this).data();
            var hedanurl = "<?= url('store/setting.line/copyline') ?>";
            layer.confirm('确认复用则会生成一条规则一样的集运路线哦！，为了方便用户选择，请修改路线名称或规则', {title: '复用路线'}
                    , function (index) {
                        $.post(hedanurl,data, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
            }); 
            
            /**
             * 全选/取消全选
             */
            $('#checkAll').on('change', function() {
                $('.item-checkbox').prop('checked', $(this).prop('checked'));
            });
            
            /**
             * 单选时检查是否全选
             */
            $(document).on('change', '.item-checkbox', function() {
                var total = $('.item-checkbox').length;
                var checked = $('.item-checkbox:checked').length;
                $('#checkAll').prop('checked', total === checked && total > 0);
            });
            
            /**
             * 批量开启
             */
            $('#j-batch-open').on('click', function() {
                var selectIds = [];
                $('.item-checkbox:checked').each(function() {
                    selectIds.push($(this).val());
                });
                
                if (selectIds.length === 0) {
                    layer.alert('请先选择要操作的线路', {icon: 5});
                    return;
                }
                
                layer.confirm('确定要开启选中的 ' + selectIds.length + ' 条线路吗？', {title: '批量开启'}, function(index) {
                    $.ajax({
                        type: "POST",
                        url: '<?= url('store/setting.line/batchChangeStatus') ?>',
                        data: {
                            ids: selectIds.join(','),
                            status: 1
                        },
                        dataType: "JSON",
                        success: function(result) {
                            layer.close(index);
                            if (result.code === 1) {
                                layer.msg(result.msg, {icon: 1, time: 1000}, function() {
                                    location.reload();
                                });
                            } else {
                                layer.msg(result.msg || '操作失败', {icon: 2});
                            }
                        },
                        error: function() {
                            layer.close(index);
                            layer.msg('网络错误，请重试', {icon: 2});
                        }
                    });
                });
            });
            
            /**
             * 批量关闭
             */
            $('#j-batch-close').on('click', function() {
                var selectIds = [];
                $('.item-checkbox:checked').each(function() {
                    selectIds.push($(this).val());
                });
                
                if (selectIds.length === 0) {
                    layer.alert('请先选择要操作的线路', {icon: 5});
                    return;
                }
                
                layer.confirm('确定要关闭选中的 ' + selectIds.length + ' 条线路吗？', {title: '批量关闭'}, function(index) {
                    $.ajax({
                        type: "POST",
                        url: '<?= url('store/setting.line/batchChangeStatus') ?>',
                        data: {
                            ids: selectIds.join(','),
                            status: 0
                        },
                        dataType: "JSON",
                        success: function(result) {
                            layer.close(index);
                            if (result.code === 1) {
                                layer.msg(result.msg, {icon: 1, time: 1000}, function() {
                                    location.reload();
                                });
                            } else {
                                layer.msg(result.msg || '操作失败', {icon: 2});
                            }
                        },
                        error: function() {
                            layer.close(index);
                            layer.msg('网络错误，请重试', {icon: 2});
                        }
                    });
                });
            });
            
            /**
             * 切换状态开关
             */
            $('.toggle-switch').on('click', function (e) {
                e.preventDefault();
                var $switch = $(this);
                var $checkbox = $switch.find('input');
                var id = $switch.data('id');
                var currentStatus = $switch.data('status');
                var newStatus = currentStatus == 1 ? 0 : 1;
                
                // 禁用开关，防止重复点击
                $switch.css('pointer-events', 'none');
                
                $.ajax({
                    type: "POST",
                    url: '<?= url('store/setting.line/changeStatus') ?>',
                    data: {
                        id: id,
                        status: newStatus
                    },
                    dataType: "JSON",
                    success: function(result) {
                        if (result.code === 1) {
                            // 更新开关状态
                            if (newStatus == 1) {
                                $switch.addClass('active');
                                $checkbox.prop('checked', true);
                            } else {
                                $switch.removeClass('active');
                                $checkbox.prop('checked', false);
                            }
                            $switch.data('status', newStatus);
                            layer.msg(result.msg, {icon: 1, time: 1000});
                        } else {
                            // 失败时恢复原状态
                            if (currentStatus == 1) {
                                $switch.addClass('active');
                                $checkbox.prop('checked', true);
                            } else {
                                $switch.removeClass('active');
                                $checkbox.prop('checked', false);
                            }
                            layer.msg(result.msg || '操作失败', {icon: 2});
                        }
                        // 恢复开关状态
                        $switch.css('pointer-events', 'auto');
                    },
                    error: function() {
                        // 失败时恢复原状态
                        if (currentStatus == 1) {
                            $switch.addClass('active');
                            $checkbox.prop('checked', true);
                        } else {
                            $switch.removeClass('active');
                            $checkbox.prop('checked', false);
                        }
                        layer.msg('网络错误，请重试', {icon: 2});
                        // 恢复开关状态
                        $switch.css('pointer-events', 'auto');
                    }
                });
            });

        });
</script>

