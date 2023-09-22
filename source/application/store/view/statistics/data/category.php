<link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
<div id="app" v-cloak class="page-statistics-data row-content am-cf">
   <!-- 排行榜 -->
    <div class="row">
        <div class="am-u-sm-6 am-margin-bottom">
            <div class="widget-ranking widget am-cf">
                <div class="widget-head">
                    <div class="widget-title">集运路线榜</div>
                </div>
                <div class="widget-body am-cf">
                    <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black">
                        <thead>
                        <tr>
                            <th class="am-text-center" width="15%">排名</th>
                            <th class="am-text-left" width="45%">路线名称</th>
                            <th class="am-text-center" width="20%">走单量</th>
                            <th class="am-text-center" width="20%">销售额</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, index) in goodsRanking">
                            <td class="am-text-middle am-text-center">
                                <div v-if="index < 3 && item.total_sales_num > 0" class="ranking-img">
                                    <img :src="'assets/store/img/statistics/ranking/0' + (index + 1) + '.png'" alt="">
                                </div>
                                <span v-else>{{ index + 1 }}</span>
                            </td>
                            <td class="am-text-middle">
                                <p class="ranking-item-title am-text-truncate">{{ item.name }}</p>
                            </td>
                            <td class="am-text-middle am-text-center">{{ item.total_sales_num }}</td>
                            <td class="am-text-middle am-text-center">{{ item.sales_volume }}</td>
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
<script src="https://unpkg.com/element-ui/lib/index.js"></script>

<script type="text/javascript">

    new Vue({
        el: '#app',
        data: {

            // 商品销售榜
            goodsRanking: <?= \app\common\library\helper::jsonEncode($goodsRanking) ?>,
            // 用户消费榜
            userExpendRanking: <?= \app\common\library\helper::jsonEncode($userExpendRanking) ?>
        },

        mounted() {
            // 近七日交易走势
            this.drawLine();
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

            /**
             * 近七日交易走势
             * @type {HTMLElement}
             */
            drawLine() {
                var dom = document.getElementById('echarts-trade');
                echarts.init(dom, 'walden').setOption({
                    tooltip: {
                        trigger: 'axis'
                    },
                    legend: {
                        data: ['成交量', '成交额']
                    },
                    toolbox: {
                        show: true,
                        showTitle: false,
                        feature: {
                            mark: {show: true},
                            magicType: {show: true, type: ['line', 'bar']}
                        }
                    },
                    calculable: true,
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: <?= $echarts7days['date'] ?>
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [
                        {
                            name: '成交额',
                            type: 'line',
                            data: <?= $echarts7days['order_total_price'] ?>
                        },
                        {
                            name: '成交量',
                            type: 'line',
                            data: <?= $echarts7days['order_total'] ?>
                        }
                    ]
                }, true);
            }

        }

    });

</script>