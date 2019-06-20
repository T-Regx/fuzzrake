<?php

declare(strict_types=1);

namespace App\Utils\Import;

use App\Entity\Artisan;
use App\Utils\ArtisanFields as Fields;
use App\Utils\DateTimeUtils;
use App\Utils\Utils;
use DateTime;
use Exception;

class Row
{
    /**
     * @var DateTime
     */
    private $timestamp;

    /**
     * @var array
     */
    private $rawInput;

    /**
     * @var Artisan
     */
    private $input;

    /**
     * @var Artisan
     */
    private $artisan;

    /**
     * @var Artisan
     */
    private $originalArtisan;

    /**
     * @var string
     */
    private $providedPasscode;

    /**
     * @var string
     */
    private $hash;

    /**
     * @param array $rawInput
     *
     * @throws Exception
     */
    public function __construct(array $rawInput)
    {
        $this->rawInput = $rawInput;
        $this->setTimestamp($rawInput);
        $this->setHash($rawInput);

        $this->providedPasscode = $rawInput[Fields::uiFormIndex(Fields::PASSCODE)];
    }

    public function getInput(): Artisan
    {
        return $this->input;
    }

    public function setInput(Artisan $input): void
    {
        $this->input = $input;
    }

    public function getArtisan(): Artisan
    {
        return $this->artisan;
    }

    public function setArtisan(Artisan $artisan): void
    {
        $this->artisan = $artisan;
    }

    public function getOriginalArtisan(): Artisan
    {
        return $this->originalArtisan;
    }

    public function setOriginalArtisan(Artisan $originalArtisan): void
    {
        $this->originalArtisan = $originalArtisan;
    }

    public function getProvidedPasscode(): string
    {
        return $this->providedPasscode;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getRawInput(): array
    {
        return $this->rawInput;
    }

    public function getIdStringSafe(): string
    {
        return Utils::artisanNamesSafe($this->getInput(), $this->getArtisan(), $this->getOriginalArtisan())
            .' ['.$this->timestamp->format(DATE_ISO8601).']';
    }

    public function getNames(): string
    {
        return Utils::artisanNamesSafe($this->getOriginalArtisan(), $this->getArtisan());
    }

    public function getMakerId(): string
    {
        return $this->artisan->getMakerId();
    }

    /**
     * It looks like Google Forms changes timestamp's timezone, so let's get rid of it for the sake of hash calculation.
     *
     * @param array $rawNewData
     *
     * @throws Exception
     */
    private function setTimestamp(array $rawNewData): void
    {
        $this->timestamp = DateTimeUtils::getUtcAt($rawNewData[Fields::uiFormIndex(Fields::TIMESTAMP)]);
    }

    private function setHash(array $rawNewData)
    {
        $rawNewData[Fields::uiFormIndex(Fields::TIMESTAMP)] = null;
        $this->hash = sha1(json_encode($rawNewData));
    }
}