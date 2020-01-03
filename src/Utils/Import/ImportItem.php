<?php

declare(strict_types=1);

namespace App\Utils\Import;

use App\Entity\Artisan;
use App\Utils\Data\ArtisanFixWip;
use App\Utils\StrUtils;

class ImportItem
{
    private RawImportItem $raw;
    private ArtisanFixWip $input;
    private ArtisanFixWip $entity;

    public function __construct(RawImportItem $raw, ArtisanFixWip $input, ArtisanFixWip $entity)
    {
        $this->raw = $raw;
        $this->input = $input;
        $this->entity = $entity;
    }

    public function getOriginalInput(): Artisan
    {
        return $this->input->getOriginal();
    }

    public function getFixedInput(): Artisan
    {
        return $this->input->getFixed();
    }

    public function getFixedEntity(): Artisan
    {
        return $this->entity->getFixed();
    }

    public function getOriginalEntity(): Artisan
    {
        return $this->entity->getOriginal();
    }

    public function getInput(): ArtisanFixWip
    {
        return $this->input;
    }

    public function getEntity(): ArtisanFixWip
    {
        return $this->entity;
    }

    public function getIdStrSafe(): string
    {
        return StrUtils::artisanNamesSafeForCli($this->getOriginalInput(), $this->getFixedEntity(), $this->getOriginalEntity())
            .' ['.$this->raw->getTimestamp()->format(DATE_ISO8601).']';
    }

    public function getNamesStrSafe(): string
    {
        return StrUtils::artisanNamesSafeForCli($this->getOriginalEntity(), $this->getFixedEntity());
    }

    public function getMakerId(): string
    {
        return $this->entity->getFixed()->getMakerId();
    }

    public function getHash(): string
    {
        return $this->raw->getHash();
    }

    public function getProvidedPasscode(): string
    {
        return $this->input->getFixed()->getPasscode();
    }
}
