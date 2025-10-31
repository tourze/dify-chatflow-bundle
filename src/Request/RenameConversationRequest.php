<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 会话重命名请求
 */
final class RenameConversationRequest extends ApiRequest
{
    public function __construct(
        private readonly string $conversationId,
        private readonly string $user,
        private readonly ?string $name = null,
        private readonly bool $autoGenerate = false,
    ) {
    }

    public function getRequestPath(): string
    {
        return '/conversations/' . $this->conversationId . '/name';
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
            'auto_generate' => $this->autoGenerate,
        ];

        // 可选参数
        if (null !== $this->name) {
            $body['name'] = $this->name;
        }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isAutoGenerate(): bool
    {
        return $this->autoGenerate;
    }
}
