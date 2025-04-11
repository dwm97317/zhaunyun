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
    .search-form .layui-form-item {
      margin-bottom: 15px;
    }
    .layui-table-tool {
      background-color: #fff;
    }
    .layui-table-view .layui-table td, .layui-table-view .layui-table th {
      padding: 9px 15px;
    }
    .status-badge {
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
<script>
layui.use(['form', 'laydate', 'jquery'], function(){
  var form = layui.form;
  var laydate = layui.laydate;
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


});
</script>
        <?php $status = [1=>'待查验',2=>'待发货',3=>'待发货','4'=>'待发货','5'=>'待发货','6'=>'已发货','7'=>'已到货','8'=>'已完成','-1'=>'问题件']; ?>
        <?php $paytime_status = [ 1=>'已支付',2=>'未支付',3=>'支付待审核'] ; ?>
        <!-- 批量操作工具栏 -->
        <div class="batch-operations">
          <div class="layui-btn-group">
            <button class="layui-btn layui-btn-sm" id="change-user">
              <i class="layui-icon layui-icon-user"></i> 修改用户
            </button>
            <button class="layui-btn layui-btn-sm" id="j-upstatus">
              <i class="layui-icon layui-icon-form"></i> 状态变更
            </button>
            <button class="layui-btn layui-btn-sm layui-btn-warm" id="merge-order">
              <i class="layui-icon layui-icon-link"></i> 合并订单
            </button>
            <button class="layui-btn layui-btn-sm layui-btn-danger" id="update-logistics">
              <i class="layui-icon layui-icon-location"></i> 更新物流
            </button>
            <button class="layui-btn layui-btn-sm" id="print-label">
              <i class="layui-icon layui-icon-print"></i> 打印面单
            </button>
            <button class="layui-btn layui-btn-sm layui-btn-warm" id="join-group">
              <i class="layui-icon layui-icon-group"></i> 加入拼团
            </button>
            <button class="layui-btn layui-btn-sm" id="join-batch">
              <i class="layui-icon layui-icon-list"></i> 加入批次
            </button>
            <button class="layui-btn layui-btn-sm layui-btn-primary" id="batch-export">
              <i class="layui-icon layui-icon-export"></i> 导出
            </button>
            <span class="selected-count" id="selected-count">已选0项</span>
          </div>
          
          <div class="layui-form layui-form-pane" style="display: inline-block; margin-left: 15px;">
            <div class="layui-form-item" style="margin-bottom: 0;">
              <div class="layui-inline">
                <label class="layui-form-label">快捷筛选</label>
                <div class="layui-input-inline">
                  <select name="quick-filter">
                    <option value="">全部状态</option>
                    <option value="1">待查验</option>
                    <option value="2">待支付</option>
                    <option value="3">已支付</option>
                    <option value="6">已发货</option>
                  </select>
                </div>
              </div>
              <div class="layui-inline">
                <label class="layui-form-label">导出</label>
                <div class="layui-input-inline">
                  <select name="export-option">
                    <option value="">选择类型</option>
                    <option value="1">订单数据</option>
                    <option value="2">分成清单</option>
                    <option value="3">清关模板</option>
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
layui.use(['form', 'table', 'laydate', 'layer', 'laypage', 'jquery'], function(){
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
    {field: 'order_sn', width: 180, title: '系统单号'},
    {field: 'inpack_type', width: 80, title: '订单类型', templet: function(d){
      var html = '';
      if(d.inpack_type == 1) html += '<span class="am-badge am-badge-secondary">拼团订单</span>';
      if(d.inpack_type == 2) html += '<span class="am-badge am-badge-secondary">直邮订单</span>';
      if(d.inpack_type == 3) html += '<span class="am-badge am-badge-success">拼邮订单</span>';
      if(d.is_exceed == 1) html += '<span class="am-badge am-badge-danger">超时订单</span>';
      return html;
    }},
    {field: 'status', width: 80, title: '状态', templet: function(d){
      return statusText[d.status] || '';
    }},
    {field: 'user', width: 180, title: '会员信息', templet: function(d){
      return d.user?d.user.nickName:'' ;
    }},
    {field: 'line', width: 180, title: '渠道', templet: function(d){
      return d.line?d.line.name:'';
    }},
    {field: 'usermark', width: 100, title: '唛头'},
    {field: 'packageitems', width: 80, title: '总件数', templet: function(d){
      return d.packageitems.length;
    }},
    {field: 'volume', width: 60, title: '体积'},
    {field: 'weight', width: 60, title: '实重'},
    {field: 'cale_weight', width: 100, title: '计费重量'},
    {field: 'batch', width: 100, title: '批次号', templet: function(d){
      return d.batch?d.batch.batch_name:'';
    }},
    {field: 't_name', width: 100, title: '承运商'},
    {field: 't_order_sn', width: 180, title: '国际单号'},
    {field: 'actions', width: 150, title: '操作', fixed: 'right', templet: function(d){
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
    // 表格渲染完成后初始化选中数量
    updateSelectedCount();
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
// 初始化选中数量显示
updateSelectedCount();
  });
  </script>