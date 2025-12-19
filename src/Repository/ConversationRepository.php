<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\DifyChatflowBundle\Entity\Conversation;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
#[AsRepository(entityClass: Conversation::class)]
final class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * 根据Dify应用查找会话
     *
     * @return Conversation[]
     */
    public function findByDifyApp(DifyApp $difyApp): array
    {
        return $this->findBy(['difyApp' => $difyApp], ['difyUpdatedAt' => 'DESC']);
    }

    /**
     * 根据用户查找会话
     *
     * @return Conversation[]
     */
    public function findByUser(string $user): array
    {
        return $this->findBy(['user' => $user], ['difyUpdatedAt' => 'DESC']);
    }

    /**
     * 根据Dify会话ID查找会话
     */
    public function findByDifyConversationId(string $difyConversationId): ?Conversation
    {
        return $this->findOneBy(['difyConversationId' => $difyConversationId]);
    }

    /**
     * 获取最新的会话列表
     *
     * @return array<Conversation>
     */
    public function findLatest(int $limit = 20): array
    {
        /** @var array<Conversation> $result */
        $result = $this->createQueryBuilder('c')
            ->orderBy('c.difyUpdatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    /**
     * 统计指定应用的会话数量
     */
    public function countByDifyApp(DifyApp $difyApp): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.difyApp = :difyApp')
            ->setParameter('difyApp', $difyApp)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function save(Conversation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Conversation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
