<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Testing;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class FunctionalTestCase extends TestCase
{
    protected ?FunctionalTester $tester;

    protected function setUp(): void
    {
        $this->tester = new FunctionalTester();
    }

    public function mockService(string $id, mixed $definition): void
    {
        $this->tester?->mockService($id, $definition);
    }

    protected function bootstrapApplication(string $definitionEnvironment = 'web', ?string $projectRootPath = null): void
    {
        $this->tester?->bootstrapApplication($definitionEnvironment, $projectRootPath);
    }

    protected function doRequest(string $method, string $url): ResponseAccessor
    {
        return $this->tester?->doRequest($method, $url) ?? throw new Exception('Either $tester or $response is null');
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->tester?->getContainer()?? throw new Exception('Either $tester or $container is null');
    }
}
