<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Testing;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class ResponseGrabber
{
    private ?ResponseAccessor $response = null;

    public function getResponse(): ResponseAccessor
    {
        return $this->response !== null
            ? $this->response
            : throw new RuntimeException('Response is null');
    }

    public function setResponse(?ResponseInterface $response): void
    {
        $this->response = $response === null ? null : new ResponseAccessor($response);
    }
}
