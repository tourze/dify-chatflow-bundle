<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

/**
 * 会话消息实体
 *
 * 存储从Dify API获取的会话消息信息
 */
#[ORM\Entity]
#[ORM\Table(name: 'dify_conversation_messages', options: ['comment' => 'Dify会话消息表'])]
class ConversationMessage implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['comment' => 'Dify消息ID（UUID格式）'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 36, max: 36)]
    #[Assert\Uuid]
    private string $difyMessageId;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 36, options: ['comment' => 'Dify会话ID（UUID格式）'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 36, max: 36)]
    #[Assert\Uuid]
    private string $difyConversationId;

    #[ORM\ManyToOne(targetEntity: DifyApp::class)]
    #[ORM\JoinColumn(name: 'dify_app_id', nullable: false)]
    #[Assert\NotNull(message: 'DifyApp cannot be null.')]
    #[Assert\Valid]
    private ?DifyApp $difyApp = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '用户输入参数（JSON格式）'])]
    #[Assert\Type(type: 'array', message: 'Inputs must be an array.')]
    private ?array $inputs = null;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '用户输入/提问内容'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 65535)]
    private string $query;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '回答消息内容'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 65535)]
    private string $answer;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '反馈评分：like/dislike'])]
    #[Assert\Length(max: 20, maxMessage: 'Feedback rating cannot be longer than {{ limit }} characters.')]
    #[Assert\Choice(choices: ['like', 'dislike'], message: 'The feedback rating must be either "like" or "dislike".')]
    private ?string $feedbackRating = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '消息文件列表（JSON格式）'])]
    #[Assert\Type(type: 'array', message: 'Message files must be an array.')]
    private ?array $messageFiles = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '检索资源列表（JSON格式）'])]
    #[Assert\Type(type: 'array', message: 'Retriever resources must be an array.')]
    private ?array $retrieverResources = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '用户标识'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 100)]
    private string $user;

    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'Dify创建时间（时间戳）'])]
    #[Assert\NotBlank(message: 'Dify created at cannot be blank.')]
    #[Assert\Type(type: 'int', message: 'Dify created at must be an integer.')]
    #[Assert\PositiveOrZero(message: 'Dify created at must be positive or zero.')]
    private int $difyCreatedAt;

    public function getDifyMessageId(): string
    {
        return $this->difyMessageId;
    }

    public function setDifyMessageId(string $difyMessageId): void
    {
        $this->difyMessageId = $difyMessageId;
    }

    public function getDifyConversationId(): string
    {
        return $this->difyConversationId;
    }

    public function setDifyConversationId(string $difyConversationId): void
    {
        $this->difyConversationId = $difyConversationId;
    }

    public function getDifyApp(): ?DifyApp
    {
        return $this->difyApp;
    }

    public function setDifyApp(?DifyApp $difyApp): void
    {
        $this->difyApp = $difyApp;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getInputs(): ?array
    {
        /** @var array<string, mixed>|null */
        return $this->inputs;
    }

    /**
     * @param array<string, mixed>|null $inputs
     */
    public function setInputs(?array $inputs): void
    {
        $this->inputs = $inputs;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    public function getFeedbackRating(): ?string
    {
        return $this->feedbackRating;
    }

    public function setFeedbackRating(?string $feedbackRating): void
    {
        $this->feedbackRating = $feedbackRating;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMessageFiles(): ?array
    {
        /** @var array<string, mixed>|null */
        return $this->messageFiles;
    }

    /**
     * @param array<string, mixed>|null $messageFiles
     */
    public function setMessageFiles(?array $messageFiles): void
    {
        $this->messageFiles = $messageFiles;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRetrieverResources(): ?array
    {
        /** @var array<string, mixed>|null */
        return $this->retrieverResources;
    }

    /**
     * @param array<string, mixed>|null $retrieverResources
     */
    public function setRetrieverResources(?array $retrieverResources): void
    {
        $this->retrieverResources = $retrieverResources;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    public function getDifyCreatedAt(): int
    {
        return $this->difyCreatedAt;
    }

    public function setDifyCreatedAt(int $difyCreatedAt): void
    {
        $this->difyCreatedAt = $difyCreatedAt;
    }

    public function __toString(): string
    {
        return sprintf('Message[%s]: %s', $this->difyMessageId ?? 'new', substr($this->query ?? 'no query', 0, 50));
    }
}
