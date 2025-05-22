<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">盲盒分享列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="tips am-margin-bottom-sm am-u-sm-12">
                       
                    </div>
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                               
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12 am-scrollable-horizontal">
                        <table width="100%"
                               class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>分享ID</th>
                                <th>分享内容</th>
                                <th>用户信息</th>
                                <!--<th>关联盲盒</th>-->
                                <th>关联包裹</th>
                                <th>是否显示</th>
                                <th>排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['wall_id'] ?></td>
                                        <td class="am-text-middle"><?= $item['content'] ?></td>
                                        <td class="am-text-middle"><?= $item['user_id'] ?></td>
                                    
                                        <td class="am-text-middle"><?= $item['package_id'] ?></td>
                                        <td class="am-text-middle"><?= $item['status']==0?"隐藏":"显示" ?></td>
                                        <td class="am-text-middle"><?= $item['sort'] ?></td>
                                        <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('market.blindbox/editblindboxwall')): ?>
                                                    <a href="<?= url('market.blindbox/editblindboxwall', ['id' => $item['wall_id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('market.blindbox/deleteblindboxwall')): ?>
                                                    <a href="javascript:void(0);"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['wall_id'] ?>">
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
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
<script>
    $(function () {

        // 删除元素
        var url = "<?= url('market.blindbox/deleteblindboxwall') ?>";
        $('.item-delete').delete('id', url);

    });
</script>

