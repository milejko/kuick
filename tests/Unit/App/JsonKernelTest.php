<?php

namespace Tests\Kuick\App;

use DivisionByZeroError;
use Kuick\App\AppException;
use Kuick\App\JsonKernel;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

/**
 * @covers \Kuick\App\JsonKernel
 * @covers \Kuick\App\KernelAbstract
 */
class JsonKernelTest extends TestCase
{
    /**
     * Needs to be run in separate process, cause emmiter sends headers
     * @runInSeparateProcess
     */
    public function testIfNotFoundRouteEmmitsNotFoundResponse(): void
    {
        $jk = new JsonKernel(dirname(__DIR__) . '/../Mocks/MockProjectDir');
        $request = new ServerRequest('GET', 'something');
        ob_start();
        $jk($request);
        $data = ob_get_clean();
        assertEquals('{"error":"Not found"}', $data);
    }

    /**
     * Needs to be run in separate process, cause DI builder won't work other way
     * @runInSeparateProcess
     */
    public function testIfContainerReturnsBuiltContainer(): void
    {
        ob_start();
        $jk = new JsonKernel(dirname(__DIR__) . '/../Mocks/MockProjectDir');
        ob_end_clean();
        $container = $jk->getContainer();
        assertEquals('Testing App', $container->get('kuick.app.name'));
    }
}
