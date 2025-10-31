<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 停止响应请求
 */
final class StopChatMessageRequest extends ApiRequest
{
    public function __construct(
        private readonly string $taskId,
        private readonly string $user,
    ) {
    }

    public function getRequestPath(): string
    {
        return '/chat-messages/' . $this->taskId . '/stop';
    }

    public function getRequestMethod(): string
    {
        return 'POST';
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

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function getUser(): string
    {
        return $this->user;
    }
}
