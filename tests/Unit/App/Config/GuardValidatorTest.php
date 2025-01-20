<?php

namespace Kuick\Tests\App\Config;

use Kuick\App\Config\ConfigException;
use Kuick\App\Config\GuardConfig;
use Kuick\App\Config\GuardValidator;
use Kuick\Tests\Mocks\MockGuard;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kuick\App\Config\GuardValidator
 */
class GuardValidatorTest extends TestCase
{
    public function testIfCorrectGuardValidatorDoesNothing(): void
    {
        $guardConfig = new GuardConfig('/test', MockGuard::class);
        new GuardValidator($guardConfig);
        $this->assertTrue(true);
    }

    public function testIfEmptyPathRaisesException(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Guard path can not be empty');
        new GuardValidator(new GuardConfig('', MockGuard::class));
    }

    public function testIfInvalidMethodRaisesException(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Guard method invalid, path: /test, method: INVALID');
        new GuardValidator(new GuardConfig('/test', MockGuard::class, ['INVALID']));
    }

    public function testIfEmptyGuardClassNameRaisesException(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Guard controller class name should not be empty, path: /test');
        new GuardValidator(new GuardConfig('/test', ''));
    }

    public function testIfInexistentGuardClassNameRaisesException(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Guard class: "InexistentGuard" does not exist, path: /test');
        new GuardValidator(new GuardConfig('/test', 'InexistentGuard'));
    }

    public function testIfNotInvokableGuardClassNameRaisesException(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Guard class: "stdClass" is not invokable, path: /test');
        new GuardValidator(new GuardConfig('/test', 'stdClass'));
    }
}
