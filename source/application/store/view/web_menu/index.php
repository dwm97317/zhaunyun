<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-fl">网站菜单管理</div>
                    <div class="widget-function am-fr">
                        <a href="javascript:;" class="am-icon-refresh" title="刷新" onclick="window.location.reload()"></a>
                    </div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <div class="am-btn-group am-btn-group-xs">
                                    <a class="j-add-menu am-btn am-btn-default am-btn-success am-radius"
                                       href="javascript:void(0);">
                                        <span class="am-icon-plus"></span> 添加一级菜单
                                    </a>
                                    <button type="button" class="j-save-sort am-btn am-btn-default am-btn-secondary am-radius">
                                        <span class="am-icon-save"></span> 保存排序
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 菜单列表展示 -->
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>菜单名称</th>
                                <th>菜单类型</th>
                             
                                <th>排序</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="menu-list">
                            <?php if (!empty($list)): ?>
                                <?php foreach ($list as $item): ?>
                                <tr data-id="<?= $item['id'] ?>">
                                    <td class="am-text-middle">
                                        <i class="am-icon-bars"></i> <?= $item['name'] ?>
                                    </td>
                                 
                                    <td class="am-text-middle"><?= $typeMap[$item['type']] ?? '-' ?></td>
                                   
                                    <td class="am-text-middle">
                                        <input type="number" class="am-input-sm sort-input" 
                                               value="<?= $item['sort'] ?>" 
                                               data-id="<?= $item['id'] ?>">
                                    </td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <a href="javascript:;" class="j-edit" data-id="<?= $item['id'] ?>"> 
                                                <i class="am-icon-pencil"></i> 编辑
                                            </a>
                                            <a href="javascript:;" class="item-delete tpl-table-black-operation-del" 
                                               data-id="<?= $item['id'] ?>">
                                                <i class="am-icon-trash"></i> 删除
                                            </a>
                                            <?php if (count($item['children'] ?? []) < 5): ?>
                                            <a href="javascript:;" class="j-add-submenu" data-parent="<?= $item['id'] ?>">
                                                <i class="am-icon-plus"></i> 添加子菜单
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- 子菜单 -->
                                <?php if (!empty($item['children'])): ?>
                                    <?php foreach ($item['children'] as $child): ?>
                                    <tr data-id="<?= $child['id'] ?>">
                                        <td class="am-text-middle">
                                            &nbsp;&nbsp;&nbsp;&nbsp;├ <?= $child['name'] ?>
                                        </td>
                                        <td class="am-text-middle"><?= $typeMap[$child['type']] ?? '-' ?></td>
                                       
                                        <td class="am-text-middle">
                                            <input type="number" class="am-input-sm sort-input" 
                                                   value="<?= $child['sort'] ?>" 
                                                   data-id="<?= $child['id'] ?>">
                                        </td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <a href="javascript:;" class="j-edit" data-id="<?= $child['id'] ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del" 
                                                   data-id="<?= $child['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="am-text-center">暂无菜单数据，请添加菜单</td>
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

<!-- 添加/编辑菜单模板 -->
<script id="tpl-menu-form" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form id="menu-form" class="am-form tpl-form-line-form" method="post" action="">
            <input type="hidden" name="id" value="{{ id }}">
            <input type="hidden" name="parent_id" value="{{ parent_id }}">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">菜单名称</label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="name" value="{{ name }}" placeholder="请输入菜单名称" required>
                        <small class="help-block">菜单显示名称，建议不超过10个字符</small>
                    </div>
                </div>
                
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">菜单类型</label>
                    <div class="am-u-sm-8 am-u-end">
                        <select name="type" data-am-selected="{btnSize: 'sm'}" class="j-menu-type">
                            {{ each typeMap value key }}
                            <option value="{{ key }}" {{ type==key?'selected':'' }}>{{ value }}</option>
                            {{ /each }}
                        </select>
                    </div>
                </div>
                
                <!-- 单页/列表配置 -->
                <div class="am-form-group j-config-group {{ (type!=10 && type!=20)?'am-hide':'' }}" data-type="10,20">
                    <label class="am-u-sm-3 am-form-label form-require">内容选择</label>
                    <div class="am-u-sm-8 am-u-end">
                        <div class="am-btn-group">
                            <button type="button" class="am-btn am-btn-default am-radius j-select-content">
                                <i class="am-icon-folder-open"></i> 选择内容
                            </button>
                            <button type="button" class="am-btn am-btn-default am-radius j-clear-content">
                                <i class="am-icon-trash"></i> 清除
                            </button>
                        </div>
                        <input type="hidden" name="link_id" value="{{ link_id }}">
                        <div id="contentPreview" class="am-margin-top-sm">
                            {{ if link_id && (type==10 || type==20) }}
                            <div class="am-alert am-alert-success">
                                已选择内容ID: {{ link_id }}
                            </div>
                            {{ /if }}
                        </div>
                        <small class="help-block">从内容库中选择关联内容</small>
                    </div>
                </div>
                
                <!-- 自定义链接配置 -->
                <div class="am-form-group j-config-group {{ type!=50?'am-hide':'' }}" data-type="50">
                    <label class="am-u-sm-3 am-form-label form-require">链接URL</label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="link_id" value="{{ link_id }}" placeholder="请输入链接URL">
                        <small class="help-block">必须以http://或https://开头</small>
                    </div>
                </div>
                
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label">排序</label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="number" name="sort" value="{{ sort || 0 }}" min="0">
                        <small class="help-block">数字越小越靠前</small>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<script>
$(function () {
    // 初始化表单验证
    $('#menu-form').superForm();
    
    // 添加一级菜单
    $('.j-add-menu').on('click', function() {
        showMenuForm({
            title: '添加一级菜单',
            data: {parent_id: 0}
        });
    });
    
    // 添加子菜单
    $('.j-add-submenu').on('click', function() {
        var parentId = $(this).data('parent');
        showMenuForm({
            title: '添加子菜单',
            data: {parent_id: parentId }
        });
    });
    
    
    // 编辑按钮点击事件 - 统一使用模态框
    $('.j-edit').on('click', function() {
        var menuId = $(this).data('id');
        $.get('<?= url("webMenu/edit") ?>', {id: menuId}, function(res) {
            if (res.code === 1) {
                showMenuForm({
                    title: '编辑菜单',
                    data: res.data
                });
            } else {
                $.show_error(res.msg);
            }
        });
    });
    
    // 删除菜单
    $('.item-delete').on('click', function() {
        var menuId = $(this).data('id');
        layer.confirm('确定要删除这个菜单吗？', {title: '删除菜单'}, function(index) {
            $.post('<?= url("webMenu/delete") ?>', {id: menuId}, function(res) {
                if (res.code === 1) {
                    $.show_success(res.msg, function() {
                        window.location.reload();
                    });
                } else {
                    $.show_error(res.msg);
                }
            });
            layer.close(index);
        });
    });
    
    // 保存排序
    $('.j-save-sort').on('click', function() {
        var sortData = [];
        $('.sort-input').each(function() {
            sortData.push({
                id: $(this).data('id'),
                sort: $(this).val()
            });
        });
        
        $.post('<?= url("webMenu/sort") ?>', {sort: sortData}, function(res) {
            if (res.code === 1) {
                $.show_success(res.msg);
            } else {
                $.show_error(res.msg);
            }
        });
    });
    

    // 显示菜单表单弹窗
    function showMenuForm(options) {
        var defaults = {
            title: '菜单编辑',
            data: {},
            area: '500px'
        };
        options = $.extend({}, defaults, options);
        
        // 获取类型映射
        options.data.typeMap = <?= json_encode($typeMap) ?>;
        
        $.showModal({
            title: options.title,
            area: options.area,
            content: template('tpl-menu-form', options.data),
            uCheck: true,
            success: function($content) {
                // 菜单类型切换
                $content.find('.j-menu-type').on('change', function() {
                    var type = $(this).val();
                    $content.find('.j-config-group').addClass('am-hide');
                    $content.find('.j-config-group[data-type="' + type + '"], .j-config-group[data-type*="' + type + '"]').removeClass('am-hide');
                });
                
                // 内容选择按钮
                $content.find('.j-select-content').on('click', function() {
                    var type = $content.find('[name="type"]').val();
                    showContentSelector(type);
                });
                
                // 清除内容选择
                $content.find('.j-clear-content').on('click', function() {
                    $content.find('[name="link_id"]').val('');
                    $content.find('#contentPreview').html('');
                });
                
                // 初始化显示正确的配置区域
                var initialType = $content.find('.j-menu-type').val();
                $content.find('.j-config-group').addClass('am-hide');
                $content.find('.j-config-group[data-type="' + initialType + '"], .j-config-group[data-type*="' + initialType + '"]').removeClass('am-hide');
            },
            yes: function($content) {
                var url = '<?= url("webMenu/add") ?>';
                if ($content.find('[name="id"]').val()) {
                    url = '<?= url("webMenu/update") ?>';
                }
                
                $content.find('#menu-form').myAjaxSubmit({
                    url: url,
                    callback: function(res) {
                        if (res.code === 1) {
                            $.show_success(res.msg, function() {
                                window.location.reload();
                            });
                        }
                        return true;
                    }
                });
                return false;
            }
        });
    }
    
    // 显示内容选择弹窗
function showContentSelector(type) {
    // 根据类型加载不同的内容选择器
    var url = type == 10 ? '<?= url("content.article/list") ?>' : '<?= url("content.article.category/list") ?>';
    
    $.get(url, function(res) {
        if (res.code === 1) {
            var html = '<div class="am-padding-xs am-padding-top">';
            html += '<div class="am-scrollable-horizontal">';
            html += '<table class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">';
            html += '<thead><tr><th>选择</th><th>标题</th><th>更新时间</th></tr></thead>';
            html += '<tbody>';
            
            $.each(res.data.list, function(i, item) {
                // 统一字段名处理
                var itemId = type == 10 ? item.article_id : item.category_id;
                var itemTitle = type == 10 ? item.article_title : item.name;
                var updateTime = type == 10 ? item.update_time : item.create_time;
                
                html += '<tr>';
                html += '<td><input type="radio" name="selected_content" value="' + itemId + '"></td>';
                html += '<td>' + itemTitle + '</td>';
                html += '<td>' + updateTime + '</td>';
                html += '</tr>';
            });
            
            if (res.data.list.length === 0) {
                html += '<tr><td colspan="3" class="am-text-center">暂无内容数据</td></tr>';
            }
            
            html += '</tbody></table></div>';
            html += '<div class="am-margin-top">';
            html += '<button type="button" class="am-btn am-btn-primary am-radius j-confirm">确定选择</button>';
            html += '<button type="button" class="am-btn am-btn-default am-radius j-cancel">取消</button>';
            html += '</div></div>';
            
            var index = layer.open({
                type: 1,
                title: type == 10 ? '选择单页内容' : '选择文章分类',
                content: html,
                area: ['80%', '80%'],
                btn: ['确定', '取消'],
                success: function(layero, index) {
                    layero.find('.j-confirm').on('click', function() {
                        var selected = layero.find('input[name="selected_content"]:checked');
                        if (selected.length > 0) {
                            var contentId = selected.val();
                            $('[name="link_id"]').val(contentId);
                            $('#contentPreview').html(
                                '<div class="am-alert am-alert-success">已选择' + 
                                (type == 10 ? '单页' : '分类') + 
                                'ID: ' + contentId + '</div>'
                            );
                            layer.close(index);
                        } else {
                            layer.msg('请选择内容', {icon: 2});
                        }
                    });
                }
            });
        } else {
            layer.msg(res.msg || '获取内容失败', {icon: 2});
        }
    });
}
});
</script>