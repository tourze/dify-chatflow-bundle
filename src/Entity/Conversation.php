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
 * 会话实体
 *
 * 存储从Dify API获取的会话信息
 */
#[ORM\Entity]
#[ORM\Table(name: 'dify_conversations', options: ['comment' => 'Dify会话表'])]
class Conversation implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['comment' => 'Dify会话ID（UUID格式）'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 36, max: 36)]
    #[Assert\Uuid]
    private string $difyConversationId;

    #[ORM\ManyToOne(targetEntity: DifyApp::class)]
    #[ORM\JoinColumn(name: 'dify_app_id', nullable: false)]
    #[Assert\NotNull(message: 'DifyApp cannot be null.')]
    #[Assert\Valid]
    private ?DifyApp $difyApp = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '会话名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    private string $name;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '用户输入参数（JSON格式）'])]
    #[Assert\Type(type: 'array', message: 'Inputs must be an array.')]
    private ?array $inputs = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '会话状态'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 50)]
    private string $status;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '开场白'])]
    #[Assert\Length(max: 65535, maxMessage: 'Introduction cannot be longer than {{ limit }} characters.')]
    private ?string $introduction = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '用户标识'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 100)]
    private string $user;

    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'Dify创建时间（时间戳）'])]
    #[Assert\NotBlank(message: 'Dify created at cannot be blank.')]
    #[Assert\Type(type: 'int', message: 'Dify created at must be an integer.')]
    #[Assert\PositiveOrZero(message: 'Dify created at must be positive or zero.')]
    private int $difyCreatedAt;

    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'Dify更新时间（时间戳）'])]
    #[Assert\NotBlank(message: 'Dify updated at cannot be blank.')]
    #[Assert\Type(type: 'int', message: 'Dify updated at must be an integer.')]
    #[Assert\PositiveOrZero(message: 'Dify updated at must be positive or zero.')]
    private int $difyUpdatedAt;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(?string $introduction): void
    {
        $this->introduction = $introduction;
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

    public function getDifyUpdatedAt(): int
    {
        return $this->difyUpdatedAt;
    }

    public function setDifyUpdatedAt(int $difyUpdatedAt): void
    {
        $this->difyUpdatedAt = $difyUpdatedAt;
    }

    public function __toString(): string
    {
        return sprintf('Conversation[%s]: %s', $this->difyConversationId ?? 'new', $this->name ?? 'unnamed');
    }
}
