<?php

namespace App\Services\Accounting;

use App\Models\Accounting\DocumentSequence;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    private const PREFIXES = ['invoice' => 'INV', 'payment' => 'PAY', 'receipt' => 'RCP'];

    public function next(string $type, CarbonInterface $date): string
    {
        if (! isset(self::PREFIXES[$type])) {
            throw new \InvalidArgumentException("Unsupported accounting document type [{$type}].");
        }

        $financialYear = $this->financialYear($date);
        $now = now();
        DB::table('accounting_document_sequences')->insertOrIgnore([
            'document_type' => $type,
            'financial_year' => $financialYear,
            'last_number' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sequence = DocumentSequence::query()
            ->where('document_type', $type)
            ->where('financial_year', $financialYear)
            ->lockForUpdate()
            ->firstOrFail();
        $sequence->increment('last_number');

        return sprintf('%s/%s/%06d', self::PREFIXES[$type], $financialYear, $sequence->last_number);
    }

    public function financialYear(CarbonInterface $date): string
    {
        $startYear = $date->month >= 4 ? $date->year : $date->year - 1;

        return sprintf('%d-%02d', $startYear, ($startYear + 1) % 100);
    }
}
