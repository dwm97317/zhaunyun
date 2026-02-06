<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title><?= $setting['store']['values']['name'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="icon" type="image/png" href="assets/common/i/favicon.ico"/>
    <link href="assets/common/css/animate.compat.css" rel="stylesheet">
    <meta name="apple-mobile-web-app-title" content="<?= $setting['store']['values']['name'] ?>"/>
    <link rel="stylesheet" href="assets/common/css/amazeui.min.css"/>
    <link rel="stylesheet" href="assets/common/css/amazeui.datetimepicker.css"/>
    
    <!--原始背景-->
    <?php if ($store['wxapp']['system_style']==10): ?>
    <link rel="stylesheet" href="assets/store/css/app.css?v=<?= $version ?>"/>
    <?php endif; ?>
    <!--蓝色背景-->
    <?php if ($store['wxapp']['system_style']==20): ?>
    <link rel="stylesheet" href="assets/store/css/app_blue.css?v=<?= $version ?>"/>
    <?php endif; ?>
    <!--静态图背景-->
    <?php if ($store['wxapp']['system_style']==30): ?>
    <link rel="stylesheet" href="assets/store/css/app_bg1.css?v=<?= $version ?>"/>
    <?php endif; ?>
    <!--数字粒子背景-->
    <?php if ($store['wxapp']['system_style']==40): ?>
    <link rel="stylesheet" href="assets/store/css/app_shu1/app_shu.css?v=<?= $version ?>"/>
    <?php endif; ?>
    <link rel="stylesheet" href="//at.alicdn.com/t/font_783249_m68ye1gfnza.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/c/font_3768063_yu9jg4vyhs.css">
    <script src="assets/common/js/jquery.min.js"></script>
    <script src="assets/common/js/base.js"></script>
    <script src="assets/common/js/amazeui.datetimepicker.min.js"></script>
    <script src="//at.alicdn.com/t/font_783249_e5yrsf08rap.js"></script>
    <script>
        BASE_URL = '<?= isset($base_url) ? $base_url : '' ?>';
        STORE_URL = '<?= isset($store_url) ? $store_url : '' ?>';
    </script>
</head>

<body data-type="">
<div class="am-g tpl-g">
    <!-- 头部 -->
    <header class="tpl-header">
        <!-- 右侧内容 -->
        <div class="tpl-header-fluid">
            <!-- 侧边切换 -->
            <div class="am-fl tpl-header-button switch-button">
                <i class="iconfont icon-menufold"></i>
            </div>
            <!-- 刷新页面 -->
            <div class="am-fl tpl-header-button refresh-button">
                <i class="iconfont icon-refresh"></i>
            </div>
            <!-- 其它功能-->
            <div class="am-fr tpl-header-navbar">
                <ul>
                    <?php if (isset($payment_audit_count)): ?>
                    <li  class="am-text-sm tpl-header-navbar-welcome">
                        <a href="<?= url('tr_order/payment_audit') ?>" style="color: <?= $payment_audit_count > 0 ? '#ff0000' : '#929292' ?>;    font-weight: 900;">
                            <span >线下支付审核 (<?= $payment_audit_count ?>)</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (isset($certificate_count)): ?>
                    <li  class="am-text-sm tpl-header-navbar-welcome">
                        <a href="<?= url('setting.certificate/index') ?>" style="color: <?= $certificate_count > 0 ? '#ff0000' : '#929292' ?>;    font-weight: 900;">
                            <span >汇款凭证审核 (<?= $certificate_count ?>)</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="am-text-sm tpl-header-navbar-welcome">
                        <a href="<?= url('tools/guide') ?>"><span>使用指南</span></a>
                    </li>
                    <?php if (isset($count)): ?>
                    <li style="margin-top:10px" class="am-text-sm tpl-header-navbar-welcome">
                        <div class="am-dropdown" data-am-dropdown>
                              <button class="am-btn <?= $count>0?"am-btn-danger":"am-btn-success" ?> am-dropdown-toggle" data-am-dropdown-toggle>待处理 <span class="am-icon-caret-down"></span></button>
                              <ul class="am-dropdown-content">
                                <li class="am-dropdown-header"><a style="line-height:20px;" href="index.php?s=/store/tr_order/exceedorder" >超时订单 <span class="tipsspan"> <?= $count ?> </span></a></li>
                                <!--<li class="am-dropdown-header"><a style="line-height:20px;" href="###" >待发货订单 <span class="tipsspan">1</span></a></li>-->
                                <!--<li class="am-dropdown-header"><a style="line-height:20px;" href="###" >待发货订单 <span class="tipsspan">1</span></a></li>-->
                                <!--<li class="am-dropdown-header"><a style="line-height:20px;" href="###" >问题包裹 <span class="tipsspan">1</span></a></li>-->
                              </ul>
                            </div>
                    </li>
                    <?php endif; ?>
                    <!-- 欢迎语 -->
                    <li class="am-text-sm tpl-header-navbar-welcome">
                        <a href="javascript:;">有限期：<span><?= $store['wxapp']['end_time'] ?></span>
                        </a>
                    </li>
                    <!-- 欢迎语 -->
                    <li class="am-text-sm tpl-header-navbar-welcome">
                        <a href="<?= url('store.user/renew') ?>">欢迎你，<span><?= $store['user']['user_name'] ?></span>
                        </a>
                    </li>
                    <!-- 退出 -->
                    <li class="am-text-sm">
                        <a href="<?= url('passport/logout') ?>">
                            <i class="iconfont icon-tuichu"></i> 退出
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <!-- 侧边导航栏 -->
    <div class="left-sidebar dis-flex" id="particles-js">
            <?php if ($store['wxapp']['system_style']==40): ?>
        <canvas class="particles-js-canvas-el" style="width: 100%; height: 100%;" width="472" height="625"></canvas>
            <?php endif; ?>
        <?php $menus = $menus ?: []; ?>
        <?php $group = $group ?: 0; ?>
        <div class="sidebar-scroll ">
            <!-- 一级菜单 -->
            <ul class="sidebar-nav">
                <li class="sidebar-nav-heading"><?= $setting['store']['values']['name'] ?></li>
                <?php foreach ($menus as $key => $item): ?>
                    <li class="sidebar-nav-link">
                        <a href="<?= isset($item['index']) ? url($item['index']) : 'javascript:void(0);' ?>"
                           class="<?= $item['active'] ? 'active' : '' ?>">
                            <?php if (isset($item['is_svg']) && $item['is_svg'] == true): ?>
                                <svg class="icon sidebar-nav-link-logo" aria-hidden="true">
                                    <use xlink:href="#<?= $item['icon'] ?>"></use>
                                </svg>
                            <?php else: ?>
                                <i class="iconfont sidebar-nav-link-logo <?= $item['icon'] ?>"
                                   style="<?= isset($item['color']) ? "color:{$item['color']};" : '' ?>"></i>
                            <?php endif; ?>
                            <?= $item['name'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <!-- 子级菜单-->
        <?php $second = isset($menus[$group]['submenu']) ? $menus[$group]['submenu'] : []; ?>
        <?php if (!empty($second)) : ?>
            <div class="sidebar-second-scroll">
                <ul class="left-sidebar-second">
                    <li class="sidebar-second-title"><?= $menus[$group]['name'] ?></li>
                    <li class="sidebar-second-item">
                        <?php foreach ($second as $item) : ?>
                            <?php if (!isset($item['submenu'])): ?>
                                <!-- 二级菜单-->
                                <a href="<?= url($item['index']) ?>"
                                   class="<?= (isset($item['active']) && $item['active']) ? 'active' : '' ?>"
                                   <?= (isset($item['open_new_tab']) && $item['open_new_tab']) ? 'target="_blank"' : '' ?>>
                                    <?= $item['name']; ?>
                                </a>
                            <?php else: ?>
                                <!-- 三级菜单-->
                                <div class="sidebar-third-item">
                                    <a href="javascript:void(0);"
                                       class="sidebar-nav-sub-title <?= $item['active'] ? 'active' : '' ?>">
                                        <i class="iconfont icon-caret"></i>
                                        <?= $item['name']; ?>
                                    </a>
                                    <ul class="sidebar-third-nav-sub">
                                        <?php foreach ($item['submenu'] as $third) : ?>
                                            <li>
                                                <a class="<?= $third['active'] ? 'active' : '' ?>"
                                                   href="<?= url($third['index']) ?>"
                                                   <?= (isset($third['open_new_tab']) && $third['open_new_tab']) ? 'target="_blank"' : '' ?>>
                                                    <?= $third['name']; ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- 内容区域 start -->
    <div class="tpl-content-wrapper <?= empty($second) ? 'no-sidebar-second' : '' ?>">
        {__CONTENT__}
    </div>
    <!-- 内容区域 end -->
    
    <!-- 订单提醒弹窗 -->
    <div id="inpack-notify-popup" style="display:none; position: fixed; top: 0; left: 0; right: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 15px 20px; z-index: 99999; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; flex: 1;">
                <i class="iconfont icon-lingdang" style="font-size: 24px; margin-right: 15px; animation: bell-ring 0.5s ease-in-out infinite;"></i>
                <div>
                    <div style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">你有新集运订单需要处理</div>
                    <div style="font-size: 14px; opacity: 0.9;" id="notify-order-sn">订单号：<span id="notify-order-sn-value"></span></div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="javascript:;" id="notify-goto-order" style="color: #fff; text-decoration: none; padding: 8px 20px; background: rgba(255,255,255,0.2); border-radius: 4px; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">查看订单</a>
                <a href="javascript:;" id="notify-close" style="color: #fff; text-decoration: none; font-size: 20px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: rgba(255,255,255,0.2); transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">&times;</a>
            </div>
        </div>
    </div>
    
    <style>
        @keyframes bell-ring {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-15deg); }
            75% { transform: rotate(15deg); }
        }
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        #inpack-notify-popup.show {
            animation: slideDown 0.3s ease-out;
        }
    </style>

</div>
<script src="assets/common/plugins/layer/layer.js"></script>
<script src="assets/common/js/jquery.form.min.js"></script>
<script src="assets/common/js/amazeui.min.js"></script>
<script src="assets/common/js/webuploader.html5only.js"></script>
<script src="assets/common/js/art-template.js"></script>
<script src="assets/store/js/app.js?v=<?= $version ?>"></script>
<script src="assets/store/js/file.library.js?v=<?= $version ?>"></script>
    <?php if ($store['wxapp']['system_style']==40): ?>
    <script src="assets/store/css/app_shu1/jiyunbg.js"></script>
    <script src="assets/store/css/app_shu1/jiyunApp.js"></script>
    <?php endif; ?>
<script>
// 菜单链接打开方式：根据配置文件中的 open_new_tab 参数决定
// 如果菜单项设置了 target="_blank"，则在新标签页打开，否则在当前页打开
$(document).ready(function() {
    // 一级菜单：在当前页面跳转
    $('.sidebar-nav a').on('click', function(e) {
        var href = $(this).attr('href');
        // 如果不是 javascript:void(0) 这样的伪链接
        if (href && href !== 'javascript:void(0);' && href.indexOf('javascript:') !== 0) {
            e.preventDefault();
            e.stopPropagation();
            window.location.href = href; // 当前页面跳转
        }
    });
    
    // 注意：二级、三级菜单现在由 HTML 的 target 属性控制
    // 如果菜单配置中有 'open_new_tab' => true，会自动添加 target="_blank"
});

// 订单提醒功能
(function() {
    'use strict';
    
    // 配置
    var POLL_INTERVAL = 30000; // 30秒轮询间隔
    var STORAGE_KEY = 'inpack_notify_last_check';
    var AUDIO_PATH = 'assets/store/order.MP3';
    
    // 音频对象（延迟加载，等待用户交互）
    var audio = null;
    var audioInitialized = false;
    
    // 初始化音频对象
    function initAudio() {
        if (audioInitialized || audio) return;
        
        try {
            audio = new Audio(AUDIO_PATH);
            audio.preload = 'auto';
            audio.volume = 0.8;
            
            // 处理音频加载错误
            audio.addEventListener('error', function(e) {
                console.warn('订单提醒音频加载失败:', e);
                audio = null;
            });
            
            audioInitialized = true;
        } catch (e) {
            console.warn('初始化音频对象失败:', e);
        }
    }
    
    // 播放提醒音频
    function playNotifySound() {
        if (!audio) {
            initAudio();
        }
        
        if (audio && audioInitialized) {
            try {
                // 重置音频到开始位置
                audio.currentTime = 0;
                var playPromise = audio.play();
                
                // 处理播放错误
                if (playPromise !== undefined) {
                    playPromise.catch(function(error) {
                        console.warn('音频播放失败（可能是浏览器自动播放限制）:', error);
                        // 如果自动播放失败，尝试在用户交互后播放
                        var onceInteraction = function() {
                            if (audio) {
                                audio.play().catch(function() {});
                            }
                            document.removeEventListener('click', onceInteraction);
                            document.removeEventListener('touchstart', onceInteraction);
                            document.removeEventListener('keydown', onceInteraction);
                        };
                        document.addEventListener('click', onceInteraction, { once: true });
                        document.addEventListener('touchstart', onceInteraction, { once: true });
                        document.addEventListener('keydown', onceInteraction, { once: true });
                    });
                }
            } catch (e) {
                console.warn('播放音频时出错:', e);
            }
        }
    }
    
    // 显示顶部通知
    function showNotifyPopup(orderSn, orderId) {
        var $popup = $('#inpack-notify-popup');
        var $orderSnValue = $('#notify-order-sn-value');
        
        if ($orderSnValue.length) {
            $orderSnValue.text(orderSn || '');
        }
        
        // 设置跳转链接
        var $gotoLink = $('#notify-goto-order');
        if ($gotoLink.length && orderId) {
            var orderUrl = BASE_URL + 'index.php?s=/store/tr_order/edit&id=' + orderId;
            $gotoLink.attr('href', orderUrl);
        }
        
        // 显示弹窗
        $popup.fadeIn(300).addClass('show');
        
        // 自动关闭（5秒后）
        setTimeout(function() {
            hideNotifyPopup();
        }, 5000);
    }
    
    // 隐藏顶部通知
    function hideNotifyPopup() {
        var $popup = $('#inpack-notify-popup');
        $popup.fadeOut(300, function() {
            $(this).removeClass('show');
        });
    }
    
    // 检查是否有新订单
    function checkNewInpack() {
        // 检查设置是否启用（从后端获取，暂时默认启用）
        // 这里可以通过AJAX获取设置，或者从页面变量中获取
        // 为了简化，我们默认启用，如果需要可以在后端返回设置值
        
        // 只在页面可见时检查
        if (document.hidden) {
            return;
        }
        
        $.ajax({
            url: BASE_URL + 'index.php?s=/store/package.Index/getLatestInpackTime',
            type: 'GET',
            dataType: 'json',
            timeout: 10000,
            success: function(res) {
                if (res.code == 1 && res.data) {
                    // 检查是否启用提醒
                    var notifyEnabled = res.data.notify_enabled !== false; // 默认为true
                    
                    if (!notifyEnabled) {
                        // 如果未启用，直接返回，不进行后续检查
                        return;
                    }
                    
                    var latestTime = res.data.latest_time || 0;
                    var latestId = res.data.latest_id || 0;
                    var latestOrderSn = res.data.latest_order_sn || '';
                    
                    if (latestTime > 0 && latestId > 0) {
                        // 获取本地存储的最后检查信息
                        var lastCheck = localStorage.getItem(STORAGE_KEY);
                        var lastCheckData = lastCheck ? JSON.parse(lastCheck) : { time: 0, id: 0 };
                        
                        // 检查是否有新订单
                        if (latestTime > lastCheckData.time && latestId != lastCheckData.id) {
                            // 有新订单，播放提醒
                            playNotifySound();
                            showNotifyPopup(latestOrderSn, latestId);
                            
                            // 更新本地存储
                            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                                time: latestTime,
                                id: latestId,
                                orderSn: latestOrderSn
                            }));
                        } else if (latestTime > lastCheckData.time) {
                            // 时间更新了但ID相同，只更新时间
                            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                                time: latestTime,
                                id: latestId,
                                orderSn: latestOrderSn
                            }));
                        }
                    }
                }
            },
            error: function(xhr, status, error) {
                // 静默失败，不显示错误
                console.warn('检查新订单失败:', status, error);
            }
        });
    }
    
    // 初始化
    $(document).ready(function() {
        // 用户交互后初始化音频（解决浏览器自动播放限制）
        var initOnInteraction = function() {
            initAudio();
            // 只初始化一次
            $(document).off('click touchstart keydown', initOnInteraction);
        };
        $(document).on('click touchstart keydown', initOnInteraction);
        
        // 关闭通知按钮事件
        $('#notify-close').on('click', function() {
            hideNotifyPopup();
        });
        
        // 点击通知区域外部也可关闭（可选）
        $('#inpack-notify-popup').on('click', function(e) {
            if ($(e.target).closest('#notify-goto-order, #notify-close').length === 0) {
                // 点击通知内容区域，跳转到订单
                var href = $('#notify-goto-order').attr('href');
                if (href && href !== 'javascript:;') {
                    window.location.href = href;
                }
            }
        });
        
        // 首次检查（延迟2秒，避免页面加载时立即请求）
        setTimeout(checkNewInpack, 2000);
        
        // 设置定时轮询
        setInterval(checkNewInpack, POLL_INTERVAL);
        
        // 页面可见性变化时检查
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // 页面变为可见时立即检查一次
                setTimeout(checkNewInpack, 1000);
            }
        });
    });
})();
</script>
</body>

</html>
