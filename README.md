# Dify Chatflow Bundle

[English](README.md) | [中文](README.zh-CN.md)

Symfony Bundle for Dify AI chatflow conversations, messaging, and conversation management.

## Features

- **Conversation Management**: Create, rename, and delete conversations
- **Message Handling**: Send and receive chat messages with streaming support
- **Variable Management**: Access and manage conversation variables
- **Suggestion System**: Get suggested follow-up questions
- **Message History**: Retrieve conversation message history
- **Stop Control**: Ability to stop ongoing message generation

## Installation

```bash
composer require tourze/dify-chatflow-bundle
```

## Configuration

Add the bundle to your `config/bundles.php`:

```php
return [
    // ... other bundles
    Tourze\DifyChatflowBundle\DifyChatflowBundle::class => ['all' => true],
];
```

## API Endpoints

- **Send Chat Message**: Send messages to Dify chatflow applications
- **Get Conversations**: List user conversations
- **Get Messages**: Retrieve conversation message history
- **Rename Conversation**: Update conversation names
- **Delete Conversation**: Remove conversations
- **Get Variables**: Access conversation variables
- **Get Suggestions**: Get suggested next questions
- **Stop Message**: Halt ongoing message generation

## Usage

### Service Usage

```php
// Send a chat message
$response = $chatflowService->sendMessage($conversationId, $message, $userId);

// Get conversation list
$conversations = $chatflowService->getConversations($userId);

// Rename a conversation
$chatflowService->renameConversation($conversationId, $newName);
```

### Console Commands

#### Sync Conversations Command

Synchronize Dify application conversation records and message data to local database.

```bash
# Sync all valid applications
php bin/console dify:sync-conversations

# Sync specific application by ID
php bin/console dify:sync-conversations <app-id>

# Limit the number of conversations per request (default: 100)
php bin/console dify:sync-conversations --limit=50

# Dry run mode - preview what will be synced without writing to database
php bin/console dify:sync-conversations --dry-run
```

**Command Options:**
- `app-id` (optional): Specific DifyApp ID to sync. If not provided, syncs all valid applications
- `--limit|-l` (optional): Number of conversations to fetch per API request (default: 100)
- `--dry-run` (optional): Preview mode that shows what would be synced without actually writing to database

**What it does:**
- Fetches conversation lists from Dify API for specified or all valid applications
- Synchronizes conversation metadata (name, status, creation time, etc.)
- Downloads and stores all messages for each conversation
- Handles pagination automatically for large datasets
- Supports incremental sync and duplicate detection

## Requirements

- PHP 8.1+
- Symfony 7.3+
- tourze/dify-core-bundle

## License

This bundle is released under the MIT license. See the [LICENSE](LICENSE) file for details.