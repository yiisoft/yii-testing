<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Testing\Tests;

use GuzzleHttp\Psr7\Stream;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Yiisoft\Yii\Testing\ResponseAccessor;

class ResponseAccessorTest extends TestCase
{
    public function testGetContent(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $this->assertEquals('{"key": "value"}', $accessor->getContent());
    }

    public function testGetContentAsJson(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $this->assertEquals(["key" => "value"], $accessor->getContentAsJson());
    }

    public function testGetProtocolVersion(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $this->assertEquals('1.1', $accessor->getProtocolVersion());
    }

    public function testWithProtocolVersion(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $this->assertEquals('1.1', $accessor->getProtocolVersion());

        $newAccessor = $accessor->withProtocolVersion('1.2');

        $this->assertNotSame($accessor, $newAccessor);
        $this->assertNotSame($response, $newAccessor->getResponse());
        $this->assertEquals('1.2', $newAccessor->getProtocolVersion());
    }

    public function testGetHeaders(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $this->assertEquals([], $accessor->getHeaders());
    }

    public function testHasHeader(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $this->assertFalse($accessor->hasHeader('header-name'));
    }

    public function testGetHeader(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $this->assertEquals([], $accessor->getHeader('Access'));
    }

    public function testGetHeaderLine(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $this->assertEquals('', $accessor->getHeaderLine('Access'));
    }

    public function testWithHeader(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $newAccessor = $accessor->withHeader('Access', 'plain/text');

        $this->assertNotSame($accessor, $newAccessor);
        $this->assertNotSame($response, $newAccessor->getResponse());
        $this->assertEquals(['plain/text'], $newAccessor->getHeader('Access'));
    }

    public function testWithAddedHeader(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $newAccessor = $accessor->withAddedHeader('Access', 'plain/text');

        $this->assertNotSame($accessor, $newAccessor);
        $this->assertNotSame($response, $newAccessor->getResponse());
        $this->assertEquals(['plain/text'], $newAccessor->getHeader('Access'));
    }

    public function testWithoutHeader(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $newAccessor = $accessor->withAddedHeader('Access', 'plain/text');
        $this->assertEquals(['plain/text'], $newAccessor->getHeader('Access'));

        $newAccessor = $newAccessor->withoutHeader('Access');

        $this->assertNotSame($accessor, $newAccessor);
        $this->assertNotSame($response, $newAccessor->getResponse());
        $this->assertEquals([], $newAccessor->getHeader('Access'));
    }

    public function testGetBody(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $stream = $accessor->getBody();
        $this->assertInstanceOf(StreamInterface::class, $stream);

        $stream->rewind();
        $content = $stream->getContents();

        $this->assertEquals('{"key": "value"}', $content);
    }

    public function testWithBody(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $newAccessor = $accessor->withBody(\Nyholm\Psr7\Stream::create('{"key2": "value2"}'));

        $this->assertNotSame($accessor, $newAccessor);
        $this->assertNotSame($response, $newAccessor->getResponse());
        $this->assertEquals('{"key2": "value2"}', $newAccessor->getContent());
    }

    public function testGetStatusCode(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $this->assertEquals(200, $accessor->getStatusCode());
    }

    public function testWithStatus(): void
    {
        [$response, $accessor] = $this->createResponseAndAccessor();

        $this->assertEquals(200, $accessor->getStatusCode());
        $this->assertEquals('Ok with body', $accessor->getReasonPhrase());

        $newAccessor = $accessor->withStatus(201, 'Ok without body');

        $this->assertNotSame($accessor, $newAccessor);
        $this->assertNotSame($response, $newAccessor->getResponse());
        $this->assertEquals(201, $newAccessor->getStatusCode());
        $this->assertEquals('Ok without body', $newAccessor->getReasonPhrase());
    }

    private function createResponseAndAccessor(): array
    {
        $response = new Response(200, body: '{"key": "value"}', reason: 'Ok with body', version: '1.1');
        $accessor = new ResponseAccessor($response);

        return [$response, $accessor];
    }
}
