<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">友情链接</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('wxapp.weblink/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('wxapp.weblink/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>友链ID</th>
                                <th>友链名称</th>
                                <th>链接图片</th>
                                <th>轮播类型</th>
                                <th>地址</th>
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
                                        <td class="am-text-middle"><?= $item['name'] ?></td>
                                        <td class="am-text-middle">
                                            <?php if (!empty($item['image'])): ?>
                                            <a target="_blank" href="<?= $item['image']['file_path'] ?>" title="点击查看大图">
                                                <img src="<?= $item['image']['file_path'] ?>" width="200" height="72" alt="">
                                            </a>
                                            <?php else: ?>
                                            --
                                            <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle"><?= $item['type']==10?'文字':'图片' ?></td>
                                        <td class="am-text-middle"><?= mb_substr($item['url'],0,80) ?></td>
                                        <td class="am-text-middle"><?= $item['sort'] ?></td>
                                        <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('wxapp.weblink/edit')): ?>
                                                    <a href="<?= url('wxapp.weblink/edit',
                                                        ['id' => $item['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('wxapp.weblink/delete')): ?>
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
                                    <td colspan="6" class="am-text-center">暂无记录</td>
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
        var url = "<?= url('store/wxapp.weblink/delete') ?>";
        $('.item-delete').delete('id', url);
    });
</script>

