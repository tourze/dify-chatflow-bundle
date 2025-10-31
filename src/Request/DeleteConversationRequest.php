<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 删除会话请求
 */
final class DeleteConversationRequest extends ApiRequest
{
    public function __construct(
        private readonly string $conversationId,
        private readonly string $user,
    ) {
    }

    public function getRequestPath(): string
    {
        return '/conversations/' . $this->conversationId;
    }

    public function getRequestMethod(): string
    {
        return 'DELETE';
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequestOptions(): array
    {
        $body = [
            'user' => $this->user,
        ];

        return [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $body,
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
}
