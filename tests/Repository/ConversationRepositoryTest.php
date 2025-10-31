<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DifyChatflowBundle\Entity\Conversation;
use Tourze\DifyChatflowBundle\Repository\ConversationRepository;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(ConversationRepository::class)]
#[RunTestsInSeparateProcesses]
final class ConversationRepositoryTest extends AbstractRepositoryTestCase
{
    private ConversationRepository $repository;

    protected function onSetUp(): void
    {
        $repository = self::getContainer()->get(ConversationRepository::class);
        self::assertInstanceOf(ConversationRepository::class, $repository);
        $this->repository = $repository;
    }

    /**
     * @return ServiceEntityRepository<Conversation>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $entity = new Conversation();
        $entity->setDifyConversationId('12345678-1234-1234-1234-' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT));
        $entity->setUser('test-user-' . uniqid());
        $entity->setName('Test Conversation ' . uniqid());
        $entity->setStatus('active');
        $entity->setDifyCreatedAt(time());
        $entity->setDifyUpdatedAt(time());

        // 创建一个真实的 DifyApp 实体用于测试
        $difyApp = $this->createRealDifyApp();
        $entity->setDifyApp($difyApp);

        return $entity;
    }

    public function testFindByDifyConversationId(): void
    {
        $result = $this->repository->findByDifyConversationId('non-existent-id');
        self::assertNull($result);
    }

    public function testFindLatest(): void
    {
        $result = $this->repository->findLatest(10);
        self::assertIsArray($result);
        self::assertContainsOnlyInstancesOf(Conversation::class, $result);
    }

    public function testFindByUser(): void
    {
        $result = $this->repository->findByUser('test-user');
        self::assertIsArray($result);
        self::assertContainsOnlyInstancesOf(Conversation::class, $result);
    }

    public function testFindByDifyApp(): void
    {
        $difyApp = $this->createRealDifyApp();
        $result = $this->repository->findByDifyApp($difyApp);
        self::assertIsArray($result);
        self::assertContainsOnlyInstancesOf(Conversation::class, $result);
    }

    public function testCountByDifyApp(): void
    {
        $difyApp = $this->createRealDifyApp();
        $count = $this->repository->countByDifyApp($difyApp);
        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }

    private function createRealDifyApp(): DifyApp
    {
        $difyApp = new DifyApp();
        $difyApp->setName('Test App ' . uniqid());
        $difyApp->setApiKey('test-api-key-' . uniqid());
        $difyApp->setBaseUrl('https://api.dify.ai');

        // 持久化 DifyApp 实体以避免 cascade persist 错误
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $em->persist($difyApp);
        $em->flush();

        return $difyApp;
    }
}
