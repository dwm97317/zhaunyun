<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-fl">{if $mpmenus->id}编辑菜单{else}添加菜单{/if}</div>
                </div>
                <form id="menuForm" class="am-form tpl-form-line-form" method="post" action="{:url('wechat_menu/save')}">
                    <div class="widget-body am-fr">
                        <input type="hidden" name="id" value="{$mpmenus->id ?? 0}">
                        <input type="hidden" name="parent_id" value="{$mpmenus->parent_id}">
                        
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">菜单名称</label>
                            <div class="am-u-sm-9">
                                <input type="text" class="tpl-form-input" name="name" value="{$mpmenus->name ?? ''}" placeholder="请输入菜单名称" maxlength="4">
                                <small class="am-form-warning">一级菜单最多4个汉字，二级菜单最多7个汉字</small>
                            </div>
                        </div>
                        
                        {if $mpmenus->parent_id == 0 && count($parentMenus) >= 3}
                            <div class="am-alert am-alert-warning">
                                一级菜单最多只能有3个，无法再添加一级菜单
                            </div>
                        {else}
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">菜单类型</label>
                                <div class="am-u-sm-9">
                                    <select name="type" id="menuType" data-am-selected="{btnSize: 'sm'}">
                                        {foreach $typeMap as $key=>$name}
                                        <option value="{$key}" {if isset($mpmenus->type) && $mpmenus->type == $key}selected{/if}>{$name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            
                            <div id="clickConfig" class="am-form-group type-config {if !isset($mpmenus->type) || $mpmenus->type != 'click'}am-hide{/if}">
                                <label class="am-u-sm-3 am-form-label">事件KEY</label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="key" value="{$mpmenus->key ?? ''}" placeholder="请输入事件KEY">
                                    <small class="am-form-warning">用于识别点击事件，建议格式: MENU_KEY_XXX</small>
                                </div>
                            </div>
                            
                            <div id="viewConfig" class="am-form-group type-config {if !isset($mpmenus->type) || $mpmenus->type != 'view'}am-hide{/if}">
                                <label class="am-u-sm-3 am-form-label">跳转URL</label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="url" value="{$mpmenus->url ?? ''}" placeholder="请输入跳转URL">
                                    <small class="am-form-warning">必须以http://或https://开头</small>
                                </div>
                            </div>
                            
                            <div id="miniprogramConfig" class="type-config {if !isset($mpmenus->type) || $mpmenus->type != 'miniprogram'}am-hide{/if}">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">小程序AppID</label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input" name="appid" value="{$mpmenus->appid ?? ''}" placeholder="请输入小程序AppID">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">小程序路径</label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input" name="pagepath" value="{$mpmenus->pagepath ?? ''}" placeholder="请输入小程序路径，如pages/index/index">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">备用网页URL</label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input" name="url" value="{$mpmenus->url ?? ''}" placeholder="请输入备用网页URL">
                                        <small class="am-form-warning">旧版微信客户端无法支持小程序时将打开此URL</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">排序</label>
                                <div class="am-u-sm-9">
                                    <input type="number" class="tpl-form-input" name="sort" value="{$mpmenus->sort ?? 0}">
                                    <small class="am-form-warning">数字越小越靠前</small>
                                </div>
                            </div>
                        {/if}
                        
                        <div class="am-form-group">
                            <div class="am-u-sm-9 am-u-sm-push-3">
                                <button type="submit" class="am-btn am-btn-primary">保存</button>
                                <a href="{:url('wechat_menu/index')}" class="am-btn am-btn-default">返回</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    // 根据菜单类型显示不同的配置项
    $('#menuType').change(function() {
        $('.type-config').addClass('am-hide');
        $('#' + $(this).val() + 'Config').removeClass('am-hide');
    });
    
    // 表单提交
    $('#menuForm').submit(function(e) {
        e.preventDefault();
        
        $.post($(this).attr('action'), $(this).serialize(), function(res) {
            if (res.code === 1) {
                layer.msg(res.msg, {icon: 1});
                setTimeout(function() {
                    window.location.href = '{:url("wechat_menu/index")}';
                }, 1500);
            } else {
                layer.msg(res.msg, {icon: 2});
            }
        });
    });
});
</script>