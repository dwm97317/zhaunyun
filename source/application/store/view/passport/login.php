<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <title>集运管理系统登录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="icon" type="image/png" href="assets/common/i/favicon.ico"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/store/css/bootstrap.css">
    <style>
        :root {
            --primary-color: #3a86ff;
            --secondary-color: #ffbe0b;
            --text-dark: #101828;
            --text-muted: #667085;
            --border-color: rgba(255, 255, 255, 0.25);
            --glass-bg: rgba(255, 255, 255, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', 'Microsoft YaHei', sans-serif;
            color: #f2f4f8;
            min-height: 100vh;
            background: linear-gradient(135deg, #0b1f3a 0%, #1d3e6c 50%, #0f5f7f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            position: relative;
            overflow: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: absolute;
            width: 620px;
            height: 620px;
            background: radial-gradient(circle, rgba(58, 134, 255, 0.25) 0%, rgba(14, 43, 90, 0) 70%);
            filter: blur(6px);
            z-index: 0;
        }

        body::before {
            top: -180px;
            left: -120px;
        }

        body::after {
            bottom: -220px;
            right: -160px;
            background: radial-gradient(circle, rgba(255, 190, 11, 0.18) 0%, rgba(14, 43, 90, 0) 70%);
        }

        .login-wrapper {
            width: 100%;
            max-width: 1080px;
            background: rgba(12, 28, 54, 0.65);
            border-radius: 26px;
            padding: 48px;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 32px;
            position: relative;
            box-shadow: 0 28px 60px rgba(5, 13, 32, 0.45);
            backdrop-filter: blur(12px);
        }

        @media (max-width: 992px) {
            .login-wrapper {
                padding: 32px;
                grid-template-columns: repeat(12, 1fr);
                backdrop-filter: blur(10px);
            }
        }

        @media (max-width: 880px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                padding: 32px 24px;
            }
        }

        .login-visual {
            grid-column: span 6;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 12px 0;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 16px;
            border-radius: 999px;
            background: rgba(58, 134, 255, 0.18);
            color: rgba(255, 255, 255, 0.85);
            letter-spacing: 0.25em;
            font-size: 13px;
            margin-bottom: 32px;
            text-transform: uppercase;
        }

        .brand-title {
            font-size: 46px;
            font-weight: 600;
            line-height: 1.15;
            margin: 0;
        }

        .brand-desc {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.7;
            margin: 18px 0 32px;
        }

        .brand-highlights {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 20px;
            padding: 0;
            list-style: none;
            margin: 0 0 40px;
        }

        .brand-highlights li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 14px;
        }

        .brand-highlights li span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(58, 134, 255, 0.2);
            color: #fff;
            font-weight: 500;
            flex-shrink: 0;
        }

        .illustration {
            align-self: flex-end;
            max-width: 420px;
            width: 100%;
            filter: drop-shadow(0 26px 48px rgba(10, 28, 58, 0.55));
        }

        .login-panel {
            grid-column: span 6;
            background: var(--glass-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px 36px;
            display: flex;
            flex-direction: column;
            gap: 28px;
            backdrop-filter: blur(18px);
        }

        @media (max-width: 880px) {
            .login-visual {
                grid-column: span 1;
                text-align: center;
                align-items: center;
            }

            .brand-title {
                font-size: 36px;
            }

            .brand-highlights {
                grid-template-columns: 1fr;
                text-align: left;
            }

            .illustration {
                max-width: 320px;
                margin: 0 auto;
            }

            .login-panel {
                grid-column: span 1;
                padding: 32px 24px;
            }
        }

        .login-panel header {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .login-panel header h2 {
            margin: 0;
            font-size: 30px;
            font-weight: 600;
            color: #fff;
        }

        .login-panel header p {
            margin: 0;
            font-size: 15px;
            color: rgba(255, 255, 255, 0.72);
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .input-field {
            position: relative;
        }

        .input-field label {
            position: absolute;
            top: -9px;
            left: 18px;
            padding: 0 8px;
            font-size: 12px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: rgba(12, 28, 54, 0.65);
            color: rgba(255, 255, 255, 0.58);
        }

        .input-field input {
            width: 100%;
            padding: 18px 20px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(7, 21, 46, 0.45);
            color: #f7fbff;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .input-field input:focus {
            outline: none;
            border-color: rgba(58, 134, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(58, 134, 255, 0.18);
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.65);
            cursor: pointer;
            font-size: 14px;
            padding: 4px 6px;
            transition: color 0.25s ease;
        }

        .toggle-password:hover {
            color: #fff;
        }

        .login-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.68);
        }

        .login-actions a {
            color: rgba(255, 255, 255, 0.78);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .login-actions a:hover {
            color: #fff;
        }

        .login-button {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary-color), #5c67ff);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.04em;
            cursor: pointer;
            box-shadow: 0 18px 32px rgba(31, 74, 165, 0.4);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .login-button:disabled {
            background: linear-gradient(135deg, rgba(58, 134, 255, 0.6), rgba(92, 103, 255, 0.6));
            cursor: not-allowed;
            box-shadow: none;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 36px rgba(31, 74, 165, 0.48);
        }

        .login-meta {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.62);
            text-align: center;
            line-height: 1.6;
        }

        .login-meta strong {
            color: rgba(255, 255, 255, 0.78);
        }

        .page-footer {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.62);
            letter-spacing: 0.02em;
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-footer a,
        .page-footer span {
            color: rgba(255, 255, 255, 0.62);
            text-decoration: none;
        }

        .page-footer a:hover {
            color: rgba(255, 255, 255, 0.85);
        }

        .checkbox {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox input {
            width: 16px;
            height: 16px;
            accent-color: var(--primary-color);
        }

        .checkbox span {
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 540px) {
            body {
                padding: 24px 16px;
            }

            .login-wrapper {
                padding: 24px 18px;
            }

            .login-panel header h2 {
                font-size: 26px;
            }
        }

        .sidebar-service {
            position: fixed;
            right: 28px;
            bottom: 28px;
            z-index: 999;
            font-family: 'Poppins', 'Microsoft YaHei', sans-serif;
        }

        .sidebar-main {
            position: relative;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 18px 32px rgba(72, 99, 190, 0.36);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }

        .sidebar-main svg {
            width: 30px;
            height: 30px;
        }

        .sidebar-main::after {
            content: '客服';
            position: absolute;
            bottom: -24px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(102, 126, 234, 0.92);
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            letter-spacing: 0.08em;
            opacity: 0;
            transition: opacity 0.2s ease, bottom 0.2s ease;
            pointer-events: none;
        }

        .sidebar-main:hover {
            transform: scale(1.08);
            box-shadow: 0 22px 40px rgba(72, 99, 190, 0.46);
        }

        .sidebar-main:hover::after {
            opacity: 1;
            bottom: -28px;
        }

        .sidebar-service.is-expanded .sidebar-main {
            transform: rotate(45deg);
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .sidebar-items {
            position: absolute;
            bottom: 72px;
            right: 0;
            display: grid;
            gap: 12px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px);
            transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s;
        }

        .sidebar-service.is-expanded .sidebar-items {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .sidebar-item {
            position: relative;
        }

        .sidebar-action {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.35);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .sidebar-action svg {
            width: 26px;
            height: 26px;
        }

        .sidebar-action[data-type="mini"] {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .sidebar-action[data-type="wechat"] {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .sidebar-action[data-type="phone"] {
            background: linear-gradient(135deg, #f97316, #ea580c);
        }

        .sidebar-action:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 32px rgba(15, 23, 42, 0.4);
        }

        .sidebar-tooltip {
            position: absolute;
            right: 64px;
            top: 50%;
            transform: translateY(-50%) scale(0.96);
            transform-origin: center right;
            background: #fff;
            color: #0b1f3a;
            border-radius: 16px;
            box-shadow: 0 24px 56px rgba(7, 16, 34, 0.38);
            padding: 18px 20px;
            width: 240px;
            border: 1px solid rgba(15, 31, 58, 0.08);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.22s ease, transform 0.22s ease, visibility 0.22s;
        }

        .sidebar-item.is-hovered .sidebar-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(-50%) scale(1);
        }

        .sidebar-tooltip::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -8px;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-left: 8px solid #fff;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
        }

        .tooltip-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .tooltip-header span {
            font-size: 14px;
            font-weight: 600;
            color: #101828;
        }

        .tooltip-section + .tooltip-section {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed rgba(15, 31, 58, 0.08);
        }

        .tooltip-qr {
            width: 164px;
            height: 164px;
            background: linear-gradient(135deg, #eef2ff, #fff);
            border-radius: 14px;
            border: 3px solid rgba(59, 130, 246, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            overflow: hidden;
        }

        .tooltip-qr.is-portrait {
            height: 232px;
        }

        .tooltip-qr img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .tooltip-meta {
            display: grid;
            gap: 6px;
            font-size: 13px;
            color: #334155;
        }

        .tooltip-meta strong {
            color: #0b1f3a;
        }

        .tooltip-meta a {
            color: #2563eb;
            text-decoration: none;
        }

        .tooltip-meta a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .sidebar-service {
                right: 18px;
                bottom: 18px;
            }

            .sidebar-main {
                width: 54px;
                height: 54px;
            }

            .sidebar-action {
                width: 48px;
                height: 48px;
            }

            .sidebar-tooltip {
                width: 210px;
            }
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <section class="login-visual">
        <span class="brand-badge">思创社</span>
        <div>
            <h1 class="brand-title">集运管理系统</h1>
            <p class="brand-desc">以数据驱动跨境物流，用极简体验完成复杂业务流程。安全、稳定、实时，助您掌握每一次出货动态。</p>
            <ul class="brand-highlights">
                <li><span>01</span>多仓库协同管理，关键节点即时反馈</li>
                <li><span>02</span>智能统计面板，洞察趋势与业绩表现</li>
                <li><span>03</span>全程SSL加密传输，保障账户安全</li>
                <li><span>04</span>适配多终端，随时随地高效处理订单</li>
            </ul>
        </div>
        <img src="assets/store/img/wuliu.png" alt="物流插画" class="illustration">
    </section>
    <section class="login-panel">
        <header>
            <h2>欢迎回来</h2>
            <p>输入您的账号与密码，开始管理今日的集运任务。</p>
        </header>
        <form id="login-form" class="login-form">
            <div class="input-field">
                <label for="username">用户名</label>
                <input id="username" type="text" name="User[user_name]" placeholder="请输入用户名" autocomplete="username" required>
            </div>
            <div class="input-field">
                <label for="password">密码</label>
                <input id="password" type="password" name="User[password]" placeholder="请输入登录密码" autocomplete="current-password" required>
                <button type="button" class="toggle-password" data-target="#password">显示</button>
            </div>
            <div class="login-actions">
                <label class="checkbox">
                    <input type="checkbox" name="remember" value="1">
                    <span>下次自动登录</span>
                </label>
                <a href="javascript:void(0);">忘记密码？</a>
            </div>
            <button id="btn-submit" class="login-button" type="submit">登录</button>
        </form>
        <div class="login-meta">
            <div>首次使用？请联系系统管理员开通账号。</div>
            <div>© <strong>2017-<?= date('Y') ?></strong> 思楼传媒 版权所有</div>
        </div>
    </section>
</div>
<footer class="page-footer">
    <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener">鄂ICP备17019709号</a>
    <span>|</span>
    <a href="tel:18086328550">客服热线 18086328550</a>
</footer>
<div class="sidebar-service" id="sidebarService">
    <button class="sidebar-main" type="button" aria-label="打开客服面板">
        <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="currentColor" d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448c247.4 0 448-200.6 448-448S759.4 64 512 64zm0 64c212.1 0 384 171.9 384 384 0 59.2-13.7 115.1-38.2 165.1-45.1-20.7-94.5-32.3-146.7-32.3-60.7 0-117.8 15.7-167.5 43.3l-2.7 1.5c-10.1 5.9-22.2 5.9-32.3 0l-2.7-1.5c-49.7-27.7-106.8-43.3-167.5-43.3-52.2 0-101.6 11.6-146.7 32.3C141.7 659.1 128 603.2 128 512c0-212.1 171.9-384 384-384zm0 512a96 96 0 1 0 0-192 96 96 0 0 0 0 192z"/></svg>
    </button>
    <div class="sidebar-items">
        <div class="sidebar-item" data-tooltip="mini">
            <button class="sidebar-action" data-type="mini" type="button" aria-label="微信公众号">
        <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M864 160H160c-26.51 0-48 21.49-48 48v480c0 26.51 21.49 48 48 48h182.4L512 896l169.6-160H864c26.51 0 48-21.49 48-48V208c0-26.51-21.49-48-48-48zm-48 480H208V256h608v384z"/><path fill="currentColor" d="M352 352h320v64H352zm0 160h224v64H352z"/></svg>
            </button>
            <div class="sidebar-tooltip">
                <div class="tooltip-header">
                    <span>微信公众号</span>
                </div>
                <div class="tooltip-section">
                    <div class="tooltip-qr">
                        <img src="https://logistics-xiaosi.oss-accelerate.aliyuncs.com/20251108100004f2c1d8266.jpg" alt="微信公众号二维码" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="display:none;color:#94a3b8;font-size:12px;text-align:center;padding:16px;">
                            暂无微信公众号二维码<br>请联系管理员上传
                        </div>
                    </div>
                </div>
                <div class="tooltip-section tooltip-meta">
                    <span>使用微信扫一扫进入微信公众号，随时掌控系统更新动态。</span>
                </div>
            </div>
        </div>
        <div class="sidebar-item" data-tooltip="wechat">
            <button class="sidebar-action" data-type="wechat" type="button" aria-label="微信客服">
                <svg viewBox="0 0 1280 1024" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M858.149 309.753c14.579 0 28.921 1.1 43.343 2.671C862.665 133.155 669.403 0 448.725 0 202.057 0 0 166.621 0 378.334 0 500.491 67.194 600.81 179.554 678.739l-44.848 133.784L291.596 734.593c56.101 10.92 101.108 22.233 157.129 22.233 14.104 0 28.05-.629 41.837-1.65-8.716-29.773-13.866-60.882-13.866-93.326 0-194.352 168.302-352.096 381.373-352.096zM616.948 189.167c33.914 0 56.18 22.075 56.18 55.541 0 33.308-22.266 55.618-56.18 55.618-33.518 0-67.273-22.31-67.273-55.618 0-33.544 33.676-55.541 67.273-55.541zM302.927 300.326c-33.676 0-67.59-22.31-67.59-55.618 0-33.465 33.914-55.541 67.59-55.541 33.676 0 56.021 21.996 56.021 55.541 0 33.308-22.345 55.618-56.021 55.618zM1267.81 656.507c0-177.855-179.553-322.794-381.214-322.794-213.546 0-381.61 145.018-381.61 322.794 0 178.326 168.143 322.794 381.61 322.794 44.69 0 89.777-11.077 134.625-22.232l123.057 66.931-33.755-111.238c90.094-67.088 157.288-155.937 157.288-256.255zM762.825 600.81c-22.266 0-44.849-22.31-44.849-44.464 0-22.153 22.583-44.463 44.849-44.463 34.072 0 56.18 22.31 56.18 44.463 0 22.467-22.107 44.464-56.18 44.464zm246.827 0c-22.108 0-44.612-22.31-44.612-44.464 0-22.153 22.424-44.463 44.612-44.463 33.755 0 56.18 22.31 56.18 44.463 0 22.467-22.424 44.464-56.18 44.464z"/></svg>
            </button>
            <div class="sidebar-tooltip">
                <div class="tooltip-header">
                    <span>微信客服</span>
                </div>
                <div class="tooltip-section">
                    <div class="tooltip-qr is-portrait">
                        <img src="https://logistics-xiaosi.oss-accelerate.aliyuncs.com/202511131019074687c4479.png" alt="客服微信二维码" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="display:none;color:#94a3b8;font-size:12px;text-align:center;padding:16px;">
                            暂无客服二维码<br>请联系管理员上传
                        </div>
                    </div>
                </div>
                <div class="tooltip-section tooltip-meta">
                    <span>扫描二维码添加客服，在线时间：09:00 - 21:00。</span>
                    <span>快速沟通，解答账号与订单问题。</span>
                </div>
            </div>
        </div>
        <div class="sidebar-item" data-tooltip="phone">
            <button class="sidebar-action" data-type="phone" type="button" aria-label="电话客服">
                <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M886 839.3c-4.2-7.7-52.6-82-60.4-91.8-2.8-3.6-6.8-5.6-11.6-5.6-8.2 0-18.3 0-29.9.2-83.9 0-187.2-55.3-263.4-131.5C444.9 455.5 397 360 408 299c4.4-23.8 21.4-55.4 37.3-76.3 3.5-4.6 5-10 3.9-15.7-2.7-13.4-53.4-122.7-61.4-139.2-4.2-8.9-13-14.4-22.6-14.4-2.3 0-4.6.3-7 .8-35.7 8.3-65 30.1-84.4 49.5C196.2 181 134 262.3 134 388.4c0 179.4 128.7 373.2 237.5 482 108.8 108.8 302.4 237.6 482 237.6 126.1 0 207.4-62.2 284.7-181.8 19.4-29.4 41.1-58.7 49.5-94.4 2-8.6-.9-17.5-7.5-23-19.2-16.5-126.7-64.6-140.2-61.5-5.6 1.3-11 3.7-15.6 7.3-20.9 15.9-52.4 32.9-76.3 37.3-8.4 1.6-14.2 8.1-16 17.1-5 25-17.1 48.6-39.3 70.7-18 18-54.5 30.8-76.3 33.4-4.2.6-8 2.6-10.9 5.7z"/></svg>
            </button>
            <div class="sidebar-tooltip">
                <div class="tooltip-header">
                    <span>电话客服</span>
                </div>
                <div class="tooltip-section tooltip-meta">
                    <strong>热线：</strong>
                    <a href="tel:18086328550">180-8632-8550</a>
                </div>
                <div class="tooltip-section tooltip-meta">
                    <span>服务时间：周一至周五 09:00 - 18:00</span>
                    <span>紧急事项请直拨热线或联系微信客服</span>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script src="assets/common/js/jquery.min.js"></script>
<script src="assets/common/plugins/layer/layer.js?v=<?= $version ?>"></script>
<script src="assets/common/js/jquery.form.min.js"></script>
<script>
    $(function () {
        var $passwordToggle = $('.toggle-password');
        $passwordToggle.on('click', function () {
            var $this = $(this);
            var target = $this.data('target');
            var $input = $(target);
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $this.text('隐藏');
            } else {
                $input.attr('type', 'password');
                $this.text('显示');
            }
        });

        // 表单提交
        var $form = $('#login-form');
        $form.submit(function () {
            var $btn_submit = $('#btn-submit');
            $btn_submit.attr("disabled", true);
            $form.ajaxSubmit({
                type: "post",
                dataType: "json",
                success: function (result) {
                    $btn_submit.attr('disabled', false);
                    if (result.code === 1) {
                        layer.msg(result.msg, {time: 1500, anim: 1}, function () {
                            window.location = result.url;
                        });
                        return true;
                    }
                    layer.msg(result.msg, {time: 1500, anim: 6});
                }
            });
            return false;
        });
    });

    (function () {
        var sidebar = document.getElementById('sidebarService');
        if (!sidebar) {
            return;
        }
        var mainButton = sidebar.querySelector('.sidebar-main');
        var items = sidebar.querySelectorAll('.sidebar-item');

        mainButton.addEventListener('click', function () {
            sidebar.classList.toggle('is-expanded');
        });

        var hideTimer;

        function clearHideTimer() {
            if (hideTimer) {
                clearTimeout(hideTimer);
                hideTimer = null;
            }
        }

        function scheduleHide() {
            clearHideTimer();
            hideTimer = setTimeout(function () {
                sidebar.classList.remove('is-expanded');
                items.forEach(function (item) {
                    item.classList.remove('is-hovered');
                });
            }, 450);
        }

        sidebar.addEventListener('mouseenter', clearHideTimer);
        sidebar.addEventListener('mouseleave', scheduleHide);

        items.forEach(function (item) {
            var action = item.querySelector('.sidebar-action');
            if (!action) {
                return;
            }
            action.addEventListener('mouseenter', function () {
                clearHideTimer();
                item.classList.add('is-hovered');
            });
            action.addEventListener('mouseleave', function () {
                item.classList.remove('is-hovered');
                scheduleHide();
            });

            var tooltip = item.querySelector('.sidebar-tooltip');
            if (tooltip) {
                tooltip.addEventListener('mouseenter', function () {
                    clearHideTimer();
                    item.classList.add('is-hovered');
                });
                tooltip.addEventListener('mouseleave', function () {
                    item.classList.remove('is-hovered');
                    scheduleHide();
                });
            }
        });

        document.addEventListener('click', function (event) {
            if (!sidebar.contains(event.target)) {
                sidebar.classList.remove('is-expanded');
                items.forEach(function (item) {
                    item.classList.remove('is-hovered');
                });
            }
        });
    })();
</script>
</html>
