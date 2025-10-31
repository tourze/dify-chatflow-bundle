<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\DifyChatflowBundle\Entity\ConversationMessage;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<ConversationMessage>
 */
#[AsRepository(entityClass: ConversationMessage::class)]
class ConversationMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationMessage::class);
    }

    /**
     * 根据会话ID查找消息
     *
     * @return ConversationMessage[]
     */
    public function findByConversationId(string $difyConversationId): array
    {
        return $this->findBy(['difyConversationId' => $difyConversationId], ['difyCreatedAt' => 'ASC']);
    }

    /**
     * 根据Dify应用查找消息
     *
     * @return ConversationMessage[]
     */
    public function findByDifyApp(DifyApp $difyApp): array
    {
        return $this->findBy(['difyApp' => $difyApp], ['difyCreatedAt' => 'DESC']);
    }

    /**
     * 根据用户查找消息
     *
     * @return ConversationMessage[]
     */
    public function findByUser(string $user): array
    {
        return $this->findBy(['user' => $user], ['difyCreatedAt' => 'DESC']);
    }

    /**
     * 根据Dify消息ID查找消息
     */
    public function findByDifyMessageId(string $difyMessageId): ?ConversationMessage
    {
        return $this->findOneBy(['difyMessageId' => $difyMessageId]);
    }

    /**
     * 获取最新的消息列表
     *
     * @return array<ConversationMessage>
     */
    public function findLatest(int $limit = 50): array
    {
        /** @var array<ConversationMessage> $result */
        $result = $this->createQueryBuilder('m')
            ->orderBy('m.difyCreatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    /**
     * 统计指定会话的消息数量
     */
    public function countByConversationId(string $difyConversationId): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.difyConversationId = :conversationId')
            ->setParameter('conversationId', $difyConversationId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 获取有反馈的消息
     *
     * @return array<ConversationMessage>
     */
    public function findWithFeedback(): array
    {
        /** @var array<ConversationMessage> $result */
        $result = $this->createQueryBuilder('m')
            ->where('m.feedbackRating IS NOT NULL')
            ->orderBy('m.difyCreatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    public function save(ConversationMessage $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ConversationMessage $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
