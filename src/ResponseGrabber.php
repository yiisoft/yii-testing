<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Testing;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class ResponseGrabber
{
    private ?ResponseInterface $response = null;

    public function getResponse(): ResponseInterface
    {
        return $this->response ?? throw new RuntimeException('Response is null');
    }

    public function setResponse(?ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
