<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link       https://github.com/milejko/kuick
 * @copyright  Copyright (c) 2010-2024 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\App;

use FilesystemIterator;
use GlobIterator;
use Symfony\Component\Console\Application;

/**
 * Console Application Kernel
 */
final class ConsoleKernel extends KernelAbstract
{
    private const APP_NAME_KEY = 'kuick.app.name';
    private const COMMAND_PATH_PATTERN = '/config/*.commands.php';

    private Application $application;

    public function __construct(string $projectDir)
    {
        parent::__construct($projectDir);
        //create a new application
        $this->application = new Application($this->container->get(self::APP_NAME_KEY));
        //adding commands
        foreach (new GlobIterator($projectDir . self::COMMAND_PATH_PATTERN, FilesystemIterator::KEY_AS_FILENAME) as $commandFile) {
            foreach (include $commandFile as $commandClass) {
                $this->application->add($this->container->get($commandClass));
            }
        }
        ini_set('max_execution_time', 0);
    }

    public function __invoke(): void
    {
        $this->application->run();
    }
}
