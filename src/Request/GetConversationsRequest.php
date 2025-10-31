<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 获取会话列表请求
 */
final class GetConversationsRequest extends ApiRequest
{
    public function __construct(
        private readonly string $user,
        private readonly ?string $lastId = null,
        private readonly int $limit = 20,
        private readonly string $sortBy = '-updated_at',
    ) {
    }

    public function getRequestPath(): string
    {
        return '/conversations';
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
            'limit' => $this->limit,
            'sort_by' => $this->sortBy,
        ];

        // 可选参数
        if (null !== $this->lastId) {
            $query['last_id'] = $this->lastId;
        }

        return [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => $query,
        ];
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getLastId(): ?string
    {
        return $this->lastId;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }
}
