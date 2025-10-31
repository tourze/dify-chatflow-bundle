<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 获取对话变量请求
 */
final class GetConversationVariablesRequest extends ApiRequest
{
    public function __construct(
        private readonly string $conversationId,
        private readonly string $user,
        private readonly ?string $lastId = null,
        private readonly int $limit = 20,
        private readonly ?string $variableName = null,
    ) {
    }

    public function getRequestPath(): string
    {
        return '/conversations/' . $this->conversationId . '/variables';
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
        ];

        // 可选参数
        if (null !== $this->lastId) {
            $query['last_id'] = $this->lastId;
        }

        if (null !== $this->variableName) {
            $query['variable_name'] = $this->variableName;
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

    public function getLastId(): ?string
    {
        return $this->lastId;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getVariableName(): ?string
    {
        return $this->variableName;
    }
}
