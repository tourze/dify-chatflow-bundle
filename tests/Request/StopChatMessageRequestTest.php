<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DifyChatflowBundle\Request\StopChatMessageRequest;

/**
 * @internal
 */
#[CoversClass(StopChatMessageRequest::class)]
final class StopChatMessageRequestTest extends RequestTestCase
{
    public function testGetRequestPath(): void
    {
        $request = new StopChatMessageRequest(
            taskId: 'task-123',
            user: 'test-user'
        );

        self::assertSame('/chat-messages/task-123/stop', $request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        $request = new StopChatMessageRequest(
            taskId: 'task-123',
            user: 'test-user'
        );

        self::assertSame('POST', $request->getRequestMethod());
    }

    public function testGetRequestOptions(): void
    {
        $request = new StopChatMessageRequest(
            taskId: 'task-123',
            user: 'test-user'
        );

        $options = $request->getRequestOptions();

        self::assertArrayHasKey('headers', $options);
        self::assertArrayHasKey('json', $options);
        self::assertIsArray($options['headers']);
        self::assertSame('application/json', $options['headers']['Content-Type']);

        self::assertIsArray($options['json']);
        $body = $options['json'];
        self::assertSame('test-user', $body['user']);
    }

    public function testGetters(): void
    {
        $request = new StopChatMessageRequest(
            taskId: 'task-123',
            user: 'test-user'
        );

        self::assertSame('task-123', $request->getTaskId());
        self::assertSame('test-user', $request->getUser());
    }
}
