<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">渠道商单号</div>
                </div>
                <div class="widget-body am-fr">

                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>单号</th>
                                <th>所属供应商</th>
                                <th>状态</th>
                                <th>绑定订单号</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['ditch_number'] ?></td>
                                    <td class="am-text-middle">
                                        <p class="item-title"
                                           style="max-width: 400px;"><?= $item['ditch']['ditch_name'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                           <span class="am-badge am-badge-<?= $item['status']==0 ? 'success' : 'warning' ?>">
                                               <?= $item['status']==0 ? '未使用' : '已使用' ?>
                                           </span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['order_no'] ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">

                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                   
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="10" class="am-text-center">暂无记录</td>
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
        var url = "<?= url('setting.ditch/deleteditch') ?>";
        $('.item-delete').delete('id', url);

    });
</script>

