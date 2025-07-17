<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">评价/投诉建议列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="page_toolbar am-margin-bottom am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            
                            <div class="am-u-sm-9 am-u-md-5">
                                <div class="am fr">
                                    <?php if ($store['user']['is_super']==1): ?>
                                    <div class="am-form-group am-fl">
                                        <select name="clerk_id"
                                                data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder: '员工客服', maxHeight: 400}">
                                            <option value=""></option>
                                            <?php if (isset($clerkList)): foreach ($clerkList as $shop): ?>
                                                <option value="<?= $shop['clerk_id'] ?>"
                                                    <?= $request->get('clerk_id') == $shop['clerk_id'] ? 'selected' : '' ?>>
                                                    <?= $shop['real_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <?php endif;?>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="search"
                                                   placeholder="请输入店员姓名/手机号"
                                                   value="<?= $request->get('search') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th class="am-text-middle">用户</th>
                                <th>评价客服</th>
                                <th>评分</th>
                                <th>评价内容</th>
                                <th>评价时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['comment_id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['user']['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <?php if($item['user']['avatarUrl']) :?>
                                                 <img src="<?= $item['user']['avatarUrl'] ?>" width="72" height="72" alt="">
                                            <?php else:?>
                                                 <img src="assets/admin/img/head.jpg" width="72" height="72" alt="">
                                            <?php endif;?>
                                        </a>
                                    </td>
                                    
                                    <td class="am-text-middle">
                                        <p class="item-title"><?= $item['type']==1?$item['clerk']['real_name']:'投诉建议' ?></p>
                                    </td>
                                    
                                    <td class="am-text-middle">
                                        <?php if ($item['star'] >=4) : ?>
                                            <span class="x-color-green">好评</span>
                                        <?php elseif ($item['star'] < 2) : ?>
                                            <span class="x-color-yellow">差评</span>
                                        <?php else: ?>
                                            <span class="x-color-red">中评</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class="item-title"><?= $item['content'] ?></p>
                                    </td>

                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('store/shop.clerk/deletecomment')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-default"
                                                   data-id="<?= $item['comment_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
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
        var url = "<?= url('store/shop.clerk/deletecomment') ?>";
        $('.item-delete').delete('comment_id', url);
    });
</script>

