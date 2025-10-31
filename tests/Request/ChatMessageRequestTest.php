<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Request;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DifyChatflowBundle\Request\ChatMessageRequest;

/**
 * @internal
 */
#[CoversClass(ChatMessageRequest::class)]
final class ChatMessageRequestTest extends RequestTestCase
{
    public function testGetRequestPath(): void
    {
        $request = new ChatMessageRequest(
            query: 'test query',
            user: 'test-user'
        );

        self::assertSame('/chat-messages', $request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        $request = new ChatMessageRequest(
            query: 'test query',
            user: 'test-user'
        );

        self::assertSame('POST', $request->getRequestMethod());
    }

    public function testGetRequestOptionsWithRequiredParameters(): void
    {
        $request = new ChatMessageRequest(
            query: 'test query',
            user: 'test-user'
        );

        $options = $request->getRequestOptions();

        self::assertArrayHasKey('headers', $options);
        self::assertArrayHasKey('json', $options);
        self::assertIsArray($options['headers']);
        self::assertSame('application/json', $options['headers']['Content-Type']);

        self::assertIsArray($options['json']);
        $body = $options['json'];
        self::assertSame('test query', $body['query']);
        self::assertSame('test-user', $body['user']);
        self::assertSame('streaming', $body['response_mode']);
        self::assertSame([], $body['inputs']);
        self::assertTrue($body['auto_generate_name']);
        self::assertArrayNotHasKey('conversation_id', $body);
        self::assertArrayNotHasKey('files', $body);
    }

    public function testGetRequestOptionsWithOptionalParameters(): void
    {
        $inputs = ['key' => 'value'];
        $files = ['file1', 'file2'];

        $request = new ChatMessageRequest(
            query: 'test query',
            user: 'test-user',
            responseMode: 'blocking',
            inputs: $inputs,
            conversationId: 'conv-123',
            files: $files,
            autoGenerateName: false
        );

        $options = $request->getRequestOptions();
        self::assertIsArray($options['json']);
        $body = $options['json'];

        self::assertSame('blocking', $body['response_mode']);
        self::assertSame($inputs, $body['inputs']);
        self::assertSame('conv-123', $body['conversation_id']);
        self::assertSame($files, $body['files']);
        self::assertFalse($body['auto_generate_name']);
    }

    public function testGetters(): void
    {
        $inputs = ['key' => 'value'];
        $files = ['file1', 'file2'];

        $request = new ChatMessageRequest(
            query: 'test query',
            user: 'test-user',
            responseMode: 'blocking',
            inputs: $inputs,
            conversationId: 'conv-123',
            files: $files,
            autoGenerateName: false
        );

        self::assertSame('test query', $request->getQuery());
        self::assertSame('test-user', $request->getUser());
        self::assertSame('blocking', $request->getResponseMode());
        self::assertSame($inputs, $request->getInputs());
        self::assertSame('conv-123', $request->getConversationId());
        self::assertSame($files, $request->getFiles());
        self::assertFalse($request->isAutoGenerateName());
    }
}
