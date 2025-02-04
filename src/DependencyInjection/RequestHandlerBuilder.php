<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick-framework)
 *
 * @link       https://github.com/milejko/kuick-framework
 * @copyright  Copyright (c) 2010-2025 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://github.com/milejko/kuick-framework?tab=MIT-1-ov-file#readme New BSD License
 */

namespace Kuick\Framework\DependencyInjection;

use DI\ContainerBuilder;
use Kuick\Framework\Config\ConfigException;
use Kuick\Framework\Kernel;
use Kuick\Framework\SystemCacheInterface;
use Kuick\Http\Server\FallbackRequestHandlerInterface;
use Kuick\Http\Server\StackRequestHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 *
 */
class RequestHandlerBuilder
{
    public function __construct(private ContainerBuilder $builder)
    {
    }

    public function __invoke(): void
    {
        // default request handler is a Stack Request Handler (by Kuick)
        $this->builder->addDefinitions([RequestHandlerInterface::class => function (ContainerInterface $container, LoggerInterface $logger, SystemCacheInterface $cache): RequestHandlerInterface {
            $requestHandler = new StackRequestHandler($container->get(FallbackRequestHandlerInterface::class));
            foreach ((new ConfigIndexer($cache, $logger))->getConfigFiles($container->get(Kernel::DI_PROJECT_DIR_KEY), 'middlewares') as $middlewareFile) {
                $middlewareClassNames = include $middlewareFile;
                foreach ($middlewareClassNames as $middlewareClassName) {
                    if (!is_string($middlewareClassName)) {
                        throw new ConfigException('Middleware must be a string');
                    }
                    if (!class_exists($middlewareClassName)) {
                        throw new ConfigException('Middleware class does not exist: ' . $middlewareClassName);
                    }
                    if (!in_array(MiddlewareInterface::class, class_implements($middlewareClassName))) {
                        throw new ConfigException('Middleware must implement: ' . MiddlewareInterface::class);
                    }
                    $logger->debug('Adding middleware: ' . $middlewareClassName);
                    $requestHandler->addMiddleware($container->get($middlewareClassName));
                }
            }
            return $requestHandler;
        }]);
    }
}
