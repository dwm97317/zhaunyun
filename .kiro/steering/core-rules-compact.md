---
title: 核心规则精简版
description: 最重要的规则，总是加载
inclusion: always
tags: [core, essential]
---

# 核心规则精简版

## 🎯 必须遵守的规则

### 1. 语言规则
- 中文输入 → 英文处理 → 中文回复
- 所有的回答必须使用中文，所有的生成的文档，注释必须使用中文

### 2. 数据库规则
- **永远先验证，再编码**
- 使用 MCP MySQL 工具: `SHOW COLUMNS FROM table`
- 不要假设字段名和类型

### 3. 文件组织
- 测试 → `tests/` (PHPUnit 单元测试)
- 临时测试脚本 → `web/` (通过 HTTP 访问)
- 文档 → `docs/`
- 临时文件 → `web/temp/`

**PHPUnit 测试框架**：
- 已安装：PHPUnit 9.6.34
- 配置文件：`source/phpunit.xml`
- 运行测试：`cd source && vendor/bin/phpunit`
- 测试文件命名：`*Test.php`（放在 `tests/` 目录）

### 4. 开发优先
- 专注代码实现
- 不要自动创建文档
- 简洁回复

### 5. 文本搜索规则
- **使用 `grepSearch` 工具代替 grep 命令**
- 搜索文件内容时使用 `grepSearch`，不要使用 bash grep
- `grepSearch` 已针对系统优化，性能更好

### 6. 测试方法规则
- **首选 PHPUnit 命令行（推荐）**: 直接运行 `php think test`
- **备选方案**: 使用 curl 访问测试脚本
  - 项目部署在 `localhost:8080`
  - 测试脚本位于 `web/tests/`
  - **示例**: `curl http://localhost:8080/tests/test_example.php`
- 验证测试结果，直到通过为止

### 7. 测试请求封装（处理权限）
当编写独立测试脚本需要处理 Token/Session 时，使用以下封装方法：

```php
/**
 * 通用测试请求封装 - 处理 Token 和 Session
 * 
 * @param string $url 请求 URL
 * @param array $data 请求数据
 * @param string $token 认证 Token（可选）
 * @param string $sessionId Session ID（可选）
 * @return array 解析后的响应数据
 */
function sendTestRequest($url, $data = [], $token = '', $sessionId = '') {
    $ch = curl_init();
    $headers = ['Content-Type: application/json'];
    
    // 传递 Token
    if ($token) {
        $headers[] = 'token: ' . $token; // 根据项目实际 Header 名称修改
    }
    
    // 传递 Session
    if ($sessionId) {
        curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . $sessionId);
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// 使用示例
$result = sendTestRequest(
    'http://localhost:8080/api/test',
    ['id' => 1],
    'my-secret-token'
);
```

**使用场景**：
- 测试需要认证的 API 接口
- 测试需要登录状态的功能
- 模拟用户请求进行集成测试

### 8. Kiro Powers 自动选用规则
根据用户指令关键词自动激活对应的 Power：

**Postman Power** - API 测试和集合管理
- 关键词: postman, api, testing, collections, rest, http, automation
- 用途: 创建工作区、集合、环境，运行 API 测试

**Terraform Power** - 基础设施即代码
- 关键词: terraform, hashicorp, infrastructure, iac, hcp, providers, modules, registry
- 用途: 管理 Terraform 注册表、提供商、模块、策略

**Power Builder** - 构建自定义 Power
- 关键词: kiro power, power builder, build power, create power, mcp power, power documentation
- 用途: 创建和测试新的 Kiro Powers

**Requirements Analyst** - 需求工程
- 关键词: requirements, PRD, user stories, MoSCoW, FURPS+
- 用途: 6 阶段需求工程流程（发现→分类→分析→澄清→验证→规范）

**Spec-Kit Power** - SDD 方法论
- 关键词: specification, SDD, requirements, planning, implementation, TDD, 功能规格, 需求文档, 技术设计
- 用途: 10 个工作流（Steering, Specify, Clarify, Plan, Tasks, Implement, Analyze, Checklist, Sync, Discover）

**使用方式**:
1. 识别关键词 → 自动激活对应 Power
2. 先 activate 了解能力 → 再 use 执行工具
3. 使用 readSteering 获取详细指南

## 📚 详细规则

需要时加载：
- 数据库详细规则: `database-verification-rules.md`
- 打印系统规则: `multi-channel-print-error-handling.md`
- 完整项目规则: `PROJECT_RULES.md`

---
# 任务执行规则

## 核心原则

**真实可执行 / 可验证 - 零妥协标准**

## 必须遵守的规则

### 1. 不接受功能降级
- ❌ 不允许简化需求
- ❌ 不允许"示意性完成"
- ❌ 不允许"部分实现"
- ✅ 必须完整实现所有功能

### 2. 遇到阻塞时的处理
- ✅ 立即停止执行
- ✅ 明确说明阻塞点
- ❌ 不要给替代方案
- ❌ 不要尝试绕过问题

### 3. 准确性优先
- ✅ 所有输出必须真实可执行
- ✅ 所有输出必须可验证
- ❌ 不允许为了完成对话而牺牲准确性
- ❌ 不允许使用占位符或伪代码

### 4. 无法满足时的处理
- ✅ 直接说明原因
- ❌ 不要提供替代方案
- ❌ 不要尝试"变通"

## 执行标准

### 代码质量
- 必须是生产级代码
- 必须包含完整的错误处理
- 必须包含必要的注释
- 必须遵循现有代码规范

### 测试要求
- 必须编写可运行的测试
- 必须使用真实数据库连接
- 必须验证所有边界条件
- 不允许使用 mock 数据

### 文档要求
- 必须准确反映实际实现
- 必须包含真实的示例
- 必须可以直接使用

## 阻塞情况示例

以下情况必须停止并说明:
1. 缺少必要的 API 密钥或凭证
2. 数据库字段不存在且无法创建
3. 第三方服务不可用
4. 依赖的库或 SDK 不存在
5. 权限不足无法执行操作

## 禁止行为

❌ 使用 "TODO" 或 "待实现" 标记  
❌ 使用占位符数据  
❌ 跳过错误处理  
❌ 简化复杂逻辑  
❌ 省略必要的验证  
❌ 使用假数据通过测试  

## 允许行为

✅ 请求用户提供缺失的信息  
✅ 说明无法继续的原因  
✅ 建议用户采取的准备工作  
✅ 暂停任务等待条件满足  

---

**版本**: v1.2.0  
**生效日期**: 2025-02-05  
**Token 数**: ~250
