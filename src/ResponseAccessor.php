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

    /**
     * @param string $version
     */
    public function withProtocolVersion($version): self
    {
        return $this->withResponse(
            $this->response->withProtocolVersion($version)
        );
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

    /**
     * @param string $name
     * @param string|string[] $value
     */
    public function withHeader($name, $value): self
    {
        return $this->withResponse(
            $this->response->withHeader($name, $value)
        );
    }

    /**
     * @param string $name
     * @param string|string[] $value
     */
    public function withAddedHeader($name, $value): self
    {
        return $this->withResponse(
            $this->response->withAddedHeader($name, $value)
        );
    }

    /**
     * @param string $name
     */
    public function withoutHeader($name): self
    {
        return $this->withResponse(
            $this->response->withoutHeader($name)
        );
    }

    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    public function withBody(StreamInterface $body): self
    {
        return $this->withResponse(
            $this->response->withBody($body)
        );
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     */
    public function withStatus($code, $reasonPhrase = ''): self
    {
        return $this->withResponse(
            $this->response->withStatus($code, $reasonPhrase)
        );
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    private function withResponse(ResponseInterface $response): self
    {
        $new = clone $this;
        $new->response = $response;
        return $new;
    }
}
