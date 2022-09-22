<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Testing;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class FunctionalTester
{
    private ?TestApplicationRunner $application = null;
    private ?MockServiceProvider $mockServiceProvider = null;

    public function mockService(string $id, mixed $definition): void
    {
        $this->mockServiceProvider ??= new MockServiceProvider();
        $this->mockServiceProvider->addDefinition($id, $definition);
    }

    public function bootstrapApplication(string $definitionEnvironment = 'web'): void
    {
        if ($this->application !== null) {
            return;
        }

        $this->application = new TestApplicationRunner(
            new ResponseGrabber(),
            dirname(__DIR__, 2),
            false,
            null,
            $definitionEnvironment
        );
        $this->mockServiceProvider ??= new MockServiceProvider();
        $this->application->addProviders([$this->mockServiceProvider]);
    }

    public function doRequest(string $method, string $url): ResponseInterface
    {
        $this->ensureApplicationLoaded();

        $this->application?->withRequest($method, $url);
        $this->application?->run();

        return $this->application?->responseGrabber?->getResponse() ?? throw new RuntimeException('Either $application or $response is null');
    }

    /**
     * @psalm-return ContainerInterface
     * @return ContainerInterface
     * @psalm-suppress NullableReturnStatement
     */
    public function getContainer(): ContainerInterface
    {
        $this->ensureApplicationLoaded();

        $this->application?->preloadContainer();

        return $this->application->container ?? throw new Exception('Container was not set.');
    }

    private function ensureApplicationLoaded(): void
    {
        if ($this->application === null) {
            throw new RuntimeException(
                'The application was not initialized. Initialize the application before the request: `$this->bootstrapApplication(\'web\')`.'
            );
        }
    }
}
