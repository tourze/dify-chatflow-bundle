<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 获取下一轮建议问题列表请求
 */
final class GetSuggestedQuestionsRequest extends ApiRequest
{
    public function __construct(
        private readonly string $messageId,
        private readonly string $user,
    ) {
    }

    public function getRequestPath(): string
    {
        return '/messages/' . $this->messageId . '/suggested';
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
            'user' => $this->user,
        ];

        return [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => $query,
        ];
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function getUser(): string
    {
        return $this->user;
    }
}
