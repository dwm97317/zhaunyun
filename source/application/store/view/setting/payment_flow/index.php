<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">支付流水列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-12">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <select name="order_type"
                                                data-am-selected="{btnSize: 'sm', placeholder: '订单类型'}">
                                            <option value="">全部</option>
                                            <option value="inpack" <?= $orderType == 'inpack' ? 'selected' : '' ?>>集运订单</option>
                                            <option value="recharge" <?= $orderType == 'recharge' ? 'selected' : '' ?>>充值订单</option>
                                            <option value="shop" <?= $orderType == 'shop' ? 'selected' : '' ?>>商城订单</option>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_date" class="am-form-field"
                                               value="<?= $startDate ?>" placeholder="请选择起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_date" class="am-form-field"
                                               value="<?= $endDate ?>" placeholder="请选择截止日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="search"
                                                   placeholder="订单号/用户昵称/用户编号" value="<?= $search ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <a href="<?= url('setting.paymentflow/index') ?>" class="am-btn am-btn-default am-btn-sm">
                                            <i class="am-icon-refresh"></i> 重置
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- 统计信息 -->
                    <div class="am-u-sm-12 am-margin-bottom">
                        <div class="am-panel am-panel-default">
                            <div class="am-panel-bd">
                                <div class="am-g">
                                    <div class="am-u-md-3">
                                        <div class="am-text-center">
                                            <strong>总订单数：</strong>
                                            <span class="am-text-danger am-text-xl"><?= $statistics['total_count'] ?></span>
                                        </div>
                                    </div>
                                    <div class="am-u-md-3">
                                        <div class="am-text-center">
                                            <strong>总支付金额：</strong>
                                            <span class="am-text-danger am-text-xl">¥<?= number_format($statistics['total_amount'], 2) ?></span>
                                        </div>
                                    </div>
                                    <div class="am-u-md-2">
                                        <div class="am-text-center">
                                            <small>集运：<?= $statistics['inpack_count'] ?>笔 / ¥<?= number_format($statistics['inpack_amount'], 2) ?></small>
                                        </div>
                                    </div>
                                    <div class="am-u-md-2">
                                        <div class="am-text-center">
                                            <small>充值：<?= $statistics['recharge_count'] ?>笔 / ¥<?= number_format($statistics['recharge_amount'], 2) ?></small>
                                        </div>
                                    </div>
                                    <div class="am-u-md-2">
                                        <div class="am-text-center">
                                            <small>商城：<?= $statistics['shop_count'] ?>笔 / ¥<?= number_format($statistics['shop_amount'], 2) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 数据列表 -->
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>订单类型</th>
                                <th>订单号</th>
                                <th>用户信息</th>
                                <th>支付金额</th>
                                <th>支付方式</th>
                                <th>交易号</th>
                                <th>支付时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($list['list'])): ?>
                                <?php $index = ($list['current_page'] - 1) * $list['per_page'] + 1; ?>
                                <?php foreach ($list['list'] as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $index++ ?></td>
                                        <td class="am-text-middle">
                                            <span class="am-badge <?= $item['order_type'] == 'inpack' ? 'am-badge-primary' : ($item['order_type'] == 'recharge' ? 'am-badge-success' : 'am-badge-warning') ?>">
                                                <?= $item['order_type_text'] ?>
                                            </span>
                                        </td>
                                        <td class="am-text-middle">
                                            <a href="javascript:;" onclick="copyText('<?= $item['order_no'] ?>')" title="点击复制">
                                                <?= $item['order_no'] ?>
                                            </a>
                                        </td>
                                        <td class="am-text-middle">
                                            <p>昵称：<?= $item['nickName'] ?></p>
                                            <?php if(isset($set['usercode_mode']['is_show']) && $set['usercode_mode']['is_show'] != 1): ?>
                                            <p>ID: <?= $item['user_id'] ?></p>
                                            <?php endif;?>
                                            <?php if(isset($set['usercode_mode']['is_show']) && $set['usercode_mode']['is_show'] != 0): ?>
                                            <p>Code: <?= $item['user_code'] ?: '-' ?></p>
                                            <?php endif;?>
                                        </td>
                                        <td class="am-text-middle am-text-danger">
                                            <strong>¥<?= number_format($item['pay_price'], 2) ?></strong>
                                        </td>
                                        <td class="am-text-middle"><?= $item['pay_type'] ?></td>
                                        <td class="am-text-middle">
                                            <?php if (!empty($item['transaction_id'])): ?>
                                                <a href="javascript:;" onclick="copyText('<?= $item['transaction_id'] ?>')" title="点击复制">
                                                    <?= $item['transaction_id'] ?>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #999;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle"><?= $item['pay_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if ($item['order_type'] == 'inpack'): ?>
                                                    <a href="<?= url('tr_order/orderdetail', ['id' => $item['id']]) ?>" target="_blank">
                                                        <i class="am-icon-eye"></i> 查看
                                                    </a>
                                                <?php elseif ($item['order_type'] == 'recharge'): ?>
                                                    <a href="<?= url('user.recharge/detail', ['id' => $item['id']]) ?>" target="_blank">
                                                        <i class="am-icon-eye"></i> 查看
                                                    </a>
                                                <?php elseif ($item['order_type'] == 'shop'): ?>
                                                    <a href="<?= url('store.order/detail', ['order_id' => $item['id']]) ?>" target="_blank">
                                                        <i class="am-icon-eye"></i> 查看
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 分页 -->
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr">
                            <?php if ($list['last_page'] > 1): ?>
                                <?php
                                // 构建基础 URL，保留 s 参数
                                $baseUrl = '?s=/' . $request->pathinfo();
                                // 构建查询参数
                                $queryParams = [];
                                if (!empty($orderType)) $queryParams['order_type'] = $orderType;
                                if (!empty($startDate)) $queryParams['start_date'] = $startDate;
                                if (!empty($endDate)) $queryParams['end_date'] = $endDate;
                                if (!empty($search)) $queryParams['search'] = $search;
                                ?>
                                <ul class="am-pagination">
                                    <?php if ($list['current_page'] > 1): ?>
                                        <?php 
                                        $prevParams = array_merge($queryParams, ['page' => $list['current_page'] - 1]);
                                        $prevUrl = $baseUrl . '&' . http_build_query($prevParams);
                                        ?>
                                        <li><a href="<?= $prevUrl ?>">«</a></li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $list['last_page']; $i++): ?>
                                        <?php if ($i == $list['current_page']): ?>
                                            <li class="am-active"><a href="javascript:;"><?= $i ?></a></li>
                                        <?php else: ?>
                                            <?php 
                                            $pageParams = array_merge($queryParams, ['page' => $i]);
                                            $pageUrl = $baseUrl . '&' . http_build_query($pageParams);
                                            ?>
                                            <li><a href="<?= $pageUrl ?>"><?= $i ?></a></li>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    <?php if ($list['current_page'] < $list['last_page']): ?>
                                        <?php 
                                        $nextParams = array_merge($queryParams, ['page' => $list['current_page'] + 1]);
                                        $nextUrl = $baseUrl . '&' . http_build_query($nextParams);
                                        ?>
                                        <li><a href="<?= $nextUrl ?>">»</a></li>
                                    <?php endif; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list['total'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 复制文本
    function copyText(text) {
        var input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        layer.msg('已复制：' + text);
    }
</script>

