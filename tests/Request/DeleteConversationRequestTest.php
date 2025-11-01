<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DifyChatflowBundle\Request\DeleteConversationRequest;

/**
 * @internal
 */
#[CoversClass(DeleteConversationRequest::class)]
final class DeleteConversationRequestTest extends RequestTestCase
{
    public function testGetRequestPath(): void
    {
        $request = new DeleteConversationRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertSame('/conversations/conv-123', $request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        $request = new DeleteConversationRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertSame('DELETE', $request->getRequestMethod());
    }

    public function testGetRequestOptions(): void
    {
        $request = new DeleteConversationRequest(
            conversationId: 'conv-123',
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
        $request = new DeleteConversationRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertSame('conv-123', $request->getConversationId());
        self::assertSame('test-user', $request->getUser());
    }
}
