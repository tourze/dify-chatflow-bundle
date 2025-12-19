<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Tourze\DifyChatflowBundle\Command\SyncConversationsCommand;
use Tourze\DifyChatflowBundle\Repository\ConversationRepository;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\DifyCoreBundle\Service\DifyApiClient;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * SyncConversationsCommand 集成测试
 * 测试重点：命令配置、参数验证、同步执行、错误处理
 * 使用真实的服务和 MockHttpClient 来模拟 API 响应
 * @internal
 */
#[CoversClass(SyncConversationsCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncConversationsCommandTest extends AbstractCommandTestCase
{
    private DifyApp $testApp;
    private ?CommandTester $commandTester = null;

    protected function getCommandTester(): CommandTester
    {
        if (null === $this->commandTester) {
            $command = self::getService(SyncConversationsCommand::class);
            $this->commandTester = new CommandTester($command);
        }

        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        // 创建并持久化真实的 DifyApp 实体
        $this->testApp = $this->createAndPersistTestApp();
    }

    public function testCommandConfiguration(): void
    {
        // Arrange & Act: 获取命令
        $command = self::getContainer()->get(SyncConversationsCommand::class);
        self::assertInstanceOf(SyncConversationsCommand::class, $command);

        $definition = $command->getDefinition();

        // Assert: 验证命令名称和描述
        self::assertSame('dify:sync-conversations', $command->getName());
        self::assertSame('同步Dify应用的会话记录和消息数据', $command->getDescription());

        // 验证app-id参数
        self::assertTrue($definition->hasArgument('app-id'));
        $appIdArgument = $definition->getArgument('app-id');
        self::assertFalse($appIdArgument->isRequired());
        self::assertSame('DifyApp ID（可选，默认同步所有有效应用）', $appIdArgument->getDescription());

        // 验证limit选项
        self::assertTrue($definition->hasOption('limit'));
        $limitOption = $definition->getOption('limit');
        self::assertTrue($limitOption->acceptValue());
        self::assertSame('100', $limitOption->getDefault());
        self::assertSame('每次请求限制数量', $limitOption->getDescription());

        // 验证dry-run选项
        self::assertTrue($definition->hasOption('dry-run'));
        $dryRunOption = $definition->getOption('dry-run');
        self::assertFalse($dryRunOption->acceptValue());
        self::assertSame('仅显示将要同步的数据，不实际写入数据库', $dryRunOption->getDescription());
    }

    public function testArgumentAppId(): void
    {
        // Arrange: 设置 MockHttpClient 模拟 API 响应
        $this->setupMockHttpClient([
            // 第一次请求：获取会话列表
            $this->createConversationsResponse(),
            // 第二次请求：获取消息列表
            $this->createMessagesResponse(),
        ]);

        // Act: 执行命令，指定 app-id
        $exitCode = $this->getCommandTester()->execute([
            'app-id' => $this->testApp->getId(),
            '--dry-run' => true,
        ]);

        // Assert: 验证返回成功状态
        self::assertSame(Command::SUCCESS, $exitCode);
        $output = $this->getCommandTester()->getDisplay();
        self::assertStringContainsString('同步完成', $output);
        self::assertStringContainsString($this->testApp->getName(), $output);
    }

    public function testOptionLimit(): void
    {
        // Arrange: 设置 MockHttpClient
        $this->setupMockHttpClient([
            $this->createConversationsResponse(),
            $this->createMessagesResponse(),
        ]);

        // Act: 执行命令，测试limit选项
        $exitCode = $this->getCommandTester()->execute([
            'app-id' => $this->testApp->getId(),
            '--limit' => '50',
            '--dry-run' => true,
        ]);

        // Assert: 验证返回成功状态
        self::assertSame(Command::SUCCESS, $exitCode);
        $output = $this->getCommandTester()->getDisplay();
        self::assertStringContainsString('同步完成', $output);
    }

    public function testOptionDryRun(): void
    {
        // Arrange: 设置 MockHttpClient
        $this->setupMockHttpClient([
            $this->createConversationsResponse(),
            $this->createMessagesResponse(),
        ]);

        // 清空会话表，确保没有旧数据
        self::getEntityManager()->getConnection()->executeStatement('DELETE FROM dify_conversations');
        self::getEntityManager()->getConnection()->executeStatement('DELETE FROM dify_conversation_messages');

        // Act: 执行命令，使用dry-run模式
        $exitCode = $this->getCommandTester()->execute([
            'app-id' => $this->testApp->getId(),
            '--dry-run' => true,
        ]);

        // Assert: 验证返回成功状态
        self::assertSame(Command::SUCCESS, $exitCode);
        $output = $this->getCommandTester()->getDisplay();
        self::assertStringContainsString('同步完成', $output);

        // 验证 dry-run 模式下没有实际写入数据库
        $conversationRepo = self::getService(ConversationRepository::class);
        $conversations = $conversationRepo->findAll();
        self::assertEmpty($conversations, 'dry-run 模式不应写入数据库');
    }

    public function testExecuteWithNoAppsFoundShouldReturnFailure(): void
    {
        // Arrange: 删除所有 DifyApp 实体以确保没有有效应用
        $em = self::getEntityManager();
        $em->getConnection()->executeStatement('DELETE FROM dify_apps');
        $em->clear();

        // 重置 commandTester 以使用新的服务状态
        $this->commandTester = null;

        // Act: 执行命令
        $exitCode = $this->getCommandTester()->execute([]);

        // Assert: 验证返回失败状态
        self::assertSame(Command::FAILURE, $exitCode);
        $output = $this->getCommandTester()->getDisplay();
        self::assertStringContainsString('未找到有效的Dify应用配置', $output);
    }

    public function testExecuteWithRealDataShouldPersistConversations(): void
    {
        // Arrange: 设置 MockHttpClient
        $this->setupMockHttpClient([
            $this->createConversationsResponse(),
            $this->createMessagesResponse(),
        ]);

        // 清空会话表
        self::getEntityManager()->getConnection()->executeStatement('DELETE FROM dify_conversations');
        self::getEntityManager()->getConnection()->executeStatement('DELETE FROM dify_conversation_messages');

        // Act: 执行命令（不使用 dry-run）
        $exitCode = $this->getCommandTester()->execute([
            'app-id' => $this->testApp->getId(),
        ]);

        // Assert: 验证返回成功状态
        self::assertSame(Command::SUCCESS, $exitCode);

        // 验证数据已持久化到数据库
        $conversationRepo = self::getService(ConversationRepository::class);
        $conversations = $conversationRepo->findAll();
        self::assertCount(1, $conversations, '应该持久化 1 个会话');

        $conversation = $conversations[0];
        self::assertSame('conv-test-123', $conversation->getDifyConversationId());
        self::assertSame('Test Conversation', $conversation->getName());
        self::assertSame('normal', $conversation->getStatus());
    }

    public function testExecuteWithPaginationShouldHandleMultiplePages(): void
    {
        // Arrange: 设置 MockHttpClient 模拟分页响应
        $this->setupMockHttpClient([
            // 第一页会话
            $this->createConversationsResponse(hasMore: true),
            // 第一页第一个会话的消息
            $this->createMessagesResponse(),
            // 第二页会话
            $this->createConversationsResponse(hasMore: false, conversationId: 'conv-test-456'),
            // 第二页第一个会话的消息
            $this->createMessagesResponse(conversationId: 'conv-test-456'),
        ]);

        // 清空会话表
        self::getEntityManager()->getConnection()->executeStatement('DELETE FROM dify_conversations');
        self::getEntityManager()->getConnection()->executeStatement('DELETE FROM dify_conversation_messages');

        // Act: 执行命令
        $exitCode = $this->getCommandTester()->execute([
            'app-id' => $this->testApp->getId(),
        ]);

        // Assert: 验证处理了多页数据
        self::assertSame(Command::SUCCESS, $exitCode);
        $conversationRepo = self::getService(ConversationRepository::class);
        $conversations = $conversationRepo->findAll();
        self::assertCount(2, $conversations, '应该处理 2 页数据，共 2 个会话');
    }

    /**
     * 创建并持久化测试用的 DifyApp 实体
     */
    private function createAndPersistTestApp(): DifyApp
    {
        $app = new DifyApp();
        $app->setName('Test Dify App');
        $app->setApiKey('test-api-key-' . uniqid());
        $app->setBaseUrl('https://api.dify.test');
        $app->setValid(true);

        $em = self::getEntityManager();
        $em->persist($app);
        $em->flush();

        return $app;
    }

    /**
     * 设置 MockHttpClient 并通过创建新的 DifyApiClient 实例来替换服务
     *
     * @param MockResponse[] $responses
     */
    private function setupMockHttpClient(array $responses): void
    {
        $mockHttpClient = new MockHttpClient($responses);

        // 创建新的 DifyApiClient 实例，使用 MockHttpClient
        $newDifyApiClient = new DifyApiClient(
            $mockHttpClient,
            self::getServiceById('event_dispatcher'),
            self::getContainer()->has('cache.app') ? self::getServiceById('cache.app') : null,
            self::getContainer()->has('lock.factory') ? self::getServiceById('lock.factory') : null,
            null
        );

        // 替换容器中的服务
        self::getContainer()->set(DifyApiClient::class, $newDifyApiClient);

        // 重置 commandTester，强制重新获取服务
        $this->commandTester = null;
    }

    /**
     * 创建模拟的会话列表响应
     */
    private function createConversationsResponse(
        bool $hasMore = false,
        string $conversationId = 'conv-test-123'
    ): MockResponse {
        $data = [
            'data' => [
                [
                    'id' => $conversationId,
                    'name' => 'Test Conversation',
                    'status' => 'normal',
                    'introduction' => 'Test intro',
                    'inputs' => ['key' => 'value'],
                    'created_at' => 1234567890,
                    'updated_at' => 1234567890,
                    'user' => 'test-user',
                ],
            ],
            'has_more' => $hasMore,
        ];

        return new MockResponse(json_encode($data), [
            'http_code' => 200,
            'response_headers' => ['Content-Type' => 'application/json'],
        ]);
    }

    /**
     * 创建模拟的消息列表响应
     */
    private function createMessagesResponse(
        string $conversationId = 'conv-test-123'
    ): MockResponse {
        $data = [
            'data' => [
                [
                    'id' => 'msg-test-' . md5($conversationId),
                    'conversation_id' => $conversationId,
                    'query' => 'Test question',
                    'answer' => 'Test answer',
                    'inputs' => [],
                    'message_files' => [],
                    'retriever_resources' => [],
                    'user' => 'test-user',
                    'created_at' => 1234567890,
                    'feedback' => [
                        'rating' => 'like',
                    ],
                ],
            ],
            'has_more' => false,
        ];

        return new MockResponse(json_encode($data), [
            'http_code' => 200,
            'response_headers' => ['Content-Type' => 'application/json'],
        ]);
    }
}
