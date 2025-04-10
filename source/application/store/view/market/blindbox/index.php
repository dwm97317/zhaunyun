<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">盲盒列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="tips am-margin-bottom-sm am-u-sm-12">
                        <div class="pre">
                            <p> 注：添加在此列表的盲盒，都会根据其概率被用户抽中，如不参与抽盲盒，请设置为不显示</p>
                        </div>
                    </div>
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('market.blindbox/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('market.blindbox/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12 am-scrollable-horizontal">
                        <table width="100%"
                               class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>盲盒ID</th>
                                <th>盲盒类型</th>
                                <th>名称</th>
                                <th>价值</th>
                                <th>库存</th>
                                <th>描述</th>
                                <th>概率</th>
                                <th>是否参与</th>
                                <th>排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                        <td class="am-text-middle"><?= $item['type']==10?"优惠券":"实物" ?></td>
                                        <td class="am-text-middle"><?= $item['type']==10?$item['coupon']['name']:$item['goods_name'] ?></td>
                                        <td class="am-text-middle"><?= $item['type']==10?$item['coupon']['reduce_price']:$item['goods_price'] ?></td>
                                        <td class="am-text-middle"><?= $item['goods_inventory'] ?></td>
                                        <td class="am-text-middle"><?= $item['goods_desc'] ?></td>
                                        <td class="am-text-middle"><?= $item['probability'] ?></td>
                                        <td class="am-text-middle"><?= $item['status']==0?"参与":"不参与" ?></td>
                                        <td class="am-text-middle"><?= $item['sort'] ?></td>
                                        <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('market.blindbox/edit')): ?>
                                                    <a href="<?= url('market.blindbox/edit', ['id' => $item['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('market.blindbox/delete')): ?>
                                                    <a href="javascript:void(0);"
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
        var url = "<?= url('market.blindbox/delete') ?>";
        $('.item-delete').delete('id', url);

    });
</script>

