<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Testing\Tests;

use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\Yii\Testing\ResponseAccessor;
use Yiisoft\Yii\Testing\ResponseGrabber;

class ResponseGrabberTest extends TestCase
{
    public function testGetResponseWillReturnResponseAccessorSetResponse(): void
    {
        $responseStub = new Response(200, body: '{"key": "value"}', reason: 'Ok with body', version: '1.1');

        $responseGrabber = new ResponseGrabber();
        $responseGrabber->setResponse($responseStub);

        $this->assertEquals(new ResponseAccessor($responseStub), $responseGrabber->getResponse());
    }

    public function testGetResponseWillThrowExceptionIfSetResponseIsCalledWithNullParameter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Response is null');

        $responseGrabber = new ResponseGrabber();
        $responseGrabber->setResponse(null);
        $responseGrabber->getResponse();
    }

    public function testGetResponseWillThrowExceptionIfResponseIsNotSet(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Response is null');

        (new ResponseGrabber())->getResponse();
    }
}
