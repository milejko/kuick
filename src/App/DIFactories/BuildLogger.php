<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link       https://github.com/milejko/kuick
 * @copyright  Copyright (c) 2010-2024 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\App\DIFactories;

use DateTimeZone;
use Kuick\App\AppException;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Logger factory
 */
class BuildLogger extends FactoryAbstract
{
    public function __invoke(): void
    {
        $this->builder->addDefinitions([LoggerInterface::class => function (ContainerInterface $container): LoggerInterface {
            $logger = new Logger($container->get('kuick.app.name'));
            $logger->useMicrosecondTimestamps((bool) $container->get('kuick.app.monolog.usemicroseconds'));
            $logger->setTimezone(new DateTimeZone($container->get('kuick.app.timezone')));
            $handlers = $container->get('kuick.app.monolog.handlers');
            $defaultLevel = $container->get('kuick.app.monolog.level') ?? LogLevel::WARNING;
            !is_array($handlers) && throw new AppException('Logger handlers are invalid, should be an array');
            foreach ($handlers as $handler) {
                $type = $handler['type'] ?? throw new AppException('Logger handler type not defined');
                $level = $handler['level'] ?? $defaultLevel;
                //@TODO: extract handler factory to the different class and add missing types
                switch ($type) {
                    case 'fingersCrossed':
                        //@TODO: add more nested handler options
                        $nestedHandler = new StreamHandler($handler['nestedPath'] ?? 'php://stdout', $handler['nestedLevel'] ?? 'debug');
                        $logger->pushHandler(new FingersCrossedHandler($nestedHandler, $level));
                        break;
                    case 'firePHP':
                        $logger->pushHandler((new FirePHPHandler($level)));
                        break;
                    case 'stream':
                        $logger->pushHandler((new StreamHandler($handler['path'] ?? 'php://stdout', $level)));
                        break;
                    default:
                        throw new AppException('Unknown Monolog handler: ' . $type);
                }
            }
            return $logger;
        }]);
    }
}
