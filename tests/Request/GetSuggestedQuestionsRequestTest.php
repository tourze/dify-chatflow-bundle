<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DifyChatflowBundle\Request\GetSuggestedQuestionsRequest;

/**
 * @internal
 */
#[CoversClass(GetSuggestedQuestionsRequest::class)]
final class GetSuggestedQuestionsRequestTest extends RequestTestCase
{
    public function testGetRequestPath(): void
    {
        $request = new GetSuggestedQuestionsRequest(
            messageId: 'msg-123',
            user: 'test-user'
        );

        self::assertSame('/messages/msg-123/suggested', $request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        $request = new GetSuggestedQuestionsRequest(
            messageId: 'msg-123',
            user: 'test-user'
        );

        self::assertSame('GET', $request->getRequestMethod());
    }

    public function testGetRequestOptions(): void
    {
        $request = new GetSuggestedQuestionsRequest(
            messageId: 'msg-123',
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
    }

    public function testGetters(): void
    {
        $request = new GetSuggestedQuestionsRequest(
            messageId: 'msg-123',
            user: 'test-user'
        );

        self::assertSame('msg-123', $request->getMessageId());
        self::assertSame('test-user', $request->getUser());
    }
}
