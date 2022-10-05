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
     * @param string|null $environment The environment name.
     */
    public function __construct(
        public ResponseGrabber $responseGrabber,
        string $rootPath,
        bool $debug,
        ?string $environment,
        protected string $definitionEnvironment = 'web',
    ) {
        parent::__construct($rootPath, $debug, $environment);
        $this->bootstrapGroup = 'bootstrap-web';
        $this->eventsGroup = 'events-web';
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

        $config = $this->getConfig();
        $this->container = $this->getContainer($config, $this->definitionEnvironment);

        $this->runBootstrap($config, $this->container);
        $this->checkEvents($config, $this->container);
    }

    protected function createDefaultContainer(ConfigInterface $config, string $definitionEnvironment): Container
    {
        $containerConfig = ContainerConfig::create()->withValidate($this->debug);

        if ($config->has($definitionEnvironment)) {
            $containerConfig = $containerConfig->withDefinitions($config->get($definitionEnvironment));
        }

        $providers = [];

        if ($config->has("providers-$definitionEnvironment")) {
            $providers = $config->get("providers-$definitionEnvironment");
        }

        if ($this->providers !== []) {
            $providers = array_merge($providers, $this->providers);
        }

        if ($providers !== []) {
            $containerConfig = $containerConfig->withProviders($providers);
        }

        if ($config->has("delegates-$definitionEnvironment")) {
            $containerConfig = $containerConfig->withDelegates($config->get("delegates-$definitionEnvironment"));
        }

        if ($config->has("tags-$definitionEnvironment")) {
            $containerConfig = $containerConfig->withTags($config->get("tags-$definitionEnvironment"));
        }

        $containerConfig = $containerConfig->withDefinitions(
            array_merge($containerConfig->getDefinitions(), [ConfigInterface::class => $config])
        );

        return new Container($containerConfig);
    }

    /**
     * @param ServiceProviderInterface[] $providers
     */
    public function addProviders(array $providers): void
    {
        $this->providers = array_merge($this->providers, $providers);
    }
}
