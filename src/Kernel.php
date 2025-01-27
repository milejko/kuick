<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick-framework)
 *
 * @link       https://github.com/milejko/kuick-framework
 * @copyright  Copyright (c) 2010-2025 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://github.com/milejko/kuick-framework?tab=MIT-1-ov-file#readme New BSD License
 */

namespace Kuick\Framework;

use ErrorException;
use Kuick\Framework\DependencyInjection\ContainerCreator;
use Kuick\Framework\Events\ExceptionRaisedEvent;
use Kuick\Framework\Events\KernelCreatedEvent;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Application Kernel
 */
class Kernel implements KernelInterface
{
    private ContainerInterface $container;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(private string $projectDir)
    {
        // building DI container
        $this->container = (new ContainerCreator())($projectDir);
        $this->eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $listenerProvider = $this->container->get(ListenerProviderInterface::class);
        // registering listeners "on the fly", as they can depend on EventDispatcher
        foreach ($this->container->get(self::DI_LISTENERS_KEY) as $listener) {
            $listenerProvider->registerListener($listener->pattern, $this->container->get($listener->listenerClassName), $listener->priority);
        }
        $this->eventDispatcher->dispatch(new KernelCreatedEvent($this));
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
