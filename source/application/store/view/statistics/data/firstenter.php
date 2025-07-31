<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-margin-bottom">
            <div class="widget-ranking widget am-cf">
                <div class="widget-head">
                    <div class="widget-title">会员首次入库&统计入库量</div>
                </div>
                <div class="widget-body am-cf">
                     <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xl am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am fr">
                                <div class="am-form-group tpl-form-border-form am-fl">
                                    <input type="text" name="start_time"
                                           class="am-form-field"
                                           autocomplete="off"
                                           value="<?= $request->get('start_time', date('Y-m-01')) ?>" 
                                           placeholder="请选择起始日期"
                                           data-am-datepicker>
                                </div>
                                <div class="am-form-group tpl-form-border-form am-fl">
                                    <input type="text" name="end_time"
                                           class="am-form-field"
                                           autocomplete="off"
                                           value="<?= $request->get('end_time', date('Y-m-d')) ?>" 
                                           placeholder="请选择截止日期"
                                           data-am-datepicker>
                                </div>
                                <div class="am-form-group am-fl">
                                        <?php $extractserviceid = $request->get('service_id'); ?>
                                        <select name="service_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '专属客服'}">
                                            <option value=""></option>
                                            <option value="0"
                                                <?= $extractserviceid === '0' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($servicelist)): foreach ($servicelist as $item): ?>
                                                <option value="<?= $item['clerk_id'] ?>"
                                                    <?= $item['clerk_id'] == $extractserviceid ? 'selected' : '' ?>><?= $item['real_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                </div>
                                <div class="am-form-group am-fl">
                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                        
                                        <div class="am-input-group-btn">
                                            <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black">
                        <thead>
                        <tr>
                            <th class="am-text-center">用户昵称</th>
                            <th class="am-text-left">用户编号</th>
                            <th class="am-text-center">手机号</th>
                            <th class="am-text-center">归属客服</th>
                            <th class="am-text-center">首次入库时间</th>
                            <th class="am-text-center">当前时间周期内入库数量</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                        <tr>
                            <td class="am-text-middle am-text-center">
                                <span><?= $item['nickName'] ?></span>
                            </td>
                            <td class="am-text-middle">
                                <p class="ranking-item-title am-text-truncate"><?= $item['user_id'] ?></p>
                            </td>
                            <td class="am-text-middle am-text-center"><?= $item['mobile'] ?></td>
                            <td class="am-text-middle am-text-center"><?= $item['service_name'] ?></td>
                            <td class="am-text-middle am-text-center"><?= $item['first_enter_time'] ?></td>
                            <td class="am-text-middle am-text-center"><?= $item['total_packages'] ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="11" class="am-text-center">暂无记录</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

