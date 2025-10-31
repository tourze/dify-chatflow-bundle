# Dify 对话流包

[English](README.md) | [中文](README.zh-CN.md)

用于 Dify AI 对话流会话、消息传递和会话管理的 Symfony Bundle。

## 功能特性

- **会话管理**：创建、重命名和删除会话
- **消息处理**：发送和接收聊天消息，支持流式传输
- **变量管理**：访问和管理会话变量
- **建议系统**：获取建议的后续问题
- **消息历史**：检索会话消息历史记录
- **停止控制**：能够停止正在进行的消息生成

## 安装

```bash
composer require tourze/dify-chatflow-bundle
```

## 配置

将 bundle 添加到 `config/bundles.php`：

```php
return [
    // ... 其他 bundles
    Tourze\DifyChatflowBundle\DifyChatflowBundle::class => ['all' => true],
];
```

## API 端点

- **发送聊天消息**：向 Dify 对话流应用发送消息
- **获取会话列表**：列出用户会话
- **获取消息**：检索会话消息历史
- **重命名会话**：更新会话名称
- **删除会话**：移除会话
- **获取变量**：访问会话变量
- **获取建议**：获取建议的下一个问题
- **停止消息**：停止正在进行的消息生成

## 使用方法

### 服务使用

```php
// 发送聊天消息
$response = $chatflowService->sendMessage($conversationId, $message, $userId);

// 获取会话列表
$conversations = $chatflowService->getConversations($userId);

// 重命名会话
$chatflowService->renameConversation($conversationId, $newName);
```

### 控制台命令

#### 同步会话命令

将 Dify 应用的会话记录和消息数据同步到本地数据库。

```bash
# 同步所有有效应用
php bin/console dify:sync-conversations

# 同步指定应用 ID
php bin/console dify:sync-conversations <app-id>

# 限制每次请求的会话数量（默认：100）
php bin/console dify:sync-conversations --limit=50

# 试运行模式 - 预览将要同步的数据，不写入数据库
php bin/console dify:sync-conversations --dry-run
```

**命令选项：**
- `app-id`（可选）：要同步的特定 DifyApp ID。如果未提供，则同步所有有效应用
- `--limit|-l`（可选）：每次 API 请求获取的会话数量（默认：100）
- `--dry-run`（可选）：预览模式，显示将要同步的内容而不实际写入数据库

**功能说明：**
- 从 Dify API 获取指定或所有有效应用的会话列表
- 同步会话元数据（名称、状态、创建时间等）
- 下载并存储每个会话的所有消息
- 自动处理大数据集的分页
- 支持增量同步和重复检测

## 系统要求

- PHP 8.1+
- Symfony 7.3+
- tourze/dify-core-bundle

## 许可证

此 bundle 基于 MIT 许可证发布。详细信息请查看 [LICENSE](LICENSE) 文件。