<?php

/**
 * This class has been auto-generated by PHP-DI.
 */
class CompiledContainer extends DI\CompiledContainer
{
    const METHOD_MAPPING = array (
    'Kuick\\Http\\Server\\Router' => 'get1',
    'Psr\\Log\\LoggerInterface' => 'get2',
    'kuick.app.env' => 'get3',
    'kuick.app.project.dir' => 'get4',
    );

    protected function get1()
    {
        return $this->resolveFactory(static function (\Psr\Container\ContainerInterface $container): \Kuick\Http\Server\Router {
            $logger = $container->get(\Psr\Log\LoggerInterface::class);
            $projectDir = $container->get(\Kuick\App\AppDIContainerBuilder::PROJECT_DIR_CONFIGURATION_KEY);
            $cacheFile = $projectDir . \Kuick\App\AppDIContainerBuilder::CACHE_PATH . \Kuick\App\DIFactories\BuildRouter::CACHE_FILE;
            $cachedRoutes = @include $cacheFile;
            $routes = [];
            if (
                !empty($cachedRoutes) &&
                \Kuick\App\KernelAbstract::ENV_PROD === $container->get(\Kuick\App\AppDIContainerBuilder::APP_ENV_CONFIGURATION_KEY)
            ) {
                $logger->debug('Routes loaded from cache');
                $routes = $cachedRoutes;
            }
            if (empty($routes)) {
                //@TODO: extract route parsing to the external class
                //app config (normal priority)
                foreach (\Kuick\App\DIFactories\BuildRouter::ROUTE_LOCATIONS as $routeLocation) {
                    $routeIterator = new \GlobIterator($projectDir . $routeLocation, \FilesystemIterator::KEY_AS_FILENAME);
                    foreach ($routeIterator as $routeFile) {
                        $routes = array_merge($routes, include $routeFile);
                    }
                }
                //validating routes
                //decorating routes with available controller arguments
                foreach ($routes as $routeKey => $route) {
                    (new \Kuick\App\DIFactories\Utils\RouteValidator())($route);
                    $routes[$routeKey]['arguments'][$route['controller']] = (new \Kuick\App\DIFactories\Utils\ClassInvokeArgumentReflector())($route['controller']);
                    if (!isset($route['guards'])) {
                        continue;
                    }
                    foreach ($route['guards'] as $guard) {
                        $routes[$routeKey]['arguments'][$guard] = (new \Kuick\App\DIFactories\Utils\ClassInvokeArgumentReflector())($guard);
                    }
                }
                if (!file_exists(dirname($cacheFile))) {
                    mkdir(dirname($cacheFile));
                }
                file_put_contents($cacheFile, sprintf('<?php return %s;', var_export($routes, true)));
                $logger->notice('Routes analyzed, cache written');
            }
            return (new \Kuick\Http\Server\Router($container->get(\Psr\Log\LoggerInterface::class)))->setRoutes($routes);
        }, 'Kuick\\Http\\Server\\Router');
    }

    protected function get2()
    {
        return $this->resolveFactory(static function (\Psr\Container\ContainerInterface $container): \Psr\Log\LoggerInterface {
            $logger = new \Monolog\Logger($container->get('kuick.app.name'));
            $logger->useMicrosecondTimestamps((bool) $container->get('kuick.app.monolog.usemicroseconds'));
            $logger->setTimezone(new \DateTimeZone($container->get('kuick.app.timezone')));
            $handlers = $container->get('kuick.app.monolog.handlers');
            $defaultLevel = $container->get('kuick.app.monolog.level') ?? \Psr\Log\LogLevel::WARNING;
            !is_array($handlers) && throw new \Kuick\App\AppException('Logger handlers are invalid, should be an array');
            foreach ($handlers as $handler) {
                $type = $handler['type'] ?? throw new \Kuick\App\AppException('Logger handler type not defined');
                $level = $handler['level'] ?? $defaultLevel;
                //@TODO: extract handler factory to the different class and add missing types
                switch ($type) {
                    case 'fingersCrossed':
                        //@TODO: add more nested handler options
                        $nestedHandler = new \Monolog\Handler\StreamHandler($handler['nestedPath'] ?? 'php://stdout', $handler['nestedLevel'] ?? 'debug');
                        $logger->pushHandler(new \Monolog\Handler\FingersCrossedHandler($nestedHandler, $level));
                        break;
                    case 'firePHP':
                        $logger->pushHandler((new \Monolog\Handler\FirePHPHandler($level)));
                        break;
                    case 'stream':
                        $logger->pushHandler((new \Monolog\Handler\StreamHandler($handler['path'] ?? 'php://stdout', $level)));
                        break;
                    default:
                        throw new \Kuick\App\AppException('Unknown Monolog handler: ' . $type);
                }
            }
            return $logger;
        }, 'Psr\\Log\\LoggerInterface');
    }

    protected function get3()
    {
        return 'prod';
    }

    protected function get4()
    {
        return '/var/www/html/tests/Mocks/MockProjectDir/empty';
    }
}
