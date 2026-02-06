<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">语言设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    默认语言
                                </label>
                                <div class="am-u-sm-9">
                                    <?php if (isset($list)): foreach ($list as $key =>$item): ?>
                                    <label class="am-checkbox-inline">
                                        <input type="radio" name="lang[default]" value="<?= $item['enname'] ?>"
                                               data-am-ucheck <?= $lang['default'] == $item['enname'] ? 'checked' : '' ?>>
                                        <?= $item['name'] ?>
                                    </label>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <?php if (checkPrivilege('wxapp.setting/lang')): ?>
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">语言列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('store/wxapp/addlang')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="j-lang am-btn am-btn-default am-btn-success am-radius"
                                           href="javascript:void(0);">
                                            <span class="am-icon-plus"></span> 新增语言
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>语言名称</th>
                                <th>英文名</th>
                                <th>AI翻译</th>
                                <th>是否启用</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($list)>0): foreach ($list as $key => $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['name'] ?></td>
                                    <td class="am-text-middle">
                                        <p class="item-title" style="max-width: 400px;"><?= $item['enname'] ?></p>
                                    </td>
                                    <td class="am-text-middle"><?= isset($item['langto'])?$item['langto']:'' ?></td>
                                    <td class="am-text-middle"><?= isset($item['status']) &&  $item['status']==1?"启用":"禁用" ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('store/wxapp/editlang')): ?>
                                                <a data-name="<?= $item['enname'] ?>" class="j-langedit" href="javascript:void(0);">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store/wxapp/deletealang')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['enname'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store/wxapp/langdetail')): ?>
                                                <a href="<?= url('store/wxapp/langdetail',['lang' => $item['enname']]) ?>" class="tpl-table-black-operation"
                                                   data-id="<?= $item['enname'] ?>">
                                                    <i class="am-icon-pencil"></i> 设置翻译
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store/wxapp/ailang')): ?>
                                                <a href="<?= url('store/wxapp/ailang',['lang' => $item['enname'],'to'=>isset($item['langto'])?$item['langto']:'']) ?>" class="tpl-table-black-operation"
                                                   data-id="<?= $item['enname'] ?>">
                                                    <i class="am-icon-pencil"></i> AI翻译
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="am-text-center">暂无记录</td>
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
<!-- 新增 -->
<script id="tpl-usermark" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form id="langform" class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 语言中文名 </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="lang[name]" value="" placeholder="请输入语言中文名">
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择语种
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="lang[langto]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择翻译语音'}">
                               <option value="en">英语</option>
                               <option value="jp">日语</option>
                               <option value="fra">法语</option>
                               <option value="kor">韩语</option>
                               <option value="ara">阿拉伯语</option>
                               <option value="th">泰语</option>
                               <option value="vie">越南语</option>
                               <option value="ru">俄语</option>
                               <option value="spa">西班牙语</option>
                        </select>
                        <div class="help-block">
                            <small>注：其他语音请联系客服，或自行翻译</small>
                    </div>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 英文名 </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="lang[enname]" value="" placeholder="请输入英文名">
                    </div>
                </div>
                 <div class="am-form-group">
                     <label class="am-u-sm-3 am-form-label form-require"> 是否启用 </label>
                        <div class="am-u-sm-9 am-u-end">
                            <label class="am-radio-inline">
                                <input type="radio" name="lang[status]" value="1" data-am-ucheck checked>
                                启用
                            </label>
                            <label class="am-radio-inline">
                                <input type="radio" name="lang[status]" value="0" data-am-ucheck>
                                禁用
                            </label>
                        </div>
                </div>
            </div>
        </form>
    </div>
</script>
<!-- 编辑 -->
<script id="tpl-edit" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form id="langform" class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 语言中文名 </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="lang[name]" value="{{ name }}" placeholder="请输入语言中文名">
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 英文名 </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="lang[enname]" value="{{ enname }}" placeholder="请输入英文名">
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择语种
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="lang[langto]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择翻译语音'}">
                               <option value="en"  data-am-ucheck {{ langto=='en'?'selected':'' }} >英语</option>
                               <option value="jp" data-am-ucheck {{ langto=='jp'?'selected':'' }}>日语</option>
                               <option value="fra" data-am-ucheck {{ langto=='fra'?'selected':'' }}>法语</option>
                               <option value="kor" data-am-ucheck {{ langto=='kor'?'selected':'' }}>韩语</option>
                               <option value="ara" data-am-ucheck {{ langto=='ara'?'selected':'' }}>阿拉伯语</option>
                               <option value="th" data-am-ucheck {{ langto=='th'?'selected':'' }}>泰语</option>
                               <option value="vie" data-am-ucheck {{ langto=='vie'?'selected':'' }}>越南语</option>
                               <option value="ru" data-am-ucheck {{ langto=='ru'?'selected':'' }}>俄语</option>
                        </select>
                        <div class="help-block">
                            <small>注：其他语音请联系客服，或自行翻译</small>
                    </div>
                    </div>
                </div>
                 <div class="am-form-group">
                     <label class="am-u-sm-3 am-form-label form-require"> 是否启用 </label>
                        <div class="am-u-sm-9 am-u-end">
                            <label class="am-radio-inline">
                                <input type="radio" name="lang[status]" value="1" data-am-ucheck {{ status==1?'checked':'' }}  >
                                启用
                            </label>
                            <label class="am-radio-inline">
                                <input type="radio" name="lang[status]" value="0" data-am-ucheck {{ status==0?'checked':'' }}>
                                禁用
                            </label>
                        </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script>
    $(function () {
        /**
         * 增加语言
         */
        $('.j-langedit').on('click', function () {
            var data = $(this).data();
            $.ajax({
                type:'post',
                url:"<?= url('store/wxapp/editlang') ?>",
                data:{name:data.name},
                dataType:"json",
                success:function(res){
                      if (res.code==1){
                          $.showModal({
                            title: '编辑语言'
                            , area: '460px'
                            , content: template('tpl-edit', res.data)
                            , uCheck: true
                            , success: function ($content) {
                            }
                            , yes: function ($content) {
                                $content.find('form').myAjaxSubmit({
                                    url: '<?= url('store/wxapp/saveeditlang') ?>',
                                    data: {user_id: data.id}
                                });
                                return true;
                            }
                        });
                      } 
                }
            })
        });
        
        /**
         * 增加语言
         */
        $('.j-lang').on('click', function () {
            var data = $(this).data();
            $.showModal({
                title: '新增语言'
                , area: '460px'
                , content: template('tpl-usermark', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/wxapp/addlang') ?>',
                        data: {user_id: data.id}
                    });
                    return true;
                }
            });
        });
        
        
        
                
        /**
         * 批量删除
         */
        $('.item-delete').on('click', function () {
            var $tabs, data = $(this).data();
            console.log(data,7777);
            var hedanurl = "<?= url('store/wxapp/deletealang') ?>";
            console.log();
            layer.confirm('请确定是否删除选中的语言', {title: '删除语言'}
                    , function (index) {
                        $.post(hedanurl,{name:data.id}, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
        
        $('#langform').superForm();
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
