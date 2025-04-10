<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>订单管理系统 - Layui风格</title>
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
<div class="layui-card">
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
        <div class="layui-col-md4">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">订单状态</label>
            <div class="layui-input-block">
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
        
        <div class="layui-col-md4">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">仓库名称</label>
            <div class="layui-input-block">
              <select name="extract_shop_id" lay-search>
                <option value="">全部仓库</option>
                <option value="1">上海仓库</option>
                <option value="2">广州仓库</option>
                <option value="3">北京仓库</option>
              </select>
            </div>
          </div>
        </div>
        
        <div class="layui-col-md4">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">路线名称</label>
            <div class="layui-input-block">
              <select name="line_id" lay-search>
                <option value="">全部路线</option>
                <option value="1">美国专线</option>
                <option value="2">欧洲专线</option>
                <option value="3">日本专线</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      
      <!-- 第二行筛选条件 -->
      <div class="layui-row layui-col-space10">
        <div class="layui-col-md6">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">起始日期</label>
            <div class="layui-input-block">
              <input type="text" name="start_time" class="layui-input" id="start-time" placeholder="请选择起始日期" autocomplete="off">
            </div>
          </div>
        </div>
        
        <div class="layui-col-md6">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">截止日期</label>
            <div class="layui-input-block">
              <input type="text" name="end_time" class="layui-input" id="end-time" placeholder="请选择截止日期" autocomplete="off">
            </div>
          </div>
        </div>
      </div>
      
      <!-- 第三行筛选条件 -->
      <div class="layui-row layui-col-space10">
        <div class="layui-col-md12">
          <div class="layui-form-item">
            <label class="layui-form-label" style="width: 100px;">订单号</label>
            <div class="layui-input-block">
              <input type="text" name="order_sn" class="layui-input" placeholder="请输入平台订单号或转运单号">
            </div>
          </div>
        </div>
      </div>
      
      <!-- 操作按钮 -->
      <div class="layui-form-item">
        <div class="layui-input-block" style="margin-left: 110px;">
          <button class="layui-btn layui-btn-normal" lay-submit lay-filter="search">
            <i class="layui-icon layui-icon-search"></i> 搜索
          </button>
          <button type="reset" class="layui-btn layui-btn-primary">
            <i class="layui-icon layui-icon-refresh"></i> 重置
          </button>
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
  
  // 如果有筛选条件，默认展开
//   if(window.location.search.length > 0) {
//     $('#toggle-filter').click();
//   }
});
</script>
        <?php $status = [1=>'待查验',2=>'待发货',3=>'待发货','4'=>'待发货','5'=>'待发货','6'=>'已发货','7'=>'已到货','8'=>'已完成','-1'=>'问题件']; ?>
        <?php $paytime_status = [ 1=>'已支付',2=>'未支付',3=>'支付待审核'] ; ?>
        <!-- 批量操作工具栏 -->
        <div class="batch-operations">
          <div class="layui-btn-group">
            <button class="layui-btn layui-btn-sm layui-btn-primary" id="check-all">
              <i class="layui-icon layui-icon-ok"></i> 全选
            </button>
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
            <table class="layui-table" lay-size="sm" lay-filter="order-table" id="order-table">
              <thead>
                <tr>
                  <th lay-data="{type:'checkbox', fixed:'left'}"></th>
                  <th lay-data="{field:'order_sn', width:180, title:'系统单号'}">系统单号</th>
                  <th lay-data="{field:'inpack_type', width:80, title:'订单类型'}">订单类型</th>
                  <th lay-data="{field:'status', width:80, title:'状态'}">状态</th>
                  <th lay-data="{field:'member_id', width:180, title:'会员信息'}">会员信息</th>
                  <th lay-data="{field:'name', width:180, title:'渠道'}">渠道</th>
                  <th lay-data="{field:'usermark', width:100, title:'唛头'}">唛头</th>
                  <th lay-data="{field:'trans_info', width:80, title:'总件数'}">总件数</th>
                  <th lay-data="{field:'receive_info', width:60, title:'体积'}">体积</th>
                  <th lay-data="{field:'weight', width:60, title:'实重'}">实重</th>
                  <th lay-data="{field:'weight', width:100, title:'计费重量'}">计费重量</th>
                  <th lay-data="{field:'batch_name', width:100, title:'批次号'}">批次号</th>
                  <th lay-data="{field:'t_name', width:100, title:'承运商'}">承运商</th>
                  <th lay-data="{field:'t_order_sn', width:180, title:'国际单号'}">国际单号</th>
                  <th lay-data="{field:'actions', width:150, title:'操作', fixed:'right'}">操作</th>
                </tr>
              </thead>
              <tbody>
                <tr></tr>
              </tbody>
            </table>
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

table.render({
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
          '<a href="<?= url("store/trOrder/edit") ?>?id='+d.id+'">'+
            '<button class="layui-btn layui-btn-xs layui-btn-normal">编辑</button>'+
          '</a>' : '')+
        (<?= checkPrivilege('tr_order/orderdetail') ? 'true' : 'false' ?> ? 
          '<a href="<?= url("store/trOrder/orderdetail") ?>?id='+d.id+'">'+
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
  $('#selected-count').text('已选' + selectedCount + '项');
  
  var total = table.cache['order-table'].length;
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
    

    
    // 全选/取消全选
    $('#check-all').click(function(){
      var isAllChecked = $(this).hasClass('layui-btn-primary');
      var checkboxes = $('input[name="id"]');
  
      if(isAllChecked){
        // 全选
        checkboxes.prop('checked', true);
        $(this).removeClass('layui-btn-primary').addClass('layui-btn-normal')
          .html('<i class="layui-icon layui-icon-ok"></i> 取消全选');
      } else {
        // 取消全选
        checkboxes.prop('checked', false);
        $(this).removeClass('layui-btn-normal').addClass('layui-btn-primary')
          .html('<i class="layui-icon layui-icon-ok"></i> 全选');
      }
      
      // 更新选中数量
      updateSelectedCount();
      form.render('checkbox');
    });
    
    // 单个复选框选中状态变化时
    $(document).on('change', 'input[name="id"]', function() {
      var totalCheckboxes = $('input[name="id"]').length;
      var checkedCount = $('input[name="id"]:checked').length;
      
      // 更新全选按钮状态
      if(checkedCount === totalCheckboxes) {
        $('#check-all').removeClass('layui-btn-primary').addClass('layui-btn-normal')
          .html('<i class="layui-icon layui-icon-ok"></i> 取消全选');
      } else if(checkedCount === 0) {
        $('#check-all').removeClass('layui-btn-normal').addClass('layui-btn-primary')
          .html('<i class="layui-icon layui-icon-ok"></i> 全选');
      } else {
        $('#check-all').removeClass('layui-btn-primary layui-btn-normal')
          .html('<i class="layui-icon layui-icon-ok"></i> 部分选中');
      }
      
      // 更新选中数量
      updateSelectedCount();
    });
    
    // 搜索表单提交
    form.on('submit(search)', function(data){
      console.log(data.field);
      return false;
    });
    
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
   var selectIds = [];
    $('input[name="id"]:checked').each(function(){
        selectIds.push($(this).val());
    });
    
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
                            table.reload('order-table');
                        });
                    }else{
                        layer.msg(res.msg || '操作失败', {icon: 2});
                    }
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
</body>
</html>