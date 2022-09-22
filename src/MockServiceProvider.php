<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Testing;

use Yiisoft\Di\ServiceProviderInterface;

final class MockServiceProvider implements ServiceProviderInterface
{
    private array $definitions = [];
    private array $extensions = [];

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function addDefinition(string $id, mixed $definition): void
    {
        $this->definitions[$id] = $definition;
    }
}
