<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\DifyChatflowBundle\Entity\Conversation;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Conversation::class)]
class ConversationTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Conversation();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'difyConversationId' => ['difyConversationId', '123e4567-e89b-12d3-a456-426614174000'],
            'name' => ['name', 'Test Conversation'],
            'status' => ['status', 'active'],
            'introduction' => ['introduction', 'Welcome to the conversation'],
            'user' => ['user', 'test_user_123'],
            'difyCreatedAt' => ['difyCreatedAt', 1640995200],
            'difyUpdatedAt' => ['difyUpdatedAt', 1640995300],
        ];
    }

    private Conversation $conversation;

    private MockObject|DifyApp $difyApp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conversation = new Conversation();
        // 使用 DifyApp mock 来测试关联关系
        // 理由：Conversation 实体与 DifyApp 存在 ManyToOne 关联关系
        $this->difyApp = $this->createMock(DifyApp::class);
    }

    public function testSetAndGetDifyConversationId(): void
    {
        $difyConversationId = '123e4567-e89b-12d3-a456-426614174000';
        $this->conversation->setDifyConversationId($difyConversationId);
        $this->assertEquals($difyConversationId, $this->conversation->getDifyConversationId());
    }

    public function testSetAndGetDifyApp(): void
    {
        $difyApp = $this->difyApp;
        $this->assertInstanceOf(DifyApp::class, $difyApp);
        $this->conversation->setDifyApp($difyApp);
        $this->assertSame($difyApp, $this->conversation->getDifyApp());
    }

    public function testSetAndGetDifyAppWithNull(): void
    {
        $this->conversation->setDifyApp(null);
        $this->assertNull($this->conversation->getDifyApp());
    }

    public function testSetAndGetName(): void
    {
        $name = 'Test Conversation Name';
        $this->conversation->setName($name);
        $this->assertEquals($name, $this->conversation->getName());
    }

    public function testSetAndGetInputs(): void
    {
        $inputs = ['param1' => 'value1', 'param2' => 'value2'];
        $this->conversation->setInputs($inputs);
        $this->assertEquals($inputs, $this->conversation->getInputs());
    }

    public function testSetAndGetInputsWithNull(): void
    {
        $this->conversation->setInputs(null);
        $this->assertNull($this->conversation->getInputs());
    }

    public function testSetAndGetInputsWithEmptyArray(): void
    {
        $inputs = [];
        $this->conversation->setInputs($inputs);
        $this->assertEquals($inputs, $this->conversation->getInputs());
    }

    public function testSetAndGetStatus(): void
    {
        $status = 'active';
        $this->conversation->setStatus($status);
        $this->assertEquals($status, $this->conversation->getStatus());
    }

    public function testSetAndGetIntroduction(): void
    {
        $introduction = 'Welcome to our conversation service';
        $this->conversation->setIntroduction($introduction);
        $this->assertEquals($introduction, $this->conversation->getIntroduction());
    }

    public function testSetAndGetIntroductionWithNull(): void
    {
        $this->conversation->setIntroduction(null);
        $this->assertNull($this->conversation->getIntroduction());
    }

    public function testSetAndGetUser(): void
    {
        $user = 'test_user_123';
        $this->conversation->setUser($user);
        $this->assertEquals($user, $this->conversation->getUser());
    }

    public function testSetAndGetDifyCreatedAt(): void
    {
        $timestamp = 1640995200;
        $this->conversation->setDifyCreatedAt($timestamp);
        $this->assertEquals($timestamp, $this->conversation->getDifyCreatedAt());
    }

    public function testSetAndGetDifyUpdatedAt(): void
    {
        $timestamp = 1640995300;
        $this->conversation->setDifyUpdatedAt($timestamp);
        $this->assertEquals($timestamp, $this->conversation->getDifyUpdatedAt());
    }

    public function testToStringWithNewEntity(): void
    {
        // 测试新实体的字符串表示
        $this->assertEquals('Conversation[new]: unnamed', (string) $this->conversation);
    }

    public function testToStringWithDifyConversationId(): void
    {
        $difyConversationId = '123e4567-e89b-12d3-a456-426614174000';
        $this->conversation->setDifyConversationId($difyConversationId);

        $expected = "Conversation[{$difyConversationId}]: unnamed";
        $this->assertEquals($expected, (string) $this->conversation);
    }

    public function testToStringWithName(): void
    {
        $difyConversationId = '123e4567-e89b-12d3-a456-426614174000';
        $name = 'Test Conversation';

        $this->conversation->setDifyConversationId($difyConversationId);
        $this->conversation->setName($name);

        $expected = "Conversation[{$difyConversationId}]: {$name}";
        $this->assertEquals($expected, (string) $this->conversation);
    }

    public function testTimestampableTraitIntegration(): void
    {
        // 验证 TimestampableAware trait 的集成
        // 通过直接调用方法来验证其存在性
        $this->assertNull($this->conversation->getCreateTime());
        $this->assertNull($this->conversation->getUpdateTime());

        $now = new \DateTimeImmutable();
        $this->conversation->setCreateTime($now);
        $this->conversation->setUpdateTime($now);

        $this->assertEquals($now, $this->conversation->getCreateTime());
        $this->assertEquals($now, $this->conversation->getUpdateTime());
    }

    public function testSnowflakeKeyTraitIntegration(): void
    {
        // 验证 SnowflakeKeyAware trait 的集成
        // 通过直接调用方法来验证其存在性
        $this->assertNull($this->conversation->getId());

        $testId = 'test_snowflake_id';
        $this->conversation->setId($testId);
        $this->assertEquals($testId, $this->conversation->getId());
    }

    public function testInputsArrayType(): void
    {
        // 测试复杂的 inputs 数组数据
        $complexInputs = [
            'user_input' => 'Hello world',
            'context' => [
                'previous_message' => 'Hi there',
                'session_id' => 'session_123',
            ],
            'metadata' => [
                'source' => 'web',
                'timestamp' => 1640995200,
            ],
        ];

        $this->conversation->setInputs($complexInputs);
        $this->assertEquals($complexInputs, $this->conversation->getInputs());
    }

    public function testDifyAppRelationship(): void
    {
        // 测试与 DifyApp 的关联关系
        $difyApp = $this->difyApp;
        $this->assertInstanceOf(DifyApp::class, $difyApp);

        // 测试关联设置
        $this->conversation->setDifyApp($difyApp);
        $this->assertSame($difyApp, $this->conversation->getDifyApp());

        // 测试关联清除
        $this->conversation->setDifyApp(null);
        $this->assertNull($this->conversation->getDifyApp());
    }

    public function testTimestampValues(): void
    {
        // 测试时间戳边界值
        $earlyTimestamp = 0;
        $currentTimestamp = time();

        $this->conversation->setDifyCreatedAt($earlyTimestamp);
        $this->assertEquals($earlyTimestamp, $this->conversation->getDifyCreatedAt());

        $this->conversation->setDifyUpdatedAt($currentTimestamp);
        $this->assertEquals($currentTimestamp, $this->conversation->getDifyUpdatedAt());
    }

    public function testLongIntroduction(): void
    {
        // 测试长文本的介绍
        $longIntroduction = str_repeat('这是一个很长的介绍内容。', 100);
        $this->conversation->setIntroduction($longIntroduction);
        $this->assertEquals($longIntroduction, $this->conversation->getIntroduction());
    }

    public function testUserIdentifierFormats(): void
    {
        // 测试不同格式的用户标识
        $userFormats = [
            'user_123',
            'email@domain.com',
            'openid_abc123def456',
            'uuid-format-user-id',
        ];

        foreach ($userFormats as $userFormat) {
            $this->conversation->setUser($userFormat);
            $this->assertEquals($userFormat, $this->conversation->getUser());
        }
    }

    public function testConversationStatuses(): void
    {
        // 测试不同的会话状态
        $statuses = ['active', 'inactive', 'completed', 'paused', 'error'];

        foreach ($statuses as $status) {
            $this->conversation->setStatus($status);
            $this->assertEquals($status, $this->conversation->getStatus());
        }
    }
}
