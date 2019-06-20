<?php

declare(strict_types=1);

namespace App\Utils\Import;

use App\Utils\ArtisanField;
use InvalidArgumentException;

class ValueCorrection
{
    const MODE_REGEXP = 'rr';
    const MODE_WHOLE = 'wr';
    const MODE_ALL = 'ar';

    private $makerId;
    private $field;
    private $wrongValue;
    private $correctedValue;
    private $mode;

    public function __construct(string $makerId, ArtisanField $field, string $mode, string $wrongValue, string $correctedValue)
    {
        $this->validateAndSetMakerId($makerId);
        $this->field = $field;
        $this->mode = $mode;
        $this->wrongValue = $wrongValue;
        $this->correctedValue = $correctedValue;
    }

    public function getMakerId(): string
    {
        return $this->makerId;
    }

    public function getField(): ArtisanField
    {
        return $this->field;
    }

    public function getWrongValue(): string
    {
        return $this->wrongValue;
    }

    public function getCorrectedValue(): string
    {
        return $this->correctedValue;
    }

    public function apply($value)
    {
        switch ($this->mode) {
            case self::MODE_REGEXP:
                $result = preg_replace($this->wrongValue, $this->correctedValue, $value);

                if (null === $result) {
                    throw new InvalidArgumentException("Regexp failed: '$this->wrongValue'");
                }

                return $result;
                break;

            case self::MODE_ALL:
                return str_replace($this->wrongValue, $this->correctedValue, $value);
                break;

            case self::MODE_WHOLE:
                if ($value === $this->wrongValue || '*' === $this->wrongValue) {
                    return $this->correctedValue;
                } else {
                    return $value;
                }
                break;

            default:
                throw new InvalidArgumentException("Invalid mode: '$this->mode'");
                break;
        }
    }

    /**
     * @param string $makerId
     */
    private function validateAndSetMakerId(string $makerId): void
    {
        if (!preg_match('#^([A-Z0-9]{7}|\*)$#', $makerId)) {
            throw new InvalidArgumentException("Invalid maker ID: '$makerId'");
        }

        $this->makerId = $makerId;
    }
}