<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DifyChatflowBundle\Request\GetMessagesRequest;

/**
 * @internal
 */
#[CoversClass(GetMessagesRequest::class)]
final class GetMessagesRequestTest extends RequestTestCase
{
    public function testGetRequestPath(): void
    {
        $request = new GetMessagesRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertSame('/messages', $request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        $request = new GetMessagesRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertSame('GET', $request->getRequestMethod());
    }

    public function testGetRequestOptionsWithRequiredParameters(): void
    {
        $request = new GetMessagesRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        $options = $request->getRequestOptions();

        self::assertArrayHasKey('headers', $options);
        self::assertArrayHasKey('query', $options);
        self::assertIsArray($options['headers']);
        self::assertSame('application/json', $options['headers']['Content-Type']);

        $query = $options['query'];
        self::assertIsArray($query);
        self::assertSame('conv-123', $query['conversation_id']);
        self::assertSame('test-user', $query['user']);
        self::assertSame(20, $query['limit']);
        self::assertArrayNotHasKey('first_id', $query);
    }

    public function testGetRequestOptionsWithOptionalParameters(): void
    {
        $request = new GetMessagesRequest(
            conversationId: 'conv-123',
            user: 'test-user',
            firstId: 'first-456',
            limit: 50
        );

        $options = $request->getRequestOptions();
        $query = $options['query'];
        self::assertIsArray($query);

        self::assertSame('conv-123', $query['conversation_id']);
        self::assertSame('test-user', $query['user']);
        self::assertSame(50, $query['limit']);
        self::assertSame('first-456', $query['first_id']);
    }

    public function testGetters(): void
    {
        $request = new GetMessagesRequest(
            conversationId: 'conv-123',
            user: 'test-user',
            firstId: 'first-456',
            limit: 50
        );

        self::assertSame('conv-123', $request->getConversationId());
        self::assertSame('test-user', $request->getUser());
        self::assertSame('first-456', $request->getFirstId());
        self::assertSame(50, $request->getLimit());
    }

    public function testGettersWithNullFirstId(): void
    {
        $request = new GetMessagesRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertNull($request->getFirstId());
    }
}
