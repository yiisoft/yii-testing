<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Testing\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Testing\MockServiceProvider;

class MockServiceProviderTest extends TestCase
{
    public function testGetDefinitionsWillReturnDefinitionsSetInArray(): void
    {
        $mockServiceProvider = new MockServiceProvider();
        $mockServiceProvider->setDefinition('example-id-001', ['class' => 'Example1\Class1\Position1']);
        $mockServiceProvider->setDefinition('example-id-002', 'Example1\Class2\Position2');
        $mockServiceProvider->setDefinition('example-id-003', 'Example3\Class3\Position3');

        $this->assertEquals([
            'example-id-001' => ['class' => 'Example1\Class1\Position1'],
            'example-id-002' => 'Example1\Class2\Position2',
            'example-id-003' => 'Example3\Class3\Position3',
        ], $mockServiceProvider->getDefinitions());
    }

    public function testGetExtensionsWillReturnEmptyArray(): void
    {
        $this->assertEmpty((new MockServiceProvider())->getExtensions());
    }
}
