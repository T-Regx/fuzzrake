<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Replacements;
use App\Utils\StringList;
use App\Utils\StrUtils;

abstract class AbstractListFixer extends StringFixer
{
    private Replacements $replacements;

    public function __construct(array $lists, array $strings)
    {
        parent::__construct($strings);

        $this->replacements = new Replacements($lists['replacements'], 'i', $lists['commonRegexPrefix'], $lists['commonRegexSuffix']);
    }

    public function fix(string $fieldName, string $subject): string
    {
        $items = StringList::split($subject, static::getSeparatorRegexp(), static::getNonsplittable());
        $items = array_filter(array_map([$this, 'fixItem'], $items));

        $subject = StringList::pack($items);
        $subject = $this->getReplacements()->do($subject);
        $subject = parent::fix($fieldName, $subject);
        $subject = StringList::unpack($subject);

        if (static::shouldSort()) {
            sort($subject);
        }

        return StringList::pack(array_unique($subject));
    }

    abstract protected static function shouldSort(): bool;

    abstract protected static function getSeparatorRegexp(): string;

    /**
     * @return string[]
     */
    protected function getNonsplittable(): array
    {
        return [];
    }

    protected function getReplacements(): Replacements
    {
        return $this->replacements;
    }

    private function fixItem(string $subject): string
    {
        $subject = trim($subject);

        if ('http' !== substr($subject, 0, 4)) {
            $subject = StrUtils::ucfirst($subject);
        }

        return $subject;
    }
}
