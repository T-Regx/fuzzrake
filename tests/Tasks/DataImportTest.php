<?php

declare(strict_types=1);

namespace App\Tests\Tasks;

use App\DataDefinitions\Fields;
use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Tasks\DataImport;
use App\Utils\Artisan\SmartAccessDecorator as Smart;
use App\Utils\Data\FixerDifferValidator;
use App\Utils\Data\Manager;
use App\Utils\Data\Printer;
use App\Utils\DataInputException;
use App\Utils\IuSubmissions\IuSubmission;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DataImportTest extends TestCase
{
    /**
     * @dataProvider imagesUpdateShouldResetMiniaturesDataProvider
     *
     * @throws DataInputException
     */
    public function testImagesUpdateShouldResetMiniatures(string $initialUrlPhotos, string $initialMiniatures, string $newUrlPhotos, string $expectedMiniatures): void
    {
        $artisan = $this->getBasicArtisan([
            Fields::MAKER_ID       => 'MAKERID',
            Fields::PASSWORD       => 'PASSWORD',
            Fields::URL_PHOTOS     => $initialUrlPhotos,
            Fields::URL_MINIATURES => $initialMiniatures,
        ]);

        $objectManager = $this->getObjectManagerMock($artisan);
        $importManager = $this->getImportManagerMock();
        $printer = $this->getPrinterMock();
        $fdv = $this->getFixerDifferValidatorMock();

        $dataImport = new DataImport($objectManager, $importManager, $printer, $fdv, false);
        $dataImport->import([$this->getIuSubmission($artisan, [
            Fields::URL_PHOTOS => [$newUrlPhotos],
        ])]);

        static::assertEquals($expectedMiniatures, Smart::wrap($artisan)->getMiniatureUrls());
    }

    public function imagesUpdateShouldResetMiniaturesDataProvider(): array
    {
        return [
            ['', '', '', ''],
            ['', '', 'NEW_PHOTOS', ''],
            ['OLD_PHOTOS', 'OLD_MINIATURES', '', ''],
            ['OLD_PHOTOS', 'OLD_MINIATURES', 'NEW_PHOTOS', ''],
            ['OLD_PHOTOS', 'OLD_MINIATURES', 'OLD_PHOTOS', 'OLD_MINIATURES'],
        ];
    }

    private function getObjectManagerMock(Artisan $artisan): EntityManagerInterface
    {
        $result = $this->createMock(EntityManagerInterface::class);
        $result->expects(self::once())->method('getRepository')->willReturn($this->getArtisanRepositoryMock($artisan));

        return $result;
    }

    private function getBasicArtisan(array $initialData): Artisan
    {
        $smart = Smart::wrap($result = new Artisan());

        foreach ($initialData as $fieldName => $value) {
            $smart->set(Fields::get($fieldName), $value);
        }

        return $result;
    }

    private function getImportManagerMock(): Manager
    {
        $result = $this->createMock(Manager::class);
        $result->expects(static::once())->method('isAccepted')->willReturn(true);

        return $result;
    }

    private function getPrinterMock(): Printer
    {
        // return new Printer(new SymfonyStyle(new StringInput(''), new DebugStdoutOutput()));
        return $this->createMock(Printer::class);
    }

    private function getFixerDifferValidatorMock(): FixerDifferValidator
    {
        return $this->createMock(FixerDifferValidator::class);
    }

    private function getIuSubmission(Artisan $artisan, array $data): IuSubmission
    {
        $allData = Smart::wrap($artisan)->getAllData();
        $allData = array_merge($allData, $data);

        return new IuSubmission(new DateTimeImmutable(), 'test_id', $allData);
    }

    private function getArtisanRepositoryMock(Artisan $artisan): ArtisanRepository
    {
        $result = $this->createMock(ArtisanRepository::class);
        $result->expects(self::once())->method('findBestMatches')->willReturn([$artisan]);

        return $result;
    }
}
