<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\DifyChatflowBundle\Command\SyncConversationsCommand;
use Tourze\DifyChatflowBundle\Entity\Conversation;
use Tourze\DifyChatflowBundle\Entity\ConversationMessage;
use Tourze\DifyChatflowBundle\Repository\ConversationMessageRepository;
use Tourze\DifyChatflowBundle\Repository\ConversationRepository;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\DifyCoreBundle\Service\DifyApiClient;
use Tourze\DifyCoreBundle\Service\DifyAppService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * SyncConversationsCommand 单元测试
 * 测试重点：命令配置、参数验证、同步执行、错误处理
 * @internal
 */
#[CoversClass(SyncConversationsCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncConversationsCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    private DifyAppService&MockObject $difyAppService;

    private DifyApiClient&MockObject $difyApiClient;

    protected function getCommandTester(): CommandTester
    {
        if (!isset($this->commandTester)) {
            $command = self::getService(SyncConversationsCommand::class);
            $this->commandTester = new CommandTester($command);
        }

        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $this->difyAppService = $this->createMock(DifyAppService::class);
        $this->difyApiClient = $this->createMock(DifyApiClient::class);

        // 只Mock关键的外部服务，避免Mock复杂的EntityManager
        self::getContainer()->set(DifyAppService::class, $this->difyAppService);
        self::getContainer()->set(DifyApiClient::class, $this->difyApiClient);
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
        // Arrange: 创建测试应用
        $testApp = $this->createTestApp();

        // 配置应用服务返回
        $this->difyAppService->expects(self::once())
            ->method('getAppsToSync')
            ->with('test-app-id')
            ->willReturn([$testApp])
        ;

        // 配置API客户端
        $this->difyApiClient->expects(self::once())
            ->method('setApp')
            ->with($testApp)
        ;

        // 配置API响应
        $mockResponse = $this->createMockApiResponse();
        $this->difyApiClient->expects(self::exactly(2))
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // Act: 执行命令
        $exitCode = $this->getCommandTester()->execute([
            'app-id' => 'test-app-id',
            '--dry-run' => true,
        ]);

        // Assert: 验证返回成功状态
        self::assertSame(Command::SUCCESS, $exitCode);
        $output = $this->getCommandTester()->getDisplay();
        self::assertStringContainsString('同步完成', $output);
    }

    public function testOptionLimit(): void
    {
        // Arrange: 创建测试应用
        $testApp = $this->createTestApp();

        // 配置应用服务返回
        $this->difyAppService->expects(self::once())
            ->method('getAppsToSync')
            ->with(null)
            ->willReturn([$testApp])
        ;

        // 配置API客户端
        $this->difyApiClient->expects(self::once())
            ->method('setApp')
            ->with($testApp)
        ;

        // 配置API响应
        $mockResponse = $this->createMockApiResponse();
        $this->difyApiClient->expects(self::exactly(2))
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // Act: 执行命令，测试limit选项
        $exitCode = $this->getCommandTester()->execute([
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
        // Arrange: 创建测试应用
        $testApp = $this->createTestApp();

        // 配置应用服务返回
        $this->difyAppService->expects(self::once())
            ->method('getAppsToSync')
            ->with(null)
            ->willReturn([$testApp])
        ;

        // 配置API客户端
        $this->difyApiClient->expects(self::once())
            ->method('setApp')
            ->with($testApp)
        ;

        // 配置API响应
        $mockResponse = $this->createMockApiResponse();
        $this->difyApiClient->expects(self::exactly(2))
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // Act: 执行命令，测试dry-run选项
        $exitCode = $this->getCommandTester()->execute([
            '--dry-run' => true,
        ]);

        // Assert: 验证返回成功状态
        self::assertSame(Command::SUCCESS, $exitCode);
        $output = $this->getCommandTester()->getDisplay();
        self::assertStringContainsString('同步完成', $output);
    }

    public function testExecuteWithNoAppsFoundShouldReturnFailure(): void
    {
        // Arrange: 配置无应用返回
        $this->difyAppService->expects(self::once())
            ->method('getAppsToSync')
            ->with(null)
            ->willReturn([])
        ;

        // Act: 执行命令
        $exitCode = $this->getCommandTester()->execute([]);

        // Assert: 验证返回失败状态
        self::assertSame(Command::FAILURE, $exitCode);
        $output = $this->getCommandTester()->getDisplay();
        self::assertStringContainsString('未找到有效的Dify应用配置', $output);
    }

    private function createTestApp(): DifyApp
    {
        $app = $this->createMock(DifyApp::class);
        $app->method('getId')->willReturn('test-app-id');
        $app->method('getName')->willReturn('Test App');

        return $app;
    }

    private function createMockApiResponse(): object
    {
        return new class {
            /**
             * @return array<string, mixed>
             */
            public function toArray(): array
            {
                return [
                    'data' => [
                        [
                            'id' => 'conv-123',
                            'name' => 'Test Conversation',
                            'status' => 'normal',
                            'introduction' => 'Test intro',
                            'inputs' => ['key' => 'value'],
                            'created_at' => 1234567890,
                            'updated_at' => 1234567890,
                            'user' => 'test-user',
                        ],
                    ],
                    'has_more' => false,
                ];
            }
        };
    }
}
