<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">城市管理</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="tips am-margin-bottom-sm am-u-sm-12">
                        <div class="pre">
                            <p> 注：城市必须添加3级
                        </div>
                    </div>
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                    <div class="am-btn-group am-btn-group-xs">
                                        <?php if (isset($id)): ?>
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('tools.city/add',['id' => $id]) ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!isset($id)): ?>
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('tools.city/addtop') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                        <?php endif; ?>
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('tools.city/getjsoncity') ?>">
                                            <span class="am-icon-plus"></span> 生成json
                                        </a>
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>城市ID</th>
                                <th>城市名称</th>
                                <th>简称</th>
                                <th>全称</th>
                                <th>层级</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($list)): foreach ($list as $first): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $first['id'] ?></td>
                                    <td class="am-text-middle"><?= $first['name'] ?></td>
                                    <td class="am-text-middle"><?= $first['shortname'] ?></td>
                                    <td class="am-text-middle"><?= $first['merger_name'] ?></td>
                                    <td class="am-text-middle"><?= $first['level'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                
                                                <a href="<?= url('tools.city/edit',
                                                    ['id' => $first['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                       
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $first['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                                
                                                <a href="<?= url('tools.city/childcity', ['id' => $first['id']]) ?>">
                                                    查看下级
                                                </a>
                             
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
<script>
    $(function () {
        // 删除元素
        var url = "<?= url('goods.category/delete') ?>";
        $('.item-delete').delete('id', url);

    });
</script>

