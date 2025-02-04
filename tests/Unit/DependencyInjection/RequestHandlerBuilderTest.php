<?php

namespace Tests\Unit\App\DependencyInjection;

use DI\ContainerBuilder;
use Kuick\Framework\Config\ConfigException;
use Kuick\Framework\DependencyInjection\RequestHandlerBuilder;
use Kuick\Framework\SystemCache;
use Kuick\Framework\SystemCacheInterface;
use Kuick\Http\Server\ExceptionHtmlRequestHandler;
use Kuick\Http\Server\FallbackRequestHandlerInterface;
use Kuick\Http\Server\JsonNotFoundRequestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @covers Kuick\Framework\DependencyInjection\RequestHandlerBuilder
 */
class RequestHandlerBuilderTest extends TestCase
{
    private static string $projectDir;
    private static string $invalidProjectDir;
    private static string $invalidProjectDir2;
    private static string $invalidProjectDir3;

    public static function setUpBeforeClass(): void
    {
        self::$projectDir = realpath(dirname(__DIR__) . '/Mocks/project-dir');
        self::$invalidProjectDir = realpath(dirname(__DIR__) . '/Mocks/invalid-project-dir');
        self::$invalidProjectDir2 = realpath(dirname(__DIR__) . '/Mocks/invalid-project-dir-2');
        self::$invalidProjectDir3 = realpath(dirname(__DIR__) . '/Mocks/invalid-project-dir-3');
    }

    public function testIfRequestHandlerIsBuilt(): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            SystemCacheInterface::class => new SystemCache(self::$projectDir, 'dev'),
            LoggerInterface::class => new NullLogger(),
            FallbackRequestHandlerInterface::class => new JsonNotFoundRequestHandler(),
            'kuick.app.projectDir' => self::$projectDir,
        ]);
        (new RequestHandlerBuilder($builder))();
        $container = $builder->build();
        $this->assertInstanceOf(RequestHandlerInterface::class, $container->get(RequestHandlerInterface::class));
    }

    public function testIfFailedConfigRaisesException(): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            SystemCacheInterface::class => new SystemCache(self::$invalidProjectDir, 'dev'),
            LoggerInterface::class => new NullLogger(),
            FallbackRequestHandlerInterface::class => new JsonNotFoundRequestHandler(),
            'kuick.app.projectDir' => self::$invalidProjectDir,
        ]);
        $this->expectException(ConfigException::class);
        (new RequestHandlerBuilder($builder))();
        $container = $builder->build();
        $container->get(RequestHandlerInterface::class);
    }

    public function testIfFailedConfigRaisesAnotherException(): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            SystemCacheInterface::class => new SystemCache(self::$invalidProjectDir2, 'dev'),
            LoggerInterface::class => new NullLogger(),
            FallbackRequestHandlerInterface::class => new JsonNotFoundRequestHandler(),
            'kuick.app.projectDir' => self::$invalidProjectDir2,
        ]);
        $this->expectException(ConfigException::class);
        (new RequestHandlerBuilder($builder))();
        $container = $builder->build();
        $container->get(RequestHandlerInterface::class);
    }

    public function testIfFailedConfigRaisesYetAnotherException(): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            SystemCacheInterface::class => new SystemCache(self::$invalidProjectDir3, 'dev'),
            LoggerInterface::class => new NullLogger(),
            FallbackRequestHandlerInterface::class => new JsonNotFoundRequestHandler(),
            'kuick.app.projectDir' => self::$invalidProjectDir3,
        ]);
        $this->expectException(ConfigException::class);
        (new RequestHandlerBuilder($builder))();
        $container = $builder->build();
        $container->get(RequestHandlerInterface::class);
    }
}
