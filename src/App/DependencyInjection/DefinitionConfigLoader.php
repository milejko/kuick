<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link       https://github.com/milejko/kuick
 * @copyright  Copyright (c) 2010-2025 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\App\DependencyInjection;

use DI\ContainerBuilder;

/**
 * DI Definition Loader
 */
class DefinitionConfigLoader
{
    private const DEFINITION_LOCATION = '/config/di/*.di.php';
    private const KUICK_VENDORS_DEFINITION_LOCATION = '/vendor/kuick/*/config/di/*.di.php';
    private const ENV_SPECIFIC_DEFINITION_LOCATION_TEMPLATE = '/config/di/*.di@%s.php';

    public function __construct(private ContainerBuilder $builder)
    {
    }

    public function __invoke(string $projectDir, string $env): void
    {
        //adding vendor definition files
        foreach (glob($projectDir . self::KUICK_VENDORS_DEFINITION_LOCATION) as $definitionFile) {
            $this->builder->addDefinitions($definitionFile);
        }
        //adding project definition files
        foreach (glob($projectDir . self::DEFINITION_LOCATION) as $definitionFile) {
            $this->builder->addDefinitions($definitionFile);
        }
        //adding env specific definition files
        foreach (glob(sprintf($projectDir . self::ENV_SPECIFIC_DEFINITION_LOCATION_TEMPLATE, $env)) as $definitionFile) {
            $this->builder->addDefinitions($definitionFile);
        }
    }
}
