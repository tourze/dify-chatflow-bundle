<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\DifyChatflowBundle\Entity\ConversationMessage;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(ConversationMessage::class)]
class ConversationMessageTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new ConversationMessage();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'difyMessageId' => ['difyMessageId', '123e4567-e89b-12d3-a456-426614174001'],
            'difyConversationId' => ['difyConversationId', '123e4567-e89b-12d3-a456-426614174000'],
            'query' => ['query', 'What is the weather today?'],
            'answer' => ['answer', 'The weather today is sunny with a temperature of 25°C.'],
            'feedbackRating' => ['feedbackRating', 'like'],
            'user' => ['user', 'test_user_123'],
            'difyCreatedAt' => ['difyCreatedAt', 1640995200],
        ];
    }

    private ConversationMessage $message;

    private MockObject|DifyApp $difyApp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->message = new ConversationMessage();
        // 使用 DifyApp mock 来测试关联关系
        // 理由：ConversationMessage 实体与 DifyApp 存在 ManyToOne 关联关系
        $this->difyApp = $this->createMock(DifyApp::class);
    }

    public function testSetAndGetDifyMessageId(): void
    {
        $difyMessageId = '123e4567-e89b-12d3-a456-426614174001';
        $this->message->setDifyMessageId($difyMessageId);
        $this->assertEquals($difyMessageId, $this->message->getDifyMessageId());
    }

    public function testSetAndGetDifyConversationId(): void
    {
        $difyConversationId = '123e4567-e89b-12d3-a456-426614174000';
        $this->message->setDifyConversationId($difyConversationId);
        $this->assertEquals($difyConversationId, $this->message->getDifyConversationId());
    }

    public function testSetAndGetDifyApp(): void
    {
        $difyApp = $this->difyApp;
        $this->assertInstanceOf(DifyApp::class, $difyApp);
        $this->message->setDifyApp($difyApp);
        $this->assertSame($difyApp, $this->message->getDifyApp());
    }

    public function testSetAndGetDifyAppWithNull(): void
    {
        $this->message->setDifyApp(null);
        $this->assertNull($this->message->getDifyApp());
    }

    public function testSetAndGetInputs(): void
    {
        $inputs = ['param1' => 'value1', 'param2' => 'value2'];
        $this->message->setInputs($inputs);
        $this->assertEquals($inputs, $this->message->getInputs());
    }

    public function testSetAndGetInputsWithNull(): void
    {
        $this->message->setInputs(null);
        $this->assertNull($this->message->getInputs());
    }

    public function testSetAndGetInputsWithEmptyArray(): void
    {
        $inputs = [];
        $this->message->setInputs($inputs);
        $this->assertEquals($inputs, $this->message->getInputs());
    }

    public function testSetAndGetQuery(): void
    {
        $query = 'What is the weather like today?';
        $this->message->setQuery($query);
        $this->assertEquals($query, $this->message->getQuery());
    }

    public function testSetAndGetAnswer(): void
    {
        $answer = 'The weather today is sunny with a temperature of 25°C and light winds.';
        $this->message->setAnswer($answer);
        $this->assertEquals($answer, $this->message->getAnswer());
    }

    public function testSetAndGetFeedbackRating(): void
    {
        $rating = 'like';
        $this->message->setFeedbackRating($rating);
        $this->assertEquals($rating, $this->message->getFeedbackRating());
    }

    public function testSetAndGetFeedbackRatingWithDislike(): void
    {
        $rating = 'dislike';
        $this->message->setFeedbackRating($rating);
        $this->assertEquals($rating, $this->message->getFeedbackRating());
    }

    public function testSetAndGetFeedbackRatingWithNull(): void
    {
        $this->message->setFeedbackRating(null);
        $this->assertNull($this->message->getFeedbackRating());
    }

    public function testSetAndGetMessageFiles(): void
    {
        /** @var array<string, mixed> $messageFiles */
        $messageFiles = [
            'file1' => ['id' => '1', 'type' => 'image', 'url' => 'https://example.com/image.jpg'],
            'file2' => ['id' => '2', 'type' => 'document', 'url' => 'https://example.com/doc.pdf'],
        ];
        $this->message->setMessageFiles($messageFiles);
        $this->assertEquals($messageFiles, $this->message->getMessageFiles());
    }

    public function testSetAndGetMessageFilesWithNull(): void
    {
        $this->message->setMessageFiles(null);
        $this->assertNull($this->message->getMessageFiles());
    }

    public function testSetAndGetRetrieverResources(): void
    {
        /** @var array<string, mixed> $retrieverResources */
        $retrieverResources = [
            'doc1' => ['id' => 'doc1', 'title' => 'Documentation Page 1', 'score' => 0.95],
            'doc2' => ['id' => 'doc2', 'title' => 'Documentation Page 2', 'score' => 0.87],
        ];
        $this->message->setRetrieverResources($retrieverResources);
        $this->assertEquals($retrieverResources, $this->message->getRetrieverResources());
    }

    public function testSetAndGetRetrieverResourcesWithNull(): void
    {
        $this->message->setRetrieverResources(null);
        $this->assertNull($this->message->getRetrieverResources());
    }

    public function testSetAndGetUser(): void
    {
        $user = 'test_user_123';
        $this->message->setUser($user);
        $this->assertEquals($user, $this->message->getUser());
    }

    public function testSetAndGetDifyCreatedAt(): void
    {
        $timestamp = 1640995200;
        $this->message->setDifyCreatedAt($timestamp);
        $this->assertEquals($timestamp, $this->message->getDifyCreatedAt());
    }

    public function testToStringWithNewEntity(): void
    {
        // 测试新实体的字符串表示
        $this->assertEquals('Message[new]: no query', (string) $this->message);
    }

    public function testToStringWithDifyMessageId(): void
    {
        $difyMessageId = '123e4567-e89b-12d3-a456-426614174001';
        $this->message->setDifyMessageId($difyMessageId);

        $expected = "Message[{$difyMessageId}]: no query";
        $this->assertEquals($expected, (string) $this->message);
    }

    public function testToStringWithQuery(): void
    {
        $difyMessageId = '123e4567-e89b-12d3-a456-426614174001';
        $query = 'What is the weather like today?';

        $this->message->setDifyMessageId($difyMessageId);
        $this->message->setQuery($query);

        $expected = "Message[{$difyMessageId}]: {$query}";
        $this->assertEquals($expected, (string) $this->message);
    }

    public function testToStringWithLongQuery(): void
    {
        $difyMessageId = '123e4567-e89b-12d3-a456-426614174001';
        $longQuery = 'This is a very long query that exceeds fifty characters and should be truncated in the string representation';

        $this->message->setDifyMessageId($difyMessageId);
        $this->message->setQuery($longQuery);

        $expectedQuery = substr($longQuery, 0, 50);
        $expected = "Message[{$difyMessageId}]: {$expectedQuery}";
        $this->assertEquals($expected, (string) $this->message);
    }

    public function testTimestampableTraitIntegration(): void
    {
        // 验证 TimestampableAware trait 的集成
        // 通过直接调用方法来验证其存在性
        $this->assertNull($this->message->getCreateTime());
        $this->assertNull($this->message->getUpdateTime());

        $now = new \DateTimeImmutable();
        $this->message->setCreateTime($now);
        $this->message->setUpdateTime($now);

        $this->assertEquals($now, $this->message->getCreateTime());
        $this->assertEquals($now, $this->message->getUpdateTime());
    }

    public function testSnowflakeKeyTraitIntegration(): void
    {
        // 验证 SnowflakeKeyAware trait 的集成
        // 通过直接调用方法来验证其存在性
        $this->assertNull($this->message->getId());

        $testId = 'test_snowflake_id';
        $this->message->setId($testId);
        $this->assertEquals($testId, $this->message->getId());
    }

    public function testInputsComplexStructure(): void
    {
        // 测试复杂的 inputs 数组数据
        $complexInputs = [
            'user_message' => 'Hello, how can I help you?',
            'conversation_context' => [
                'previous_messages' => [
                    ['role' => 'user', 'content' => 'Hi'],
                    ['role' => 'assistant', 'content' => 'Hello! How can I help you today?'],
                ],
                'session_metadata' => [
                    'session_id' => 'session_123',
                    'user_preferences' => ['language' => 'en', 'theme' => 'dark'],
                ],
            ],
            'system_info' => [
                'timestamp' => 1640995200,
                'source' => 'web_app',
                'version' => '1.0.0',
            ],
        ];

        $this->message->setInputs($complexInputs);
        $this->assertEquals($complexInputs, $this->message->getInputs());
    }

    public function testMessageFilesStructure(): void
    {
        // 测试消息文件的数据结构
        /** @var array<string, mixed> $messageFiles */
        $messageFiles = [
            'file_001' => [
                'id' => 'file_001',
                'type' => 'image',
                'url' => 'https://example.com/uploads/image.jpg',
                'name' => 'screenshot.jpg',
                'size' => 1024000,
                'mime_type' => 'image/jpeg',
            ],
            'file_002' => [
                'id' => 'file_002',
                'type' => 'document',
                'url' => 'https://example.com/uploads/document.pdf',
                'name' => 'specification.pdf',
                'size' => 2048000,
                'mime_type' => 'application/pdf',
            ],
        ];

        $this->message->setMessageFiles($messageFiles);
        $this->assertEquals($messageFiles, $this->message->getMessageFiles());
    }

    public function testRetrieverResourcesStructure(): void
    {
        // 测试检索资源的数据结构
        /** @var array<string, mixed> $retrieverResources */
        $retrieverResources = [
            'resource_001' => [
                'id' => 'resource_001',
                'title' => 'Product Documentation - Getting Started',
                'content' => 'This guide will help you get started with our product...',
                'score' => 0.95,
                'source' => 'docs/getting-started.md',
                'metadata' => [
                    'section' => 'introduction',
                    'last_updated' => '2024-01-15',
                ],
            ],
            'resource_002' => [
                'id' => 'resource_002',
                'title' => 'API Reference - Authentication',
                'content' => 'Learn how to authenticate with our API...',
                'score' => 0.87,
                'source' => 'docs/api/auth.md',
                'metadata' => [
                    'section' => 'api',
                    'last_updated' => '2024-01-10',
                ],
            ],
        ];

        $this->message->setRetrieverResources($retrieverResources);
        $this->assertEquals($retrieverResources, $this->message->getRetrieverResources());
    }

    public function testDifyAppRelationship(): void
    {
        // 测试与 DifyApp 的关联关系
        $difyApp = $this->difyApp;
        $this->assertInstanceOf(DifyApp::class, $difyApp);

        // 测试关联设置
        $this->message->setDifyApp($difyApp);
        $this->assertSame($difyApp, $this->message->getDifyApp());

        // 测试关联清除
        $this->message->setDifyApp(null);
        $this->assertNull($this->message->getDifyApp());
    }

    public function testFeedbackRatingValues(): void
    {
        // 测试有效的反馈评分值
        $validRatings = ['like', 'dislike'];

        foreach ($validRatings as $rating) {
            $this->message->setFeedbackRating($rating);
            $this->assertEquals($rating, $this->message->getFeedbackRating());
        }

        // 测试 null 值
        $this->message->setFeedbackRating(null);
        $this->assertNull($this->message->getFeedbackRating());
    }

    public function testLongTextContent(): void
    {
        // 测试长文本的查询和回答
        $longQuery = str_repeat('这是一个很长的查询内容。', 100);
        $longAnswer = str_repeat('这是一个很长的回答内容。', 200);

        $this->message->setQuery($longQuery);
        $this->message->setAnswer($longAnswer);

        $this->assertEquals($longQuery, $this->message->getQuery());
        $this->assertEquals($longAnswer, $this->message->getAnswer());
    }

    public function testUserIdentifierFormats(): void
    {
        // 测试不同格式的用户标识
        $userFormats = [
            'user_123',
            'email@domain.com',
            'openid_abc123def456',
            'uuid-format-user-id',
            'session_user_789',
        ];

        foreach ($userFormats as $userFormat) {
            $this->message->setUser($userFormat);
            $this->assertEquals($userFormat, $this->message->getUser());
        }
    }

    public function testTimestampValues(): void
    {
        // 测试时间戳边界值
        $earlyTimestamp = 0;
        $currentTimestamp = time();

        $this->message->setDifyCreatedAt($earlyTimestamp);
        $this->assertEquals($earlyTimestamp, $this->message->getDifyCreatedAt());

        $this->message->setDifyCreatedAt($currentTimestamp);
        $this->assertEquals($currentTimestamp, $this->message->getDifyCreatedAt());
    }

    public function testEmptyArrayFields(): void
    {
        // 测试空数组字段
        $emptyArray = [];

        $this->message->setInputs($emptyArray);
        $this->assertEquals($emptyArray, $this->message->getInputs());

        $this->message->setMessageFiles($emptyArray);
        $this->assertEquals($emptyArray, $this->message->getMessageFiles());

        $this->message->setRetrieverResources($emptyArray);
        $this->assertEquals($emptyArray, $this->message->getRetrieverResources());
    }
}
