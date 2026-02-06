---
title: ThinkPHP 测试脚本规则
description: 从 tests/ 目录运行 ThinkPHP 测试的最佳实践
tags: [thinkphp, testing, path-fix, initialization]
inclusion: manual
---

# ThinkPHP 测试脚本规则

## 核心问题

从 `tests/` 目录运行 ThinkPHP 脚本时会遇到三个问题：
1. **路径问题**: vendor 目录找不到
2. **参数拦截**: ThinkPHP 会把命令行参数当作模块
3. **数据库连接**: MySQL认证错误 `SQLSTATE[HY000] [2054]`

## ⭐ 推荐方案：使用 start.php（最可靠）

**重要**: 这是经过实战验证的最可靠方案！

```php
<?php
/**
 * 测试脚本模板（推荐）
 */

// 1. 定义路径常量（关键：必须定义 VENDOR_PATH）
define('APP_PATH', __DIR__ . '/../../source/application/');
define('ROOT_PATH', __DIR__ . '/../../');
define('VENDOR_PATH', __DIR__ . '/../../source/vendor/');

// 2. 加载框架（使用 start.php）
require __DIR__ . '/../../source/thinkphp/start.php';

// 3. 测试逻辑
echo "测试开始...\n";
// ...
```

**优点**：
- ✅ 自动加载数据库配置
- ✅ 自动初始化所有ThinkPHP组件
- ✅ 避免MySQL认证错误
- ✅ 路径配置简单明了

**关键点**：
- ⚠️ **必须定义 `VENDOR_PATH` 常量**，否则会出现 `vendor\phpmailer not found` 错误
- ⚠️ `VENDOR_PATH` 必须指向 `source/vendor/`，不是项目根目录的 `vendor/`
- ⚠️ `ROOT_PATH` 指向项目根目录，不是 `source/` 目录

**参考示例**: 
- `tests/sf/test_sf_all_features.php` - 顺丰测试（推荐）
- `tests/jd/test_jd_sdk_basic.php` - 京东测试（推荐）
- `tests/jd/test_jd_get_templates.php` - 京东API测试（推荐）

## 备选方案：使用 base.php（需要手动配置）

**重要**: 如果使用 `base.php` 方式，需要手动加载数据库配置。

```php
<?php
/**
 * 测试脚本模板（base.php 方式）
 */

// 1. 定义路径常量
define('WEB_PATH', __DIR__ . '/../../');
define('APP_PATH', WEB_PATH . 'source/application/');

// 2. 加载框架核心（使用 base.php 而不是 start.php）
require APP_PATH . '../thinkphp/base.php';
require APP_PATH . 'common.php';

// 3. 加载数据库配置（必须手动加载）
$dbConfig = include APP_PATH . 'database.php';
\think\Db::setConfig($dbConfig);

// 4. 测试逻辑
echo "测试开始...\n";
// ...
```

**优点**：
- ✅ 不需要处理命令行参数
- ✅ 避免框架路由拦截
- ✅ 可以直接使用 `$argv` 参数

**缺点**：
- ❌ 需要手动加载数据库配置
- ❌ 需要手动加载 `common.php`
- ❌ 配置较复杂

**参考示例**: `tests/sf/test_sf_oauth.php`

## 不推荐方案：使用 start.php 但不定义 VENDOR_PATH（会失败）

**❌ 错误示例 - 会导致 MySQL 认证错误**:

```php
// ❌ 错误：缺少 VENDOR_PATH 定义
define('APP_PATH', __DIR__ . '/../../source/application/');
define('ROOT_PATH', __DIR__ . '/../../');
require __DIR__ . '/../../source/thinkphp/start.php';
```

**错误信息**:
```
SQLSTATE[HY000] [2054] The server requested authentication method unknown to the client
```

**原因**: 
- `common.php` 中使用了 `VENDOR_PATH` 常量加载 PHPMailer
- 如果未定义 `VENDOR_PATH`，会尝试从错误的路径加载
- 导致 vendor 目录找不到，进而引发数据库连接错误

**解决方案**: 必须定义 `VENDOR_PATH` 常量！

```php
// ✅ 正确：定义 VENDOR_PATH
define('APP_PATH', __DIR__ . '/../../source/application/');
define('ROOT_PATH', __DIR__ . '/../../');
define('VENDOR_PATH', __DIR__ . '/../../source/vendor/');  // 必须！
require __DIR__ . '/../../source/thinkphp/start.php';
```

## 路径配置详解

### 正确的路径配置

```php
// ✅ 正确的路径配置
define('APP_PATH', __DIR__ . '/../../source/application/');  // 应用目录
define('ROOT_PATH', __DIR__ . '/../../');                    // 项目根目录
define('VENDOR_PATH', __DIR__ . '/../../source/vendor/');    // vendor 目录（关键！）
```

**说明**:
- `APP_PATH`: 指向 `source/application/` 目录
- `ROOT_PATH`: 指向项目根目录（不是 `source/` 目录）
- `VENDOR_PATH`: 指向 `source/vendor/` 目录（**必须定义**）

### 常见错误路径配置

#### ❌ 错误 1: ROOT_PATH 指向 source 目录
```php
define('ROOT_PATH', __DIR__ . '/../../source/');  // 错误！
```
**结果**: SDK 路径错误，找不到文件

#### ❌ 错误 2: 未定义 VENDOR_PATH
```php
define('APP_PATH', __DIR__ . '/../../source/application/');
define('ROOT_PATH', __DIR__ . '/../../');
// 缺少 VENDOR_PATH 定义
```
**结果**: `vendor\phpmailer not found` → MySQL 认证错误

#### ❌ 错误 3: VENDOR_PATH 指向错误位置
```php
define('VENDOR_PATH', __DIR__ . '/../../vendor/');  // 错误！
```
**结果**: vendor 目录不存在（应该是 `source/vendor/`）

## 参数处理（可选）

### 方法 1: 在框架初始化前保存参数
$testMode = 'default';
if (isset($argv[1])) {
    $testMode = $argv[1];
}

// 然后再 require ThinkPHP
require __DIR__ . '/../../source/thinkphp/start.php';

// 使用保存的参数
$mode = $testMode;
```

### 3. 环境变量（最可靠）

```bash
# PowerShell
$env:TEST_MODE='multibox'; php tests/sf/test.php

# Bash
TEST_MODE=multibox php tests/sf/test.php
```

```php
// PHP 中读取
$mode = getenv('TEST_MODE') ?: 'default';
```

## 完整模板（start.php 方式）

```php
<?php
// 1. 在框架初始化前处理参数
$testMode = 'default';
if (isset($argv[1])) {
    $testMode = $argv[1];
}

// 2. 修复路径
define('APP_PATH', __DIR__ . '/../../source/application/');
define('ROOT_PATH', __DIR__ . '/../../source/');
define('VENDOR_PATH', ROOT_PATH . 'vendor/');

// 3. 初始化 ThinkPHP
require __DIR__ . '/../../source/thinkphp/start.php';

// 4. 使用参数
$mode = $testMode;
if (isset($_GET['mode'])) {
    $mode = $_GET['mode'];
} elseif (getenv('TEST_MODE')) {
    $mode = getenv('TEST_MODE');
}

// 5. 测试逻辑
// ...
```

## 两种方式对比

| 特性 | base.php 方式 | start.php 方式 |
|------|--------------|----------------|
| 路径定义 | 简单 (WEB_PATH, APP_PATH) | 复杂 (APP_PATH, ROOT_PATH, VENDOR_PATH) |
| 参数处理 | 直接使用 `$argv` | 需要在初始化前保存 |
| 路由拦截 | 无 | 有（需要处理） |
| 数据库 | 需要手动加载配置 | 自动加载 |
| 推荐度 | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |

**建议**: 新测试脚本优先使用 `base.php` 方式

## 常见错误

### ❌ 错误 1: 路径未修复
```php
define('ROOT_PATH', __DIR__ . '/../../');  // 错误：指向项目根目录
```
**结果**: `vendor\phpmailer not found`

### ❌ 错误 2: 参数在框架初始化后处理
```php
require __DIR__ . '/../../source/thinkphp/start.php';
$mode = $argv[1];  // 错误：ThinkPHP 已拦截参数
```
**结果**: `module not exists`

### ❌ 错误 3: 使用 cd 命令
```bash
cd tests/sf
php test.php  # 错误：相对路径失效
```

## ✅ 正确做法

```bash
# 始终从项目根目录运行
php tests/sf/test_sf_e2e_cloud_print.php

# 或使用环境变量
$env:TEST_MODE='multibox'; php tests/sf/test_sf_e2e_cloud_print.php
```

## 参考示例

**推荐参考**:
- `tests/sf/test_sf_oauth.php` - base.php 方式（推荐）
- `tests/jd/test_jd_oauth.php` - base.php 方式（推荐）

**备选参考**:
- `tests/sf/test_sf_e2e_cloud_print.php` - start.php 方式（完整的端到端测试）
- `web/test_vendor_path_check.php` - Web 版本路径修复

## 京东物流 AccessToken 获取方式

### 方式1: 使用官方SDK（Jd.php）

```php
use Lop\LopOpensdkPhp\Support\DefaultClient;
use Lop\LopOpensdkPhp\Filters\IsvFilter;

// SDK 自动处理 AccessToken
$isvFilter = new IsvFilter(
    $this->config['app_key'],
    $this->config['app_secret'],
    $this->config['access_token']  // 从配置读取
);
$request->addFilter($isvFilter);
```

**特点**:
- ✅ SDK 自动处理认证细节
- ✅ 支持多种签名算法
- ✅ 代码简洁
- ⚠️ 需要预先配置 access_token

### 方式2: 手动OAuth2.0（JdlAuth.php）

```php
// 手动调用 OAuth2.0 接口
$url = $baseUrl . '/oauth2/accessToken';
$params = [
    'app_key' => $appKey,
    'app_secret' => $appSecret,
    'grant_type' => 'client_credentials',
];

$response = $client->post($url, json_encode($params));
$token = $data['access_token'];
```

**特点**:
- ✅ 完整的 OAuth2.0 流程
- ✅ 支持 Token 缓存
- ✅ 支持 Token 刷新
- ⚠️ 需要在京东开放平台配置域名白名单

**注意事项**:
1. 京东API要求在开放平台注册服务域名白名单
2. 错误 "服务域参数错误：api.jdl.com 未注册" 表示域名未配置
3. 生产环境: `https://api.jdl.com`
4. 沙箱环境: `https://api-sbox.jdl.com`

---

**版本**: v2.0.0  
**Token 数**: ~500  
**最后更新**: 2025-02-04  
**更新内容**: 
- 新增 base.php 推荐方案
- 新增两种方式对比表
- 新增京东物流 AccessToken 获取方式说明
- 优化文档结构
