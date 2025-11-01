<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DifyChatflowBundle\Request\RenameConversationRequest;

/**
 * @internal
 */
#[CoversClass(RenameConversationRequest::class)]
final class RenameConversationRequestTest extends RequestTestCase
{
    public function testGetRequestPath(): void
    {
        $request = new RenameConversationRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertSame('/conversations/conv-123/name', $request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        $request = new RenameConversationRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertSame('POST', $request->getRequestMethod());
    }

    public function testGetRequestOptionsWithRequiredParameters(): void
    {
        $request = new RenameConversationRequest(
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
        self::assertFalse($body['auto_generate']);
        self::assertArrayNotHasKey('name', $body);
    }

    public function testGetRequestOptionsWithOptionalParameters(): void
    {
        $request = new RenameConversationRequest(
            conversationId: 'conv-123',
            user: 'test-user',
            name: 'New Conversation Name',
            autoGenerate: true
        );

        $options = $request->getRequestOptions();
        self::assertIsArray($options['json']);
        $body = $options['json'];

        self::assertSame('test-user', $body['user']);
        self::assertTrue($body['auto_generate']);
        self::assertSame('New Conversation Name', $body['name']);
    }

    public function testGetters(): void
    {
        $request = new RenameConversationRequest(
            conversationId: 'conv-123',
            user: 'test-user',
            name: 'New Conversation Name',
            autoGenerate: true
        );

        self::assertSame('conv-123', $request->getConversationId());
        self::assertSame('test-user', $request->getUser());
        self::assertSame('New Conversation Name', $request->getName());
        self::assertTrue($request->isAutoGenerate());
    }

    public function testGettersWithNullName(): void
    {
        $request = new RenameConversationRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertNull($request->getName());
        self::assertFalse($request->isAutoGenerate());
    }
}
