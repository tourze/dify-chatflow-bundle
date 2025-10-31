<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 获取会话历史消息请求
 */
final class GetMessagesRequest extends ApiRequest
{
    public function __construct(
        private readonly string $conversationId,
        private readonly string $user,
        private readonly ?string $firstId = null,
        private readonly int $limit = 20,
    ) {
    }

    public function getRequestPath(): string
    {
        return '/messages';
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequestOptions(): array
    {
        $query = [
            'conversation_id' => $this->conversationId,
            'user' => $this->user,
            'limit' => $this->limit,
        ];

        // 可选参数
        if (null !== $this->firstId) {
            $query['first_id'] = $this->firstId;
        }

        return [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => $query,
        ];
    }

    public function getConversationId(): string
    {
        return $this->conversationId;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getFirstId(): ?string
    {
        return $this->firstId;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
