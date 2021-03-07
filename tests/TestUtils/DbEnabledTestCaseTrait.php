<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Entity\Artisan;
use App\Entity\Event;
use App\Utils\DateTime\DateTimeUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use RuntimeException;

trait DbEnabledTestCaseTrait
{
    private static ?EntityManager $entityManager = null;

    protected static function bootKernel(array $options = [])
    {
        $result = parent::bootKernel($options);

        self::$entityManager = null;
        self::resetDB();

        return $result;
    }

    protected static function getEM(): EntityManager
    {
        return self::$entityManager ??= self::$container->get('doctrine.orm.default_entity_manager');
    }

    protected static function resetDB(): void
    {
        SchemaTool::resetOn(self::getEM());
    }

    protected static function addSimpleArtisan(): Artisan
    {
        $artisan = self::getArtisan();

        self::persistAndFlush($artisan);

        return $artisan;
    }

    protected static function addSimpleGenericEvent(): Event
    {
        $event = (new Event())
            ->setDescription('Test event')
        ;

        self::persistAndFlush($event);

        return $event;
    }

    protected static function getArtisan(string $name = 'Test artisan', string $makerId = 'TEST000', string $country = 'CZ'): Artisan
    {
        return (new Artisan())
            ->setName($name)
            ->setMakerId($makerId)
            ->setCountry($country)
            ->getCommissionsStatus()
            ->setLastChecked(DateTimeUtils::getNowUtc())
            ->getArtisan();
    }

    protected static function persistAndFlush(object ...$entities): void
    {
        try {
            foreach ($entities as $entity) {
                self::getEM()->persist($entity);
            }

            self::getEM()->flush();
        } catch (ORMException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}