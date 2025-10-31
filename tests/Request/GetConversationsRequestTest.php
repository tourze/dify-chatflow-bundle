<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Request;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DifyChatflowBundle\Request\GetConversationsRequest;

/**
 * @internal
 */
#[CoversClass(GetConversationsRequest::class)]
final class GetConversationsRequestTest extends RequestTestCase
{
    public function testGetRequestPath(): void
    {
        $request = new GetConversationsRequest(
            user: 'test-user'
        );

        self::assertSame('/conversations', $request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        $request = new GetConversationsRequest(
            user: 'test-user'
        );

        self::assertSame('GET', $request->getRequestMethod());
    }

    public function testGetRequestOptionsWithRequiredParameters(): void
    {
        $request = new GetConversationsRequest(
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
        self::assertSame('-updated_at', $query['sort_by']);
        self::assertArrayNotHasKey('last_id', $query);
    }

    public function testGetRequestOptionsWithOptionalParameters(): void
    {
        $request = new GetConversationsRequest(
            user: 'test-user',
            lastId: 'last-123',
            limit: 50,
            sortBy: 'created_at'
        );

        $options = $request->getRequestOptions();

        self::assertIsArray($options['query']);
        $query = $options['query'];

        self::assertSame('test-user', $query['user']);
        self::assertSame('last-123', $query['last_id']);
        self::assertSame(50, $query['limit']);
        self::assertSame('created_at', $query['sort_by']);
    }

    public function testGetters(): void
    {
        $request = new GetConversationsRequest(
            user: 'test-user',
            lastId: 'last-123',
            limit: 50,
            sortBy: 'created_at'
        );

        self::assertSame('test-user', $request->getUser());
        self::assertSame('last-123', $request->getLastId());
        self::assertSame(50, $request->getLimit());
        self::assertSame('created_at', $request->getSortBy());
    }

    public function testGettersWithNullLastId(): void
    {
        $request = new GetConversationsRequest(
            user: 'test-user'
        );

        self::assertNull($request->getLastId());
    }
}
