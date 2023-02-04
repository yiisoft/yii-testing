<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Testing;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Di\ServiceProviderInterface;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\Yii\Http\Application;
use Yiisoft\Yii\Http\Handler\ThrowableHandler;
use Yiisoft\Yii\Runner\ApplicationRunner;
use Yiisoft\Yii\Runner\Http\ServerRequestFactory;

final class TestApplicationRunner extends ApplicationRunner
{
    private array $requestParameters = [];
    public ?ContainerInterface $container = null;
    /**
     * @var ServiceProviderInterface[]
     */
    private array $providers = [];

    /**
     * @param string $rootPath The absolute path to the project root.
     * @param bool $debug Whether the debug mode is enabled.
     * @param bool $checkEvents Whether to check events' configuration.
     * @param string|null $environment The environment name.
     * @param string $bootstrapGroup The bootstrap configuration group name.
     * @param string $eventsGroup The events' configuration group name.
     * @param string $diGroup The container definitions' configuration group name.
     * @param string $diProvidersGroup The container providers' configuration group name.
     * @param string $diDelegatesGroup The container delegates' configuration group name.
     * @param string $diTagsGroup The container tags' configuration group name.
     * @param string $paramsGroup The configuration parameters group name.
     * @param array $nestedParamsGroups Configuration group names that included into configuration parameters group.
     * This is needed for recursive merging of parameters.
     * @param array $nestedEventsGroups Configuration group names that included into events' configuration group. This
     * is needed for reverse and recursive merge of events' configurations.
     *
     * @psalm-param list<string> $nestedParamsGroups
     * @psalm-param list<string> $nestedEventsGroups
     */
    public function __construct(
        public ResponseGrabber $responseGrabber,
        string $rootPath,
        bool $debug = false,
        bool $checkEvents = false,
        ?string $environment = null,
        string $bootstrapGroup = 'bootstrap-web',
        string $eventsGroup = 'events-web',
        string $diGroup = 'di-web',
        string $diProvidersGroup = 'di-providers-web',
        string $diDelegatesGroup = 'di-delegates-web',
        string $diTagsGroup = 'di-tags-web',
        string $paramsGroup = 'params-web',
        array $nestedParamsGroups = ['params'],
        array $nestedEventsGroups = ['events'],
    ) {
        parent::__construct(
            $rootPath,
            $debug,
            $checkEvents,
            $environment,
            $bootstrapGroup,
            $eventsGroup,
            $diGroup,
            $diProvidersGroup,
            $diDelegatesGroup,
            $diTagsGroup,
            $paramsGroup,
            $nestedParamsGroups,
            $nestedEventsGroups,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function run(): void
    {
        $this->preloadContainer();

        /** @var ContainerInterface $container */
        $container = $this->container;

        /** @var Application $application */
        $application = $container->get(Application::class);

        /**
         * @var ServerRequestInterface
         * @psalm-suppress MixedMethodCall
         */
        $serverRequest = $container
            ->get(ServerRequestFactory::class)
            ->createFromParameters(
                ...$this->requestParameters,
            );

        /**
         * @var ResponseInterface|null $response
         */
        $response = null;
        try {
            $application->start();
            $response = $application->handle($serverRequest);
        } catch (Throwable $throwable) {
            $handler = new ThrowableHandler($throwable);
            /**
             * @psalm-suppress MixedMethodCall
             */
            $response = $container
                ->get(ErrorCatcher::class)
                ->process($serverRequest, $handler);
        } finally {
            $application->afterEmit($response ?? null);
            $application->shutdown();
            $this->responseGrabber->setResponse($response);
        }
    }

    public function withRequest(
        string $method,
        string $url,
        array $queryParams = [],
        array $postParams = [],
        mixed $body = null,
        array $headers = [],
        array $cookies = [],
        array $files = [],
    ): void {
        $this->requestParameters = [
            'server' => [
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => $method,
                'SERVER_PROTOCOL' => '1.1',
                'REQUEST_URI' => $url,
            ],
            'headers' => $headers,
            'cookies' => $cookies,
            'get' => $queryParams,
            'post' => $postParams,
            'files' => $files,
            'body' => $body,
        ];
    }

    public function preloadContainer(): void
    {
        /**
         * @psalm-suppress UnresolvableInclude
         */
        require_once $this->rootPath . '/autoload.php';

        $this->container = $this->getContainer();

        $this->runBootstrap();
        $this->checkEvents();
    }

    /**
     * @param ServiceProviderInterface[] $providers
     */
    public function addProviders(array $providers): void
    {
        $this->providers = array_merge($this->providers, $providers);
    }
}
