<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 发送对话消息请求
 */
final class ChatMessageRequest extends ApiRequest
{
    public function __construct(
        private readonly string $query,
        private readonly string $user,
        private readonly string $responseMode = 'streaming',
        /** @var array<string, mixed> */
        private readonly array $inputs = [],
        private readonly ?string $conversationId = null,
        /** @var array<mixed> */
        private readonly array $files = [],
        private readonly bool $autoGenerateName = true,
    ) {
    }

    public function getRequestPath(): string
    {
        return '/chat-messages';
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
            'query' => $this->query,
            'user' => $this->user,
            'response_mode' => $this->responseMode,
            'inputs' => $this->inputs,
            'auto_generate_name' => $this->autoGenerateName,
        ];

        // 可选参数
        if (null !== $this->conversationId) {
            $body['conversation_id'] = $this->conversationId;
        }

        if ([] !== $this->files) {
            $body['files'] = $this->files;
        }

        return [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $body,
        ];
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getResponseMode(): string
    {
        return $this->responseMode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }

    public function getConversationId(): ?string
    {
        return $this->conversationId;
    }

    /**
     * @return array<mixed>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function isAutoGenerateName(): bool
    {
        return $this->autoGenerateName;
    }
}
