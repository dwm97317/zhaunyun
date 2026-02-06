<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">订单列表
                    <?php if($dataType=='verify'): ?>
                    <small class="tipssmall">(提示：用户提交打包或仓管提交打包后，会在待查验状态，完成包裹的拆包并打包后，将包裹状态改为完成查验，订单将进入待发货阶段)</small>
                    <?php endif ;?>
                    <?php if($dataType=='pay'): ?>
                    <small class="tipssmall">(提示：只要客户没有付款，不管支付模式是货到付款还是立即支付，都可以在此列表中看到)</small>
                    <?php endif ;?>
                    </div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form search-content" id="searchContent" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <?php if($dataType=='all' && isset($_GET['inpack_type'])): ?>
                            <input type="hidden" name="inpack_type" value="<?= $request->get('inpack_type') ?>">
                            <?php endif ;?>
                            <div class="am-u-sm-12 am-u-md-12">
                                <div class="am fl">
                                    <div class="am-form-group am-fl">
                                        <?php $extractpageno = $request->get('limitnum'); ?>
                                        <select name="limitnum"
                                                data-am-selected="{btnSize: 'sm', placeholder: '显示条数'}">
                                            <?php if(isset($adminstyle['pageno'])): ?>
                                            <option value="<?= $adminstyle['pageno']['inpack'] ?>" <?=  $adminstyle['pageno']['inpack'] == 500 ? 'selected' : '' ?>>系统默认<?= $adminstyle['pageno']['inpack'] ?>条</option>
                                            <?php endif;?>
                                            <option value="15" <?= $extractpageno == 15 ? 'selected' : '' ?> >显示15条</option>
                                            <option value="30" <?= $extractpageno == 30 ? 'selected' : '' ?>>显示30条</option>
                                            <option value="50" <?= $extractpageno == 50 ? 'selected' : '' ?>>显示50条</option>
                                            <option value="100" <?= $extractpageno == 100 ? 'selected' : '' ?>>显示100条</option>
                                            <option value="200" <?= $extractpageno== 200 ? 'selected' : '' ?>>显示200条</option>
                                            <option value="500" <?= $extractpageno == 500 ? 'selected' : '' ?>>显示500条</option>
                                        </select>
                                    </div>
                                   
                                    <?php if($dataType=='all'): ?>
                                    <div class="am-form-group am-fl">
                                        <?php $extractStatus = $request->get('status'); ?>
                                        <select name="status"
                                                data-am-selected="{btnSize: 'sm', placeholder: '订单状态'}">
                                            <option value=""></option>
                                            <option value="-1,0,1,2,3,4,5,6,7,8,9"
                                                <?= $extractStatus === '0' ? 'selected' : '' ?>>全部
                                            </option>
                                            <option value="1"
                                                <?= $extractStatus === '1' ? 'selected' : '' ?>>待查验
                                            </option>
                                            <!--1 状态 1 待查验 2 待支付 3 已支付 4 拣货中 5 已打包  6已发货 7 已到货 8 已完成  9已取消-->
                                            <option value="2,3,4,5"
                                                <?= $extractStatus === '2' ? 'selected' : '' ?>>待发货
                                            </option>
                                            <option value="6"
                                                <?= $extractStatus === '6' ? 'selected' : '' ?>>已发货
                                            </option>
                                            <option value="7"
                                                <?= $extractStatus === '7' ? 'selected' : '' ?>>已到货
                                            </option>
                                            <option value="8"
                                                <?= $extractStatus === '8' ? 'selected' : '' ?>>已完成
                                            </option>
                                            <option value="9"
                                                <?= $extractStatus === '-1' ? 'selected' : '' ?>>问题件
                                            </option>
                                        </select>
                                    </div>
                                    <?php endif;?>
                                    <div class="am-form-group am-fl">
                                        <?php $extractispay = $request->get('is_pay'); ?>
                                        <!--支付状态 [1,已支付 2 未支付,3 待审核]-->
                                        <select name="is_pay"
                                                data-am-selected="{btnSize: 'sm', placeholder: '支付状态'}">
                                            <option value=""></option>
                                            <option value="0">全部</option>
                                            <option value="1"
                                                <?= $extractispay === '1' ? 'selected' : '' ?>>已支付
                                            </option>
                                            <option value="2"
                                                <?= $extractispay === '2' ? 'selected' : '' ?>>未支付
                                            </option>
                                            <option value="3"
                                                <?= $extractispay === '3' ? 'selected' : '' ?>>待审核
                                            </option>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $extractispay = $request->get('is_doublecheck'); ?>
                                        <!--支付状态 [1,已支付 2 未支付,3 待审核]-->
                                        <select name="is_doublecheck"
                                                data-am-selected="{btnSize: 'sm', placeholder: '费用审核状态'}">
                                            <option value=""></option>
                                            <option value="-1">全部</option>
                                            <option value="0"
                                                <?= $extractispay === '0' ? 'selected' : '' ?>>未审核
                                            </option>
                                            <option value="1"
                                                <?= $extractispay === '1' ? 'selected' : '' ?>>已审核
                                            </option>
                                        </select>
                                    </div>
                                    <?php if ($store['user']['is_super']==1): ?>
                                    <div class="am-form-group am-fl">
                                        <?php $extractShopId = $request->get('extract_shop_id'); ?>
                                        <select name="extract_shop_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '仓库名称'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $extractShopId === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($shopList)): foreach ($shopList as $item): ?>
                                                <option value="<?= $item['shop_id'] ?>"
                                                    <?= $item['shop_id'] == $extractShopId ? 'selected' : '' ?>><?= $item['shop_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <?php endif;?>
                                    <div class="am-form-group am-fl">
                                        <?php $extractlineid = $request->get('line_id'); ?>
                                        <select name="line_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '路线名称'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $extractlineid === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($lineList)): foreach ($lineList as $item): ?>
                                                <option value="<?= $item['id'] ?>"
                                                    <?= $item['id'] == $extractlineid ? 'selected' : '' ?>><?= $item['name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $extractserviceid = $request->get('service_id'); ?>
                                        <select name="service_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '专属客服'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $extractserviceid === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($servicelist)): foreach ($servicelist as $item): ?>
                                                <option value="<?= $item['clerk_id'] ?>"
                                                    <?= $item['clerk_id'] == $extractserviceid ? 'selected' : '' ?>><?= $item['real_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $orderparam = $request->get('orderparam'); ?>
                                        <select name="orderparam"
                                                data-am-selected="{btnSize: 'sm', placeholder: '排序参数'}">
                                            <option value=""></option>
                                            <option value="created_time" <?= $orderparam == 'created_time' ? 'selected' : '' ?>>提交打包时间排序</option>
                                            <option value="pay_time" <?= $orderparam == 'pay_time' ? 'selected' : '' ?>>支付完成时间排序</option>
                                            <option value="pick_time" <?= $orderparam == 'pick_time' ? 'selected' : '' ?>>打包完成时间排序</option>
                                            <option value="settle_time" <?= $orderparam == 'settle_time' ? 'selected' : '' ?>>佣金结算时间排序</option>
                                            <option value="sendout_time" <?= $orderparam == 'sendout_time' ? 'selected' : '' ?>>订单发货时间排序</option>
                                            <option value="receipt_time" <?= $orderparam == 'receipt_time' ? 'selected' : '' ?>>用户签收时间排序</option>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $descparam = $request->get('descparam'); ?>
                                        <select name="descparam"
                                                data-am-selected="{btnSize: 'sm', placeholder: '排序方式'}">
                                            <option value=""></option>
                                            <option value="desc" <?= $descparam == 'desc' ? 'selected' : '' ?>>降序排序(大到小，新到旧)</option>
                                            <option value="asc" <?= $descparam == 'asc' ? 'selected' : '' ?>>升序排序(小到大，旧到新)</option>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $extracttimetype = $request->get('time_type'); ?>
                                        <select name="time_type"
                                                data-am-selected="{btnSize: 'sm', placeholder: '时间类型'}">
                                            <option value="created_time" <?= $extracttimetype == 'created_time' ? 'selected' : '' ?>>提交打包时间</option>
                                            <option value="pay_time" <?= $extracttimetype == 'pay_time' ? 'selected' : '' ?>>支付完成时间</option>
                                            <option value="pick_time" <?= $extracttimetype == 'pick_time' ? 'selected' : '' ?>>打包完成时间</option>
                                            <option value="settle_time" <?= $extracttimetype == 'settle_time' ? 'selected' : '' ?>>佣金结算时间</option>
                                            <option value="sendout_time" <?= $extracttimetype == 'sendout_time' ? 'selected' : '' ?>>订单发货时间</option>
                                            <option value="receipt_time" <?= $extracttimetype == 'receipt_time' ? 'selected' : '' ?>>用户签收时间</option>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input style="padding:6px 5px;" type="text" name="start_time"
                                               class="am-form-field"
                                               value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input style="padding:6px 5px;" type="text" name="end_time"
                                               class="am-form-field"
                                               value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group am-fl" style="padding-bottom:1px;">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <textarea cols="200" rows="2"  class="am-form-field" name="tr_number"
                                                   placeholder="可输入多个发货单号,按换车换行" value="<?= $request->get('tr_number') ?>"></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="am-form-group am-fl" style="padding-bottom:1px;">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input style="width:250px;" type="text" class="am-form-field" name="order_sn"
                                                   placeholder="请输入平台订单号或转运单号" value="<?= $request->get('order_sn') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl" style="padding-bottom:1px;">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input style="width:250px;" type="text" class="am-form-field" name="batch_no"
                                                   placeholder="请输入批次号或提单号" value="<?= $request->get('batch_no') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl" style="padding-bottom:2px;">
                                        <?php $extracttimetype = $request->get('search_type'); ?>
                                        <select name="search_type"
                                                data-am-selected="{btnSize: 'sm', placeholder: '请选择查询类型'}">
                                            <option value="all" <?= $extracttimetype == 'all' ? 'selected' : '' ?>>模糊查询</option>
                                            <option value="member_id" <?= $extracttimetype == 'member_id' ? 'selected' : '' ?>>用户ID</option>
                                            <option value="user_code" <?= $extracttimetype == 'user_code' ? 'selected' : '' ?>>用户CODE</option>
                                            <option value="user_mark" <?= $extracttimetype == 'user_mark' ? 'selected' : '' ?>>用户唛头</option>
                                            <option value="nickName" <?= $extracttimetype == 'nickName' ? 'selected' : '' ?>>用户昵称</option>
                                            <option value="mobile" <?= $extracttimetype == 'mobile' ? 'selected' : '' ?>>手机号</option>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input autocomplete="off" type="text" class="am-form-field" name="search"
                                                   placeholder="请输入用户昵称或ID或用户编号" value="<?= $request->get('search') ?>">
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
                   <div class="page_toolbar am-margin-bottom-xs am-cf" style="margin-bottom:20px; margin-left:15px;">
                        <?php if (checkPrivilege('package.index/changeuser')): ?>
                        <button type="button" id="j-upuser" class="am-btn am-btn-success am-radius"><i class="iconfont icon-yonghu "></i> 修改所属用户</button>
                        <?php endif;?>
                        <!--状态变更-->
                        <?php if (checkPrivilege('tr_order/upsatatus')): ?>
                        <button type="button" id="j-upstatus" class="am-btn am-btn-secondary am-radius"><i class="iconfont icon-755danzi"></i> 状态变更</button>
                        <?php endif;?>
                        
                        <!--合并订单-->
                        <?php if (checkPrivilege('tr_order/hedan')): ?>
                        <?php if($dataType=='verify' || $dataType=='all'): ?>
                        <button type="button" id="j-hedan" class="am-btn am-btn-danger am-radius"><i class="iconfont  icon-hebing"></i> 合并订单</button>
                        <?php endif;endif;?>
                        
                        <!--批量更新订单动态-->
                        <?php if (checkPrivilege('tr_order/alllogistics')): ?>
                        <button type="button" id="j-wuliu" class="am-btn am-btn-warning am-radius"><i class="iconfont icon-guojiwuliu"></i> 批量更新订单动态</button>
                        <?php endif;?>
                        
                        <!--批量打印云面单-->
                        <button type="button" id="j-batch-cloud-print" class="am-btn am-btn-secondary am-radius"><i class="iconfont icon-dayinji_o"></i> 批量打印云面单</button>

                        <!--批量打印面单-->
                        <?php if (checkPrivilege('tr_order/expressbillbatch')): ?>
                        <button type="button" id="j-batch-print" class="am-btn am-btn-warning am-radius"><i class="iconfont icon-dayinji_o"></i> 批量打印面单</button>
                        <?php endif;?>

                        
                        <!--批量推送到渠道商-->
                        <?php if (checkPrivilege('tr_order/sendtoqudaoshang')): ?>
                        <button type="button" id="j-batch-push" class="am-btn am-btn-primary am-radius"><i class="iconfont icon-fasong"></i> 批量推送</button>
                        <?php endif;?>
                        
                        <!--加入拼团-->
                        <?php if (checkPrivilege('tr_order/pintuan')): ?>
                        <button type="button" id="j-pintuan" class="am-btn am-btn-success am-radius"><i class="iconfont icon-pintuan"></i> 加入拼团</button>
                        <?php endif;?>
                        
                        <!--加入批次-->
                        <?php if (checkPrivilege('batch/addtobatch')): ?>
                        <button type="button" id="j-batch" class="am-btn am-btn-secondary am-radius"><i class="iconfont icon-pintuan"></i> 加入批次</button>
                        <?php endif;?>
                        
                        <!--导出-->
                        <?php if (checkPrivilege('tr_order/loaddingoutexcel')): ?>
                        <button type="button" id="j-export" class="am-btn am-btn-warning am-radius"><i class="iconfont icon-daochu"></i> 导出订单</button>
                        <?php endif;?>
                        
                        <!--导出分成清单-->
                        <?php if (checkPrivilege('tr_order/exportinpack')): ?>
                        <?php if($dataType=='complete'): ?>
                        <button type="button" id="j-exportInpack" class="am-btn am-btn-success am-radius"><i class="iconfont icon-daochujiesuan"></i>导出分成清单</button>
                        <?php endif;?>
                        <?php endif;?>
                        
                        <!--导出清关模板-->
                        <?php if (checkPrivilege('tr_order/clearance')): ?>
                        <?php if($dataType=='sending'): ?>
                        <button type="button" id="j-clearance" class="am-btn am-btn-success am-radius"><i class="iconfont icon-daochujiesuan"></i>导出清关模板</button>
                        <?php endif;?>
                        <?php endif;?>
                        <div class="j-opSelect operation-select am-dropdown">
                            <button type="button" style="padding:7px 12px;color: #ffffff;background: #0d0fff;"
                                    class="am-dropdown-toggle am-btn am-btn-sm am-btn-secondary">
                                <span>批量操作</span>
                                <span class="am-icon-caret-down"></span>
                            </button>
                            <ul class="am-dropdown-content">
                                <li>
                                    <a id="changeLine" class="am-dropdown-item" 
                                       href="javascript:;">批量修改集运路线</a>
                                </li>
                                <li>
                                    <a id="sendpaymess" class="am-dropdown-item" 
                                       href="javascript:;">批量发送支付通知</a>
                                </li>
                                <?php if (checkPrivilege('tr_order/batchPayStatus')): ?>
                                <li>
                                    <a id="batchPayStatus" class="am-dropdown-item" 
                                       href="javascript:;">批量设置订单支付状态</a>
                                </li>
                                <?php endif;?>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- 订单类型Tab（仅在all_list页面显示） -->
                    <?php if($dataType=='all'): ?>
                    <?php 
                        $currentInpackType = $request->get('inpack_type');
                        $hasInpackType = isset($_GET['inpack_type']) || isset($_REQUEST['inpack_type']);
                    ?>
                    <div class="order-type-tabs" style="margin-left: 15px;">
                        <a class="tab-item <?= !$hasInpackType ? 'active' : '' ?>" 
                           href="<?= url('store/tr_order/all_list') ?>">全部 <span class="tab-count"><?= isset($inpackTypeCount['all']) ? $inpackTypeCount['all'] : 0 ?></span></a>
                        <a class="tab-item <?= $currentInpackType == '1' ? 'active' : '' ?>" 
                           href="<?= url('store/tr_order/all_list') ?>&inpack_type=1">拼团 <span class="tab-count"><?= isset($inpackTypeCount['type_1']) ? $inpackTypeCount['type_1'] : 0 ?></span></a>
                        <a class="tab-item <?= $currentInpackType == '2' ? 'active' : '' ?>" 
                           href="<?= url('store/tr_order/all_list') ?>&inpack_type=2">直邮 <span class="tab-count"><?= isset($inpackTypeCount['type_2']) ? $inpackTypeCount['type_2'] : 0 ?></span></a>
                        <a class="tab-item <?= $currentInpackType == '3' ? 'active' : '' ?>" 
                           href="<?= url('store/tr_order/all_list') ?>&inpack_type=3">拼邮 <span class="tab-count"><?= isset($inpackTypeCount['type_3']) ? $inpackTypeCount['type_3'] : 0 ?></span></a>
                        <!-- 搜索折叠按钮 -->
                        <span class="search-toggle-btn" id="searchToggleBtn" style="margin-left: 20px;">
                            <i class="am-icon-filter"></i> 筛选条件 <i class="am-icon-chevron-down"></i>
                        </span>
                    </div>
                    <?php endif ;?>
                    
                    <?php if($dataType!='all'): ?>
                    <!-- 非all页面的筛选按钮 -->
                    <div style="margin-left: 15px; margin-bottom: 15px;">
                        <span class="search-toggle-btn" id="searchToggleBtn">
                            <i class="am-icon-filter"></i> 筛选条件 <i class="am-icon-chevron-down"></i>
                        </span>
                    </div>
                    <?php endif ;?>
                    
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black ">
                            <thead>
                            <tr>
                                <th><input id="checkAll" type="checkbox"></th>
                                <th>单号信息</th>
                                <th width='200px'>转运信息</th>
                                <th width='350px'>收货信息</th>
                                <?php if (checkPrivilege('tr_order/freelist')): ?>
                                <th>费用信息</th>
                                <?php endif;?>
                                <th>包裹信息</th>
                                <th>时间</th>
                                <th>支付状态</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                            <?php $status = [1=>'待查验',2=>'待发货',3=>'待发货','4'=>'待发货','5'=>'待发货','6'=>'已发货','7'=>'已到货','8'=>'已完成','-1'=>'问题件',9=>'已取消']; ?>
                            <?php $paytime_status = [ 1=>'已支付',2=>'未支付',3=>'支付待审核'] ; ?>
                        
                                <tr>
                                    <td class="am-text-middle">
                                       <input name="checkIds" type="checkbox" value="<?= $item['id'] ?>" data-t-number="<?= $item['t_number'] ?>"> 
                                    </td>

                                    <td class="am-text-middle">
                                        系统单号：<?= $item['order_sn'] ?><br>
                                        <?php if ($item['inpack_type']==1): ?> 
                                            拼团订单：<a href="<?= url('store/apps.sharing.order/edit', ['order_id' => $item['sharingorder']['order_id']]) ?>">
                                                <span class="am-badge"><?= $item['sharingorder']['title'] ?? $item['sharingorder']['order_sn'] ?></span>
                                            </a>
                                            <?php if (isset($item['sharingorder']['title'])): ?>
                                            （<?= $item['sharingorder']['order_sn'] ?>）<br>
                                            <?php endif; ?>
                                        <?php endif ;?>
                                        <?php if ($item['inpack_type']==1): ?> 
                                            <span class="am-badge am-badge-secondary">拼团订单</span>
                                        <?php endif ;?>
                                        <?php if ($item['inpack_type']==2): ?> 
                                            <span class="am-badge am-badge-secondary">直邮订单</span>
                                        <?php endif ;?>
                                        <?php if ($item['inpack_type']==3): ?> 
                                            <span class="am-badge am-badge-success">拼邮订单</span>
                                        <?php endif ;?>
                                        <?php if ($item['is_exceed']==1): ?> 
                                            <span class="am-badge am-badge-danger">超时订单</span>
                                        <?php endif ;?>
                                        <?php if ($item['batch_id']): ?> 
                                        批次号：<?= $item['batch']['batch_name'] ?><br>
                                        提单号/装箱号：<?= $item['batch']['batch_no'] ?><br>
                                        <?php endif ;?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if (!empty($item['user']['service'])): ?> 
                                        专属客服：<?= $item['user']['service']['real_name']; ?></br>
                                        <?php endif ;?>
                                        <span class="am-badge <?= $item['delivery_method']==1?'am-badge-success':'am-badge-secondary' ?>">
                                            <?= $item['delivery_method']==1?"转运":"自提"   ?>
                                        </span></br>
                                        <?php if (!empty($item['t_order_sn'])): ?> 
                                        承运商:
                                        <span style="cursor:pointer" text="<?= $item['t_name'] ?>" onclick="copyUrl2(this)"><?= $item['t_name'] ?></span></br>
                                        <?php 
                                        // 检查是否有多个箱子
                                        $hasMultipleBoxes = !empty($item['packageitems']) && count($item['packageitems']) > 1;
                                        ?>
                                        <?php if ($hasMultipleBoxes): ?>
                                            <!-- 多箱情况：显示母单 + 子单 -->
                                            国际单号:
                                            <span style="cursor:pointer" text="<?= $item['t_order_sn'] ?>" onclick="copyUrl2(this)"><?= $item['t_order_sn'] ?></span>
                                            <?php 
                                            // 获取母单（第一个箱子）的重量
                                            $motherWeight = isset($item['packageitems'][0]['weight']) ? $item['packageitems'][0]['weight'] : 0;
                                            if ($motherWeight > 0): 
                                            ?>
                                            <span style="color:#999;font-size:11px;">(<?= $motherWeight ?>kg)</span>
                                            <?php endif; ?>
                                            <span class="am-badge am-badge-primary am-radius" style="font-size:10px;">母单</span>
                                            <a href="javascript:;" onclick="getlog(this)" value="<?= $item['id'] ?>" >[物流]</a>
                                            <a href="javascript:;" onclick="copyAllWaybills(this)" data-waybills="<?php 
                                                $waybills = [];
                                                foreach ($item['packageitems'] as $box) {
                                                    if (!empty($box['t_order_sn'])) {
                                                        $waybills[] = $box['t_order_sn'];
                                                    }
                                                }
                                                echo implode(',', $waybills);
                                            ?>" style="margin-left:5px;">[复制全部]</a>
                                            <?php if ($item['status'] > 5): ?>
                                            <a href="javascript:;" onclick="printAllWaybills(<?= $item['id'] ?>)" 
                                               style="color:#1E9FFF;margin-left:5px;">
                                                <i class="am-icon-print"></i> 打印全部(<?= count($item['packageitems']) ?>)
                                            </a>
                                            <a href="javascript:;" onclick="printSingleWaybill(<?= $item['id'] ?>, '<?= $item['t_order_sn'] ?>')" 
                                               style="color:#1E9FFF;margin-left:5px;">
                                                <i class="am-icon-print"></i> 打印母单
                                            </a>
                                            <?php endif; ?>
                                            </br>
                                            <?php foreach ($item['packageitems'] as $index => $box): ?>
                                                <?php if ($index > 0 && !empty($box['t_order_sn'])): ?>
                                                    <span style="margin-left:20px;color:#999;">└ 子单:</span>
                                                    <span style="cursor:pointer" text="<?= $box['t_order_sn'] ?>" onclick="copyUrl2(this)"><?= $box['t_order_sn'] ?></span>
                                                    <span style="color:#999;font-size:11px;">(<?= $box['weight'] ?>kg)</span>
                                                    <?php 
                                                        $traceCode = isset($box['last_trace_code']) ? $box['last_trace_code'] : '';
                                                        if ($traceCode == '44'): ?>
                                                            <span class="am-badge am-badge-warning am-radius" style="font-size:10px;padding:2px 4px;">派送中</span>
                                                        <?php elseif ($traceCode == '80'): ?>
                                                            <span class="am-badge am-badge-success am-radius" style="font-size:10px;padding:2px 4px;">已签收</span>
                                                        <?php elseif (in_array($traceCode, ['50', '3036'])): ?>
                                                            <span class="am-badge am-badge-secondary am-radius" style="font-size:10px;padding:2px 4px;">已揽收</span>
                                                        <?php endif; ?>
                                                    <a href="javascript:;" onclick="getlog(this)" value="<?= $item['id'] ?>" >[物流]</a>
                                                    <?php if ($item['status'] > 5): ?>
                                                    <a href="javascript:;" onclick="printSingleWaybill(<?= $item['id'] ?>, '<?= $box['t_order_sn'] ?>')" 
                                                       style="color:#1E9FFF;margin-left:5px;">
                                                        <i class="am-icon-print"></i> 打印
                                                    </a>
                                                    <?php endif; ?>
                                                    </br>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <!-- 单箱：正常显示 -->
                                            国际单号:
                                            <span style="cursor:pointer" text="<?= $item['t_order_sn'] ?>" onclick="copyUrl2(this)"><?= $item['t_order_sn'] ?></span>
                                            <a href="javascript:;" onclick="getlog(this)" value="<?= $item['id'] ?>" >[物流]</a>
                                            <?php if ($item['status'] > 5): ?>
                                            <a href="javascript:;" onclick="printSingleWaybill(<?= $item['id'] ?>, '<?= $item['t_order_sn'] ?>')" 
                                               style="color:#00b894;margin-left:5px;">
                                                <i class="am-icon-print"></i> 打印
                                            </a>
                                            <?php endif; ?>
                                            </br>
                                        <?php endif; ?>
                                        <?php endif ;?>
                                        <?php if (!empty($item['t2_order_sn'])): ?>
                                        转运商:
                                        <span style="cursor:pointer" text="<?= $item['t2_name'] ?>" onclick="copyUrl2(this)"><?= $item['t2_name'] ?></span></br>
                                        转单单号:
                                        <span style="cursor:pointer" text="<?= $item['t2_order_sn'] ?>" onclick="copyUrl2(this)"><?= $item['t2_order_sn'] ?></span></br>
                                        <?php endif ;?>
                                        线路:
                                        <span style="cursor:pointer" text="<?= $item['line']['name'] ?>" onclick="copyUrl2(this)"><?= $item['line']['name'] ?></span></br>
                                        寄件仓库:
                                        <span style="cursor:pointer" text="<?= $item['storage']['shop_name'] ?>" onclick="copyUrl2(this)"><?= $item['storage']['shop_name'] ?></span></br>
                                        取件仓库:
                                        <span style="cursor:pointer" text="<?= $item['shop']['shop_name'] ?>" onclick="copyUrl2(this)"><?= $item['shop']['shop_name'] ?></span></br>
                                    </td>
                                    <td class="am-text-middle" >
                                        用户昵称:<span style="color:#56a6ed;cursor:pointer"><?= $item['nickName']; ?></span>
                                        <span style="color:#ff6666;cursor:pointer" text="<?= $item['nickName']; ?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php if($set['usercode_mode']['is_show']!=1) :?>
                                        用户ID:<?= $item['member_id']; ?></br>
                                        <?php endif;?>
                                        <?php if($set['usercode_mode']['is_show']!=0) :?>
                                        用户Code:<?= $item['user']['user_code']; ?>
                                        <span style="color:#ff6666;cursor:pointer" text="<?= $item['user']['user_code']; ?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif;?>
                                        <?php if(!empty($item['address'])): ?>
                                        收件人:<?= $item['address']['name'] ?>
                                        <span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['name'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        
                                        电话:<?= $item['address']['phone'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['phone'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php if ($userclient['address']['reciveaddress_setting']['is_identitycard']==1): ?> 
                                        身份证:<?= $item['address']['identitycard'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['identitycard'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($userclient['address']['reciveaddress_setting']['is_clearancecode']==1): ?> 
                                        通关代码:<?= $item['address']['clearancecode'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['clearancecode'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        地址:国家/地区：<?= $item['address']['country'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['country'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        
                                        <?php if ($userclient['address']['reciveaddress_setting']['is_province']==1): ?> 
                                        省/州：<?= $item['address']['province'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['province'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($userclient['address']['reciveaddress_setting']['is_city']==1): ?> 
                                        市：<?= $item['address']['city'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['city'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <!--区：<?= $item['address']['region']=='0'?'未填':$item['address']['region']?></br>-->
                                        <?php if ($userclient['address']['reciveaddress_setting']['is_street']==1): ?>
                                        街道：<?= $item['address']['street']=='0'?'未填':$item['address']['street']?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['street'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($userclient['address']['reciveaddress_setting']['is_door']==1): ?> 
                                        门牌：<?= $item['address']['door'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['door'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                         <?php endif ;?>
                                        <?php if ($userclient['address']['reciveaddress_setting']['is_detail']==1): ?> 
                                        详细地址：<?= $item['address']['detail'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['detail'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        拼接详细地址：<span style="word-break:break-all;"><?= $item['address']['chineseregion'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['chineseregion'];?>" onclick="copyUrl2(this)">[复制]</span></span></br>
                                        <?php endif ;?>
                                        
                                        <?php if ($userclient['address']['reciveaddress_setting']['is_code']==1): ?> 
                                        邮编：<?= $item['address']['code']==''?'未填': $item['address']['code']?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['code'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($userclient['address']['reciveaddress_setting']['is_email']==1): ?> 
                                        邮箱：<?= !isset($item['address']['email'])?'未填':$item['address']['email'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['email'];?>" onclick="copyUrl2(this)">[复制]</span>
                                        <?php endif ;?>
                                        <?php else: ?>
                                        暂无收货地址信息
                                        <?php endif; ?>
                                    </td>
                                    <?php if (checkPrivilege('tr_order/freelist')): ?>
                                    <td class="am-text-middle">
                                        基础线路费用:<span style="color:#ff6666;cursor:pointer" text="<?= $item['free'] ?>" onclick="copyUrl2(this)"><?= $item['free'] ?></span></br>
                                        
                                        保险费:<span style="color:#ff6666;cursor:pointer" text="<?= $item['insure_free'] ?>" onclick="copyUrl2(this)"><?= $item['insure_free'] ?></span></br>
                                        
                                        包装费:<span style="color:#ff6666;cursor:pointer" text="<?= $item['pack_free'] ?>" onclick="copyUrl2(this)"><?= $item['pack_free'] ?></span></br>

                                        其他费用:<span style="color:#ff6666;cursor:pointer" text="<?= $item['other_free'] ?>" onclick="copyUrl2(this)"><?= $item['other_free'] ?></span></br>
                                        
                                        优惠金额：<span style="color:#ff6666;cursor:pointer" text="<?= $item['user_coupon_money'] ?>" onclick="copyUrl2(this)"><?= $item['user_coupon_money']; ?></span></br>
                                        
                                        费用合计:<span style="color:#ff6666;cursor:pointer" text="<?= $item['free'] + $item['pack_free'] + $item['other_free'] + $item['insure_free']  ?>" onclick="copyUrl2(this)"><?= $item['free'] + $item['pack_free'] + $item['other_free'] + $item['insure_free'] ?></span></br>
                                        
                                        <?php if ($item['is_pay']==1 || $item['is_pay']==3): ?>
                                        实际支付：<span style="color:#ff6666;cursor:pointer" text="<?= $item['real_payment'] ?>" onclick="copyUrl2(this)"><?= $item['real_payment']; ?></span></br></br>
                                        <?php endif ;?>
                                        
                                        <?php if (isset($userclient['packit']['is_waitreceivedmoney']) && $userclient['packit']['is_waitreceivedmoney']==1): ?> 
                                        代收款:<span style="color:#ff6666;cursor:pointer" text="<?= $item['waitreceivedmoney'] ?>" onclick="copyUrl2(this)"><?= $item['waitreceivedmoney'] ?></span></br></br>
                                        <?php endif ;?>
                                        <?php if ($item['usercoupon']): ?>
                                        优惠券:<span style="color:#ff6666;cursor:pointer" text="<?= $item['usercoupon']['name'] ?>" onclick="copyUrl2(this)"><?= $item['usercoupon']['name'] ?></span></br>
                                        
                                        <?php endif ;?>
                                        
                                    </td>
                                    <?php endif ;?>
                                    <?php $line_type_unit = [ 10=>'g',20=>'kg',30=>'bls',40=>'cbm'] ; ?>
                                    <td class="am-text-middle">
                                        实际重量(<?= $set['weight_mode']['unit'] ?>):<?= $item['weight'] ?></br>
                                        体积重量(<?= $set['weight_mode']['unit'] ?>):<?= $item['volume'] ?></br>
                                        计费重量(<?= $set['weight_mode']['unit'] ?>):<?= $item['cale_weight'] ?></br>
                                        <?php if ($item['line'] && $item['line']['line_type_unit']): ?>
                                        线路重量(<?= $line_type_unit[$item['line']['line_type_unit']] ?>):<?= $item['line_weight'] ?></br></br>
                                        <?php endif ;?>
                                        共有 <?= count($item['packagelist'] ) ?> 个包裹 </br>
                                        <a href="<?= url('store/trOrder/package', ['id' => $item['id']]) ?>">查看包裹明细</a></br></br>
                                        
                                        共有 <?= count($item['packageitems']) ?> 个子订单/分箱 </br>
                                        <a href="<?= url('store/trOrder/orderdetail', ['id' => $item['id']]) ?>">查看子订单明细</a>
                                    </td>
                            
                                    <td class="am-text-middle">
                                        提交打包：<?= $item['created_time'] ?> </br>
                                        
                                        <?php if ($item['pay_time'] && $item['is_pay']==1): ?>
                                        支付时间：<?= $item['pay_time'] ?> </br>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['shoprk_time']): ?>
                                        到货时间：<?= $item['shoprk_time'] ?> </br>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['receipt_time']): ?>
                                        签收时间：<?= $item['receipt_time'] ?> </br>
                                        <?php endif; ?>
                                         
                                        <?php if ($item['status']==8 && $item['is_settled']==1): ?>
                                        结算时间：<?= $item['settle_time'] ?> </br>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['pick_time'] ): ?>
                                        打包完成：<?= $item['pick_time'] ?> </br>
                                        <?php endif; ?>
                                        <?php if ($item['cancel_time'] ): ?>
                                        取消时间：<?= $item['cancel_time'] ?> </br>
                                        <?php endif; ?>
                                        
                                    </td>
                                    
                                    <td class="am-text-middle">
                                        <span class="am-badge <?= $item['is_pay']==1?'am-badge-success':'am-badge-danger'?>">
                                            <?= $paytime_status[$item['is_pay']];?>
                                        </span><br>
                                        
                                        <?php if ($item['is_pay']==1): ?>
                                        <span class="am-badge <?= $item['is_pay']==1?'am-badge-success':'am-badge-danger'?>">
                                            <?= $item['is_pay_type']['text'];?>
                                        </span><br>
                                        <?php endif; ?>
                                        
                                        <span class="am-badge <?= $item['pay_type']['value']==1?'am-badge-warning':'am-badge-primary'?>">
                                            <?= $item['pay_type']['text'];?>
                                        </span>
                                        <?php if(isset($is_verify_free) && $is_verify_free == 1 && isset($item['is_doublecheck']) && $item['is_doublecheck'] == 0): ?>
                                        <br><span class="am-badge am-badge-warning" style="margin-top:5px;">
                                            费用未审核
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $status[$item['status']] ?>
                                        <span class="am-badge <?= $item['print_status_jhd']['value']==1?'am-badge-success':'am-badge-danger'?>">
                                            <?= $item['print_status_jhd']['text'];?>
                                        </span><br>
                                    </td>
                                    <td class="am-text-middle" style="width:200px;">
                                        <div class="tpl-table-black-operation">
                                            <!--编辑-->
                                            <?php if (checkPrivilege('tr_order/edit')): ?>
                                            <a href="<?= url('store/trOrder/edit', ['id' => $item['id']]) ?>">
                                                <i class="am-icon-pencil"></i> 编辑
                                            </a>
                                            <?php endif; ?>
                                            <!--详情-->
                                            <?php if (checkPrivilege('tr_order/orderdetail')): ?>
                                            <a href="<?= url('store/trOrder/orderdetail', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-xiangqing"></i> 详情
                                            </a>
                                            <?php endif; ?>
                                             <!--复制订单-->
                                            <?php if (checkPrivilege('tr_order/copyOrder')): ?>
                                            <a href="javascript:void(0);" class="j-copy-order" data-id="<?= $item['id'] ?>">
                                                <i class="am-icon-copy"></i> 复制订单
                                            </a>
                                            <?php endif; ?>
                                            <!--删除-->
                                            <?php if($item['status']==8 || $item['status']==-1): ?>
                                            <?php if (checkPrivilege('tr_order/orderdelete')): ?>
                                            <a href="javascript:void(0);"
                                               class="item-delete tpl-table-black-operation-del"
                                               data-id="<?= $item['id'] ?>">
                                                <i class="am-icon-trash"></i> 删除
                                            </a>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="tpl-table-black-operation" style="margin-top:10px">
                                            <?php if (checkPrivilege('tr_order/deliverysave')): ?>
                                            <?php if (in_array($item['status'],[2,3,4,5]) && (in_array($item['pay_type']['value'],[1,2]) || $item['is_pay']==1)): ?>
                                             <a href="<?= url('store/trOrder/delivery', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-baoguo_fahuo_o"></i> 发货
                                            </a>
                                            <?php endif ;?>
                                            <?php endif ;?>
                                            <!--打印面单-->
                                            <?php if (checkPrivilege('tr_order/expressbill')): ?>
                                            <?php if ($item['status']>5): ?>
                                            <a href="javascript:void(0);" data-id="<?= $item['id'] ?>" class="j-express">
                                                <i class="iconfont icon-dayinji_o"></i> 打印面单
                                            </a>
                                            <?php endif ;endif;?>
                                            <!--物流更新-->
                                            <?php if (checkPrivilege('tr_order/logistics')): ?>
                                            <?php if ($item['status']>=6): ?>
                                            <a  href="<?= url('store/trOrder/logistics', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-755danzi"></i> 物流更新
                                            </a>
                                            <?php endif ;endif;?>
                                            
                                            
                                         </div>
                                         <div class="tpl-table-black-operation" style="margin-top:10px">
                                            <!--打印标签-->
                                            <?php if (checkPrivilege('tr_order/expresslabel')): ?>
                                            <a class='tpl-table-black-operation-green j-label' href="javascript:void(0);" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-biaoqian"></i> 打印标签
                                            </a>
                                            <?php endif ;?>
                                            
                                            <?php if (checkPrivilege('tr_order/printpacklist')): ?>
                                            <a class='tpl-table-black-operation-del j-packlist' href="javascript:void(0);" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-biaoqian"></i> 打印拣货单
                                            </a>
                                            <?php endif ;?>
                                         </div>
                                         <div class="tpl-table-black-operation" style="margin-top:10px">
                                            <!--打印账单-->
                                            <?php if (checkPrivilege('tr_order/freelistlabel')): ?>
                                            <?php if ($item['status']>=2): ?>
                                             <a class='tpl-table-black-operation-del j-freelist' href="javascript:void(0);" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-biaoqian"></i> 打印账单
                                            </a>
                                            <?php endif ;?>
                                            <?php endif ;?>
                                            
                                            <a class='tpl-table-black-operation-del j-exportdetail' href="javascript:void(0);" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-daochu"></i> 导出包裹
                                            </a>
                                            
                                            <!--转单-->
                                            <?php if (checkPrivilege('tr_order/changesn')): ?>
                                            <?php if (in_array($item['status'],[6,7,8])): ?>
                                             <a href="<?= url('store/trOrder/changesn', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-baoguo_fahuo_o"></i> 转单
                                            </a>
                                            <?php endif ;?>
                                            <?php endif ;?>
                                            
                                         </div>
                                         <div class="tpl-table-black-operation" style="margin-top:10px">
                                             <?php if (checkPrivilege('tr_order/payyue') && $item['is_pay'] ==2) : ?>
                                            <a class='tpl-table-black-operation-warning j-payyue' href="javascript:void(0);" data-balance="0" data-price="0" data-name="<?= $item['nickName'] ?>" data-user_id="<?= $item['member_id'] ?>" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-dizhi"></i> 余额扣除
                                            </a>
                                             <?php endif ;?>
                                            <?php if (checkPrivilege('tr_order/cashforprice') && ($item['is_pay'] ==2 || $item['is_pay'] ==3)) : ?>
                                            <a class='tpl-table-black-operation-warning j-payxianjin' href="javascript:void(0);" data-balance="0" data-price="0" data-name="<?= $item['nickName'] ?>" data-user_id="<?= $item['member_id'] ?>" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-dizhi"></i> 现金收款
                                            </a>
                                            <?php endif ;?>
                                        </div>
                                        <div class="tpl-table-black-operation" style="margin-top:10px">
                                            <!--变更地址-->
                                            <?php if (checkPrivilege('tr_order/updateaddress')): ?>
                                             <a class='tpl-table-black-operation-green j-changeaddress' href="javascript:void(0);" data-user_id="<?= $item['member_id'] ?>" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-dizhi"></i> 变更地址
                                            </a>
                                            <?php endif ;?>
                                            
                                            <a class='tpl-table-black-operation-green j-invoice' href="javascript:void(0);" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-daochu"></i> 导出INVOICE
                                            </a>
                                        </div>
                                        
                                        <div class="tpl-table-black-operation" style="margin-top:10px">
                                            <?php if (checkPrivilege('tr_order/cancelorder') && in_array($item['status'],[1,2,3,4,5,6,7])): ?>
                                            <a class='tpl-table-black-operation-del j-cancel' href="javascript:void(0);" data-id="<?= $item['id'] ?>" data-is-pay="<?= $item['is_pay'] ?? 0 ?>" data-real-payment="<?= $item['real_payment'] ?? 0 ?>">
                                                <i class="iconfont icon-daochu"></i> 取消订单
                                            </a>
                                            <?php endif ;?>
                                            <?php if (checkPrivilege('tr_order/auditorder') && $item['is_pay']==3): ?>
                                            <a class='tpl-table-black-operation-green j-audit-order' href="javascript:void(0);" data-id="<?= $item['id'] ?>" style="margin-left:5px;">
                                                <i class="iconfont icon-shenhe"></i> 线下支付审核 
                                            </a>
                                            <?php endif ;?>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <?php if ($item['cancel_reason']): ?>
                                    <td colspan="3" class="am-text-left">取消原因：<?= $item['cancel_reason'] ?></td>
                                    <?php endif ;?>
                                    <td colspan="8" class="am-text-left">
                                        <?= $item['remark']?$item['remark']:'请输入订单备注' ?>
                                        <a class="j-audit" data-id="<?= $item['id'] ?>" data-remark="<?= $item['remark'] ?>" href="javascript:void(0);">
                                            <i class="am-icon-pencil"></i>
                                        </a>
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
<script id="tpl-dealer-apply" type="text/template">
    <div class="am-padding-top-sm">
        <form class="form-dealer-apply am-form tpl-form-line-form" method="post"
              action="<?= url('store/trOrder/changeRemark') ?>">
            <input type="hidden" name="id" value="{{ id }}">
            <div class="am-form-group">
                <label class="am-u-sm-3 am-form-label"> 备注信息 </label>
                <div class="am-u-sm-9">
                    <input type="text" class="tpl-form-input" name="remark" placeholder="请填写备注"
                           value="{{ remark }}">
                </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
        </a>
        <input type="hidden" name="user_id" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
</script>
<script id="tpl-grade" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包裹数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{ selectCount }} 包裹</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择用户
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                         <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius" onclick="doSelectUser()">
                                            <i class="am-icon-cloud-upload"></i> 选择用户
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                        </div>
                                        <div class="am-block">
                                            <small>选择后不可更改</small>
                                        </div>
                                    </div>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>
<script id="tpl-status" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包裹数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{ selectCount }} 包裹</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择状态
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="pack[status]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择线路'}">
                               
                               <option value="6">已发货</option>
                               <option value="7">已到货</option>
                               <option value="8">已完成</option>
                               <option value="5">回退到待发货</option>
                               <option value="9">回退到待查验</option>
                        </select>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>

<script id="tpl-wuliu" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包裹数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{ selectCount }} 包裹</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3  form-require am-form-label">轨迹模板 </label>
                    <div class="am-u-sm-9 am-u-end">
                         <select name="track_id" id="" data-am-selected="{searchBox: 1,maxHeight:300}">
                             <option value="">选择模板</option>
                         <?php if (isset($tracklist)):
                                foreach ($tracklist as $item): ?>
                                    <option value="<?= $item['track_id'] ?>"><?= $item['track_name'] ?></option>
                                <?php endforeach; endif; ?>
                         </select>
                         <div class="help-block">
                            <small>注：你可以在下方自定义轨迹，或者选择预设好的轨迹</small>
                    </div>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        输入物流状态
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="logistics_describe" value="">
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择物流时间
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text"  name="created_time" placeholder="请选择起始日期" value="<?php echo date("Y-m-d H:i:s",time()) ?>" id="datetimepicker" class="am-form-field">
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-shelf" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择集运单数
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <p class='am-form-static'> 共选中 {{ selectCount }} 订单</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择拼团
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="pintuan_id"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" onchange="getSelectData(this)" data-select_type='shelf'>
                                        <option value="">请选择</option>
                                        <?php if (isset($pintuanlist) && !$pintuanlist->isEmpty()):
                                            foreach ($pintuanlist as $item): ?>
                                                <option value="<?= $item['order_id'] ?>"><?= $item['title'] ?> - <?= $item['user']['nickName'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>
<script id="tpl-batch" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择集运单数
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <p class='am-form-static'> 共选中 {{ selectCount }} 订单</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择批次
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="batch_id"
                                data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" onchange="getSelectData(this)" data-select_type='shelf'>
                            <option value="">请选择</option>
                            <?php if (isset($batchlist) && !$batchlist->isEmpty()):
                                foreach ($batchlist as $item): ?>
                                    <option value="<?= $item['batch_id'] ?>"><?= $item['batch_name'] ?> - <?= $item['batch_no'] ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<!-- 顺丰云打印插件 -->
<script src="https://scp-tcdn.sf-express.com/prd/sdk/lodop/2.7/SCPPrint.js"></script>
<script>
     function doSelectUser(){
           var $userList = $('.user-list');
            $.selectData({
                title: '选择用户',
                uri: 'user/lists',
                dataIndex: 'user_id',
                done: function (data) {
                    var user = [data[0]];
                    console.log(user,98999);
                    $userList.html(template('tpl-user-item', user));
                }
            });
    }
    
    $(function () {
        
        /**
         * 修改会员
         */
        $('#j-upuser').on('click', function () {
             var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '修改会员'
                , area: '460px'
                , content: template('tpl-grade', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('/store/tr_Order/changeUser') ?>',
                        data: {selectIds:data.selectId}
                    });
                    return true;
                }
            });
        });
        
        
        $('#datetimepicker').datetimepicker({
          format: 'yyyy-mm-dd hh:ii'
        });
        
        
        $('#datetimepicker').datetimepicker().on('changeDate', function(ev){
            // $('#datetimepicker').datetimepicker('hide');
          });
          
          
        /**
         * 审核操作
         */
        $('.j-audit').click(function () {
            var $this = $(this);
            layer.open({
                type: 1
                , title: '修改备注信息'
                , area: '500px'
                , offset: 'auto'
                , anim: 1
                , closeBtn: 1
                , shade: 0.3
                , btn: ['确定', '取消']
                , content: template('tpl-dealer-apply', $this.data())
                , success: function (layero) {
                    // 注册radio组件
                    layero.find('input[type=radio]').uCheck();
                }
                , yes: function (index, layero) {
                    // 表单提交
                    layero.find('.form-dealer-apply').ajaxSubmit({
                        type: 'post',
                        dataType: 'json',
                        success: function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        }
                    });
                    layer.close(index);
                }
            });
        });
    });
     
</script>
<script id="tpl-pay-status" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择订单数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{ selectCount }} 个订单</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        支付状态
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="pay_status"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择支付状态'}">
                               <option value="">请选择</option>
                               <option value="1">已支付</option>
                               <option value="2">未支付</option>
                               <option value="3">待审核</option>
                        </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        支付方式
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="pay_type"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择支付方式'}">
                               <option value="">请选择</option>
                               <option value="0">后台操作</option>
                               <option value="1">微信支付</option>
                               <option value="2">余额支付</option>
                               <option value="3">汉特支付</option>
                               <option value="4">OMIPAY</option>
                               <option value="5">现金支付</option>
                               <option value="6">线下支付</option>
                        </select>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>
<script id="tpl-errors" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-4 am-form-label form-require">
                        用户信息
                    </label>
                    <div class="am-u-sm-8">
                       <p class='am-form-static'>{{ name }}</p>
                    </div>
                </div>
               <div class="am-form-group">
                    <label class="am-u-sm-4 am-form-label form-require">
                        订单信息
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'>用户余额：{{ balance }} / 订单金额：{{ price }}</p>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>
<script id="tpl-xianjin" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-4 am-form-label form-require">
                        用户信息
                    </label>
                    <div class="am-u-sm-8">
                       <p class='am-form-static'>{{ name }}</p>
                    </div>
                </div>
               <div class="am-form-group">
                    <label class="am-u-sm-4 am-form-label form-require">
                        订单信息
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'>订单金额：{{ price }}</p>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>
<script id="tpl-log" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <div class="am-u-sm-12">
                    <table class="am-table">
                        <thead>
                            <tr class="am-primary">
                                <th>操作时间</th>
                                <th>轨迹内容</th>
                                <th>操作人</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{each data value}}
                                <tr class="am-success">
                                    <td>{{ value.created_time }}</td>
                                    <td>{{ value.logistics_describe }}</td>
                                    <td>{{ value.clerk?value.clerk.real_name:'' }}</td>
                                </tr>
                            {{/each}}  
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-label" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <button onclick="printlabel(10,{{ inpack_id }})"   style="margin:10px;" type="button" class="am-btn-lg am-btn am-btn-primary ">标签模板1</button>
                    <button onclick="printlabel(20,{{ inpack_id }})" style="margin:10px;" type="button" class="am-btn-lg am-btn am-btn-secondary ">标签模板2</button>
                    <button onclick="printlabel(30,{{ inpack_id }})" style="margin:10px;" type="button" class="am-btn-lg am-btn am-btn-success ">标签模板3</button>
                    <button onclick="printlabel(50,{{ inpack_id }})" style="margin:10px;" type="button" class="am-btn-lg am-btn am-btn-success ">标签模板4</button>
                    <button onclick="printlabel(60,{{ inpack_id }})" style="margin:10px;" type="button" class="am-btn-lg am-btn am-btn-danger ">标签模板5</button>
                    <button onclick="printlabel(40,{{ inpack_id }})" style="margin:10px;" type="button" class="am-btn-lg am-btn am-btn-warning ">渠道 标 签</button>
                    
                </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-line" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择集运单数
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <p class='am-form-static'> 共选中 {{ selectCount }} 订单</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择批次
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="line_id"
                                data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}">
                            <option value="">请选择</option>
                            <?php if (isset($lineList) && !$lineList->isEmpty()):
                                foreach ($lineList as $item): ?>
                                    <option value="<?= $item['id'] ?>"><?= $item['name'] ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>
<script>
    var _render = false;
    var getSelectData = function(_this){
        if (_render){
            return 
        }
        var sType = _this.getAttribute('data-select_type');
        var api_group = {'shelf':'<?= url('store/shelf_manager.index/getShelf')?>','shelf_unit':'<?= url('store/shelf_manager.index/getshelf_unit')?>'};
        if (sType=='shelf'){
            var $selected = $('#select-shelf');
            var data = {'shop_id':_this.value}
        }
        if (sType=='shelf_unit'){
            var $selected = $('#select_shelf_unit');
            var data = {'shelf_id':_this.value}
        }
        
        $.ajax({
            type:"GET",
            url:api_group[sType],
            data:data,
            dataType:'json',
            success:function(res){
                var _data = res.msg.data;
                if (sType=='shelf'){
                    for (var i=0;i<_data.length;i++){
                        // _html += '<option value="">'+_data[i]['shelf_name']+'</option>';
                        $selected.append('<option value="' + _data[i]['id'] +'">' + _data[i]['shelf_name'] + '</option>');
                    }
                }else{
                    console.log(444);
                    for (var i=0;i<_data.length;i++){
                        // _html += '<option value="">'+_data[i]['shelf_name']+'</option>';
                        $selected.append('<option value="' + _data[i]['shelf_unit_id'] +'">' +_data[i]['shelf_unit_floor']+ '层'+ _data[i]['shelf_unit_no'] + '号</option>');
                    }
                }
                _render = true;
                setTimeout(function() {
                    _render = false;
                }, 10);
            }
        })
    }
    
    
    // 审核订单功能
    $('.j-audit-order').on('click', function () {
        var data = $(this).data();
        var auditUrl = "<?= url('store/trOrder/auditOrder') ?>";
        
        layer.open({
            type: 1,
            title: '审核订单',
            area: ['400px', '300px'],
            content: '<div style="padding: 20px;">' +
                '<div class="am-form-group">' +
                '<label class="am-form-label">审核状态：</label>' +
                '<select id="audit-status" class="am-form-field">' +
                '<option value="1">审核通过</option>' +
                '<option value="0">审核不通过</option>' +
                '</select>' +
                '</div>' +
                '<div class="am-form-group">' +
                '<label class="am-form-label">审核备注：</label>' +
                '<textarea id="audit-remark" class="am-form-field" rows="3" placeholder="请输入审核备注"></textarea>' +
                '</div>' +
                '</div>',
            btn: ['确定', '取消'],
            yes: function(index, layero) {
                var auditStatus = $('#audit-status').val();
                var auditRemark = $('#audit-remark').val();
                
                if (auditStatus == '1' && !auditRemark.trim()) {
                    layer.msg('审核通过时请输入审核备注');
                    return false;
                }
                
                $.post(auditUrl, {
                    id: data.id,
                    audit_status: auditStatus,
                    audit_remark: auditRemark
                }, function(result) {
                    if (result.code === 1) {
                        $.show_success(result.msg, result.url);
                        layer.close(index);
                    } else {
                        $.show_error(result.msg);
                    }
                });
            },
            btn2: function(index) {
                layer.close(index);
            }
        });
    });

    $(function () {
       checker = {
          num:0, 
          check:[],
          init:function(){
              this.check = document.getElementById('body').getElementsByTagName('input');
              this.num = this.check.length;
              this.bindEvent();
          },
          bindEvent:function(){
              var that = this;
              for(var i=0; i< this.check.length; i++){
                  this.check[i].onclick = function(){
                       var _check = that.isFullCheck();
                       if (_check){
                           document.getElementById('checkAll').checked = 'checked';
                       }else{
                           document.getElementById('checkAll').checked = '';
                       }
                  }
              }
              
              var  allCheck = document.getElementById('checkAll');
              allCheck.onclick = function(){
                  if (this.checked){
                      that.setFullCheck();
                  }else{
                      that.setFullCheck('');
                  }
              }
              
          },
          setFullCheck:function(checked='checked'){
             for (var ik =0; ik<this.num; ik++){
                  this.check[ik].checked = checked; 
              } 
          },
          isFullCheck:function(){
              var hasCheck = 0;
              for (var k =0; k<this.num; k++){
                   if (this.check[k].checked){
                       hasCheck++;
                   }
              }
              return hasCheck==this.num?true:false;
          },
          getCheckSelect:function(){
              var selectIds = [];
              for (var i=0;i<this.check.length;i++){
                    if (this.check[i].checked){
                       selectIds.push(this.check[i].value);
                    }
              }
              return selectIds;
          }
       }
       
       // 复制订单
        $('.j-copy-order').on('click', function () {
            var orderId = $(this).data('id');
            if (!orderId) {
                layer.alert('订单ID不存在', {icon: 5});
                return;
            }
            layer.confirm('确定要复制此订单吗？复制后将生成一个完全相同的新订单。', {
                title: '复制订单',
                icon: 3
            }, function (index) {
                $.ajax({
                    url: '<?= url('store/trOrder/copyOrder') ?>',
                    type: 'POST',
                    data: {id: orderId},
                    dataType: 'json',
                    success: function (result) {
                        if (result.code === 1) {
                            layer.msg(result.msg, {icon: 1}, function () {
                                // 刷新页面
                                window.location.reload();
                            });
                        } else {
                            layer.alert(result.msg, {icon: 5});
                        }
                    },
                    error: function () {
                        layer.alert('复制订单失败，请重试', {icon: 5});
                    }
                });
                layer.close(index);
            });
        });
       
        $('.j-cancel').on('click', function () {
            var $tabs, data = $(this).data();
            console.log(data,7656);
            var hedanurl = "<?= url('store/trOrder/cancelorder') ?>";
            
            // 转换数据类型
            var isPay = parseInt(data.isPay) || 0;
            var realPayment = parseFloat(data.realPayment) || 0;
            
            // 根据订单支付状态显示不同的提示内容
            var confirmContent = '是否确认取消订单';
            if (isPay == 1 && realPayment > 0) {
                confirmContent += '，如订单已支付则将支付的金额（' + realPayment + '）退还到用户余额';
            }
            confirmContent += '，取消订单后订单中的包裹单号将回退到待打包状态';
            
            layer.confirm(confirmContent, {title: '确认取消订单'}
                    , function (index) {
                        $.post(hedanurl, {id:data.id}, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
       
       checker.init();
        // 删除元素
        var url = "<?= url('store/trOrder/orderdelete') ?>";
        $('.item-delete').delete('id', url);

        

        /**
         * 注册操作事件
         * @type {jQuery|HTMLElement}
         */
        var $dropdown = $('.j-opSelect');
        $dropdown.dropdown();
        
        
        //余额抵扣集运
        $('.j-payyue').click(function (e) {
            var data = $(this).data();
            var user_id = $(this).data().user_id;
            var id=  $(this).data().id;
            
            if(!user_id){
                layer.alert('用户信息有误', {icon: 5});
                return false;
            }
            // data.balance = user_id;
            $.post('<?= url('store/trOrder/balanceAndPrice') ?>',{id:id,user_id:user_id}, function (result) {
                if(result.code == 1 ){
                    data.balance = result.data.balance;
                    data.price = result.data.price;
                    $.showModal({
                        title: '余额抵扣'
                        , area: '460px'
                        , content: template('tpl-errors', data)
                        , uCheck: true
                        , success: function ($content) {
                        }
                        , yes: function ($content) {
                            $.post('<?= url('store/trOrder/payyue') ?>',{id:id,user_id:user_id}, function (result) {
                                result.code === 1 ? $.show_success(result.msg, result.url)
                                    : $.show_error(result.msg);
                            });
                        }
                    });
                }else{
                  $.show_error(result.msg);   
                }
            });
        });
        
         //现金抵扣集运
        $('.j-payxianjin').click(function (e) {
            var data = $(this).data();
            var user_id = $(this).data().user_id;
            var id=  $(this).data().id;
            
            if(!user_id){
                layer.alert('用户信息有误', {icon: 5});
                return false;
            }
            
            $.post('<?= url('store/trOrder/balanceAndPrice') ?>',{id:id,user_id:user_id}, function (result) {
                if(result.code == 1 ){
                    data.balance = result.data.balance;
                    data.price = result.data.price;
                    $.showModal({
                        title: '现金收款'
                        , area: '460px'
                        , content: template('tpl-xianjin', data)
                        , uCheck: true
                        , success: function ($content) {
                        }
                        , yes: function ($content) {
                            $.post('<?= url('store/trOrder/cashforPrice') ?>',{id:id,user_id:user_id}, function (result) {
                                result.code === 1 ? $.show_success(result.msg, result.url)
                                    : $.show_error(result.msg);
                            });
                        }
                    });
                }else{
                  $.show_error(result.msg);   
                }
            });

        });
        
        
        
         /**
         * 加入批次
         */
        $('#j-batch').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            console.log(data.selectId)
            $.showModal({
                title: '将订单加入到批次中'
                , area: '460px'
                , content: template('tpl-batch', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/batch/addtobatch') ?>',
                        data: {selectIds:data.selectId},
                    });
                    return true;
                }
            });
        });
        
        /**
         * 批量设置订单支付状态
         */
        $('#batchPayStatus').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '批量设置订单支付状态'
                , area: '460px'
                , content: template('tpl-pay-status', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/trOrder/batchPayStatus') ?>',
                        data: {
                            selectIds: data.selectId
                        }
                    });
                    return true;
                }
            });
        });

            
        // 变更地址
        $('.j-changeaddress').click(function (e) {
            var user_id = $(this).data().user_id;
            if(!user_id){
                layer.alert('用户信息有误', {icon: 5});
                return false;
            }
            var id=  $(this).data().id;
            $.selectData({
                title: '变更地址',
                uri: 'Address/AddressList'+'/user_id/'+user_id,
                dataIndex: 'address_id',
                done: function (list) {
                    var data = {};
                    var select_ids = [];
                    if (list.length>1){
                        layer.alert('只能勾选一个', {icon: 5});
                        return;
                    }
                    console.log(list);
                    // 请求服务器修改地址
                    $.ajax({
                        type:"POST",
                        url:'<?= url('store/trOrder/updateAddress') ?>',
                        data:{id:id,address_id:list[0]["address_id"]},
                        dataType:"JSON",
                        success:function(result){
                           window.location.reload(true);
                        }
                    })
                    
                }
            });
        });
            
        /**
		 * 导出包裹
		 */
		$('#j-export').on('click', function() {
			var $tabs, data = $(this).data();
			var selectIds = checker.getCheckSelect();
			var serializeObj = {};
			var fordata = $(".toolbar-form").serializeArray().forEach(function(item) {
				if (item.name != 's') {
					serializeObj[item.name] = item.value;
				}

			});
			if (isEmpty(serializeObj['search']) && selectIds.length == 0) {
				layer.alert('请先选择包裹或者搜索后再点导出', {
					icon: 5
				});
				return;
			}
			// 弹出格式选择窗口
			layer.open({
				type: 1,
				title: '选择导出格式',
				area: ['400px', '200px'],
				content: '<div style="padding: 20px;">' +
						'<div style="margin-bottom: 20px;">' +
						'<button type="button" class="am-btn am-btn-primary am-btn-block" style="margin-bottom: 10px;" onclick="exportOrder(\'csv\')">导出为 CSV 格式</button>' +
						'<button type="button" class="am-btn am-btn-success am-btn-block" onclick="exportOrder(\'excel\')">导出为 Excel 格式</button>' +
						'</div>' +
						'</div>',
				success: function(layero, index) {
					// 将数据存储到全局变量，供导出函数使用
					window.exportData = {
						selectIds: selectIds,
						serializeObj: serializeObj
					};
				}
			});
		});
		
		// 导出订单函数
		window.exportOrder = function(format) {
			layer.closeAll();
			var loadIndex = layer.load(1);
			var exportData = window.exportData || {};
			$.ajax({
				type: 'post',
				url: "<?= url('store/trOrder/loaddingOutExcel') ?>",
				data: {
					selectId: exportData.selectIds,
					seach: exportData.serializeObj,
					format: format || 'csv'
				},
				dataType: "json",
				success: function(res) {
					layer.close(loadIndex);
					if (res.code == 1) {
						console.log(res.url.file_name);
						var a = document.createElement('a');
						document.body.appendChild(a);
						a.href = res.url.file_name;
						a.click();
						layer.msg('导出成功', {icon: 1});
					} else {
						layer.msg(res.msg || '导出失败', {icon: 2});
					}
				},
				error: function() {
					layer.close(loadIndex);
					layer.msg('导出失败', {icon: 2});
				}
			});
		};
				
				/**
				 * 导出包裹
				 */
				$('.j-exportdetail').on('click', function() {
					var $tabs, data = $(this).data();
				console.log(data,999)
					$.ajax({
						type: 'post',
						url: "<?= url('store/trOrder/exportInpackpackage') ?>",
						data: {
							id: data.id,
						},
						dataType: "json",
						success: function(res) {
							if (res.code == 1) {
								console.log(res.url.file_name);
								var a = document.createElement('a');
								document.body.appendChild(a);
								a.href = res.url.file_name;
								a.click();
							}
						}
					})
				});
				
				
				/**
				 * 导出包裹
				 */
				$('.j-invoice').on('click', function() {
					var $tabs, data = $(this).data();
				console.log(data,999)
					$.ajax({
						type: 'post',
						url: "<?= url('store/trOrder/invoice') ?>",
						data: {
							id: data.id,
						},
						dataType: "json",
						success: function(res) {
							if (res.code == 1) {
								console.log(res.url.file_name);
								var a = document.createElement('a');
								document.body.appendChild(a);
								a.href = res.url.file_name;
								a.click();
							}
						}
					})
				});
				
        
        
                /**
				 * 导出集运结算单
				 */
				$('#j-exportInpack').on('click', function() {
					var $tabs, data = $(this).data();
					var selectIds = checker.getCheckSelect();
					var serializeObj = {};
					var fordata = $(".toolbar-form").serializeArray().forEach(function(item) {
						if (item.name != 's') {
							serializeObj[item.name] = item.value;
						}

					});
					if (isEmpty(serializeObj['search']) && selectIds.length == 0) {
						layer.alert('请先选择订单或者搜索后再点导出', {
							icon: 5
						});
						return;
					}
					$.ajax({
						type: 'post',
						url: "<?= url('store/trOrder/exportInpack') ?>",
						data: {
							selectId: selectIds,
							seach: serializeObj
						},
						dataType: "json",
						success: function(res) {
							if (res.code == 1) {
								console.log(res.url.file_name);
								var a = document.createElement('a');
								document.body.appendChild(a);
								a.href = res.url.file_name;
								a.click();
							}
						}
					})
				});
				
				
				/**
				 * 导出集运清关文件
				 */
				$('#j-clearance').on('click', function() {
					var $tabs, data = $(this).data();
					var selectIds = checker.getCheckSelect();
					var serializeObj = {};
					var fordata = $(".toolbar-form").serializeArray().forEach(function(item) {
						if (item.name != 's') {
							serializeObj[item.name] = item.value;
						}

					});
					if (isEmpty(serializeObj['search']) && selectIds.length == 0) {
						layer.alert('请先选择订单或者搜索后再点导出', {
							icon: 5
						});
						return;
					}
					$.ajax({
						type: 'post',
						url: "<?= url('store/trOrder/clearance') ?>",
						data: {
							selectId: selectIds,
							seach: serializeObj
						},
						dataType: "json",
						success: function(res) {
							if (res.code == 1) {
								console.log(res.url.file_name);
								var a = document.createElement('a');
								document.body.appendChild(a);
								a.href = res.url.file_name;
								a.click();
							}
						}
					})
				});
				
				
				/**
				 * 修改集运路线
				 */
				$('#changeLine').on('click', function() {
					var $tabs, data = $(this).data();
					var selectIds = checker.getCheckSelect();
				    data.selectId = selectIds.join(',');
                    data.selectCount = selectIds.length;
					if (selectIds.length == 0) {
						layer.alert('请先选择集运订单', {
							icon: 5
						});
						return;
					}
					$.showModal({
                        title: '修改集运订单集运路线'
                        , area: '460px'
                        , content: template('tpl-line',data)
                        , uCheck: true
                        , success: function ($content) {}
                        , yes: function ($content) {
                            $content.find('form').myAjaxSubmit({
                                url: "<?= url('store/trOrder/changeLine') ?>",
                                data: {selectId:selectIds},
                            });
                            return true;
            				
                        }
                    });
					
				});
				
				/**
         * 合并订单
         */
        $('#sendpaymess').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            var hedanurl = "<?= url('store/trOrder/sendpaymess') ?>";
            if (selectIds.length==0){
                layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            console.log(selectIds,99);
            layer.confirm('请确定是否批量发送支付通知', {title: '批量发送支付通知'}
                    , function (index) {
                        $.post(hedanurl, {selectIds}, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
        
        
        /**
         * 修改入库状态
         */
        $('#j-upstatus').on('click', function(){
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '入库状态'
                , area: '460px'
                , content: template('tpl-status', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/trOrder/upsatatus') ?>',
                        data: {
                            selectIds:data.selectId
                        }
                    });
                    return true;
                }
            });
        });
       
        $(".j-express").on('click',function(){
           var data = $(this).data();
           $.ajax({
               url:'<?= url('store/trOrder/expressBill') ?>',
               type:"get",
               data:{id:data['id']},
               success:function(result){
                   console.log(result);

                    if(result.code ===0){
                       layer.alert(result.msg, {icon: 5});
                       return; 
                    }
                   
                   $.showModal({
                        title: '标签打印预览'
                        , area: '600px,700px'
                        , content: result
                        , success: function ($content) {
                            console.log($content)
                             
                        }
                        , yes: function ($content) {
                            PrintDiv(result)
                        }
                    });
               }
               
           })
        }); 
        
       
       
        
        //打印拣货单
        $(".j-packlist").on('click',function(){
           var data = $(this).data();
           $.ajax({
               url:'<?= url('store/trOrder/printpacklist') ?>',
               type:"get",
               data:{id:data['id']},
               success:function(result){
                   console.log(result);
                    if(result.code ===0){
                       layer.alert(result.msg, {icon: 5});
                       return; 
                    }
                   
                  $.showModal({
                        title: '账单打印预览'
                        , area: '600px,700px'
                        , content: result
                        , success: function ($content) {
                            console.log($content)
                             
                        }
                        , yes: function ($content) {
                            PrintDiv(result)
                            $.ajax({
                                url: '<?= url('store/trOrder/updatePrintStatus') ?>',
                                type: "post",
                                data: {id: data['id']},
                                success: function(response) {
                                    if (response.code === 1) {
                                        layer.msg('打印状态已更新', {icon: 1});
                                    } else {
                                        layer.alert(response.msg, {icon: 5});
                                    }
                                },
                                error: function() {
                                    layer.alert('更新打印状态失败', {icon: 5});
                                }
                            });
                        }
                    });
               }
               
           })
        });
        
        //打印账单
        $(".j-freelist").on('click',function(){
           var data = $(this).data();
           $.ajax({
               url:'<?= url('store/trOrder/freelistLabel') ?>',
               type:"get",
               data:{id:data['id']},
               success:function(result){
                   console.log(result);
                    if(result.code ===0){
                       layer.alert(result.msg, {icon: 5});
                       return; 
                    }
                   
                  $.showModal({
                        title: '账单打印预览'
                        , area: '600px,700px'
                        , content: result
                        , success: function ($content) {
                            console.log($content)
                             
                        }
                        , yes: function ($content) {
                            PrintDiv(result)
                        }
                    });
               }
               
           })
        });
        
        //打印标签
         $(".j-label").on('click',function(){
          var data = $(this).data();
           $.showModal({
                title: '标签打印预览'
                , area: '460px'
                , content: template('tpl-label',{inpack_id:data.id})
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    
                }
            });
        });
        

        
 
        
        function PrintDiv(content) {
            var win = window.open("");
            win.document.write(content);
            win.document.close();
            //Chrome
            if (navigator.userAgent.indexOf("Chrome") != -1) {
                win.onload = function () {
                    win.document.execCommand('print');
                    win.close();
                }
            }
            //Firefox
            else {
                win.print();
                win.close();
            }
        }
        
        /**
         * 批量手动更新物流信息
         */
        $('#j-wuliu').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
               layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '批量更新订单动态'
                , area: '460px'
                , content: template('tpl-wuliu', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/trOrder/alllogistics') ?>',
                        data: {
                            selectIds:data.selectId
                        }
                    });
                    return true;
                }
            });
            
        $('#datetimepicker').datetimepicker({
          format: 'yyyy-mm-dd hh:ii'
        });
        
        
        $('#datetimepicker').datetimepicker().on('changeDate', function(ev){
            $('#datetimepicker').datetimepicker('hide');
          });
            
        });
        
        /**
         * 根据运输方式筛选渠道商
         * @param {Array} ditchList - 完整的渠道商列表
         * @param {String} transfer - 运输方式 ('1'=运输商, '0'=自有物流)
         * @returns {Array} 筛选后的渠道商列表
         */
        function filterDitchByTransfer(ditchList, transfer) {
            if (transfer == '1') {
                // 运输商：暂时留空（因为用户已将所有渠道归为代理分类）
                return [];
            } else {
                // 自有物流/渠道商：显示所有已集成的渠道类型
                return ditchList.filter(function(item) {
                    var type = parseInt(item.ditch_type);
                    return [1, 2, 3, 4, 5].indexOf(type) !== -1;
                });
            }
        }
        
        /**
         * 批量推送到渠道商
         */
        $('#j-batch-push').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            
            if (selectIds.length == 0) {
                layer.alert('请先选择要推送的订单', { icon: 5 });
                return;
            }
            
            $.showModal({
                title: '批量推送到渠道商',
                area: '500px',
                content: template('tpl-batch-push', data),
                uCheck: true,
                success: function ($content) {
                    var $select = $content.find('#batch-push-ditch-select');
                    
                    // 先销毁模板中自动初始化的 selected 组件
                    if ($select.data('amui.selected')) {
                        $select.selected('destroy');
                    }
                    
                    // 加载渠道商列表
                    $.ajax({
                        url: '<?= url('store/tr_order/getDitchList') ?>',
                        type: 'GET',
                        dataType: 'json',
                        success: function(result) {
                            if (result.code === 1 && result.data && result.data.list) {
                                var ditchList = result.data.list;
                                
                                // 保存完整的渠道商列表到 DOM 数据
                                $select.data('fullDitchList', ditchList);
                                
                                // 根据默认选中的运输方式筛选渠道商
                                var transfer = $content.find('input[name="transfer"]:checked').val() || '0';
                                var filteredList = filterDitchByTransfer(ditchList, transfer);
                                
                                // 清空现有选项（保留第一个"请选择"）
                                $select.find('option:not(:first)').remove();
                                
                                // 添加筛选后的渠道商选项
                                $.each(filteredList, function(index, item) {
                                    $select.append(
                                        $('<option></option>')
                                            .val(item.ditch_id)
                                            .text(item.ditch_name)
                                    );
                                });
                                
                                // 手动初始化 amazeui selected 组件
                                $select.selected({
                                    searchBox: 1,
                                    btnSize: 'sm',
                                    placeholder: '请选择渠道商',
                                    maxHeight: 400
                                });
                                
                                // 监听运输方式变化
                                $content.find('input[name="transfer"]').on('change', function() {
                                    var newTransfer = $(this).val();
                                    var fullList = $select.data('fullDitchList');
                                    var newFilteredList = filterDitchByTransfer(fullList, newTransfer);
                                    
                                    // 销毁旧组件
                                    $select.selected('destroy');
                                    
                                    // 更新选项
                                    $select.find('option:not(:first)').remove();
                                    $.each(newFilteredList, function(index, item) {
                                        $select.append(
                                            $('<option></option>')
                                                .val(item.ditch_id)
                                                .text(item.ditch_name)
                                        );
                                    });
                                    
                                    // 重新初始化 selected
                                    $select.selected({
                                        searchBox: 1,
                                        btnSize: 'sm',
                                        placeholder: '请选择渠道商',
                                        maxHeight: 400
                                    });
                                });
                            } else {
                                layer.msg('加载渠道商列表失败', { icon: 2 });
                            }
                        },
                        error: function(xhr, status, error) {
                            layer.msg('加载渠道商列表失败', { icon: 2 });
                        }
                    });
                },
                yes: function ($content) {
                    var ditchId = $content.find('select[name="ditch_id"]').val();
                    var async = $content.find('input[name="push_async"]').is(':checked');
                    var transfer = $content.find('input[name="transfer"]:checked').val();
                    
                    if (!ditchId) {
                        layer.msg('请选择渠道商', { icon: 2 });
                        return false;
                    }
                    
                    // 调用批量推送 JS 模块，传递运输方式参数
                    OrderBatchPusher.pushWithUI(selectIds, ditchId, {
                        async: async,
                        transfer: transfer,
                        onSuccess: function(data) {
                            if (!async) {
                                setTimeout(function() { window.location.reload(); }, 1500);
                            }
                        }
                    });
                    
                    return true;
                }
            });
        });
        
        /**
         * 批量打印面单
         */
        $('#j-batch-print').on('click', function () {
            var selectIds = checker.getCheckSelect();
            if (selectIds.length == 0){
                layer.msg('请先选择要打印的集运单', {icon: 5, time: 1500});
                return;
            }
            
            // 显示加载提示
            var loadIndex = layer.load(1, { 
                shade: [0.3, '#000'],
                content: '正在生成面单PDF，请稍候...'
            });
            
            // 请求服务器生成pdf地址
            $.ajax({
                type: "POST",
                url: '<?= url('store/trOrder/expressBillbatch') ?>',
                data: { 
                    selectIds: selectIds.join(',') // 数组转字符串
                },
                dataType: "json", // 后端返回JSON格式
                timeout: 30000, // 30秒超时
                success: function(result) {
                    layer.close(loadIndex);
                    
                    // 检查返回结果
                    if (!result) {
                        layer.msg('生成失败: 无效的响应', {icon: 2, time: 2000});
                        return;
                    }
                    
                    // 处理成功响应
                    if (result.code === 1) {
                        var pdfUrl = '';
                        
                        // 获取PDF URL
                        if (result.data && result.data.url) {
                            pdfUrl = result.data.url;
                        } else if (result.url) {
                            pdfUrl = result.url;
                        } else if (result.data && typeof result.data === 'string') {
                            pdfUrl = result.data;
                        }
                        
                        // 验证URL格式
                        if (!pdfUrl || (typeof pdfUrl !== 'string')) {
                            layer.msg('生成失败: 无效的PDF地址', {icon: 2, time: 2000});
                            return;
                        }
                        
                        // 统一处理URL格式（修复双斜杠问题）
                        pdfUrl = pdfUrl.replace(/([^:]\/)\/+/g, '$1');
                        
                        // 如果是相对路径，添加BASE_URL前缀
                        if (pdfUrl.indexOf('http://') !== 0 && pdfUrl.indexOf('https://') !== 0) {
                            if (typeof BASE_URL !== 'undefined' && BASE_URL) {
                                pdfUrl = BASE_URL.replace(/\/$/, '') + '/' + pdfUrl.replace(/^\//, '');
                            } else {
                                // 如果没有BASE_URL，使用当前域名
                                pdfUrl = window.location.origin + '/' + pdfUrl.replace(/^\//, '');
                            }
                        }
                        
                        // 打开新窗口显示PDF
                        try {
                            var printWindow = window.open(pdfUrl, '_blank');
                            if (printWindow) {
                                layer.msg(result.msg || '面单生成成功，正在打开...', {icon: 1, time: 1500});
                            } else {
                                layer.msg('请允许弹出窗口以查看面单', {icon: 2, time: 2000});
                            }
                        } catch (e) {
                            layer.msg('打开打印页面失败: ' + e.message, {icon: 2, time: 2000});
                        }
                    } else {
                        // 处理错误响应
                        layer.msg(result.msg || '生成失败', {icon: 2, time: 2000});
                    }
                },
                error: function(xhr, status, error) {
                    layer.close(loadIndex);
                    var errorMsg = '生成面单失败';
                    
                    if (status === 'timeout') {
                        errorMsg = '请求超时，请稍后重试';
                    } else if (xhr.responseText) {
                        try {
                            var errorResult = JSON.parse(xhr.responseText);
                            errorMsg = errorResult.msg || errorResult.message || errorMsg;
                        } catch (e) {
                            // 如果不是JSON，尝试从响应文本中提取错误信息
                            if (xhr.responseText.length < 200) {
                                errorMsg = xhr.responseText;
                            }
                        }
                    } else {
                        errorMsg = error || '网络错误，请检查网络连接';
                    }
                    
                    layer.msg(errorMsg, {icon: 2, time: 3000});
                }
            });
        });

        /**
         * 批量打印云面单 (AmaueUI 版本)
         */
        $('#j-batch-cloud-print').on('click', function() {
            // 1. 获取选中订单
            var selectIds = checker.getCheckSelect();
            if (selectIds.length === 0) {
                layer.msg('请先选择要打印的订单', {icon: 5});
                return;
            }

            // 2. 识别并分组渠道
            var groups = {};
            var noDitchCount = 0;
            
            // 遍历所有选中的 checkbox 获取 data-t-number
            $('input[name="checkIds"]:checked').each(function() {
                var orderId = $(this).val();
                var ditchId = $(this).data('t-number');
                
                if (!ditchId || ditchId == 0) {
                    noDitchCount++;
                    return;
                }
                if (!groups[ditchId]) {
                    groups[ditchId] = [];
                }
                groups[ditchId].push(orderId);
            });

            var keys = Object.keys(groups);
            
            // 3. 处理无法打印的情况
            if (keys.length === 0) {
                layer.alert('选中的订单尚未推送至渠道商，无打印信息。请先执行“发货”推送订单。', {icon: 7});
                return;
            }

            // 4. 执行打印逻辑
            if (keys.length === 1) {
                // 单一渠道，直接打印
                var ditchId = keys[0];
                OrderBatchPrinter.printWithUI(groups[ditchId], ditchId);
            } else {
                // 多渠道，提示用户
                var msg = '选中订单包含 ' + keys.length + ' 个不同渠道：<br>';
                keys.forEach(function(dId) {
                    msg += '- 渠道ID ' + dId + ' (' + groups[dId].length + '单)<br>';
                });
                if (noDitchCount > 0) {
                    msg += '<br><span style="color:red">注：另有 ' + noDitchCount + ' 单未分配渠道被忽略。</span>';
                }
                msg += '<br>是否分派多个打印任务并行执行？';

                layer.confirm(msg, {title: '批量打印确认', area: '400px'}, function(index) {
                    layer.close(index);
                    keys.forEach(function(ditchId) {
                        OrderBatchPrinter.printWithUI(groups[ditchId], ditchId);
                    });
                });
            }
        });

        
        /**
         * 合并订单
         */
        $('#j-hedan').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            var hedanurl = "<?= url('store/trOrder/hedan') ?>";
            if (selectIds.length==0){
                layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            // data.selectCount = selectIds.length;
            layer.confirm('请确定是否合并订单，请选择同一个用户进行合单！不同用户敬请期待拼邮功能开发(*^_^*)', {title: '合并订单'}
                    , function (index) {
                        $.post(hedanurl, data.selectId, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
        
        
        
         /**
         * 加入拼团
         */
        $('#j-pintuan').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            console.log(data.selectId)
            $.showModal({
                title: '加入拼团'
                , area: '460px'
                , content: template('tpl-shelf', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/trOrder/pintuan') ?>',
                        data: {selectIds:data.selectId},
                    });
                    return true;
                }
            });
        });

        
        
        		function isEmpty(val) {
					let valType = Object.prototype.toString.call(val);
					let isEmpty = false;
					switch (valType) {
						case "[object Undefined]":
						case "[object Null]":
							isEmpty = true;
							break;
						case "[object Array]":
						case "[object String]":
							try {
								isEmpty = val + "" === "null" || val + "" === "undefined" || val.length <= 0 || val.split(
									"").length <= 0 ? true : false;
							} catch (error) {
								isEmpty = false;
							};
							break;
						case "[object Object]":
							try {
								let temp = JSON.stringify(val);
								isEmpty = temp + "" === "null" || temp + "" === "undefined" || temp === "{}" ? true :
								false;
							} catch (error) {
								isEmpty = false;
							}
							break;
						case "[object Number]":
							isEmpty = val + "" === "NaN" || val + "" === "Infinity" ? true : false;
							break;
						default:
							isEmpty = false;
							break;
					}
					return isEmpty;
				};
		
        
      
    });
    
        function printlabel(e,s){
            var $tabs, data = $(this).data();
            console.log(s,999999);
            $.ajax({
               url:'<?= url('store/trOrder/expressLabel') ?>',
               type:"get",
               data:{id:s,label:e},
               success:function(result){
                    if(result.code ===0){
                       layer.alert(result.msg, {icon: 5});
                       return; 
                    }
                    
                    if(e==40 && result.code ===1){
                       console.log(result,9999)
                       window.open(result.url, '_blank');
                       return;  
                    }
                   $.showModal({
                        title: '标签打印预览'
                        , area: '600px,700px'
                        , content: result
                        , success: function ($content) {
                            console.log($content)
                        }
                        , yes: function ($content) {
                            PrintDiv(result)
                        }
                    });
               }
               
           })
        }
        
    function PrintDiv(content) {
            var win = window.open("");
            win.document.write(content );
            win.document.close();
            //Chrome
            if (navigator.userAgent.indexOf("Chrome") != -1) {
                win.onload = function () {
                    win.document.execCommand('print');
                    win.close();
                }
            }
            //Firefox
            else {
                win.print();
                win.close();
            }
    }
    
    function getlog(_this){
        var number = _this.getAttribute('value');
        console.log(3434);
        $.ajax({
			type: 'post',
			url: "<?= url('store/tr_order/getlog') ?>",
			data: {id: number},
			dataType: "json",
			success: function(res) {
				if (res.code == 1) {
				    console.log(res.data,87);
        				$.showModal({
                         title: '物流信息'
                        , area: '600px'
                        , content: template('tpl-log', res.data)
                        , uCheck: false
                        , success: function (index) {}
                        ,yes: function (index) {window.location.reload();}
                    });
				}
			}
		})
    }
    
    // 搜索折叠功能
    $(function() {
        var $toggleBtn = $('#searchToggleBtn');
        var $searchContent = $('#searchContent');
        var isCollapsed = localStorage.getItem('trOrderSearchCollapsed') === 'true';
        
        // 初始化状态
        if (isCollapsed) {
            $searchContent.addClass('collapsed');
            $toggleBtn.addClass('collapsed');
        }
        
        // 点击切换
        $toggleBtn.on('click', function() {
            isCollapsed = !isCollapsed;
            if (isCollapsed) {
                $searchContent.addClass('collapsed');
                $toggleBtn.addClass('collapsed');
            } else {
                $searchContent.removeClass('collapsed');
                $toggleBtn.removeClass('collapsed');
            }
            localStorage.setItem('trOrderSearchCollapsed', isCollapsed);
        });
    });
    
    /**
     * 打印单个运单
     * @param {number} orderId 订单ID
     * @param {string} waybillNo 运单号
     */
    function printSingleWaybill(orderId, waybillNo) {
        // 显示加载提示
        var loadingIndex = layer.msg('正在获取打印数据...', {
            icon: 16,
            shade: 0.3,
            time: 0
        });
        
        // 调用打印接口
        $.ajax({
            url: '<?= url("store/trOrder/getPrintTask") ?>',
            method: 'GET',
            timeout: 180000, // 增加超时时间到3分钟（180秒）
            data: {
                id: orderId,
                waybill_no: waybillNo,
                label: 60  // 标签类型
            },
            success: function(res) {
                layer.close(loadingIndex);
                
                if (res.code === 1) {
                    // 根据打印模式处理
                    if (res.data.mode === 'pdf_url') {
                        // PDF模式: 打开新窗口
                        window.open(res.data.url, '_blank');
                    } else if (res.data.mode === 'sf_plugin') {
                        // 顺丰插件模式
                        printWithSfPlugin(res.data);
                    } else if (res.data.mode === 'cainiao') {
                        // 菜鸟组件模式
                        printWithCainiao(res.data);
                    } else if (res.data.mode === 'jd_cloud_print') {
                        // 京东云打印模式
                        printWithJdComponent(res.data);
                    }
                    
                    // 更新打印状态
                    updatePrintStatus(orderId, waybillNo);
                } else {
                    layer.msg('获取打印数据失败: ' + res.msg, {icon: 2});
                }
            },
            error: function(xhr, status, error) {
                layer.close(loadingIndex);
                console.error('打印请求失败:', status, error);
                
                // 根据错误类型给出更友好的提示
                if (status === 'timeout') {
                    layer.msg('请求超时，请检查网络连接后重试', {icon: 2, time: 3000});
                } else {
                    layer.msg('网络错误，请重试', {icon: 2});
                }
            }
        });
    }
    
    /**
     * 打印所有运单(子母件)
     * @param {number} orderId 订单ID
     */
    function printAllWaybills(orderId) {
        layer.confirm('确定要打印所有运单吗?', {
            btn: ['确定', '取消']
        }, function(index) {
            layer.close(index);
            
            // 显示加载提示
            var loadingIndex = layer.msg('正在获取打印数据，请稍候...', {
                icon: 16,
                shade: 0.3,
                time: 0
            });
            
            // 调用统一打印接口 - 打印全部模式
            $.ajax({
                url: '<?= url("store/trOrder/getPrintTask") ?>',
                method: 'POST',
                timeout: 300000, // 增加超时时间到5分钟（300秒）
                data: {
                    id: orderId,
                    print_all: 1  // 打印全部模式
                },
                success: function(res) {
                    layer.close(loadingIndex);
                    
                    if (res.code === 1) {
                        // 根据打印模式处理
                        if (res.data.mode === 'sf_plugin') {
                            // 顺丰插件模式 - 打印全部
                            printWithSfPlugin(res.data, true);  // 传递 true 表示打印全部
                        } else if (res.data.mode === 'jd_cloud_print') {
                            // 京东云打印模式 - 打印全部
                            printWithJdComponent(res.data);
                        } else if (res.data.pdf_url) {
                            // PDF模式
                            window.open(res.data.pdf_url, '_blank');
                            layer.msg('打印成功! 共' + res.data.success + '张', {icon: 1});
                        } else {
                            layer.msg('打印成功!', {icon: 1});
                        }
                    } else {
                        layer.msg('打印失败: ' + res.msg, {icon: 2});
                    }
                },
                error: function(xhr, status, error) {
                    layer.close(loadingIndex);
                    console.error('批量打印请求失败:', status, error);
                    
                    // 根据错误类型给出更友好的提示
                    if (status === 'timeout') {
                        layer.msg('请求超时，请检查网络连接后重试', {icon: 2, time: 3000});
                    } else {
                        layer.msg('网络错误，请重试', {icon: 2});
                    }
                }
            });
        });
    }
    
    /**
     * 更新打印状态
     */
    function updatePrintStatus(orderId, waybillNo) {
        $.ajax({
            url: '<?= url("store/trOrder/updatePackagePrintStatus") ?>',
            method: 'POST',
            data: {
                order_id: orderId,
                waybill_no: waybillNo
            },
            success: function(res) {
                if (res.code === 1) {
                    // 刷新页面显示最新状态
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            }
        });
    }
    
    /**
     * 顺丰插件打印
     * 根据顺丰云打印 SDK 文档实现
     * @param {object} responseData 打印数据
     * @param {boolean} isPrintAll 是否打印全部（批量打印）
     */
    function printWithSfPlugin(responseData, isPrintAll) {
        isPrintAll = isPrintAll || false;
        
        // 检查 SCPPrint 是否已加载
        if (typeof SCPPrint === 'undefined') {
            layer.msg('请先安装顺丰打印插件', {icon: 0});
            return;
        }
        
        try {
            // 从响应数据中提取 partnerID 和打印数据
            var partnerID = responseData.partnerID || '';
            var printData = responseData.data || responseData;
            var printOptions = responseData.printOptions || {};
            
            if (!partnerID) {
                layer.msg('缺少客户编码(partnerID)', {icon: 2});
                console.error('响应数据:', responseData);
                return;
            }
            
            // 创建 SCPPrint 实例（注意：只需要创建一次实例）
            // 如果已经存在实例，则复用
            if (!window.sfPrintInstance) {
                // 判断环境：检查 accessToken 或 templateCode 来判断
                // 沙箱环境的 token 通常较短，或者检查后端返回的其他标识
                var env = 'sbox'; // 默认沙箱环境（因为你的配置是沙箱）
                
                // 如果有明确的环境标识，可以从后端传递
                if (responseData.env) {
                    env = responseData.env;
                }
                
                window.sfPrintInstance = new SCPPrint({
                    partnerID: partnerID,
                    env: env,
                    notips: false // 显示 SDK 提示
                });
                
                console.log('SCPPrint 实例已创建:', {
                    partnerID: partnerID,
                    env: env
                });
            }
            
            console.log('顺丰打印参数:', {
                partnerID: partnerID,
                printData: printData,
                printOptions: printOptions,
                isPrintAll: isPrintAll
            });
            
            // 判断是否需要打印机选择
            var enableSelectPrinter = printOptions.enable_select_printer || false;
            var enablePreview = printOptions.enable_preview || false;
            var defaultPrinter = printOptions.default_printer || '';
            
            // 打印执行函数
            var executePrint = function(selectedPrinter) {
                // 如果指定了打印机，先设置
                if (selectedPrinter) {
                    window.sfPrintInstance.setPrinter(selectedPrinter);
                    console.log('已设置打印机:', selectedPrinter);
                }
                
                // 构建 options 参数
                var sdkOptions = {
                    lodopFn: enablePreview ? 'PREVIEW' : 'PRINT' // 根据配置决定预览或直接打印
                };
                
                // 如果是打印全部，设置 allPreview: true（根据文档要求）
                if (isPrintAll) {
                    sdkOptions.allPreview = true;
                    console.log('打印全部模式: allPreview = true');
                }
                
                // 调用 print 方法
                window.sfPrintInstance.print(printData, function(result) {
                    console.log('顺丰打印回调:', result);
                    
                    if (result.code === 1) {
                        layer.msg('打印成功', {icon: 1});
                    } else if (result.code === 2 || result.code === 3) {
                        // 需要下载打印插件
                        layer.confirm('需要安装顺丰打印插件，是否立即下载？', {
                            btn: ['下载', '取消']
                        }, function() {
                            window.open(result.downloadUrl);
                        });
                    } else {
                        layer.msg('打印失败: ' + (result.msg || '未知错误'), {icon: 2});
                    }
                }, sdkOptions);
            };
            
            // 如果启用打印机选择
            if (enableSelectPrinter) {
                // 获取打印机列表
                window.sfPrintInstance.getPrinters(function(result) {
                    console.log('打印机列表回调:', result);
                    
                    if (result.code === 1 && result.printers && result.printers.length > 0) {
                        // 构建打印机选择对话框
                        var printerOptions = '';
                        var defaultIndex = -1;
                        
                        result.printers.forEach(function(printer, index) {
                            var isDefault = false;
                            
                            // 如果配置了默认打印机名称，匹配它
                            if (defaultPrinter && printer.name.indexOf(defaultPrinter) !== -1) {
                                isDefault = true;
                                defaultIndex = index;
                            }
                            
                            printerOptions += '<option value="' + printer.name + '"' + 
                                (isDefault ? ' selected' : '') + '>' + 
                                printer.name + '</option>';
                        });
                        
                        // 显示打印机选择对话框
                        layer.open({
                            type: 1,
                            title: '选择打印机',
                            area: ['500px', '300px'],
                            content: '<div style="padding: 20px;">' +
                                '<div style="margin-bottom: 15px;">请选择要使用的打印机：</div>' +
                                '<select id="printer-select" class="am-form-field" style="width: 100%;">' +
                                printerOptions +
                                '</select>' +
                                '</div>',
                            btn: ['确定打印', '取消'],
                            yes: function(index) {
                                var selectedPrinter = $('#printer-select').val();
                                layer.close(index);
                                executePrint(selectedPrinter);
                            }
                        });
                    } else if (result.code === 2 || result.code === 3) {
                        // 需要下载打印插件
                        layer.confirm('需要安装顺丰打印插件，是否立即下载？', {
                            btn: ['下载', '取消']
                        }, function() {
                            window.open(result.downloadUrl);
                        });
                    } else {
                        // 获取打印机列表失败，使用默认打印机
                        executePrint(null);
                    }
                });
            } else {
                // 不需要选择打印机，直接打印
                executePrint(null);
            }
            
        } catch (error) {
            console.error('顺丰打印错误:', error);
            layer.msg('打印失败: ' + error.message, {icon: 2});
        }
    }
    
    /**
     * 菜鸟组件打印
     */
    function printWithCainiao(data) {
        // 调用菜鸟打印组件
        if (typeof CainiaoWaybillPrint !== 'undefined') {
            CainiaoWaybillPrint.print(data);
        } else {
            layer.msg('请先安装菜鸟打印组件', {icon: 0});
        }
    }
    /**
     * 京东云打印组件打印
     * 通过 WebSocket 连接到本地打印服务
     * @param {object} resData 响应数据
     */
    function printWithJdComponent(resData) {
        var printRequest = resData.printRequest;
        var sendResult = resData.send_result;
        
        console.log('京东打印请求:', printRequest);
        console.log('服务端发送结果:', sendResult);
        
        // 如果服务端已经发送成功（通常是在本地开发环境或局域网环境）
        if (sendResult && sendResult.success) {
            layer.msg('打印指令已发送到本地组件 (服务端触发)', {icon: 1});
            return;
        }
        
        // 如果服务端发送失败，或者为了确保万无一失，前端再次通过 WebSocket 发送
        var wsUrl = 'ws://127.0.0.1:9113';
        var loadingIndex = layer.msg('正在连接本地打印组件...', {icon: 16, shade: 0.3, time: 0});
        
        try {
            var socket = new WebSocket(wsUrl);
            
            socket.onopen = function() {
                layer.close(loadingIndex);
                console.log('京东打印组件 WebSocket 已连接');
                socket.send(JSON.stringify(printRequest));
                layer.msg('打印数据已成功推送到本地组件', {icon: 1});
                
                // 1秒后自动关闭连接
                setTimeout(function() {
                    socket.close();
                }, 1000);
            };
            
            socket.onerror = function(err) {
                layer.close(loadingIndex);
                console.error('WebSocket 错误:', err);
                layer.msg('未能连接到京东打印组件，请确保组件已启动 (ws://127.0.0.1:9113)', {icon: 2, time: 5000});
            };
            
            socket.onmessage = function(event) {
                console.log('收到组件响应:', event.data);
                try {
                    var response = JSON.parse(event.data);
                    // 处理组件返回的状态...
                } catch(e) {}
            };
            
        } catch (error) {
            layer.close(loadingIndex);
            console.error('WebSocket 初始化失败:', error);
            layer.msg('连接打印组件失败: ' + error.message, {icon: 2});
        }
    }
</script>

<style>
    tbody tr:nth-child(2n){
        background: #fff !important;
        color:#ff6666;
    }
    /* 订单类型Tab样式 */
    .order-type-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 15px 15px;
        border-bottom: 1px solid #e8e8e8;
        margin-bottom: 15px;
        background: #fafafa;
        align-items: center;
    }
    .order-type-tabs a.tab-item {
        display: inline-flex !important;
        align-items: center !important;
        padding: 8px 16px !important;
        font-size: 14px !important;
        color: #666 !important;
        background: #fff !important;
        border: 1px solid #d9d9d9 !important;
        border-radius: 20px !important;
        cursor: pointer !important;
        text-decoration: none !important;
        transition: all 0.3s ease !important;
        min-width: 80px;
        justify-content: center;
        height: 34px;
        line-height: 18px;
    }
    .order-type-tabs a.tab-item:hover {
        color: #1890ff !important;
        border-color: #1890ff !important;
        background: #e6f7ff !important;
    }
    .order-type-tabs a.tab-item.active,
    .order-type-tabs a.tab-item.active:hover,
    .order-type-tabs a.tab-item.active:focus,
    .order-type-tabs a.tab-item.active:visited {
        color: #fff !important;
        background: #1890ff !important;
        background-image: linear-gradient(135deg, #1890ff 0%, #096dd9 100%) !important;
        border-color: #1890ff !important;
        box-shadow: 0 2px 6px rgba(24, 144, 255, 0.35) !important;
    }
    .order-type-tabs .tab-count {
        display: inline-block;
        margin-left: 6px;
        padding: 2px 8px;
        font-size: 12px;
        background: rgba(0,0,0,0.08);
        border-radius: 10px;
        color: #666;
    }
    .order-type-tabs a.tab-item.active .tab-count {
        background: rgba(255,255,255,0.3) !important;
        color: #fff !important;
    }
    /* 搜索折叠按钮样式 */
    .search-toggle-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        background: #1890ff;
        color: #fff;
        border-radius: 20px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
        height: 34px;
        line-height: 18px;
        margin-bottom: 0;
    }
    .search-toggle-btn:hover {
        background: #40a9ff;
    }
    .search-toggle-btn i {
        margin-right: 5px;
        transition: transform 0.3s;
    }
    .search-toggle-btn.collapsed i {
        transform: rotate(-90deg);
    }
    .search-content {
        transition: all 0.3s ease;
    }
    .search-content.collapsed {
        display: none;
    }
    
    /* 打印按钮样式 */
    a.print-btn-single,
    a.print-btn-all {
        color: #00b894;
        font-size: 12px;
        margin-left: 5px;
        transition: all 0.2s;
        text-decoration: none;
    }
    
    a.print-btn-single:hover,
    a.print-btn-all:hover {
        color: #00a383;
        text-decoration: none;
    }
    
    a.print-btn-all {
        color: #1E9FFF;
    }
    
    a.print-btn-all:hover {
        color: #0984e3;
    }
</style>

<script>
// 批量复制所有运单号
function copyAllWaybills(element) {
    var waybills = $(element).data('waybills');
    if (!waybills) {
        layer.msg('没有可复制的运单号', {icon: 2});
        return;
    }
    
    // 创建临时文本域
    var $temp = $("<textarea>");
    $("body").append($temp);
    $temp.val(waybills.replace(/,/g, '\n')).select();
    
    try {
        document.execCommand("copy");
        layer.msg('已复制所有运单号到剪贴板', {icon: 1});
    } catch (err) {
        layer.msg('复制失败，请手动复制', {icon: 2});
    }
    
    $temp.remove();
}
</script>

<!-- 引入批量推送 JS -->
<script src="assets/common/js/order-batch-pusher.js"></script>

<!-- 引入批量打印 JS -->
<script src="assets/common/js/order-batch-printer.js"></script>


<!-- 批量推送模板 -->
<script id="tpl-batch-push" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择订单数
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <p class='am-form-static'> 共选中 {{ selectCount }} 个订单</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        运输方式
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <label class="am-radio-inline">
                            <input type="radio" id="transfer-carrier" name="transfer" value="1" data-am-ucheck>
                            运输商
                        </label>
                        <label class="am-radio-inline">
                            <input type="radio" id="transfer-self" name="transfer" value="0" data-am-ucheck checked>
                            自有物流（某些物流的代理）
                        </label>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择渠道商
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <select name="ditch_id" id="batch-push-ditch-select">
                            <option value="">请选择渠道商</option>
                        </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label">
                        推送模式
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <label class="am-checkbox">
                            <input type="checkbox" name="push_async" value="1"> 异步推送（后台执行）
                        </label>
                        <small class="am-text-muted">勾选后将在后台异步执行推送任务</small>
                    </div>
                </div>
                <div class="am-form-group">
                    <div class="am-u-sm-12">
                        <div class="am-alert am-alert-warning">
                            <p><strong>提示：</strong></p>
                            <ul style="margin:5px 0; padding-left:20px;">
                                <li>批量推送将把选中的订单推送到同一个渠道商</li>
                                <li>推送前请确认订单信息正确</li>
                                <li>推送失败的订单可以重新推送</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>


