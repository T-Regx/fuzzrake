<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;

class ArtisanFixWip
{
    private Artisan $fixed;

    public function __construct(
        private Artisan $original,
        private ?string $submissionId = null,
    ) {
        $this->fixed = clone $original;
    }

    public function getOriginal(): Artisan
    {
        return $this->original;
    }

    public function getFixed(): Artisan
    {
        return $this->fixed;
    }

    public function getSubmissionId(): ?string
    {
        return $this->submissionId;
    }

    public function apply(): void
    {
        foreach (Fields::persisted() as $field) {
            $this->applyField($field);
        }
    }

    public function applyField(Field $field): void
    {
        $this->original->set($field, $this->fixed->get($field));
    }
}
