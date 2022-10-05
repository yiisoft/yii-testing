<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Testing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class ResponseAccessor implements ResponseInterface
{
    public function __construct(private ResponseInterface $response)
    {
    }

    public function getContent(): string
    {
        $stream = $this->response->getBody();
        $stream->rewind();
        return $stream->getContents();
    }

    public function getContentAsJson(
        int $flags = JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
    ): mixed {
        return json_decode($this->getContent(), flags: $flags);
    }

    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion($version): ResponseAccessor
    {
        $response = $this->response->withProtocolVersion($version);
        return new self($response);
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader($name, $value): ResponseAccessor
    {
        $response = $this->response->withHeader($name, $value);
        return new self($response);
    }

    public function withAddedHeader($name, $value): ResponseAccessor
    {
        $response = $this->response->withAddedHeader($name, $value);
        return new self($response);
    }

    public function withoutHeader($name): ResponseAccessor
    {
        $response = $this->response->withoutHeader($name);
        return new self($response);
    }

    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    public function withBody(StreamInterface $body): ResponseAccessor
    {
        $response = $this->response->withBody($body);
        return new self($response);
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = ''): ResponseAccessor
    {
        $response = $this->response->withStatus($code, $reasonPhrase);
        return new self($response);
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
