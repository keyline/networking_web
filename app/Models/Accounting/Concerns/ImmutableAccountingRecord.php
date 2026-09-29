<?php

namespace App\Models\Accounting\Concerns;

trait ImmutableAccountingRecord
{
    public static function bootImmutableAccountingRecord(): void
    {
        static::updating(function (): never {
            throw new \LogicException('Posted accounting records are immutable; create a reversal instead.');
        });
        static::deleting(function (): never {
            throw new \LogicException('Posted accounting records cannot be deleted; create a reversal instead.');
        });
    }
}
