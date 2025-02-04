<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick-framework)
 *
 * @link       https://github.com/milejko/kuick-framework
 * @copyright  Copyright (c) 2010-2025 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://github.com/milejko/kuick-framework?tab=MIT-1-ov-file#readme New BSD License
 */

namespace Kuick\Framework;

use Psr\Container\ContainerInterface;

/**
 * Kernel Interface
 */
interface KernelInterface
{
    public const APP_ENV = 'APP_ENV';

    public const DI_APP_ENV_KEY = 'kuick.app.env';
    public const DI_APP_NAME_KEY = 'kuick.app.name';
    public const DI_PROJECT_DIR_KEY = 'kuick.app.projectDir';
    public const DI_LISTENERS_KEY = 'kuick.app.listeners';

    public const ENV_DEV = 'dev';
    public const ENV_PROD = 'prod';

    public function getContainer(): ContainerInterface;
    public function getProjectDir(): string;
}
