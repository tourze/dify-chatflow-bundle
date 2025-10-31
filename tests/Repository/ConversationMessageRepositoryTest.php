<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DifyChatflowBundle\Entity\ConversationMessage;
use Tourze\DifyChatflowBundle\Repository\ConversationMessageRepository;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(ConversationMessageRepository::class)]
#[RunTestsInSeparateProcesses]
final class ConversationMessageRepositoryTest extends AbstractRepositoryTestCase
{
    private ConversationMessageRepository $repository;

    protected function onSetUp(): void
    {
        $repository = self::getContainer()->get(ConversationMessageRepository::class);
        self::assertInstanceOf(ConversationMessageRepository::class, $repository);
        $this->repository = $repository;
    }

    /**
     * @return ServiceEntityRepository<ConversationMessage>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $entity = new ConversationMessage();
        $entity->setDifyConversationId('12345678-1234-1234-1234-' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT));
        $entity->setDifyMessageId('87654321-1234-1234-1234-' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT));
        $entity->setQuery('Test query content');
        $entity->setAnswer('Test answer content');
        $entity->setUser('test-user-' . uniqid());
        $entity->setDifyCreatedAt(time());

        // 创建一个真实的 DifyApp 实体用于测试
        $difyApp = $this->createRealDifyApp();
        $entity->setDifyApp($difyApp);

        return $entity;
    }

    public function testFindByConversationId(): void
    {
        $result = $this->repository->findByConversationId('non-existent-id');
        self::assertIsArray($result);
        self::assertContainsOnlyInstancesOf(ConversationMessage::class, $result);
    }

    public function testFindByDifyMessageId(): void
    {
        $result = $this->repository->findByDifyMessageId('non-existent-id');
        self::assertNull($result);
    }

    public function testFindLatest(): void
    {
        $result = $this->repository->findLatest(10);
        self::assertIsArray($result);
        self::assertContainsOnlyInstancesOf(ConversationMessage::class, $result);
    }

    public function testCountByConversationId(): void
    {
        $count = $this->repository->countByConversationId('test-conversation-id');
        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testFindWithFeedback(): void
    {
        $result = $this->repository->findWithFeedback();
        self::assertIsArray($result);
        self::assertContainsOnlyInstancesOf(ConversationMessage::class, $result);
    }

    public function testFindByUser(): void
    {
        $result = $this->repository->findByUser('test-user');
        self::assertIsArray($result);
        self::assertContainsOnlyInstancesOf(ConversationMessage::class, $result);
    }

    public function testFindByDifyApp(): void
    {
        $difyApp = $this->createRealDifyApp();
        $result = $this->repository->findByDifyApp($difyApp);
        self::assertIsArray($result);
        self::assertContainsOnlyInstancesOf(ConversationMessage::class, $result);
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
