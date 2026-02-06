<link rel="stylesheet" href="assets/store/css/index.css">
<div id="app" v-cloak class="page-statistics-data row-content am-cf">
   <!-- 排行榜 -->
    <div class="row">
        <div class="am-u-sm-12 am-margin-bottom">
            <div class="widget-ranking widget am-cf">
                <div class="widget-head">
                    <div class="widget-title">集运订单统计</div>
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
                                           value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期"
                                           data-am-datepicker>
                                </div>
                                <div class="am-form-group tpl-form-border-form am-fl">
                                    <input type="text" name="end_time"
                                           class="am-form-field"
                                           autocomplete="off"
                                           value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期"
                                           data-am-datepicker>
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
                            <th class="am-text-center" width="15%">月份</th>
                            <th class="am-text-left" width="15%">付款方式</th>
                            <th class="am-text-center" width="10%">总营收</th>
                            <th class="am-text-center" width="10%">已付款总额</th>
                            <th class="am-text-center" width="10%">下单客户数</th>
                            <th class="am-text-center" width="20%">订单总数</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, index) in ditchRanking">
                            <td class="am-text-middle am-text-center">
                                <span>{{ item.mouth }}</span>
                            </td>
                            <td class="am-text-middle">
                                <p class="ranking-item-title am-text-truncate">{{ item.pay_type }}</p>
                            </td>
                            <td class="am-text-middle am-text-center">{{ item.totalprice }}</td>
                            <td class="am-text-middle am-text-center">{{ item.haspay }}</td>
                            <td class="am-text-middle am-text-center">{{ item.customnum }}</td>
                            <td class="am-text-middle am-text-center">{{ item.ordernum }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/common/js/echarts.min.js"></script>
<script src="assets/common/js/echarts-walden.js"></script>
<script src="assets/common/js/vue.min.js?v=1.1.35"></script>
<script src="assets/store/js/index.js"></script>
<script id="tpl-wuliu" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
            
                <div class="widget-body am-cf">
                    <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black">
                        <thead>
                        <tr>
                            <th class="am-text-left" width="15%">渠道</th>
                            <th class="am-text-center" width="10%">渠道总订单</th>
                            <th class="am-text-center" width="10%">超时订单量</th>
                            <th class="am-text-center" width="10%">渠道超时率</th>
                            <th class="am-text-center" width="20%">订单总额</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{each data value}}
                        <tr>
                            <td class="am-text-middle">
                                <p class="ranking-item-title am-text-truncate">{{ value.name }}</p>
                            </td>
                            <td class="am-text-middle am-text-center">{{ value.total_order }}</td>
                            <td class="am-text-middle am-text-center">{{ value.exceed }}</td>
                            <td class="am-text-middle am-text-center">{{ value.exced_ratio }}</td>
                            <td class="am-text-middle am-text-center">{{ value.total_free }}</td>

                        </tr>
                        {{/each}} 
                        </tbody>
                    </table>
                </div>
              
            </div>
        </form>
    </div>
</script>
<script>
    function linedata(_this){
        var ditch_id = _this.getAttribute('data-id');
        $.ajax({
			type: 'post',
			url: "<?= url('store/tr_order/getlinedata') ?>",
			data: {ditch_id: ditch_id},
			dataType: "json",
			success: function(res) {
				if (res.code == 1) {
				    console.log(res.data,87);
        				$.showModal({
                         title: '渠道下的路线数据情况'
                        , area: '800px'
                        , content: template('tpl-wuliu', res.data)
                        , uCheck: false
                        , success: function (index) {
                            
                        }
                        ,yes: function (index) {
                            window.location.reload();
                        }
                    });
				}
			}
		})
    } 
</script>
<script type="text/javascript">
    
    new Vue({
        el: '#app',
        data: {
            ditchRanking: <?= \app\common\library\helper::jsonEncode($ditchRanking) ?>,
        },

        mounted() {

        },

        methods: {

            // 监听事件：日期选择快捷导航
            onFastDate: function (days) {
                var startDate, endDate;
                // 清空日期
                if (days === 0) {
                    this.survey.dateValue = [];
                } else {
                    startDate = $.getDay(-days);
                    endDate = $.getDay(0);
                    this.survey.dateValue = [startDate, endDate];
                }
                // api: 获取数据概况
                this.__getApiData__survey(startDate, endDate);
            },

            // 监听事件：日期选择框改变
            onChangeDate: function (e) {
                // api: 获取数据概况
                this.__getApiData__survey(e[0], e[1]);
            },

            // 获取数据概况
            __getApiData__survey: function (startDate, endDate) {
                var app = this;
                // 请求api数据
                app.survey.loading = true;
                // api地址
                var url = '<?= url('statistics.data/survey') ?>';
                $.post(url, {
                    startDate: startDate,
                    endDate: endDate
                }, function (result) {
                    app.survey.values = result.data;
                    app.survey.loading = false;
                });
            },
        }

    });

</script>