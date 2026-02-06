<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-fl">微信公众号自定义菜单</div>
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
                                    <button type="button" class="j-publish am-btn am-btn-default am-btn-secondary am-radius">
                                        <span class="am-icon-paper-plane"></span> 发布到微信
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
                                <th>菜单Key/URL</th>
                                <th>排序</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($mpmenus)): ?>
                                <?php foreach ($mpmenus as $mpme): ?>
                                <tr>
                                    <td class="am-text-middle">
                                        <i class="am-icon-bars"></i> <?= $mpme['name'] ?>
                                    </td>
                                    <td class="am-text-middle"><?= $typeMap[$mpme['type']] ?? '-' ?></td>
                                    <td class="am-text-middle">
                                        <?php if ($mpme['type'] === 'click') { ?>
                                            Key: <?php echo $mpme['key']; ?>
                                        <?php } elseif ($mpme['type'] === 'view') { ?>
                                            URL: <?php echo $mpme['url']; ?>
                                        <?php } elseif ($mpme['type'] === 'media_id') { ?>
                                            素材ID: <?php echo $mpme['key']; ?>
                                        <?php } ?>
                                    </td>
                                    <td class="am-text-middle"><?= $mpme['sort'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <a href="javascript:;" class="j-edit" data-id="<?= $mpme['id'] ?>" data-parent_id="<?= $mpme['parent_id'] ?>"> 
                                                <i class="am-icon-pencil"></i> 编辑
                                            </a>
                                            <a href="javascript:;" class="item-delete tpl-table-black-operation-del" 
                                               data-id="<?= $mpme['id'] ?>">
                                                <i class="am-icon-trash"></i> 删除
                                            </a>
                                            <?php if (count($mpme['sub_button'] ?? []) < 5): ?>
                                            <a href="javascript:;" class="j-add-submenu" data-parent="<?= $mpme['id'] ?>">
                                                <i class="am-icon-plus"></i> 添加子菜单
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- 子菜单 -->
                                <?php if (!empty($mpme['subMenus'])): ?>
                                    <?php foreach ($mpme['subMenus'] as $subMenu): ?>
                                    <tr>
                                        <td class="am-text-middle">
                                            &nbsp;&nbsp;&nbsp;&nbsp;├ <?= $subMenu['name'] ?>
                                        </td>
                                        <td class="am-text-middle"><?= $typeMap[$subMenu['type']] ?? '-' ?></td>
                                        <td class="am-text-middle">
                                            <?php if ($subMenu['type'] === 'click') { ?>
                                                Key: <?php echo $subMenu['key']; ?>
                                            <?php } elseif ($subMenu['type'] === 'view') { ?>
                                                URL: <?php echo $subMenu['url']; ?>
                                            <?php } elseif ($subMenu['type'] === 'media_id') { ?>
                                                素材ID: <?php echo $subMenu['key']; ?>
                                            <?php } ?>
                                        </td>
                                        <td class="am-text-middle"><?= $subMenu['sort'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <a href="javascript:;" class="j-edit" data-id="<?= $subMenu['id'] ?>" data-parent_id="<?= $mpme['parent_id'] ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del" 
                                                   data-id="<?= $subMenu['id'] ?>">
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
                                    <td colspan="5" class="am-text-center">暂无菜单数据，请添加菜单</td>
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
                        <small class="help-block">一级菜单最多4个汉字，二级菜单最多7个汉字</small>
                    </div>
                </div>
                
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">菜单类型</label>
                    <div class="am-u-sm-8 am-u-end">
                        <select name="type" data-am-selected="{btnSize: 'sm'}" class="j-menu-type">
                            <option value="click" {{ type=='click'?'selected':'' }}>点击事件</option>
                            <option value="view" {{ type=='view'?'selected':'' }}>跳转链接</option>
                            <option value="miniprogram" {{ type=='miniprogram'?'selected':'' }}>小程序</option>
                            <option value="scancode_push" {{ type=='scancode_push'?'selected':'' }}>扫码推事件</option>
                            <option value="media_id" {{ type=='media_id'?'selected':'' }}>素材消息</option>
                        </select>
                    </div>
                </div>
                
                <!-- 点击事件配置 -->
                <div class="am-form-group j-config-group {{ type!='click'?'am-hide':'' }}" data-type="click">
                    <label class="am-u-sm-3 am-form-label form-require">事件KEY</label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="key" value="{{ key }}" placeholder="请输入事件KEY">
                        <small class="help-block">用于识别点击事件，建议格式: MENU_KEY_XXX</small>
                    </div>
                </div>
                
                <!-- 跳转链接配置 -->
                <div class="am-form-group j-config-group {{ type!='view'?'am-hide':'' }}" data-type="view">
                    <label class="am-u-sm-3 am-form-label form-require">跳转URL</label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="url" value="{{ url }}" placeholder="请输入跳转URL">
                        <small class="help-block">必须以http://或https://开头</small>
                    </div>
                </div>
                
                <!-- 小程序配置 -->
                <div class="am-form-group j-config-group {{ type!='miniprogram'?'am-hide':'' }}" data-type="miniprogram">
                    <label class="am-u-sm-3 am-form-label form-require">小程序AppID</label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="appid" value="{{ appid }}" placeholder="请输入小程序AppID">
                    </div>
                    
                    <label class="am-u-sm-3 am-form-label form-require">小程序路径</label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="pagepath" value="{{ pagepath }}" placeholder="请输入小程序路径">
                    </div>
                    
                    <label class="am-u-sm-3 am-form-label">备用网页URL</label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="burl" value="{{ burl }}" placeholder="请输入备用网页URL">
                        <small class="help-block">旧版微信客户端无法支持小程序时将打开此URL</small>
                    </div>
                </div>
                
                <!-- 扫码推事件配置 -->
                <div class="am-form-group j-config-group {{ type!='scancode_push'?'am-hide':'' }}" data-type="scancode_push">
                    <label class="am-u-sm-3 am-form-label form-require">事件KEY</label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="key" value="{{ key }}" placeholder="请输入事件KEY">
                    </div>
                </div>
                
                <!-- 素材消息配置 -->
                <div class="am-form-group j-config-group {{ type!='media_id'?'am-hide':'' }}" data-type="media_id">
                    <label class="am-u-sm-3 am-form-label form-require">素材选择</label>
                    <div class="am-u-sm-8 am-u-end">
                        <div class="am-btn-group">
                            <button type="button" class="am-btn am-btn-default am-radius j-select-material">
                                <i class="am-icon-folder-open"></i> 选择素材
                            </button>
                            <button type="button" class="am-btn am-btn-default am-radius j-clear-material">
                                <i class="am-icon-trash"></i> 清除
                            </button>
                        </div>
                        <input type="hidden" name="media_id" value="{{ key }}">
                        <div id="materialPreview" class="am-margin-top-sm">
                            {{ if key && type=='media_id' }}
                            <div class="am-alert am-alert-success">
                                已选择素材ID: {{ key }}
                            </div>
                            {{ /if }}
                        </div>
                        <small class="help-block">从素材库中选择永久素材</small>
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
<script id="tpl-wechat-material" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <div class="am-tabs" data-am-tabs>
            <ul class="am-tabs-nav am-nav am-nav-tabs">
                <li class="am-active"><a href="#tab-material-news">图文素材</a></li>
                <li><a href="#tab-material-image">图片素材</a></li>
                <li><a href="#tab-material-voice">语音素材</a></li>
                <li><a href="#tab-material-video">视频素材</a></li>
            </ul>
            
            <div class="am-tabs-bd">
                <!-- 图文素材 -->
                <div class="am-tab-panel am-fade am-in am-active" id="tab-material-news">
                    <div class="am-scrollable-horizontal">
                        <table class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                                <tr>
                                    <th>选择</th>
                                    <th>标题</th>
                                    <th>更新时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{ each materials.news item }}
                                <tr>
                                    <td>
                                        <input type="radio" name="selected_material" 
                                               value="{{ item.media_id }}" 
                                               data-type="news">
                                    </td>
                                    <td>
                                        {{ item.content.news_item[0].title || '无标题' }}
                                        {{ if item.content.news_item.length > 1 }}
                                            <span class="am-badge am-badge-secondary">多图文</span>
                                        {{ /if }}
                                    </td>
                                    <td>{{ formatTime(item.update_time) }}</td>
                                    <td>
                                        <button class="am-btn am-btn-xs am-btn-default j-preview" 
                                                data-media_id="{{ item.media_id }}"
                                                data-type="news">
                                            <i class="am-icon-eye"></i> 预览
                                        </button>
                                    </td>
                                </tr>
                                {{ /each }}
                                {{ if materials.news.length === 0 }}
                                <tr>
                                    <td colspan="4" class="am-text-center">暂无图文素材</td>
                                </tr>
                                {{ /if }}
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- 图片素材 -->
                <div class="am-tab-panel am-fade" id="tab-material-image">
                    <div class="am-scrollable-horizontal">
                        <table class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                                <tr>
                                    <th>选择</th>
                                    <th>图片</th>
                                    <th>更新时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{ each materials.image item }}
                                <tr>
                                    <td>
                                        <input type="radio" name="selected_material" 
                                               value="{{ item.media_id }}" 
                                               data-type="image">
                                    </td>
                                    <td>
                                        <img src="{{ item.url || item.content.image_url }}" style="max-height: 50px;">
                                    </td>
                                    <td>{{ formatTime(item.update_time) }}</td>
                                </tr>
                                {{ /each }}
                                {{ if materials.image.length === 0 }}
                                <tr>
                                    <td colspan="3" class="am-text-center">暂无图片素材</td>
                                </tr>
                                {{ /if }}
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- 其他素材类型... -->
            </div>
        </div>
        
        <div class="am-margin-top">
            <button type="button" class="am-btn am-btn-primary am-radius j-confirm">确定选择</button>
            <button type="button" class="am-btn am-btn-default am-radius j-cancel">取消</button>
        </div>
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
            data: {parent_id: parentId}
        });
    });
    
    // 编辑菜单
    $('.j-edit').on('click', function() {
        var menuId = $(this).data('id');
        var parentId = $(this).data('parent_id');
        $.get('<?= url("wechat_menu/edit") ?>', {id: menuId, parent_id: parentId}, function(res) {
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
            $.post('<?= url("wechat_menu/delete") ?>', {id: menuId}, function(res) {
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
    
    // 发布菜单到微信
    $('.j-publish').on('click', function() {
        layer.confirm('确定要将当前菜单发布到微信公众号吗？', {title: '发布菜单'}, function(index) {
            $.post('<?= url("wechat_menu/publish") ?>', function(res) {
                if (res.code === 1) {
                    $.show_success(res.msg);
                } else {
                    $.show_error(res.msg);
                }
            });
            layer.close(index);
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
                    $content.find('.j-config-group[data-type="' + type + '"]').removeClass('am-hide');
                });
                
                // 素材选择按钮
                $content.find('.j-select-material').on('click', function() {
                    showMaterialSelector();
                });
                
                // 清除素材选择
                $content.find('.j-clear-material').on('click', function() {
                    $content.find('[name="media_id"]').val('');
                    $content.find('[name="key"]').val('');
                    $content.find('#materialPreview').html('');
                });
                
                // 初始化显示正确的配置区域
                var initialType = $content.find('.j-menu-type').val();
                $content.find('.j-config-group').addClass('am-hide');
                $content.find('.j-config-group[data-type="' + initialType + '"]').removeClass('am-hide');
            },
            yes: function($content) {
                $content.find('#menu-form').myAjaxSubmit({
                    url: '<?= url("wechat_menu/save") ?>',
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
    
    // 全局辅助函数
    function formatTime(timestamp) {
        if (!timestamp) return '';
        var date = new Date(timestamp * 1000);
        return date.getFullYear() + '-' + 
               (date.getMonth() + 1).toString().padStart(2, '0') + '-' + 
               date.getDate().toString().padStart(2, '0') + ' ' + 
               date.getHours().toString().padStart(2, '0') + ':' + 
               date.getMinutes().toString().padStart(2, '0');
    }
    
    // 素材选择回调函数
    function selectMaterialCallback(mediaId, type) {
        // 这里处理选择后的逻辑
        $('[name="media_id"]').val(mediaId);
        $('[name="key"]').val(mediaId);
        $('#materialPreview').html(
            '<div class="am-alert am-alert-success">已选择素材ID: ' + mediaId + '</div>'
        );
        layer.closeAll();
    }
    
    // 显示素材选择弹窗
    function showMaterialSelector() {
        // 先显示加载中
        var loadingIndex = layer.load(1);
        
        // 获取素材数据
        $.get('<?= url("wechat_menu/wechat_material") ?>', function(res) {
            layer.close(loadingIndex);
            
            if (res.code === 1) {
                // 渲染模板
                var html = template('tpl-wechat-material', {
                    materials: {
                        news: res.data.news || [],
                        image: res.data.image || [],
                        voice: res.data.voice || [],
                        video: res.data.video || []
                    },
                    formatTime: formatTime
                });
                
                // 显示弹窗
                var index = layer.open({
                    type: 1,
                    title: '选择微信素材',
                    content: html,
                    area: ['90%', '90%'],
                    btn: ['确定', '取消'],
                    success: function(layero, index) {
                        // 预览按钮事件
                        layero.find('.j-preview').on('click', function() {
                            var mediaId = $(this).data('media_id');
                            var type = $(this).data('type');
                            previewMaterial(mediaId, type);
                        });
                        
                        // 确定按钮事件
                        layero.find('.j-confirm').on('click', function() {
                            var selected = layero.find('input[name="selected_material"]:checked');
                            if (selected.length > 0) {
                                var mediaId = selected.val();
                                var type = selected.data('type');
                                selectMaterialCallback(mediaId, type);
                            } else {
                                layer.msg('请选择素材', {icon: 2});
                            }
                        });
                    }
                });
            } else {
                layer.msg(res.msg || '获取素材失败', {icon: 2});
            }
        }).fail(function() {
            layer.close(loadingIndex);
            layer.msg('请求失败，请检查网络', {icon: 2});
        });
    }

});
</script>