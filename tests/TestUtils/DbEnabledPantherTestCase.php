<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

abstract class DbEnabledPantherTestCase extends PantherTestCase
{
    use DbEnabledTestCaseTrait;

    protected static function createPantherClient(array $options = [], array $kernelOptions = [], array $managerOptions = []): PantherClient
    {
        $result = parent::createPantherClient($options, $kernelOptions, $managerOptions);

        /* @noinspection PhpFieldAssignmentTypeMismatchInspection */
        self::$entityManager = self::$container->get('doctrine.orm.default_entity_manager');

        SchemaTool::resetOn(self::$entityManager);

        return $result;
    }
}
