  <link rel="stylesheet" href="//unpkg.com/layui@2.6.8/dist/css/layui.css">
  <style>
    .layui-card {
      margin-bottom: 15px;
      border-radius: 2px;
      box-shadow: 0 1px 2px 0 rgba(0,0,0,.05);
    }
    .layui-card-header {
      border-bottom: 1px solid #f6f6f6;
    }
    .layui-form-label {
        width: 100px;
    }
    .search-form .layui-form-item {
      margin-bottom: 15px;
    }
    .layui-table-tool {
      background-color: #fff;
    }
    .layui-table-view .layui-table td, .layui-table-view .layui-table th {
      padding: 9px 15px;
    }
    .status-badge {
      padding: 2px 5px;
      border-radius: 2px;
      font-size: 12px;
    }
    .status-1 { background-color: #FFB800; color: #fff; }
    .status-2 { background-color: #1E9FFF; color: #fff; }
    .status-3 { background-color: #009688; color: #fff; }
    .status-4 { background-color: #FF5722; color: #fff; }
    .status-5 { background-color: #393D49; color: #fff; }
    .status-6 { background-color: #01AAED; color: #fff; }
    .status-7 { background-color: #5FB878; color: #fff; }
    .status-8 { background-color: #2F4056; color: #fff; }
    .status-9 { background-color: #FF5722; color: #fff; }
    .copy-btn {
      color: #1E9FFF;
      cursor: pointer;
      margin-left: 5px;
    }
    .action-btn-group{
        display: flex;
        justify-content: space-between;
    }
    .action-btn-group a{
       margin：5px ;
    }
    .action-btn-group .layui-btn {
      margin-bottom: 5px;
    }
    .batch-operations {
      margin-bottom: 15px;
      padding: 10px;
      background-color: #f8f8f8;
      border-radius: 2px;
    }
    .selected-count {
      display: inline-block;
      margin-left: 10px;
      color: #FF5722;
      font-weight: bold;
    }
    .copyable-text:hover {
        opacity: 0.8;
        text-decoration: underline;
    }
    .layui-icon-copy {
        margin-left: 3px;
        vertical-align: middle;
    }


.layui-dropdown-trigger {
  padding-right: 25px !important; /* 扩大点击区域 */
}
.layui-table-grid-down{
 z-index: 99;   
}

/* 显示下拉图标 */
.layui-dropdown-trigger .layui-icon-down {
  font-size: 12px !important; /* 显式控制图标大小 */
  margin-left: 3px;
}


.layui-dropdown-menu.show {
  display: block !important;
}

.layui-btn-container {
  position: relative;
  display: inline-block;
}
.layui-table-cell {
  overflow: visible !important;
  height: auto !important;
  padding: 5px 15px !important;
}
.layui-table-body
{
    overflow: overlay; !important;
  position: relative;
}
.layui-table-box{
  overflow: visible !important;
  position: relative;
}
/* 下拉菜单容器 - 增强版 */
.layui-dropdown-menu {
  position: absolute !important;
  top: 100%;
  left: 0;
  min-width: 120px !important;
  z-index: 999999 !important;
  display: none;
  background: #fff;
  border: 1px solid #e6e6e6;
  border-radius: 2px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.12);
  padding: 5px 0;
  margin-top: 5px;
  margin-bottom: 0;
  animation: fadeIn 0.2s ease-out;
}

/* 向上弹出时的样式 */
.layui-dropdown-menu.upward {
  top: auto;
  bottom: 100%;
  margin-top: 0;
  margin-bottom: 5px;
}

/* 菜单项样式 */
.layui-dropdown-menu li {
  position: relative;
  line-height: 36px;
}

.layui-dropdown-menu li a {
  display: block;
  padding: 0 15px;
  color: #333;
  text-decoration: none;
  transition: all 0.3s;
  font-size: 13px;
}

/* 悬停效果 */
.layui-dropdown-menu li a:hover {
  background-color: #f2f2f2;
  color: #009688;
}

/* 图标间距 */
.layui-dropdown-menu .layui-icon {
  margin-right: 8px;
  font-size: 14px;
}

/* 分隔线 */
.layui-dropdown-menu li.layui-menu-item-divider {
  height: 1px;
  margin: 5px 0;
  background-color: #f0f0f0;
  overflow: hidden;
}

/* 显示动画 */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* 激活状态 */
.layui-dropdown-menu.show {
  display: block !important;
}

/* 按钮容器定位 */
.layui-btn-container {
  position: relative;
  display: inline-block;
}
  </style>
</head>
<body>
  <div class="layui-fluid">
    <div class="layui-row layui-col-space15">
      <!-- 主内容区 -->
      <div class="layui-col-md12">
        <!-- 搜索卡片 -->
<div class="layui-card" style="margin-top:10px;">
  <div class="layui-card-header">
    <div class="layui-row">
      <div class="layui-col-md6">
        <span>订单筛选</span>
      </div>
      <div class="layui-col-md6" style="text-align: right;">
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="toggle-filter">
          <i class="layui-icon layui-icon-down"></i> 展开筛选
        </button>
      </div>
    </div>
  </div>
  <div class="layui-card-body" id="filter-body" style="display: none;">
    <form class="layui-form" action="">
      <div class="layui-row layui-col-space10">
        <!-- 第一行筛选条件 -->
        <div class="layui-col-md2">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">订单状态</label>
            <div class="layui-input-block">
              <?php $extractStatus = $request->get('status'); ?>
              <select name="status" lay-search>
                <option value="">全部状态</option>
                <option value="1">待查验</option>
                <option value="2">待支付</option>
                <option value="3">已支付</option>
                <option value="4">拣货中</option>
                <option value="5">已打包</option>
                <option value="6">已发货</option>
                <option value="7">已到货</option>
                <option value="8">已完成</option>
                <option value="9">已取消</option>
              </select>
            </div>
          </div>
        </div>
        
        <div class="layui-col-md2">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">仓库名称</label>
            <div class="layui-input-block">
                <?php $extractShopId = $request->get('extract_shop_id'); ?>
              <select name="extract_shop_id" lay-search>
                <option value="">全部仓库</option>
                <?php if (isset($shopList)): foreach ($shopList as $item): ?>
                    <option value="<?= $item['shop_id'] ?>"
                        <?= $item['shop_id'] == $extractShopId ? 'selected' : '' ?>><?= $item['shop_name'] ?>
                    </option>
                <?php endforeach; endif; ?>
              </select>
            </div>
          </div>
        </div>
        
        <div class="layui-col-md2">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">路线名称</label>
            <div class="layui-input-block">
                <?php $extractlineid = $request->get('line_id'); ?>
              <select name="line_id" lay-search>
                <option value="">全部路线</option>
                <?php if (isset($lineList)): foreach ($lineList as $item): ?>
                    <option value="<?= $item['id'] ?>"
                        <?= $item['id'] == $extractlineid ? 'selected' : '' ?>><?= $item['name'] ?>
                    </option>
                <?php endforeach; endif; ?>
              </select>
            </div>
          </div>
        </div>
        
        <div class="layui-col-md2">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">排序参数</label>
            <div class="layui-input-block">
              <?php $orderparam = $request->get('orderparam'); ?>
              <select name="orderparam" lay-search>
                <option value=""></option>
                <option value="created_time" <?= $orderparam == 'created_time' ? 'selected' : '' ?>>提交打包时间排序</option>
                <option value="pay_time" <?= $orderparam == 'pay_time' ? 'selected' : '' ?>>支付完成时间排序</option>
                <option value="pick_time" <?= $orderparam == 'pick_time' ? 'selected' : '' ?>>打包完成时间排序</option>
                <option value="settle_time" <?= $orderparam == 'settle_time' ? 'selected' : '' ?>>佣金结算时间排序</option>
                <option value="sendout_time" <?= $orderparam == 'sendout_time' ? 'selected' : '' ?>>订单发货时间排序</option>
                <option value="receipt_time" <?= $orderparam == 'receipt_time' ? 'selected' : '' ?>>用户签收时间排序</option>
              </select>
            </div>
          </div>
        </div>
        <div class="layui-col-md2">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">排序方式</label>
            <div class="layui-input-block">
              <?php $descparam = $request->get('descparam'); ?>
              <select name="descparam" lay-search>
                <option value=""></option>
                <option value="desc" <?= $descparam == 'desc' ? 'selected' : '' ?>>降序排序(大到小，新到旧)</option>
                <option value="asc" <?= $descparam == 'asc' ? 'selected' : '' ?>>升序排序(小到大，旧到新)</option>
              </select>
            </div>
          </div>
        </div>
        <div class="layui-col-md2">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">时间类型</label>
            <div class="layui-input-block">
              <?php $extracttimetype = $request->get('time_type'); ?>
              <select name="time_type" lay-search>
                <option value="created_time" <?= $extracttimetype == 'created_time' ? 'selected' : '' ?>>提交打包时间</option>
                <option value="pay_time" <?= $extracttimetype == 'pay_time' ? 'selected' : '' ?>>支付完成时间</option>
                <option value="pick_time" <?= $extracttimetype == 'pick_time' ? 'selected' : '' ?>>打包完成时间</option>
                <option value="settle_time" <?= $extracttimetype == 'settle_time' ? 'selected' : '' ?>>佣金结算时间</option>
                <option value="sendout_time" <?= $extracttimetype == 'sendout_time' ? 'selected' : '' ?>>订单发货时间</option>
                <option value="receipt_time" <?= $extracttimetype == 'receipt_time' ? 'selected' : '' ?>>用户签收时间</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      
      <!-- 第二行筛选条件 -->
      <div class="layui-row layui-col-space10">
        <div class="layui-col-md2">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">起始日期</label>
            <div class="layui-input-block">
              <input type="text" name="start_time" class="layui-input" id="start-time" placeholder="请选择起始日期" value="<?= $request->get('start_time') ?>" autocomplete="off">
            </div>
          </div>
        </div>
        
        <div class="layui-col-md2">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">截止日期</label>
            <div class="layui-input-block">
              <input type="text" name="end_time" class="layui-input" id="end-time" placeholder="请选择截止日期" value="<?= $request->get('end_time') ?>" autocomplete="off">
            </div>
          </div>
        </div>
        <div class="layui-col-md3">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">运单号</label>
            <div class="layui-input-block">
              <input type="text" name="batch_no" value="<?= $request->get('batch_no') ?>" class="layui-input" placeholder="请输入平台订单号或转运单号">
            </div>
          </div>
        </div>
        <div class="layui-col-md3">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">订单号</label>
            <div class="layui-input-block">
              <input type="text" name="order_sn" value="<?= $request->get('order_sn') ?>"  class="layui-input" placeholder="请输入平台订单号或转运单号">
            </div>
          </div>
        </div>
        
      </div>
      <!-- 第三行筛选条件 -->
<div class="layui-row layui-col-space10">
  <div class="layui-col-md2">
    <div class="layui-form-item">
      <label class="layui-form-label" style="width: 100px;">模糊查询</label>
      <div class="layui-input-block">
        <?php $extracttimetype = $request->get('search_type'); ?>
        <select name="search_type" lay-search>
          <option value="all" <?= $extracttimetype == 'all' ? 'selected' : '' ?>>模糊查询</option>
          <option value="member_id" <?= $extracttimetype == 'member_id' ? 'selected' : '' ?>>用户ID</option>
          <option value="user_code" <?= $extracttimetype == 'user_code' ? 'selected' : '' ?>>用户CODE</option>
          <option value="user_mark" <?= $extracttimetype == 'user_mark' ? 'selected' : '' ?>>用户唛头</option>
          <option value="nickName" <?= $extracttimetype == 'nickName' ? 'selected' : '' ?>>用户昵称</option>
          <option value="mobile" <?= $extracttimetype == 'mobile' ? 'selected' : '' ?>>手机号</option>
        </select>
      </div>
    </div>
  </div>
  <div class="layui-col-md3">
    <div class="layui-form-item">
      <label class="layui-form-label" style="width: 100px;">用户搜索</label>
      <div class="layui-input-block">
        <input type="text" name="search" value="<?= $request->get('search') ?>" class="layui-input" placeholder="请输入用户昵称或ID或用户编号">
      </div>
    </div>
  </div>
  <div class="layui-col-md3">
    <div class="layui-form-item" style="text-align: left; padding-right: 20px;">
      <button class="layui-btn layui-btn-normal" lay-submit lay-filter="search">
        <i class="layui-icon layui-icon-search"></i> 搜索
      </button>
      <button type="reset" class="layui-btn layui-btn-primary">
        <i class="layui-icon layui-icon-refresh"></i> 重置
      </button>
    </div>
  </div>
</div>
    </form>
  </div>
</div>

<script src="//unpkg.com/layui@2.6.8/dist/layui.js"></script>
<script src="/assets/common/js/order-batch-printer.js"></script>

<script>
layui.use(['form', 'laydate', 'jquery','table'], function(){
  var form = layui.form;
  var laydate = layui.laydate;
  var table = layui.table;
  var $ = layui.jquery;
  
  // 初始化日期选择器
  laydate.render({
    elem: '#start-time',
    type: 'datetime',
    trigger: 'click'
  });
  
  laydate.render({
    elem: '#end-time',
    type: 'datetime',
    trigger: 'click'
  });
  
  // 表单渲染
  form.render();
  
  // 切换筛选表单显示/隐藏
  $('#toggle-filter').click(function(){
    var filterBody = $('#filter-body');
    var icon = $(this).find('i');
    
    if(filterBody.is(':visible')){
      filterBody.slideUp();
      icon.removeClass('layui-icon-up').addClass('layui-icon-down');
      $(this).find('span').text('展开筛选');
    } else {
      filterBody.slideDown();
      icon.removeClass('layui-icon-down').addClass('layui-icon-up');
      $(this).find('span').text('收起筛选');
    }
  });
  
// 更精确的判断是否有筛选条件
function hasSearchParams() {
  var search = window.location.search;
  if (!search) return false;
  
  // 排除基本参数
  var basicParams = ['page', 'limitnum', 's'];
  var params = new URLSearchParams(search);
  
  for (var key of params.keys()) {
    if (!basicParams.includes(key)) {
      return true; // 存在非基本参数，说明有筛选条件
    }
  }
  
  return false;
}

// 使用更精确的判断
if (hasSearchParams()) {
  $('#toggle-filter').click();
}

// 导出选择监听
form.on('select(export-select)', function(data){
    var value = data.value;
    if (!value) return;

    // 获取表格选中行
    var checkStatus = table.checkStatus('order-table');
    var selectIds = checkStatus.data.map(function(item) {
        return item.id;
    }).filter(Boolean);
   console.log($('#j-exportInpack').length); // 应该输出 1
    switch(value) {
        case '1': // 订单数据
            loaddingoutexcel();
            break;
        case '2': // 分成清单
            exportInpack(selectIds);
             // 触发现有分成清单导出
            break;
        case '3': // 清关模板
            exportClearanceTemplate(selectIds);
            break;
    }
    
    // 重置选择框
    $('[name="export-option"]').val('');
    form.render('select');
});


/**
 * 导出集运结算单 (Layui兼容版)
 */
function exportInpack(selectIds){
    // 1. 获取选中订单（兼容Layui表格）
    var checkStatus = table.checkStatus('order-table');
    var selectIds = checkStatus.data.map(function(item) {
        return item.id;
    }).filter(Boolean); // 过滤无效ID

    // 2. 获取搜索表单数据（兼容Layui表单）
    var serializeObj = {};
    $(".layui-form").serializeArray().forEach(function(item) {
        if (item.name !== 's' && item.value) { // 过滤空值和s参数
            serializeObj[item.name] = item.value;
        }
    });

    // 3. 验证导出条件
    if (Object.keys(serializeObj).length === 0 && selectIds.length === 0) {
        layer.msg('请先选择订单或者设置搜索条件', {icon: 5});
        return;
    }

    // 4. 显示加载提示
    var loadIndex = layer.load(1, {shade: 0.3});

    // 5. 发送导出请求
    $.ajax({
        type: 'POST',
        url: "<?= url('store/trOrder/exportInpack') ?>",
        data: {
            selectIds: selectIds,  // 改为数组格式
            search: serializeObj  // 修正参数名 seach -> search
        },
        dataType: "json",
        success: function(res) {
            layer.close(loadIndex);
            
            if (res && res.code == 1 && res.url && res.url.file_name) {
                // 创建隐藏链接下载文件
                var a = document.createElement('a');
                a.style.display = 'none';
                a.href = res.url.file_name;
                a.download = '集运结算单_' + new Date().toLocaleDateString() + '.xlsx';
                document.body.appendChild(a);
                a.click();
                setTimeout(function() {
                    document.body.removeChild(a);
                    layer.msg('导出成功', {icon: 1});
                }, 100);
            } else {
                layer.msg(res.msg || '导出文件生成失败', {icon: 2});
            }
        },
        error: function(xhr) {
            layer.close(loadIndex);
            var errorMsg = xhr.responseJSON && xhr.responseJSON.msg 
                         ? xhr.responseJSON.msg 
                         : '导出失败，状态码: ' + xhr.status;
            layer.msg(errorMsg, {icon: 2});
        }
    });
}

// 通用文件下载函数
function downloadFile(url, fileName) {
    var a = document.createElement('a');
    a.href = url;
    a.download = fileName || 'export_' + new Date().getTime() + '.xlsx';
    document.body.appendChild(a);
    a.click();
    setTimeout(function() {
        document.body.removeChild(a);
        layer.msg('文件下载已开始', {icon: 1});
    }, 100);
}

// 订单数据导出函数
function loaddingoutexcel() {
    var loadIndex = layer.load(1);
    var checkStatus = table.checkStatus('order-table');
    var selectIds = checkStatus.data.map(function(item) {
        return item.id;
    }).filter(Boolean); // 过滤无效ID
    
    $.ajax({
        url: '<?= url("store/trOrder/loaddingoutexcel") ?>',
        type: 'POST',
        data: { selectId: selectIds },
        dataType: 'json',
        success: function(res) {
            layer.close(loadIndex);
            if(res.code == 1 && res.url) {
                downloadFile(res.url.file_name, '订单数据_'+getCurrentDate()+'.csv');
            } else {
                layer.msg(res.msg || '导出失败', {icon: 2});
            }
        }
    });
}

// 获取当前日期字符串
function getCurrentDate() {
    var date = new Date();
    return date.getFullYear() + '-' + 
           (date.getMonth()+1).toString().padStart(2, '0') + '-' + 
           date.getDate().toString().padStart(2, '0');
}

});
</script>
        <?php $status = [1=>'待查验',2=>'待发货',3=>'待发货','4'=>'待发货','5'=>'待发货','6'=>'已发货','7'=>'已到货','8'=>'已完成','-1'=>'问题件']; ?>
        <?php $paytime_status = [ 1=>'已支付',2=>'未支付',3=>'支付待审核'] ; ?>
        <!-- 批量操作工具栏 -->
        <div class="batch-operations">
          <div class="layui-btn-group">
            <?php if (checkPrivilege('package.index/changeuser')): ?>
                <button class="layui-btn layui-btn-sm" id="j-upuser">
                  <i class="layui-icon layui-icon-user"></i> 修改用户
                </button>
            <?php endif;?>
            <button class="layui-btn layui-btn-sm" id="j-upstatus">
              <i class="layui-icon layui-icon-form"></i> 状态变更
            </button>
            <button class="layui-btn layui-btn-sm layui-btn-warm" id="j-hedan">
              <i class="layui-icon layui-icon-link"></i> 合并订单
            </button>
            <button class="layui-btn layui-btn-sm layui-btn-danger" id="j-wuliu">
              <i class="layui-icon layui-icon-location"></i> 更新物流
            </button>
            <button class="layui-btn layui-btn-sm layui-btn-primary" id="j-batch-cloud-print">
              <i class="layui-icon layui-icon-print"></i> 批量打印云面单
            </button>
            <button class="layui-btn layui-btn-sm" id="j-batch-print">
              <i class="layui-icon layui-icon-print"></i> 打印面单
            </button>

            <button class="layui-btn layui-btn-sm layui-btn-warm" id="j-pintuan">
              <i class="layui-icon layui-icon-group"></i> 加入拼团
            </button>
            <button class="layui-btn layui-btn-sm" id="j-batch">
              <i class="layui-icon layui-icon-list"></i> 加入批次
            </button>
            <!--<button class="layui-btn layui-btn-sm layui-btn-primary" id="j-export">-->
            <!--  <i class="layui-icon layui-icon-export"></i> 导出-->
            <!--</button>-->
            <span class="selected-count" id="selected-count">已选0项</span>
          </div>
          
          <div class="layui-form layui-form-pane" style="display: inline-block; margin-left: 15px;">
            <div class="layui-form-item" style="margin-bottom: 0;">
              <div class="layui-inline">
                <label class="layui-form-label">导出</label>
                <div class="layui-input-inline">
                    <select name="export-option" lay-filter="export-select">
                        <option value="">选择类型</option>
                        <?php if (checkPrivilege('tr_order/loaddingoutexcel')): ?>
                        <option value="1">订单数据</option>
                        <?php endif; ?>
                        <?php if (checkPrivilege('tr_order/exportinpack')): ?>
                        <?php if($dataType=='complete'): ?>
                        <option value="2">分成清单</option>
                        <?php endif; ?>
                        <?php endif; ?>
                        <?php if (checkPrivilege('tr_order/clearance')): ?>
                        <?php if($dataType=='sending'): ?>
                        <option value="3">清关模板</option>
                        <?php endif; ?>
                        <?php endif; ?>
                    </select>
                </div>
                </div>
            </div>
          </div>
        </div>
        
        <!-- 订单列表卡片 -->
        <div class="layui-card">
          <div class="layui-card-header">
            <span>订单列表</span>
          </div>
          <div class="layui-card-body">
            <table class="layui-table" lay-size="sm" lay-filter="order-table" id="order-table"></table>
            <div id="pagination" style="text-align: right;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
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
                            <button type="button" class="j-selectUser upload-file am-btn am-btn-secondary am-radius" data-action="selectUser">
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
<script id="tpl-wuliu" type="text/template">
<div class="layui-form" style="padding: 15px;">
    <form class="layui-form" lay-filter="wuliu-form">
        <div class="layui-form-item">
            <label class="layui-form-label">选择数量</label>
            <div class="layui-input-block">
                <div class="layui-input" style="border: none; line-height: 38px;">
                    共选中 {{ selectCount }} 包裹
                </div>
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="layui-form-required">*</span>轨迹模板</label>
            <div class="layui-input-block">
                <select name="track_id" lay-search lay-verify="required">
                    <option value="">选择模板</option>
                    <?php if (isset($tracklist)): 
                        foreach ($tracklist as $item): ?>
                        <option value="<?= $item['track_id'] ?>"><?= $item['track_name'] ?></option>
                    <?php endforeach; endif; ?>
                </select>
                <div class="layui-form-mid layui-word-aux">
                    注：你可以在下方自定义轨迹，或者选择预设好的轨迹
                </div>
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="layui-form-required">*</span>物流状态</label>
            <div class="layui-input-block">
                <input type="text" name="logistics_describe" 
                       lay-verify="required" placeholder="请输入物流状态" 
                       class="layui-input">
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="layui-form-required">*</span>物流时间</label>
            <div class="layui-input-block">
                <input type="text" name="created_time" id="datetimepicker" 
                       lay-verify="required" placeholder="请选择时间" 
                       value="<?= date("Y-m-d H:i:s",time()) ?>" 
                       class="layui-input">
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
<script id="tpl-label" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <button onclick="printlabel(10,{{ inpack_id }})"   style="margin:10px;" type="button" class="am-btn-lg am-btn am-btn-primary ">标签模板1</button>
                    <button onclick="printlabel(20,{{ inpack_id }})" style="margin:10px;" type="button" class="am-btn-lg am-btn am-btn-secondary ">标签模板2</button>
                    <button onclick="printlabel(30,{{ inpack_id }})" style="margin:10px;" type="button" class="am-btn-lg am-btn am-btn-success ">标签模板3</button>
                    <button onclick="printlabel(40,{{ inpack_id }})" style="margin:10px;" type="button" class="am-btn-lg am-btn am-btn-warning ">渠道 标 签</button>
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
                       <p class='am-form-static'> 共选中 {{ d.selectCount }} 包裹</p>
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
                        </select>
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
<script id="tpl-tuan" type="text/template">
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
                                data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}">
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
                                data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}">
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
<script>
  var statusText = {
  '1': '待查验',
  '2': '待发货',
  '3': '待发货',
  '4': '待发货',
  '5': '待发货',
  '6': '已发货',
  '7': '已到货',
  '8': '已完成',
  '-1': '问题件'
};
var paytime_status = {'1':'已支付','2':'未支付','3':'支付待审核'};
layui.use(['form', 'table', 'laydate', 'layer', 'laypage', 'jquery','dropdown'], function(){
    var form = layui.form;
    var table = layui.table;
    var laydate = layui.laydate;
    var layer = layui.layer;
    var laypage = layui.laypage;
    var $ = layui.jquery;
    // 初始化日期选择器
    laydate.render({
      elem: '#start-time',
      type: 'datetime'
    });
    laydate.render({
      elem: '#end-time',
      type: 'datetime'
    });
    
  // 初始化表格
// 在表格初始化时保存实例
var orderTable = table.render({
  elem: '#order-table',
  id: 'order-table',
  cols: [[
    {type: 'checkbox', fixed: 'left'},
    {field: 'inpack_type', width: 40, title: '类型', templet: function(d){
      var html = '';
      if(d.inpack_type == 0) html += '<span class="am-badge am-badge-success">拼邮</span>';
      if(d.inpack_type == 1) html += '<span class="am-badge am-badge-secondary">拼团</span>';
      if(d.inpack_type == 2) html += '<span class="am-badge am-badge-primary">直邮</span>';
      if(d.inpack_type == 3) html += '<span class="am-badge am-badge-success">拼邮</span>';
      if(d.is_exceed == 1) html += '<span class="am-badge am-badge-danger">超时</span>';
      return html;
    }},
    {field: 'order_sn', width: 120, title: '系统单号',style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;'},
    {field: 'status', width: 80, title: '状态', templet: function(d){
      var html = '';
      if(d.status == 0) html += '<span class="am-badge layui-bg-red">' +  statusText[d.status] || '' +'</span>';
      if(d.status == 1) html += '<span class="am-badge layui-bg-red">' +  statusText[d.status] || '' +'</span>';
      if(d.status == 2) html += '<span class="am-badge layui-bg-blue">' +  statusText[d.status] || '' +'</span>';
      if(d.status == 3) html += '<span class="am-badge am-badge-danger">' +  statusText[d.status] || '' +'</span>';
      if(d.status == 4) html += '<span class="am-badge am-badge-secondary">' +  statusText[d.status] || '' +'</span>';
      if(d.status == 5) html += '<span class="am-badge am-badge-success">' +  statusText[d.status] || ''+'</span>';
      if(d.status == 6) html += '<span class="am-badge layui-bg-cyan">' +  statusText[d.status] || '' +'</span>';
      if(d.status == 7) html += '<span class="am-badge layui-bg-blue">' +  statusText[d.status] || '' +'</span>';
      if(d.status == 8) html += '<span class="am-badge am-badge-success">' +  statusText[d.status] || '' +'</span>';
      if(d.status == -1) html += '<span class="am-badge am-badge-danger">' +  statusText[d.status] || '' +'</span>';
      return html;
    }},
   
    {field: 'user', width: 120, title: '会员信息',style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;', templet: function(d){
        var html = d.user ? d.user.nickName : '无主订单';
        // 用户Code（根据PHP条件动态显示）
        <?php if($set['usercode_mode']['is_show'] == 0): ?>
        if (d.user && d.user.user_id) {
            html += '(' + d.user.user_id + ')'+
                   '<span class="copyable-text" data-text="'+d.user.user_id+'" style="cursor:pointer;color:#1E9FFF">';
        }
        <?php endif; ?>
        <?php if($set['usercode_mode']['is_show'] == 1): ?>
        if (d.user && d.user.user_code) {
            html += '(' + d.user.user_code + ')'+
                   '<span class="copyable-text" data-text="'+d.user.user_code+'" style="cursor:pointer;color:#1E9FFF">';
        }
        <?php endif; ?>
        return html;
    }},
    {field: 'packagelist', width: 100, title: '包裹/快递', templet: function(d){
      return '<a href="<?= url('store/trOrder/package') ?>/id/'+ d.id +'">'+ d.packagelist.length +'&nbsp;&nbsp;<span style="cursor:pointer;color:#1E9FFF">[查看清单]</span></a>';
    }},
    {field: 'packageitems', width: 80, title: '箱数/件数', templet: function(d){
      return d.packageitems.length;
    }},
    {field: 'line', width: 120, title: '渠道', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',templet: function(d){
      return d.line?d.line.name:'';
    }},
    {field: 'line', width: 120, title: '寄件仓库',style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;', templet: function(d){
      return d.storage?d.storage.shop_name:'';
    }},
    {field: 'line', width: 120, title: '现处仓库',style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;', templet: function(d){
      return d.shop?d.shop.shop_name:'';
    }},
    
    {field: 'volume', width: 60, title: '体积'},
    {field: 'weight', width: 60, title: '实重'},
    {field: 'cale_weight', width: 60, title: '计费重量'},
    {field: 'free', width: 60, title: '基础费用', templet: function(d){
      return d.free?d.free:0;
    }},
    {field: 'pack_free', width: 60, title: '包装费', templet: function(d){
      return d.pack_free?d.pack_free:0;
    }},
    {field: 'insure_free', width: 60, title: '保险费', templet: function(d){
      return d.insure_free?d.insure_free:0;
    }},
    {field: 'other_free', width: 60, title: '其他费用', templet: function(d){
      return d.other_free?d.other_free:0;
    }},
    {field: 'user_coupon_money', width: 60, title: '优惠券', templet: function(d){
      return d.usercoupon?d.usercoupon.name:'';
    }},
    {field: 'user_coupon_money', width: 60, title: '优惠金额', templet: function(d){
      return d.user_coupon_money?d.user_coupon_money:0;
    }},
    {
      field: 'total_free', 
      title: '费用合计', 
      templet: function(d) {
            const total = 
            parseFloat(d.free || 0) + 
            parseFloat(d.insure_free || 0) + 
            parseFloat(d.pack_free || 0) + 
            parseFloat(d.other_free || 0)
          return isNaN(total) ? "0.00" : total.toFixed(2); // 处理意外NaN
      }
    },
    {field: 'real_payment', width: 60, title: '实际支付', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;', templet: function(d){
      // 只有已支付(is_pay==1)或支付失败(is_pay==3)时才显示实际支付金额，待审核状态不显示
      if(d.is_pay == 1 || d.is_pay == 3) {
        return d.real_payment; 
      }
      return '-';
    }},
    {field: 'waitreceivedmoney', width: 60, title: '代收款', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;', templet: function(d){
      return d.waitreceivedmoney; 
    }},
    {field: 'status', width: 60, title: '支付状态',  style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',templet: function(d){
      var html = '';
      if(d.is_pay == 1) html += '<span class="am-badge am-badge-success">' + paytime_status[d.is_pay] +'</span>';
      if(d.is_pay == 2) html += '<span class="am-badge am-badge-secondary">' + paytime_status[d.is_pay] +'</span>';
      if(d.is_pay == 3) html += '<span class="am-badge am-badge-danger">' + paytime_status[d.is_pay] +'</span>';
      return html;
    }},
    {field: 'pay_type', width: 60, title: '支付类型', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;', templet: function(d){
      var html = '';
      if(d.pay_type.value == 0) html += '<span class="am-badge am-badge-success">' + d.pay_type.text +'</span>';
      if(d.pay_type.value == 1) html += '<span class="am-badge am-badge-danger">' + d.pay_type.text +'</span>';
      if(d.pay_type.value == 2) html += '<span class="am-badge am-badge-primary">' + d.pay_type.text +'</span>';
      return html;
    }}, 
    {field: 'is_pay_type', width: 60, title: '支付方式', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;', templet: function(d){
      var html = '';
      if(d.is_pay_type.value == 0) html += '<span class="am-badge layui-bg-red">' + d.is_pay_type.text +'</span>';
      if(d.is_pay_type.value == 1) html += '<span class="am-badge layui-bg-green">' + d.is_pay_type.text +'</span>';
      if(d.is_pay_type.value == 2) html += '<span class="am-badge layui-bg-blue">' + d.is_pay_type.text +'</span>';
      if(d.is_pay_type.value == 3) html += '<span class="am-badge am-badge-danger">' + d.is_pay_type.text +'</span>';
      if(d.is_pay_type.value == 4) html += '<span class="am-badge am-badge-secondary">' + d.is_pay_type.text +'</span>';
      if(d.is_pay_type.value == 5) html += '<span class="am-badge am-badge-success">' + d.is_pay_type.text +'</span>';
      if(d.is_pay_type.value == 6) html += '<span class="am-badge layui-bg-cyan">' + d.is_pay_type.text +'</span>';
      return html;
    }},
    
    {field: 'usermark', width: 60, title: '唛头', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 'batch_name', width: 100, align: 'center',title: '批次号', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 'batch_no', width: 100,align: 'center', title: '提单号/装箱号', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 't_name', width: 100, title: '承运商', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 't_order_sn', width: 250, title: '国际单号', templet: function(d) { 
        var html = '';
        var hasMultipleBoxes = d.packageitems && d.packageitems.length > 1;
        
        // Fallback: If mother SN is empty, use the first package's SN
        var mainSn = d.t_order_sn || (d.packageitems && d.packageitems.length > 0 ? d.packageitems[0].t_order_sn : '');

        // Status Badge Helper
        var getStatusBadge = function(code) {
           var strCode = String(code || '').trim();
           if (!strCode || strCode == '0') return '';

           if (strCode == '44') {
                return '<span class="am-badge am-badge-warning am-radius" style="font-size:10px;padding:2px 4px;margin-left:2px">派送中</span>';
            } else if (strCode == '80') {
                return '<span class="am-badge am-badge-success am-radius" style="font-size:10px;padding:2px 4px;margin-left:2px">已签收</span>';
            } else if (['50', '3036', '30'].includes(strCode)) {
                return '<span class="am-badge am-badge-secondary am-radius" style="font-size:10px;padding:2px 4px;margin-left:2px">已揽收</span>';
            }
            return '';
        };

        // Determine Mother Status (Frontend Force Fallback)
        var mainStatus = d.last_trace_code;
        if ((!mainStatus || String(mainStatus) == '0') && d.packageitems && d.packageitems.length > 0) {
             mainStatus = d.packageitems[0].last_trace_code;
        }

        if (hasMultipleBoxes) {
            // Mother Order Line
            html += '<div>国际单号: <span style="cursor:pointer;color:#1E9FFF" class="copyable-text" data-text="' + (mainSn || '') + '">' + (mainSn || '-') + '</span>';

            // Mother Weight (from first package)
            var motherWeight = (d.packageitems && d.packageitems[0]) ? d.packageitems[0].weight : 0;
            if (motherWeight > 0) {
                html += '<span style="color:#999;font-size:11px;">(' + motherWeight + 'kg)</span>';
            }
            html += '<span class="am-badge am-badge-primary am-radius" style="font-size:10px;">母单</span>';
            
            // Mother Order Status (Use calculated mainStatus)
            html += getStatusBadge(mainStatus);

            html += '<a href="javascript:;" onclick="getlog(this)" value="' + d.id + '" style="margin-left:5px;color:blue">[物流]</a>';

            // Copy all logic
             var waybills = [];
             if(d.packageitems){
                 d.packageitems.forEach(function(box){
                     if(box.t_order_sn) waybills.push(box.t_order_sn);
                 });
             }
             var waybillsStr = waybills.join(',');
             html += '<a href="javascript:;" onclick="copyAllWaybills(this)" data-waybills="' + waybillsStr + '" style="margin-left:5px;color:blue">[复制全部]</a></div>';

            // Sub orders (Skip index 0 as it serves as the mother/main SN)
            if(d.packageitems) {
                d.packageitems.forEach(function(box, index) {
                    if (index > 0 && box.t_order_sn) {
                         html += '<div style="margin-top:5px;margin-left:10px;color:#999;">└ 子单: ';
                         html += '<span style="cursor:pointer;color:#1E9FFF" class="copyable-text" data-text="' + box.t_order_sn + '">' + box.t_order_sn + '</span>';
                         html += '<span style="color:#999;font-size:11px;">(' + box.weight + 'kg)</span>';

                         // Sub Order Status
                         html += getStatusBadge(box.last_trace_code);

                         html += '<a href="javascript:;" onclick="getlog(this)" value="' + d.id + '" style="margin-left:5px;color:blue">[物流]</a></div>';
                    }
                });
            }

        } else {
             // Single Box
             html += '国际单号: <span style="cursor:pointer;color:#1E9FFF" class="copyable-text" data-text="' + (mainSn || '') + '">' + (mainSn || '-') + '</span>';
             
             // Single Order Status
             html += getStatusBadge(d.last_trace_code);
             
             html += '<a href="javascript:;" onclick="getlog(this)" value="' + d.id + '" style="margin-left:5px;color:blue">[物流]</a>';
        }

        return html;
    }},
    {field: 't2_name', width: 100, title: '转运商', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 't2_order_sn', width: 100, title: '转单单号', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 'created_time', width: 130, title: '提交打包', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 'pick_time', width: 130, title: '打包完成', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 'pay_time', width: 130, title: '支付时间', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 'shoprk_time', width: 130, title: '到货时间', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 'receipt_time', width: 130, title: '签收时间', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 'settle_time', width: 130, title: '结算时间', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 'cancel_time', width: 130, title: '取消时间', style: 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;',},
    {field: 'actions', width: 220, title: '操作', fixed: 'right', templet: function(d){
      return '<div class="action-btn-group">'+
        (<?= checkPrivilege('tr_order/edit') ? 'true' : 'false' ?> ? 
          '<a href="<?= url("store/trOrder/edit") ?>/id/'+d.id+'">'+
            '<button class="layui-btn layui-btn-xs layui-btn-normal">编辑</button>'+
          '</a>' : '')+
        (<?= checkPrivilege('tr_order/orderdetail') ? 'true' : 'false' ?> ? 
          '<a href="<?= url("store/trOrder/orderdetail") ?>/id/'+d.id+'">'+
            '<button class="layui-btn layui-btn-xs">详情</button>'+
          '</a>' : '')+
        (<?= checkPrivilege('tr_order/orderdelete') ? 'true' : 'false' ?> ? 
          '<a href="javascript:void(0);" class="item-delete" data-id="'+d.id+'">'+
            '<button class="layui-btn layui-btn-xs layui-btn-danger">删除</button>'+
          '</a>' : '')+
        '<div class="layui-btn-container">' +
              '<button class="layui-btn layui-btn-xs" >'+
                '更多 <i class="layui-icon layui-icon-down"></i>' +
              '</button>' +
              '<ul class="layui-dropdown-menu">' +
                (<?= checkPrivilege('tr_order/deliverysave') ? 'true' : 'true' ?> ? 
                  '<li><a href="<?= url("store/trOrder/delivery") ?>/id/'+d.id+'"><i class="layui-icon layui-icon-export"></i> 发货</a></li>' : '') +
                (<?= checkPrivilege('tr_order/print') ? 'true' : 'false' ?> ? 
                  '<li><a href="javascript:void(0);" class="j-express"  data-id="'+d.id+'"><i class="layui-icon layui-icon-print"></i> 打印面单</a></li>' : '') +
                (<?= checkPrivilege('tr_order/expresslabel') ? 'true' : 'false' ?> ? 
                  '<li><a href="javascript:void(0);" class="j-label"  data-id="'+d.id+'"><i class="layui-icon layui-icon-tabs"></i> 打印标签</a></li>' : '') +
                (<?= checkPrivilege('tr_order/updateaddress') ? 'true' : 'false' ?> ? 
                  '<li><a href="javascript:void(0);" class="j-changeaddress"  data-id="'+d.id+'" data-user="'+d.member_id+'"><i class="layui-icon layui-icon-location"></i> 变更地址</a></li>' : '') +
                (<?= checkPrivilege('tr_order/payyue') ? 'true' : 'false' ?> ? 
                  '<li><a class="j-payyue" data-id="'+d.id+'" data-name="'+d.nickName+'" data-user_id="'+d.member_id+'"><i class="layui-icon layui-icon-rmb"></i> 余额扣除</a></li>' : '') +
                (<?= checkPrivilege('tr_order/cashforprice') ? 'true' : 'false' ?> ? 
                  '<li><a class="j-payxianjin" data-id="'+d.id+'" data-name="'+d.nickName+'" data-user_id="'+d.member_id+'"><i class="layui-icon layui-icon-dollar"></i> 现金收款</a></li>' : '') +
                (<?= checkPrivilege('tr_order/freelistlabel') ? 'true' : 'false' ?> ? 
                  '<li><a class="j-freelist" data-id="'+d.id+'" href="javascript:void(0);"><i class="layui-icon layui-icon-form"></i> 打印账单</a></li>' : '') +
                  '<li><a class="j-exportdetail" data-id="'+d.id+'" href="javascript:void(0);"><i class="layui-icon layui-icon-release"></i> 导出包裹</a></li>'
              '</ul>' +
            '</div>' +
          '</div>';
        }}
  ]],
  data: <?= json_encode($list->items()) ?>,
  page: false,
  limit: <?= $list->listRows() ?>,
  limits: [15, 30, 50, 100, 200, 300, 500],
  even: true,
  skin: 'line',
  done: function(res, curr, count){

     // 手动绑定点击事件（备用方案）
$('.layui-btn-container button').off('click').on('click', function(e) {
    e.stopPropagation();
    var $menu = $(this).next('.layui-dropdown-menu');
    var $button = $(this);
    
    // 隐藏其他所有下拉菜单
    $('.layui-dropdown-menu').not($menu).hide();
    
    // 计算按钮和菜单的位置
    var buttonRect = $button[0].getBoundingClientRect();
    var menuHeight = $menu.outerHeight();
    var windowHeight = window.innerHeight;
    
    // 判断是否有足够的空间在下方显示
    if (buttonRect.bottom + menuHeight > windowHeight) {
        // 空间不足，向上弹出
        $menu.css({
            'top': 'auto',
            'bottom': '100%',
            'margin-top': '0',
            'margin-bottom': '5px'
        });
    } else {
        // 空间足够，向下弹出（恢复默认）
        $menu.css({
            'top': '100%',
            'bottom': 'auto',
            'margin-top': '5px',
            'margin-bottom': '0'
        });
    }
    
    // 计算水平位置，防止右侧溢出
    var menuWidth = $menu.outerWidth();
    if (buttonRect.left + menuWidth > window.innerWidth) {
        $menu.css({
            'left': 'auto',
            'right': '0'
        });
    } else {
        $menu.css({
            'left': '0',
            'right': 'auto'
        });
    }
    
    // 切换当前菜单的显示/隐藏
    $menu.toggle();
});
  

    // 表格渲染完成后初始化选中数量
    updateSelectedCount();
  }
});



// 点击页面其他位置关闭下拉菜单
$(document).on('click', function(e) {
    if(!$(e.target).closest('.layui-dropdown-menu').length && 
       !$(e.target).closest('.layui-btn-container').length) {
        $('.layui-dropdown-menu').hide();
    }
});

    
// 分页初始化
laypage.render({
  elem: 'pagination',
  count: <?= $list->total() ?>, // 总记录数
  limit: <?= $list->listRows() ?>, // 每页显示数量
  curr: <?= $list->currentPage() ?>, // 当前页
  limits: [15, 30, 50, 100,200,300,500], // 可选的每页条数
  layout: ['count', 'prev', 'page', 'next', 'limit', 'skip'],
  jump: function(obj, first){
    // 首次不执行
    if(!first){
      // 跳转到新页面
      var url = updateQueryStringParameter(window.location.href, 'page', obj.curr);
      url = updateQueryStringParameter(url, 'limitnum', obj.limit);
      window.location.href = url;
    }
  }
});
 
// 点击复制功能
$(document).on('click', '.copyable-text', function(){
    var text = $(this).data('text');
    copyToClipboard(text);
});

// 复制到剪贴板函数
function copyToClipboard(text) {
    var $temp = $('<input>');
    $('body').append($temp);
    $temp.val(text).select();
    
    try {
        var successful = document.execCommand('copy');
        if(successful) {
            layer.msg('已复制: ' + text, {icon: 1, time: 1500});
        } else {
            layer.msg('复制失败，请手动复制', {icon: 2});
        }
    } catch (err) {
        layer.msg('浏览器不支持自动复制', {icon: 2});
    }
    
    $temp.remove();
}

// 复制全部运单号
function copyAllWaybills(element) {
    var waybills = $(element).data('waybills');
    if(waybills) {
        copyToClipboard(waybills);
    }
} 
  
// 更新URL参数的辅助函数
function updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
      return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
      return uri + separator + key + "=" + value;
    }
}
  
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
  
// 更新选中数量显示
function updateSelectedCount() {
    var checkStatus = table.checkStatus('order-table');
    var selectedCount = checkStatus.data.length;
    var total = table.cache['order-table'].length;
    
    $('#selected-count').text('已选' + selectedCount + '项');
    
    if(selectedCount === total && total > 0) {
        $('#check-all').removeClass('layui-btn-primary').addClass('layui-btn-normal')
            .html('<i class="layui-icon layui-icon-ok"></i> 取消全选');
    } else if(selectedCount === 0) {
        $('#check-all').removeClass('layui-btn-normal').addClass('layui-btn-primary')
            .html('<i class="layui-icon layui-icon-ok"></i> 全选');
    } else {
        $('#check-all').removeClass('layui-btn-primary layui-btn-normal')
            .html('<i class="layui-icon layui-icon-ok"></i> 部分选中');
    }
}



// 监听表格复选框变化
table.on('checkbox(order-table)', function(obj){
    updateSelectedCount();
});
    
// 搜索表单提交
form.on('submit(search)', function(data){
  // 获取当前URL的基础部分（去掉已有参数）
  var baseUrl = window.location.pathname + '?s=' + window.location.search.match(/s=([^&]*)/)[1];
  
  // 构建参数对象
  var params = {
    page: 1, // 搜索时重置到第一页
    limitnum: <?= $list->listRows() ?> // 保持当前每页数量
  };
  
  // 添加搜索参数（只添加有值的参数）
  if(data.field.status) params.status = data.field.status;
  if(data.field.extract_shop_id) params.extract_shop_id = data.field.extract_shop_id;
  if(data.field.line_id) params.line_id = data.field.line_id;
  if(data.field.orderparam) params.orderparam = data.field.orderparam;
  if(data.field.descparam) params.descparam = data.field.descparam;
  if(data.field.time_type) params.time_type = data.field.time_type;
  if(data.field.start_time) params.start_time = data.field.start_time;
  if(data.field.end_time) params.end_time = data.field.end_time;
  if(data.field.batch_no) params.batch_no = data.field.batch_no;
  if(data.field.order_sn) params.order_sn = data.field.order_sn;
  if(data.field.search_type) params.search_type = data.field.search_type;
  if(data.field.search) params.search = data.field.search;
  
  // 构建查询字符串（以&开头）
  var queryString = '';
  for(var key in params) {
    queryString += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
  }
  
  // 跳转到新URL
  window.location.href = baseUrl + queryString;
  
  return false; // 阻止表单默认提交
});

// 重置按钮
$('button[type="reset"]').click(function(){
    window.location.href = getBaseUrl() + '&page=1&limitnum=15';
    return false;
});
  
// 获取基础URL
function getBaseUrl() {
    var path = window.location.pathname;
    var search = window.location.search;
    var sParam = search.match(/s=([^&]*)/);
    return path + (sParam ? '?s=' + sParam[1] : '');
}
    
    // 修改用户按钮事件
    $('#change-user').click(function(){
      var checked = $('input[name="id"]:checked').length;
      if(checked === 0){
        layer.msg('请至少选择一条订单', {icon: 5});
        return;
      }
      layer.open({
        type: 1,
        title: '修改用户',
        content: '<div style="padding: 20px;">' +
                 '<div class="layui-form-item">' +
                 '<label class="layui-form-label">选择用户</label>' +
                 '<div class="layui-input-block">' +
                 '<select name="user" lay-search><option value="">搜索选择用户</option>' +
                 '<option value="1">用户1 (ID:1001)</option>' +
                 '<option value="2">用户2 (ID:1002)</option>' +
                 '</select>' +
                 '</div></div></div>',
        area: '500px',
        btn: ['确定', '取消'],
        yes: function(index, layero){
          layer.close(index);
        }
      });
      form.render();
    });
    
    // 其他按钮事件...
    $('#change-status').click(function(){
      // 状态变更逻辑
    });
    
    $('#merge-order').click(function(){
      // 合并订单逻辑
    });
/**
 * 修改入库状态 - 兼容Layui的实现
 */
$('#j-upstatus').on('click', function(){
    var checkStatus = table.checkStatus('order-table');
    var selectIds = checkStatus.data.map(function(item){ return item.id; });
    if (selectIds.length === 0){
        layer.msg('请先选择集运单', {icon: 5});
        return;
    }
    
    var templateData = {
        statusOptions: [
            {value: '1', name: '待入库', selected: false},
            {value: '2', name: '已入库', selected: true},
            {value: '3', name: '已上架', selected: false}
        ],
        defaultRemark: '批量修改'+selectIds.length+'条记录的状态',
        selectCount: selectIds.length
    };
    var templateHtml = layui.laytpl($('#tpl-status').html()).render(templateData);
    // 打开弹窗
    layer.open({
        type: 1,
        title: '入库状态',
        area: '460px',
        content: templateHtml, // 假设模板ID为tpl-status
        btn: ['确定', '取消'],
        yes: function(index, layero){
            // 获取表单数据
            var formData = $(layero).find('form').serialize();
            
            // 添加选中的ID
            formData += '&selectIds=' + selectIds.join(',');
            // 显示加载中
            var loadIndex = layer.load(1);
            // 发送AJAX请求
            $.ajax({
                url: '<?= url("store/trOrder/upsatatus") ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(res){
                    layer.close(loadIndex);
                    if(res.code === 1){
                        layer.msg(res.msg, {icon: 1}, function(){
                            // 刷新表格数据
                          window.location.reload();
                        //   var currentPage = 1; // 默认回到第一页
                        //     if(orderTable && orderTable.config){
                        //         currentPage = orderTable.config.page.curr;
                        //     }
                        //     // 重新加载表格数据
                        //     table.reload('order-table', {
                        //         url: '<?= url("store/trOrder/getTroderList") ?>', // 新增数据接口
                        //         where: {
                        //             status: $('select[name="status"]').val(),
                        //             extract_shop_id: $('select[name="extract_shop_id"]').val(),
                        //             line_id: $('select[name="line_id"]').val(),
                        //             start_time: $('#start-time').val(),
                        //             end_time: $('#end-time').val(),
                        //             order_sn: $('input[name="order_sn"]').val()
                        //         },
                        //         page: {
                        //             curr: currentPage // 保持当前页
                        //         }
                        //     });
                            
                        });
                    }else{
                        layer.msg(res.msg || '操作失败', {icon: 2});
                    }
                    layer.close(index)
                },
                error: function(xhr, status, error){
                    layer.close(loadIndex);
                    layer.msg('请求失败: ' + error, {icon: 2});
                }
            });
        },
        btn2: function(index, layero){
            // 取消按钮回调
        },
        success: function(layero, index){
            // 弹窗成功打开后执行
            form.render(); // 渲染表单元素
        }
    });
});   
    
 

// 复制功能
$('.copy-btn').click(function(){
  var text = $(this).prev().text().trim();
  var $temp = $("<input>");
  $("body").append($temp);
  $temp.val(text).select();
  document.execCommand("copy");
  $temp.remove();
  layer.msg('已复制: ' + text, {icon: 1, time: 1000});
});
    
    
// 删除操作
$('.item-delete').click(function(){
    var id = $(this).data('id');
    layer.confirm('确定要删除该订单吗？', function(index){
      $.post('<?= url("store/trOrder/orderdelete") ?>', {id: id}, function(res){
        if(res.code === 1){
          layer.msg(res.msg, {icon: 1}, function(){
            window.location.reload();
          });
        }else{
          layer.msg(res.msg, {icon: 2});
        }
      }, 'json');
      layer.close(index);
    });
});

$('#j-upuser').on('click', function () {
    var checkStatus = table.checkStatus('order-table');
    var selectIds = checkStatus.data.map(function(item){ return item.id; });
    if (selectIds.length === 0){
        layer.msg('请先选择集运单', {icon: 5});
        return;
    }
    
    var data = {};
    data.selectId = selectIds.join(',');
    data.selectCount = selectIds.length;
    
    layer.open({
        type: 1,
        title: '修改会员',
        area: '460px',
        content: template('tpl-grade', data),
        btn: ['确定', '取消'],
        yes: function(index, layero) {
            var $content = $(layero).find('.layui-layer-content');
            $content.find('form').myAjaxSubmit({
                url: '<?= url('/store/tr_Order/changeUser') ?>',
                data: {selectIds: data.selectId},
                success: function() {
                    layer.close(index);
                }
            });
        },
        success: function(layero, index) {
            // 使用事件委托代替直接 onclick
            $(layero).on('click', '[data-action="selectUser"]', function() {
                var $userList = $(layero).find('.user-list');
                $.selectData({
                    title: '选择用户',
                    uri: 'user/lists',
                    dataIndex: 'user_id',
                    done: function (data) {
                        var user = [data[0]];
                        $userList.html(template('tpl-user-item', user));
                    }
                });
            });
        }
    });
});

/**
 * 合并订单 (Layui兼容版)
 */
$('#j-hedan').on('click', function () {
    // 使用Layui表格的选中状态
    var checkStatus = table.checkStatus('order-table'); // 获取表格选中数据
    var selectIds = checkStatus.data.map(function(item) { 
        return item.id; // 获取选中行的ID数组
    });
    
    if (selectIds.length === 0) {
        layer.msg('请先选择集运单', {icon: 5});
        return;
    }
    
    // 检查是否同一用户（可选）
    var userIds = [];
    checkStatus.data.forEach(function(item) {
        if(item.user && item.user.user_id) {
            userIds.push(item.user.user_id);
        }
    });
    
    // 如果有用户信息且用户不一致
    if(userIds.length > 0 && new Set(userIds).size > 1) {
        layer.alert('请选择同一用户的订单进行合并！不同用户敬请期待拼邮功能开发(*^_^*)', {
            title: '提示',
            icon: 5,
            closeBtn: 0
        });
        return;
    }
    
    layer.confirm('确定合并选中的 ' + selectIds.length + ' 个订单吗？<br>合并后订单将无法拆分！', {
        title: '合并订单确认',
        icon: 3,
        btn: ['确定合并', '取消']
    }, function(index) {
        // 显示加载中
        var loadIndex = layer.load(1);
        
        // 发送请求
        $.ajax({
            url: "<?= url('store/trOrder/hedan') ?>",
            type: "POST",
            data: { 
                ids: selectIds.join(',') 
            },
            dataType: "json",
            success: function(result) {
                layer.close(loadIndex);
                if(result.code === 1) {
                    // 成功提示
                    layer.msg(result.msg, {
                        icon: 1,
                        time: 1500
                    }, function() {
                        // 刷新页面或表格
                        if(result.url) {
                            window.location.href = result.url;
                        } else {
                            table.reload('order-table'); // 重新加载表格
                        }
                    });
                } else {
                    // 错误提示
                    layer.msg(result.msg || '操作失败', {
                        icon: 2,
                        time: 2000
                    });
                }
            },
            error: function() {
                layer.close(loadIndex);
                layer.msg('请求失败，请稍后重试', {icon: 2});
            }
        });
        
        layer.close(index);
    });
});

$('#j-wuliu').on('click', function() {
    var checkStatus = table.checkStatus('order-table');
    var selectIds = checkStatus.data.map(function(item) { return item.id; });
    
    if (selectIds.length === 0) {
        layer.msg('请先选择集运单', {icon: 5});
        return;
    }
    
    var data = {
        selectId: selectIds.join(','),
        selectCount: selectIds.length
    };
    
    layer.open({
        type: 1,
        title: '批量更新物流信息',
        area: ['500px', 'auto'],
        content: template('tpl-wuliu', data),
        btn: ['确认提交', '取消'],
        success: function(layero, index) {
            // 初始化表单
            form.render();
            
            // 初始化日期时间选择器
            laydate.render({
                elem: '#datetimepicker',
                type: 'datetime',
                value: new Date(),
                format: 'yyyy-MM-dd HH:mm:ss',
                trigger: 'click'
            });
        },
        yes: function(index, layero) {
            // 表单验证
            form.on('submit(wuliu-form)', function(data){
                data.field.selectIds = selectIds.join(',');
                
                // 显示加载中
                var loadIndex = layer.load(1);
                
                $.ajax({
                    url: '<?= url('store/trOrder/alllogistics') ?>',
                    type: 'POST',
                    data: data.field,
                    dataType: 'json',
                    success: function(res) {
                        layer.close(loadIndex);
                        if(res.code === 1) {
                            layer.msg(res.msg, {icon: 1}, function() {
                                layer.close(index);
                                table.reload('order-table');
                            });
                        } else {
                            layer.msg(res.msg || '更新失败', {icon: 2});
                        }
                    },
                    error: function() {
                        layer.close(loadIndex);
                        layer.msg('请求失败，请检查网络', {icon: 2});
                    }
                });
                return false;
            });
            
            // 触发表单提交
            $(layero).find('form').submit();
        }
    });
});

/**
 * 加入批次 (Layui兼容版)
 */
$('#j-batch').on('click', function() {
    // 获取表格选中行
    var checkStatus = table.checkStatus('order-table');
    var selectIds = checkStatus.data.map(function(item) {
        return item.id;
    });
    
    if (selectIds.length === 0) {
        layer.msg('请先选择集运单', {icon: 5});
        return;
    }
    
    var data = {
        selectId: selectIds.join(','),
        selectCount: selectIds.length
    };
    
    // 使用Layui弹窗
    layer.open({
        type: 1,
        title: '将订单加入到批次中',
        area: ['460px', 'auto'],
        content: template('tpl-batch', data),
        btn: ['确认加入', '取消'],
        success: function(layero, index) {
            // 初始化表单元素
            form.render();
            
            // 如果需要初始化其他组件可以在这里添加
        },
        yes: function(index, layero) {
            // 获取表单数据
            var formData = {
                selectIds: data.selectId,
                batch_id: $(layero).find('select[name="batch_id"]').val()
            };
            
            // 显示加载中
            var loadIndex = layer.load(1);
            
            // 发送请求
            $.ajax({
                url: '<?= url('store/batch/addtobatch') ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(res) {
                    layer.close(loadIndex);
                    if(res.code === 1) {
                        layer.msg(res.msg, {icon: 1}, function() {
                            layer.close(index); // 关闭弹窗
                            table.reload('order-table'); // 刷新表格
                        });
                    } else {
                        layer.msg(res.msg || '操作失败', {icon: 2});
                    }
                },
                error: function() {
                    layer.close(loadIndex);
                    layer.msg('请求失败，请检查网络', {icon: 2});
                }
            });
        }
    });
});

/**
 * 批量打印运单 (优化版-处理PDF路径)
 */
$('#j-batch-print').on('click', function() {
    // 1. 获取选中订单
    var checkStatus = table.checkStatus('order-table');
    var selectIds = checkStatus.data.map(function(item) {
        return item && item.id ? item.id : null;
    }).filter(Boolean); // 过滤无效ID
    
    if (selectIds.length === 0) {
        layer.msg('请先选择要打印的集运单', {icon: 5, time: 1500});
        return;
    }

    // 2. 显示加载中
    var loadIndex = layer.load(1, { 
        shade: [0.3, '#000'],
        content: '正在生成面单...'
    });

    // 3. 发送请求
    $.ajax({
        type: "POST",
        url: '<?= url('store/trOrder/expressBillbatch') ?>',
        data: { 
            selectIds: selectIds.join(',') // 数组转字符串
        },
        dataType: "json",
        success: function(res) {
            layer.close(loadIndex);
            
            // 3.1 处理成功响应
            if (res && (res.url || typeof res === 'string')) {
                // 统一处理URL格式（修复双斜杠问题）
                var pdfUrl = typeof res === 'string' ? res : res.url;
                pdfUrl = pdfUrl.replace(/([^:]\/)\/+/g, '$1'); // 移除多余斜杠
                
                // 3.2 安全打开新窗口
                try {
                    var printWindow = window.open('', '_blank');
                    if (printWindow) {
                        printWindow.location.href = pdfUrl;
                    } else {
                        layer.msg('请允许弹出窗口以查看面单', {icon: 2});
                    }
                } catch (e) {
                    layer.msg('打开打印页面失败: ' + e.message, {icon: 2});
                }
                
            } 
            // 3.3 处理错误响应
            else {
                var errorMsg = res && res.msg ? res.msg : '无效的响应格式';
                layer.msg('生成失败: ' + errorMsg, {icon: 2});
            }
        },
        error: function(xhr) {
            layer.close(loadIndex);
            var errorMsg = xhr.responseJSON ? 
                         xhr.responseJSON.msg || '服务器错误' : 
                         '网络连接失败 (状态码: ' + xhr.status + ')';
            layer.msg(errorMsg, {icon: 2, time: 3000});
        }
    });
});


/**
 * 批量打印云面单 (新功能 - 调用 OrderBatchPrinter)
 */
$('#j-batch-cloud-print').on('click', function() {
    // 1. 获取选中订单
    var checkStatus = table.checkStatus('order-table');
    var data = checkStatus.data;
    
    if (data.length === 0) {
        layer.msg('请先选择要打印的订单', {icon: 5});
        return;
    }

    // 2. 识别并分组渠道
    var groups = {};
    var noDitchCount = 0;
    
    data.forEach(function(item) {
        var ditchId = item.t_number;
        if (!ditchId || ditchId == 0) {
            noDitchCount++;
            return;
        }
        if (!groups[ditchId]) {
            groups[ditchId] = [];
        }
        groups[ditchId].push(item.id);
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
        OrderBatchPrinter.printWithUI(groups[ditchId], ditchId, {
            onSuccess: function() {
                // 成功后根据需要刷新（可选）
                // table.reload('order-table');
            }
        });
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
 * 加入拼团（兼容 Layui）
 */
$('#j-pintuan').on('click', function () {
    var checkStatus = table.checkStatus('order-table');
    var selectIds = checkStatus.data.map(function(item) {
        return item && item.id ? item.id : null;
    }).filter(Boolean);

    // 1. 检查是否选中集运单
    if (selectIds.length == 0) {
        layer.alert('请先选择集运单', { icon: 5 });
        return;
    }

    var data = {
        selectId: selectIds.join(','),
        selectCount: selectIds.length
    };

    // 2. 使用 Layui 弹层
    layer.open({
        type: 1,
        title: '加入拼团',
        area: '460px',
        content: template('tpl-tuan', data),
        btn: ['确认', '取消'],
        yes: function(index, layero) {
            // 3. 获取用户选择的 pintuan_id
            var pintuanId = layero.find('select[name="pintuan_id"]').val();
            
            if (!pintuanId) {
                layer.msg('请选择拼团', { icon: 5 });
                return false; // 阻止关闭弹层
            }

            // 4. 提交数据（包含 selectIds 和 pintuan_id）
            $.ajax({
                url: '<?= url('store/trOrder/pintuan') ?>',
                type: 'POST',
                data: { 
                    selectIds: data.selectId,
                    pintuan_id: pintuanId  // 添加拼团ID
                },
                success: function(res) {
                    layer.close(index);
                    if (res.code === 0) {
                        layer.msg('操作成功', { icon: 5 });
                       
                        // 可选：刷新页面或表格数据
                        table.reload('order-table');
                    } else {
                        layer.alert(res.msg || '操作失败', { icon: 2 });
                    }
                    
                    
                    if(res.code === 1){
                        layer.msg(res.msg, {icon: 1}, function(){
                            // 刷新表格数据
                          window.location.reload();
                        });
                    }else{
                        layer.msg(res.msg || '操作失败', {icon: 2});
                    }
                    layer.close(index)
                    
                },
                error: function() {
                    layer.alert('网络错误', { icon: 2 });
                }
            });
        }
    });
});

$(".j-express").on('click', function(){
    var data = $(this).data();
    $.ajax({
        url: '<?= url('store/trOrder/expressBill') ?>',
        type: "get",
        data: {id: data['id']},
        success: function(result) {
            console.log(result);

            if(result.code === 0) {
                layer.alert(result.msg, {icon: 5});
                return; 
            }
            
            layer.open({
                type: 1,
                title: '标签打印预览',
                area: ['400px', '700px'],
                content: result,
                btn: ['打印', '取消'],
                success: function(layero, index) {
                    console.log(layero, index);
                },
                yes: function(index, layero) {
                    PrintDiv(result);
                    layer.close(index);
                },
                btn2: function(index, layero) {
                    layer.close(index);
                }
            });
        }
    });
});

/**
 * 打印指定内容的兼容性函数（支持Layui环境）
 * @param {string|jQueryObject} content 要打印的内容 
 */
function PrintDiv(content) {
    // 确保Layui模块已加载
    layui.use(['layer'], function(){
        var layer = layui.layer;
        
        try {
            // 创建打印窗口
            var printWindow = window.open("", "_blank");
            if (!printWindow) {
                layer.msg('弹出窗口被阻止，请允许浏览器弹出窗口', {icon: 2});
                return;
            }

            // 构建打印文档结构
            printWindow.document.write(content);
            printWindow.document.close();

            // 浏览器兼容性处理
            if (navigator.userAgent.indexOf("Chrome") !== -1) {
                // Chrome浏览器
                printWindow.onload = function() {
                    setTimeout(function() { // 确保内容完全加载
                        printWindow.document.execCommand('print');
                        printWindow.close();
                    }, 300);
                };
            } else if (navigator.userAgent.indexOf("Firefox") !== -1) {
                // Firefox浏览器
                printWindow.onload = function() {
                    printWindow.print();
                    printWindow.close();
                };
            } else {
                // 其他浏览器（IE/Edge等）
                setTimeout(function() {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            }
            
        } catch (e) {
            layer.msg('打印出错: ' + e.message, {icon: 2});
            console.error("打印错误:", e);
        }
    });
}

$(".j-changeaddress").on('click', function(){
    var data = $(this).data();
    layui.use(['layer', 'jquery'], function(){
        var layer = layui.layer;
        var $ = layui.jquery;
        var id = data['id'];
        var user_id = data['user'];
        if(!user_id) {
            // 调试输出
            console.error("获取用户ID失败，当前元素data属性：", $(this).data());
            layer.alert('用户信息有误', {icon: 5});
            return false;
        }
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
                    type: "POST",
                    url: '<?= url('store/trOrder/updateAddress') ?>',
                    data: {
                        id: id, 
                        address_id: list[0].address_id
                    },
                    success: function(res){
                        if(res.code === 1) {
                            layer.msg('地址修改成功', {icon: 1});
                            setTimeout(function(){ 
                                location.reload();
                            }, 1500);
                        } else {
                            layer.alert(res.msg || '修改失败', {icon: 5});
                        }
                    },
                    error: function() {
                        layer.alert('网络请求失败', {icon: 5});
                    }
                });
                }
            });

    });
});

// 打印标签（兼容Layui）
$(".j-label").on('click', function() {
    var data = $(this).data();
    
    // 使用layui的layer弹窗
    layui.use('layer', function() {
        var layer = layui.layer;
        
        layer.open({
            type: 1,                     // 页面层类型
            title: '批量更新订单动态',     // 标题
            area: '460px',               // 弹窗宽度
            content: template('tpl-label', { inpack_id: data.id }), // 使用模板引擎渲染内容
            btn: ['确定', '取消'],        // 按钮组
            success: function(layero, index) {
                // 弹窗成功回调
                console.log("弹窗渲染完成");
            },
            yes: function(index, layero) {
                // 点击"确定"按钮的回调
                console.log("执行打印逻辑");
                // 这里可以调用打印方法，例如：
                // PrintDiv(layero.find('.layui-layer-content').html());
                layer.close(index); // 关闭弹窗
            },
            btn2: function(index, layero) {
                // 点击"取消"按钮的回调
                layer.close(index); // 关闭弹窗
            },
            cancel: function() {
                // 点击右上角关闭按钮的回调
                // 可留空或添加额外逻辑
            }
        });
    });
});




window.printlabel = function(e, s) {
    layui.use(['layer', 'jquery'], function(){
        var layer = layui.layer;
        var $ = layui.jquery;
        
        /**
 * 打印标签函数（兼容Layui）
 * @param {number} e 标签类型 
 * @param {number|string} s 订单ID
 */
function printlabel(e, s) {
    var data = $(this).data();
    console.log("标签参数:", s, e);
    
    layui.use(['layer', 'jquery'], function(){
        var layer = layui.layer;
        var $ = layui.jquery;
        
        $.ajax({
            url: '<?= url('store/trOrder/expressLabel') ?>',
            type: "GET",
            data: {id: s, label: e},
            success: function(result) {
                // 错误处理
                if(result.code === 0) {
                    layer.alert(result.msg, {icon: 5});
                    return; 
                }
                
                // 特殊类型标签处理（直接下载）
                if(e == 40 && result.code === 1) {
                    console.log("下载标签结果:", result);
                    window.open(result.url, '_blank');
                    return;  
                }
                
                // 正常标签打印预览
                layer.open({
                    type: 1,
                    title: '标签打印预览',
                    area: ['600px', '700px'],
                    content: result,
                    btn: ['打印', '取消'],
                    success: function(layero, index) {
                        console.log("弹窗DOM:", layero);
                    },
                    yes: function(index, layero) {
                        PrintDiv(result);  // 调用打印函数
                        layer.close(index);
                    },
                    btn2: function(index, layero) {
                        layer.close(index);
                    },
                    cancel: function() {
                        // 右上角关闭回调
                    }
                });
            },
            error: function(xhr, status, error) {
                layer.alert('请求失败: ' + error, {icon: 2});
            }
        });
    });
}
    });
};

// 余额抵扣集运（兼容Layui版本）
$('.j-payyue').click(function(e) {
    // 初始化Layui模块
    var data = $(this).data();
   
    layui.use(['layer', 'jquery'], function() {
        var layer = layui.layer;
        var $ = layui.jquery;
        
        // 获取数据
        console.log(data,99)
        var user_id = data['user_id']; // 推荐明确使用data('user_id')
        var id = data['id'];
        
        // 验证用户ID
        if(!user_id) {
            layer.alert('用户信息有误', {icon: 5});
            return false;
        }
         
        // 获取余额和价格信息
        $.ajax({
            url: 'store/tr_Order/balanceAndPrice',
            type: 'POST',
            data: {id: id, user_id: user_id},
            dataType: 'json',
            success: function(result) {
                if(result.code == 1) {
                    // 准备弹窗数据
                    var modalData = {
                        balance: result.data.balance,
                        price: result.data.price,
                        id: id,
                        user_id: user_id,
                        name:data.name
                    };
                    
                    // 使用Layui弹窗替代$.showModal
                    layer.open({
                        type: 1,
                        title: '余额抵扣',
                        area: '460px',
                        content: template('tpl-errors', modalData),
                        btn: ['确认抵扣', '取消'],
                        yes: function(index, layero) {
                            // 执行抵扣操作
                            $.ajax({
                                url: 'store/tr_Order/payyue',
                                type: 'POST',
                                data: {id: id, user_id: user_id},
                                dataType: 'json',
                                success: function(result) {
                                    if(result.code === 1) {
                                        layer.msg(result.msg, {icon: 1}, function() {
                                            if(result.url) {
                                                window.location.href = result.url;
                                            } else {
                                                window.location.reload();
                                            }
                                        });
                                    } else {
                                        layer.msg(result.msg, {icon: 2});
                                    }
                                },
                                error: function() {
                                    layer.msg('请求失败，请重试', {icon: 2});
                                }
                            });
                            layer.close(index);
                        },
                        btn2: function(index, layero) {
                            // 取消按钮回调
                            layer.close(index);
                        },
                        success: function(layero, index) {
                            // 弹窗成功回调
                            console.log('弹窗已打开');
                        }
                    });
                } else {
                    layer.msg(result.msg, {icon: 2});
                }
            },
            error: function() {
                layer.msg('获取余额信息失败', {icon: 2});
            }
        });
    });
});

// 现金抵扣集运（兼容Layui版本）
$('.j-payxianjin').click(function(e) {
    // 初始化Layui模块
    var data = $(this).data();
    layui.use(['layer', 'jquery'], function() {
        var layer = layui.layer;
        var $ = layui.jquery;
        
        // 获取数据（推荐明确使用data()方法）
        
        var user_id = data['user_id'];
        var id = data['id'];
        
        // 验证用户ID
        if(!user_id) {
            layer.alert('用户信息有误', {icon: 5});
            return false;
        }
        
        // 显示加载中
        var loadIndex = layer.load(1, {shade: 0.3});
        
        // 获取金额信息
        $.ajax({
            url: 'store/tr_Order/balanceAndPrice',
            type: 'POST',
            data: {id: id, user_id: user_id},
            dataType: 'json',
            success: function(result) {
                layer.close(loadIndex);
                
                if(result.code == 1) {
                    // 准备弹窗数据
                    var modalData = {
                        balance: result.data.balance,
                        price: result.data.price,
                        id: id,
                        user_id: user_id,
                        name:data.name
                    };
                    
                    // 使用Layui弹窗
                    layer.open({
                        type: 1,
                        title: '现金收款',
                        area: '460px',
                        content: template('tpl-xianjin', modalData),
                        btn: ['确认收款', '取消'],
                        success: function(layero, index) {
                            // 弹窗渲染完成后执行
                            form.render(); // 如果表单中有Layui元素需要渲染
                        },
                        yes: function(index, layero) {
                            // 提交现金收款
                            var submitIndex = layer.load(2, {shade: 0.3});
                            $.ajax({
                                url: 'store/tr_Order/cashforPrice',
                                type: 'POST',
                                data: {id: id, user_id: user_id},
                                dataType: 'json',
                                success: function(result) {
                                    layer.close(submitIndex);
                                    if(result.code === 1) {
                                        layer.msg(result.msg, {
                                            icon: 1,
                                            time: 1500
                                        }, function() {
                                            // 跳转或刷新
                                            if(result.url) {
                                                window.location.href = result.url;
                                            } else {
                                                window.location.reload();
                                            }
                                        });
                                    } else {
                                        layer.msg(result.msg, {icon: 2});
                                    }
                                },
                                error: function() {
                                    layer.close(submitIndex);
                                    layer.msg('请求失败，请重试', {icon: 2});
                                }
                            });
                            layer.close(index);
                        },
                        btn2: function(index, layero) {
                            // 取消按钮回调
                        }
                    });
                } else {
                    layer.msg(result.msg, {icon: 2});
                }
            },
            error: function() {
                layer.close(loadIndex);
                layer.msg('获取金额信息失败', {icon: 2});
            }
        });
    });
});

// 打印账单（兼容Layui版本）
$(".j-freelist").on('click', function() {
    // 初始化Layui模块
    layui.use(['layer', 'jquery'], function() {
        var layer = layui.layer;
        var $ = layui.jquery;
        
        var data = $(this).data();
        
        // 显示加载中
        var loadIndex = layer.load(1, {shade: 0.3});
        
        $.ajax({
            url: '<?= url('store/trOrder/freelistLabel') ?>',
            type: "GET",
            data: {id: data['id']},
            success: function(result) {
                layer.close(loadIndex);
                
                if(result.code === 0) {
                    layer.alert(result.msg, {icon: 5});
                    return;
                }
                
                // 使用Layui弹窗
                layer.open({
                    type: 1,
                    title: '账单打印预览',
                    area: ['600px', '700px'],
                    content: result,
                    btn: ['打印', '取消'],
                    success: function(layero, index) {
                        // 弹窗成功回调
                        console.log('弹窗内容:', layero.find('.layui-layer-content'));
                    },
                    yes: function(index, layero) {
                        // 调用打印功能
                        PrintDiv(result);
                        layer.close(index);
                    },
                    btn2: function(index, layero) {
                        // 取消按钮回调
                        layer.close(index);
                    },
                    cancel: function() {
                        // 右上角关闭回调
                    }
                });
            },
            error: function(xhr, status, error) {
                layer.close(loadIndex);
                layer.alert('请求失败: ' + error, {icon: 2});
            }
        });
    });
});

/**
 * 导出包裹（兼容Layui版本）
 */
$('.j-exportdetail').on('click', function() {
    var data = $(this).data();
    layui.use(['layer', 'jquery'], function() {
        var layer = layui.layer;
        var $ = layui.jquery;
        // 显示加载中
        var loadIndex = layer.load(1, {shade: 0.3});

        $.ajax({
            type: 'POST',
            url: "<?= url('store/trOrder/exportInpackpackage') ?>",
            data: {id: data.id},
            dataType: "json",
            success: function(res) {
                layer.close(loadIndex);
                
                if (res.code == 1 && res.url && res.url.file_name) {
                    // 创建隐藏链接下载文件
                    var a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = res.url.file_name;
                    a.download = '包裹数据_' + new Date().toLocaleDateString() + '.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    
                    setTimeout(function() {
                        document.body.removeChild(a);
                        layer.msg('导出成功', {icon: 1});
                    }, 100);
                } else {
                    layer.msg(res.msg || '导出文件生成失败', {icon: 2});
                }
            },
            error: function(xhr) {
                layer.close(loadIndex);
                var errorMsg = xhr.responseJSON && xhr.responseJSON.msg 
                             ? xhr.responseJSON.msg 
                             : '导出失败，状态码: ' + xhr.status;
                layer.msg(errorMsg, {icon: 2, time: 3000});
            }
        });
    });
});

// 初始化选中数量显示
updateSelectedCount();
  });
  </script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>