<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Request;

use PHPUnit\Framework\Attributes\CoversClass;
use HttpClientBundle\Tests\Request\RequestTestCase;
use Tourze\DifyChatflowBundle\Request\GetConversationVariablesRequest;

/**
 * @internal
 */
#[CoversClass(GetConversationVariablesRequest::class)]
final class GetConversationVariablesRequestTest extends RequestTestCase
{
    public function testGetRequestPath(): void
    {
        $request = new GetConversationVariablesRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertSame('/conversations/conv-123/variables', $request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        $request = new GetConversationVariablesRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertSame('GET', $request->getRequestMethod());
    }

    public function testGetRequestOptionsWithRequiredParameters(): void
    {
        $request = new GetConversationVariablesRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        $options = $request->getRequestOptions();

        self::assertArrayHasKey('headers', $options);
        self::assertArrayHasKey('query', $options);
        self::assertIsArray($options['headers']);
        self::assertSame('application/json', $options['headers']['Content-Type']);

        self::assertIsArray($options['query']);
        $query = $options['query'];
        self::assertSame('test-user', $query['user']);
        self::assertSame(20, $query['limit']);
        self::assertArrayNotHasKey('last_id', $query);
        self::assertArrayNotHasKey('variable_name', $query);
    }

    public function testGetRequestOptionsWithOptionalParameters(): void
    {
        $request = new GetConversationVariablesRequest(
            conversationId: 'conv-123',
            user: 'test-user',
            lastId: 'last-456',
            limit: 50,
            variableName: 'test-variable'
        );

        $options = $request->getRequestOptions();
        self::assertIsArray($options['query']);
        $query = $options['query'];

        self::assertSame('test-user', $query['user']);
        self::assertSame(50, $query['limit']);
        self::assertSame('last-456', $query['last_id']);
        self::assertSame('test-variable', $query['variable_name']);
    }

    public function testGetters(): void
    {
        $request = new GetConversationVariablesRequest(
            conversationId: 'conv-123',
            user: 'test-user',
            lastId: 'last-456',
            limit: 50,
            variableName: 'test-variable'
        );

        self::assertSame('conv-123', $request->getConversationId());
        self::assertSame('test-user', $request->getUser());
        self::assertSame('last-456', $request->getLastId());
        self::assertSame(50, $request->getLimit());
        self::assertSame('test-variable', $request->getVariableName());
    }

    public function testGettersWithNullOptionalParameters(): void
    {
        $request = new GetConversationVariablesRequest(
            conversationId: 'conv-123',
            user: 'test-user'
        );

        self::assertNull($request->getLastId());
        self::assertNull($request->getVariableName());
    }
}
