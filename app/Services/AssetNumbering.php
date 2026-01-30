<?php

// app/Services/AssetNumbering.php

namespace App\Services;

use App\Models\Assets\Settings\{AssetBook, AssetLocation, FixedAssetClass};
use App\Models\Assets\Settings\AssetNumberingRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssetNumbering
{
    /**
     * Generate next Asset Code using the most specific active rule matching the context.
     * @param array $ctx = ['ClassID'=>?, 'LocationID'=>?, 'BookID'=>?, 'Date'=>Y-m-d]
     */
    public function nextAssetCode(array $ctx = []): string
    {
        $today = isset($ctx['Date']) ? Carbon::parse($ctx['Date']) : Carbon::today();

        return DB::transaction(function () use ($ctx, $today) {
            // Match rule where each of Class/Loc/Book either NULL (wildcard) or equals context
            $rule = AssetNumberingRule::where('IsActive', 1)
                ->where(function ($q) use ($ctx) {
                    $q->whereNull('ClassID')->orWhere('ClassID', $ctx['ClassID'] ?? null);
                })
                ->where(function ($q) use ($ctx) {
                    $q->whereNull('LocationID')->orWhere('LocationID', $ctx['LocationID'] ?? null);
                })
                ->where(function ($q) use ($ctx) {
                    $q->whereNull('BookID')->orWhere('BookID', $ctx['BookID'] ?? null);
                })
                // Prefer most specific: class+loc+book > class+loc > class > loc > book > global
                ->orderByRaw("
                    (CASE WHEN ClassID   IS NULL THEN 0 ELSE 4 END) +
                    (CASE WHEN LocationID IS NULL THEN 0 ELSE 2 END) +
                    (CASE WHEN BookID    IS NULL THEN 0 ELSE 1 END) DESC
                ")
                ->lockForUpdate()
                ->first();

            if (! $rule) {
                // fallback safe code
                return 'AST-' . now()->format('ymdHisv');
            }

            // Handle resets (YEAR/MONTH)
            $needsReset = false;
            if ($rule->ResetPeriod === 'YEAR') {
                $needsReset = ! $rule->LastResetOn || (Carbon::parse($rule->LastResetOn)->year !== $today->year);
            } elseif ($rule->ResetPeriod === 'MONTH') {
                $needsReset = ! $rule->LastResetOn || (Carbon::parse($rule->LastResetOn)->format('Ym') !== $today->format('Ym'));
            }
            if ($needsReset) {
                $rule->NextSeq = 1;
                $rule->LastResetOn = $today->toDateString();
            }

            $seq = (int)$rule->NextSeq;
            $rule->NextSeq = $seq + 1;
            if (! $rule->LastResetOn) {
                $rule->LastResetOn = $today->toDateString();
            }
            $rule->save();

            // Build code
            $pad = max(1, (int)$rule->PadLength);
            $seqStr = str_pad((string)$seq, $pad, '0', STR_PAD_LEFT);
            $prefix = (string)($rule->Prefix ?? '');
            $suffix = (string)($rule->Suffix ?? '');

            $pattern = $rule->CodePattern ?: '{PREFIX}-{SEQ}{SUFFIX}';

            // Optional lookups for tokens
            $classCode = $ctx['ClassID'] ?? null;
            $locCode = $ctx['LocationID'] ?? null;
            $bookCode = $ctx['BookID'] ?? null;

            if (str_contains($pattern, '{CLASS}') && ! empty($ctx['ClassID'])) {
                $fc = FixedAssetClass::find($ctx['ClassID']);
                $classCode = $fc?->Code ?: (string)$ctx['ClassID'];
            }
            if (str_contains($pattern, '{LOC}') && ! empty($ctx['LocationID'])) {
                $lc = AssetLocation::find($ctx['LocationID']);
                $locCode = $lc?->Code ?: (string)$ctx['LocationID'];
            }
            if (str_contains($pattern, '{BOOK}') && ! empty($ctx['BookID'])) {
                $bk = AssetBook::find($ctx['BookID']);
                $bookCode = $bk?->Code ?: ($bk?->Name ?? (string)$ctx['BookID']);
                // keep it compact
                $bookCode = preg_replace('/[^A-Za-z0-9]/', '', substr((string)$bookCode, 0, 8));
            }

            $code = $pattern;
            $repl = [
                '{PREFIX}' => $prefix,
                '{SUFFIX}' => $suffix ? ('-' . $suffix) : '',
                '{YYYY}' => $today->format('Y'),
                '{YY}' => $today->format('y'),
                '{MM}' => $today->format('m'),
                '{SEQ}' => $seqStr,
                '{CLASS}' => (string)($classCode ?? ''),
                '{LOC}' => (string)($locCode ?? ''),
                '{BOOK}' => (string)($bookCode ?? ''),
            ];
            $code = strtr($code, $repl);

            // Clean possible double dashes (if some tokens empty)
            $code = preg_replace('/-+/', '-', trim($code, '-'));

            return $code;
        });
    }
}
