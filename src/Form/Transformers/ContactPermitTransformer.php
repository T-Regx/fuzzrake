<?php

declare(strict_types=1);

namespace App\Form\Transformers;

use App\DataDefinitions\ContactPermit;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<ContactPermit, ?string>
 */
class ContactPermitTransformer implements DataTransformerInterface
{
    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function transform($value): mixed
    {
        return $value?->value;
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection - Interface compatibility */
    public function reverseTransform($value): mixed
    {
        return ContactPermit::tryFrom($value ?? '');
    }
}
