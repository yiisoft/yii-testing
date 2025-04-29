<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Testing;

use Exception;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class FunctionalTester
{
    private ?TestApplicationRunner $application = null;
    private ?MockServiceProvider $mockServiceProvider = null;

    public function __construct(
        private readonly array $defaultMocks = [
            'Yiisoft\Session\SessionInterface' => 'Yiisoft\Session\NullSession',
        ]
    ) {
    }

    public function mockService(string $id, mixed $definition): void
    {
        $this->getMockServiceProvider()->setDefinition($id, $definition);
    }

    public function bootstrapApplication(?string $projectRootPath = null): void
    {
        if ($this->application !== null) {
            return;
        }

        if ($projectRootPath === null) {
            $projectRootPath = dirname(__DIR__, 5);
        }

        $this->application = new TestApplicationRunner(
            responseGrabber: new ResponseGrabber(),
            rootPath: $projectRootPath,
            debug: (bool)$_ENV['YII_DEBUG'],
            checkEvents: false,
            environment: $_ENV['YII_ENV'] ?? null,
        );
        $this->application->addProviders([$this->getMockServiceProvider()]);
    }

    public function doRequest(
        string $method,
        string $url,
        array $queryParams = [],
        array $postParams = [],
        mixed $body = null,
        array $headers = [],
    ): ResponseAccessor {
        $this->ensureApplicationLoaded();

        $this->application?->withRequest($method, $url, $queryParams, $postParams, $body, $headers);
        $this->application?->run();

        return $this->application?->responseGrabber?->getResponse() ?? throw new RuntimeException(
            'Either $application or $response is null'
        );
    }

    /**
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

    private function getMockServiceProvider(): MockServiceProvider
    {
        if ($this->mockServiceProvider === null) {
            $this->mockServiceProvider = new MockServiceProvider();
            $this->fillDefaultMocks($this->mockServiceProvider);
        }
        return $this->mockServiceProvider;
    }

    private function fillDefaultMocks(MockServiceProvider $mockServiceProvider): void
    {
        foreach ($this->defaultMocks as $key => $value) {
            if (interface_exists($key) || class_exists($key)) {
                $mockServiceProvider->setDefinition($key, $value);
            }
        }
    }
}
