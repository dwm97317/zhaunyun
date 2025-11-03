<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">下级用户列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="am-scrollable-horizontal am-u-sm-12 am-padding-top">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>用户ID</th>
                                <th>微信头像</th>
                                <th>微信昵称</th>
                                <th>性别</th>
                                <th>累积消费金额</th>
                                <th>注册时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['user']['user_id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['user']['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['user']['avatarUrl'] ?>"
                                                 width="50" height="50" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <p><span><?= $item['user']['nickName'] ?></span></p>
                                    </td>
                                    <td class="am-text-middle"><?= $item['user']['gender']['text'] ?></td>
                                    <td class="am-text-middle"><?= $item['user']['pay_money'] ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('apps.dealer.user/deletefan')): ?>
                                                <a class="tpl-table-black-operation-del j-delete-fan"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['user_id'] ?>"
                                                   title="解除关系">
                                                    <i class="am-icon-trash"></i> 解除关系
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="7" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
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
</div>
<script>
    $(function () {
        /**
         * 解除粉丝关系
         */
        $('.j-delete-fan').on('click', function () {
            var userId = $(this).data('id');
            layer.confirm(
                '确定要解除该用户与分销商的关系吗？解除后该用户将不再属于此分销商的下级成员，不可恢复，请谨慎操作！'
                , {title: '友情提示'}
                , function (index) {
                    $.post("<?= url('apps.dealer.user/deleteFan') ?>", {user_id: userId}, function (result) {
                        result.code === 1 ? $.show_success(result.msg, result.url)
                            : $.show_error(result.msg);
                    });
                    layer.close(index);
                }
            );
        });
    });
</script>

