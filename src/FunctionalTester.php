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
        private array $defaultMocks = [
            'Yiisoft\Session\SessionInterface' => 'Yiisoft\Session\NullSession',
        ]
    ) {
    }

    public function mockService(string $id, mixed $definition): void
    {
        $this->getMockServiceProvider()->addDefinition($id, $definition);
    }

    public function bootstrapApplication(string $definitionEnvironment = 'web', ?string $projectRootPath = null): void
    {
        if ($this->application !== null) {
            return;
        }

        if ($projectRootPath === null) {
            $projectRootPath = dirname(__DIR__, 5);
        }

        $this->application = new TestApplicationRunner(
            new ResponseGrabber(),
            $projectRootPath,
            false,
            $_ENV['YII_ENV'],
            $definitionEnvironment
        );
        $this->application->addProviders([$this->getMockServiceProvider()]);
    }

    public function doRequest(string $method, string $url): ResponseAccessor
    {
        $this->ensureApplicationLoaded();

        $this->application?->withRequest($method, $url);
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
                $mockServiceProvider->addDefinition($key, $value);
            }
        }
    }
}
