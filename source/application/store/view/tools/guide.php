<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统初始化指南</title>

</head>
<body>
    <div class="row-content am-cf">
        <div class="row">
            <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
                <div class="widget am-cf">
                    <div class="widget-body">
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">系统初始化指南</div>
                        </div>
                        <div class="link-list">
                            <div data-am-widget="accordion" class="am-accordion am-accordion-gapped" data-am-accordion='{ "multiple": false }'>
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第一步：新建仓库
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            仓库管理：<a target="_blank" href="<?= url('/store/shop/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第二步：创建支持的国家或地区
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            国家支持：<a target="_blank" href="<?= url('/store/setting.country/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第三步：创建运输方式
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            运输方式：<a target="_blank" href="<?= url('/setting.line_category/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第四步：创建支持类目
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            类目管理：<a target="_blank" href="<?= url('/setting.category/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第五步：集运线路
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            集运线路：<a target="_blank" href="<?= url('/setting.line/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第六步：打包服务
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            打包服务：<a target="_blank" href="<?= url('/setting.package/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第七步：增值服务
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            增值服务：<a target="_blank" href="<?= url('/setting.addservice/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第八步：物流公司
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            物流公司：<a target="_blank" href="<?= url('/store/setting.express/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第九步：渠道商管理
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            渠道商管理：<a target="_blank" href="<?= url('/store/setting.ditch/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                                
                                <dl class="am-accordion-item">
                                    <dt class="am-accordion-title">
                                        第十步：保险服务
                                    </dt>
                                    <dd class="am-accordion-bd am-collapse">
                                        <div class="am-accordion-content">
                                            保险服务：<a target="_blank" href="<?= url('/store/setting.insure/index') ?>">点击前往</a>
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">功能说明</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            // AmazeUI 会自动初始化折叠面板
            // 如果需要自定义行为，可以这样写：
            
            // 确保所有面板初始状态为折叠
            $('.am-accordion-bd').removeClass('am-in');
            
            // 添加点击事件处理（可选）
            $('.am-accordion-title').click(function() {
                // 可以在这里添加自定义逻辑
                console.log('点击了折叠面板标题');
            });
        });
    </script>
</body>
</html> 