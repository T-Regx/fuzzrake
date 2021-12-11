<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataDefinitions\Ages;
use App\DataDefinitions\Features;
use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\OrderTypes;
use App\DataDefinitions\ProductionModels;
use App\Tests\TestUtils\DbEnabledWebTestCase;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Symfony\Component\DomCrawler\Crawler;

class StatisticsControllerTest extends DbEnabledWebTestCase
{
    public function testStatisticsPageLoads(): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/statistics.html');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h1#data_statistics', 'Data statistics');
    }

    public function testInactiveMakersDontCount(): void
    {
        $client = static::createClient();

        $a1 = self::getArtisan('A1', 'AAAAAAA1')
            ->setFeatures(Features::FOLLOW_ME_EYES)
            ->setProductionModels(ProductionModels::STANDARD_COMMISSIONS)
            ->setCountry('CZ')
        ;
        $a2 = self::getArtisan('A2', 'AAAAAAA2')
            ->setFeatures(Features::FOLLOW_ME_EYES)
            ->setOrderTypes(OrderTypes::FULL_DIGITIGRADE)
            ->setCountry('SK')
        ;
        $a3 = self::getArtisan('A3', 'AAAAAAA3')
            ->setProductionModels(ProductionModels::STANDARD_COMMISSIONS)
            ->setInactiveReason('Inactive')
            ->setCountry('IT')
        ;

        self::persistAndFlush($a1, $a2, $a3);

        $client->request('GET', '/statistics.html');
        $crawler = $client->getCrawler();

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertRowValueEquals('2 (100.00%)', Features::FOLLOW_ME_EYES, $crawler);
        static::assertRowValueEquals('1 (50.00%)', ProductionModels::STANDARD_COMMISSIONS, $crawler);
        static::assertRowValueEquals('1 (50.00%)', OrderTypes::FULL_DIGITIGRADE, $crawler);
        static::assertRowValueEquals('2 (100.00%)', 'Unknown', $crawler->filterXPath('//h1[text()="Styles"]')->nextAll()->first());
        static::assertRowValueEquals('2 (100.00%)', 'Total', $crawler->filterXPath('//h1[text()="Commission status"]')->nextAll()->first());
        static::assertRowValueEquals('2 (100.00%)', Field::NAME->name, $crawler);
        static::assertRowValueEquals('1 (50.00% × 2 = 100.00%)', 'CZ, SK', $crawler);
        // https://github.com/veelkoov/fuzzrake/issues/74
        // static::assertRowValueEquals('1 (50.00%)', '40-49%', $crawler);
        // static::assertRowValueEquals('1 (50.00%)', '50-59%', $crawler);
    }

    public function testFakeFormerMakerIdsDontCount(): void
    {
        $client = static::createClient();

        $artisanOnlyFakeId = new Artisan();
        $artisanFakeIdAndNew = new Artisan();
        $artisanFakeIdAndOldAndNew = new Artisan();

        self::persistAndFlush($artisanFakeIdAndNew, $artisanOnlyFakeId, $artisanFakeIdAndOldAndNew);

        $artisanOnlyFakeId->setMakerId('')->setFormerMakerIds(sprintf('M%06d', $artisanOnlyFakeId->getId()));
        $artisanFakeIdAndNew->setMakerId('MAKERID')
            ->setFormerMakerIds(sprintf('M%06d', $artisanFakeIdAndNew->getId()));
        $artisanFakeIdAndOldAndNew->setMakerId('MAKE3ID')
            ->setFormerMakerIds(sprintf("MAKE2ID\nM%06d", $artisanFakeIdAndOldAndNew->getId()));

        $artisan3 = (new Artisan())->setMakerId('AAAAAAA')->setFormerMakerIds("BBBBBBB\nCCCCCCC");
        $artisan4 = (new Artisan())->setMakerId('DDDDDDD')->setFormerMakerIds('EEEEEEE');
        $artisan5 = (new Artisan())->setMakerId('FFFFFFF')->setFormerMakerIds('');

        self::persistAndFlush($artisan3, $artisan4, $artisan5);

        $client->request('GET', '/statistics.html');
        $crawler = $client->getCrawler();

        self::assertEquals('3 (50.00%)', $this->getRowValue($crawler, 'FORMER_MAKER_IDS'));
    }

//    public function testBooleanValuesCountProperly(): void // FIXME
//    {
//        $client = static::createClient();
//
//        $a1 = self::getArtisan('Is minor: true', 'M000001', ages: Ages::MINORS);
//        $a2 = self::getArtisan('Works with minors: true 1', 'M000002', worksWithMinors: true);
//        $a3 = self::getArtisan('Works with minors: true 2', 'M000003', worksWithMinors: true);
//        $a4 = self::getArtisan('Only nulls', 'M000004');
//
//        self::persistAndFlush($a1, $a2, $a3, $a4);
//
//        $client->request('GET', '/statistics.html');
//        $crawler = $client->getCrawler();
//
//        self::assertEquals('4 (100.00%)', $this->getRowValue($crawler, 'NAME'));
//        self::assertEquals('2 (50.00%)', $this->getRowValue($crawler, 'WORKS_WITH_MINORS'));
//        self::assertEquals('1 (25.00%)', $this->getRowValue($crawler, 'AGES'));
//    }

    private static function assertRowValueEquals(string $expected, string $rowLabel, Crawler $crawler): void
    {
        static::assertEquals($expected, static::getRowValue($crawler, $rowLabel));
    }

    private static function getRowValue(Crawler $crawler, string $rowLabel): string
    {
        return $crawler->filter('td:contains("'.$rowLabel.'")')->siblings()->text();
    }
}
