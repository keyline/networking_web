<?php

namespace App\Services;

use App\Models\Companies\CompaniesDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Smart directory search.
 *
 * Every business is flattened into one row of `business_search_index`
 * (name, owner names, category names, published products/services, about text,
 * keywords) with a FULLTEXT index, so a search is one indexed query on one
 * small table instead of joins + LIKE '%…%' across five tables.
 *
 * Freshness:
 *  - model observers call touch() and the affected businesses are re-indexed
 *    after the response is sent (see AppServiceProvider);
 *  - the whole index is rebuilt in the background at most once per hour,
 *    triggered by a search, so raw DB writes (imports, API) are picked up
 *    without needing a cron job;
 *  - `php artisan businesses:reindex` rebuilds it on demand.
 */
class BusinessSearchIndex
{
    public const TABLE = 'business_search_index';
    private const BUILT_KEY = 'business_search_index:built_at';
    private const LOCK_KEY = 'business_search_index:rebuilding';
    private const REBUILD_EVERY_MINUTES = 60;
    // Line markers inside `content`; punctuation + 1 letter, so FULLTEXT never indexes them.
    private const ITEM = '§i ';
    private const KEYWORDS = '§k ';

    // InnoDB's default FULLTEXT stopwords: these are matched with LIKE instead.
    private const STOPWORDS = ['a', 'about', 'an', 'are', 'as', 'at', 'be', 'by', 'com', 'de', 'en', 'for', 'from', 'how', 'i', 'in', 'is', 'it', 'la', 'of', 'on', 'or', 'that', 'the', 'this', 'to', 'was', 'what', 'when', 'where', 'who', 'will', 'with', 'und', 'www'];

    /** @var array<int, true> */
    private static array $pending = [];
    private static bool $flushRegistered = false;

    /* ------------------------------------------------------------------ */
    /* Searching                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Ranked matches: [cmp_id => ['score' => float, 'match' => ?string, 'row' => object]].
     * `match` explains why a business matched when it wasn't its name
     * (e.g. "Owner: Subrata Kundu", "Offers: Web development").
     */
    public function search(string $query, int $limit = 300, bool $publicOnly = true): Collection
    {
        $tokens = $this->tokens($query);
        if ($tokens === [] || ! $this->ready()) {
            return collect();
        }

        $phrase = implode(' ', $tokens);
        $long = array_values(array_filter($tokens, fn ($t) => mb_strlen($t) >= 3 && ! in_array($t, self::STOPWORDS, true)));
        $short = array_values(array_diff($tokens, $long));

        // 1) every word must match (prefix match: "web" finds "website")
        $rows = $this->run($phrase, $long, $short, true, $limit, $publicOnly);
        // 2) words hidden inside other words ("tech" in "Digitech")
        if ($rows->isEmpty()) {
            $rows = $this->run($phrase, [], $tokens, true, $limit, $publicOnly);
        }
        // 3) any word matches, best first
        if ($rows->isEmpty() && count($long) > 1) {
            $rows = $this->run($phrase, $long, [], false, $limit, $publicOnly);
        }
        // 4) close spellings: shorten long words to their stem ("digital" -> "digi*" finds "DigiTech",
        //    "consultants" -> "consult*"), still one indexed FULLTEXT query
        if ($rows->isEmpty()) {
            $stems = array_values(array_unique(array_map(
                fn ($t) => mb_strlen($t) >= 5 ? mb_substr($t, 0, max(4, mb_strlen($t) - 3)) : $t,
                $long
            )));
            if ($stems !== $long && $stems !== []) {
                $rows = $this->run($phrase, $stems, [], count($stems) === 1, $limit, $publicOnly);
            }
        }

        return $rows->mapWithKeys(fn ($row) => [(int) $row->cmp_id => [
            'score' => (float) $row->score,
            'match' => $this->matchReason($row, $tokens),
            'row' => $row,
        ]]);
    }

    /** Ranked business ids matching the query (best first). */
    public function businessIds(string $query, bool $publicOnly = true, int $limit = 500): array
    {
        return $this->search($query, $limit, $publicOnly)->keys()->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Ranked member (user) ids for a member search: owners of matching businesses
     * (name, owner name, category, products/services, keywords) plus a direct phone
     * match when the query looks like a number. Two small indexed queries.
     */
    public function memberIds(string $query, int $limit = 500): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }
        $ids = [];
        $digits = preg_replace('/\D+/', '', $query);
        if (strlen($digits) >= 4 && preg_match('/^[\d\s+()-]+$/', $query)) {
            $ids = DB::table('user_master')->where('um_mobile_no', 'like', '%'.$digits.'%')->limit($limit)->pluck('um_id')->map(fn ($id) => (int) $id)->all();
        }
        $businessIds = $this->businessIds($query, false, $limit);
        if ($businessIds) {
            $owners = DB::table('user_companies_map')->whereIn('ucm_cmp_id', $businessIds)->get(['ucm_cmp_id', 'ucm_um_id']);
            $rank = array_flip($businessIds);
            foreach ($owners->sortBy(fn ($o) => $rank[$o->ucm_cmp_id] ?? PHP_INT_MAX) as $owner) {
                $ids[] = (int) $owner->ucm_um_id;
            }
        }

        return array_values(array_unique($ids));
    }

    /** Small, cached result set for the live suggestion dropdown. */
    public function suggest(string $query, int $limit = 6): array
    {
        $key = 'business_search_suggest:'.md5(implode(' ', $this->tokens($query)).'|'.$limit);

        return Cache::remember($key, 120, function () use ($query, $limit) {
            return $this->search($query, $limit)->map(fn ($hit) => [
                'name' => $hit['row']->name,
                'url' => route('business.show', $hit['row']->slug),
                'category' => Str::before((string) $hit['row']->categories, ','),
                'match' => $hit['match'],
                'logo' => $hit['row']->logo ? env('UPLOADS_URL').'company/'.$hit['row']->logo : null,
                'initials' => $this->initials($hit['row']->name),
                'sponsored' => (bool) $hit['row']->is_sponsored,
            ])->values()->all();
        });
    }

    private function run(string $phrase, array $fulltext, array $like, bool $all, int $limit, bool $publicOnly): Collection
    {
        if ($fulltext === [] && $like === []) {
            return collect();
        }

        $haystack = "CONCAT_WS(' ', name, owners, categories, content)";
        $boolean = implode(' ', array_map(fn ($t) => ($all ? '+' : '').$t.'*', $fulltext));
        $q = DB::table(self::TABLE)->select(['cmp_id', 'slug', 'name', 'owners', 'categories', 'content', 'logo', 'is_sponsored']);

        $score = 'CASE WHEN name LIKE ? THEN 30 WHEN name LIKE ? THEN 20 WHEN categories LIKE ? THEN 8 WHEN owners LIKE ? THEN 6 ELSE 0 END';
        $bindings = [$this->like($phrase, true), $this->like($phrase), $this->like($phrase), $this->like($phrase)];
        if ($fulltext !== []) {
            $score .= ' + MATCH(name, owners, categories, content) AGAINST (? IN BOOLEAN MODE)';
            $bindings[] = $boolean;
            $q->whereRaw('MATCH(name, owners, categories, content) AGAINST (? IN BOOLEAN MODE)', [$boolean]);
        }
        foreach ($like as $token) {
            $q->whereRaw("{$haystack} LIKE ?", [$this->like($token)]);
        }

        return $q->selectRaw("({$score}) AS score", $bindings)
            ->when($publicOnly, fn ($q) => $q->where('is_public', 1))
            ->orderByDesc('score')->orderByDesc('is_sponsored')->orderBy('name')
            ->limit($limit)
            ->get();
    }

    private function matchReason(object $row, array $tokens): ?string
    {
        $has = fn (?string $text) => $text !== null && $text !== '' && collect($tokens)->contains(fn ($t) => Str::contains(mb_strtolower($text), $t));

        if ($has($row->name)) {
            return null;
        }
        foreach (array_filter(array_map('trim', explode(',', (string) $row->owners))) as $owner) {
            if ($has($owner)) {
                return 'Owner: '.$owner;
            }
        }
        foreach (array_filter(array_map('trim', explode(',', (string) $row->categories))) as $category) {
            if ($has($category)) {
                return 'Category: '.$category;
            }
        }
        foreach (preg_split('/\R/', (string) $row->content) as $line) {
            if (! $has($line)) {
                continue;
            }
            if (Str::startsWith($line, self::ITEM)) {
                return 'Offers: '.Str::limit(Str::before(Str::after($line, self::ITEM), ' — '), 50);
            }
            if (Str::startsWith($line, self::KEYWORDS)) {
                return 'Keyword match';
            }
            return 'About: '.$this->snippet($line, $tokens);
        }

        return null;
    }

    /* ------------------------------------------------------------------ */
    /* Indexing                                                             */
    /* ------------------------------------------------------------------ */

    /** Queue businesses to re-index once the current request/command finishes. */
    public static function touch(int|array|null $cmpIds): void
    {
        foreach ((array) $cmpIds as $id) {
            if ((int) $id > 0) {
                self::$pending[(int) $id] = true;
            }
        }
        if (self::$pending && ! self::$flushRegistered) {
            self::$flushRegistered = true;
            app()->terminating(function () {
                $ids = array_keys(self::$pending);
                self::$pending = [];
                self::$flushRegistered = false;
                try {
                    app(self::class)->refresh($ids);
                } catch (\Throwable $e) {
                    Log::warning('Business search index refresh failed', ['ids' => $ids, 'message' => $e->getMessage()]);
                }
            });
        }
    }

    /** Rebuild every row (chunked). Returns the number of businesses indexed. */
    public function rebuildAll(): int
    {
        $count = 0;
        $seen = [];
        DB::table('companies_details')->select('cmpd_cmp_id')->orderBy('cmpd_cmp_id')
            ->chunk(200, function ($chunk) use (&$count, &$seen) {
                $ids = $chunk->pluck('cmpd_cmp_id')->map(fn ($id) => (int) $id)->all();
                $count += $this->refresh($ids);
                array_push($seen, ...$ids);
            });
        // drop rows for businesses that no longer exist
        DB::table(self::TABLE)->when($seen, fn ($q) => $q->whereNotIn('cmp_id', $seen))->delete();
        Cache::forever(self::BUILT_KEY, now()->timestamp);

        return $count;
    }

    /** Re-index the given businesses with a fixed number of queries per batch. */
    public function refresh(array $cmpIds): int
    {
        $cmpIds = array_values(array_unique(array_filter(array_map('intval', $cmpIds))));
        if ($cmpIds === [] || ! Schema::hasTable(self::TABLE)) {
            return 0;
        }

        $details = CompaniesDetail::query()->whereIn('cmpd_cmp_id', $cmpIds)->get()->keyBy('cmpd_cmp_id');

        $owners = DB::table('user_companies_map as ucm')
            ->join('user_master as um', 'um.um_id', '=', 'ucm.ucm_um_id')
            ->leftJoin('user_details as ud', 'ud.ud_um_id', '=', 'um.um_id')
            ->whereIn('ucm.ucm_cmp_id', $cmpIds)
            ->get(['ucm.ucm_cmp_id', 'um.um_status', 'um.um_user_name', 'ud.ud_first_name', 'ud.ud_last_name', 'ud.ud_keywords'])
            ->groupBy('ucm_cmp_id');

        $categories = DB::table('categories_to_companies as ctc')
            ->join('business_category_master as bcm', 'bcm.bcm_id', '=', 'ctc.ctc_bcm_id')
            ->whereIn('ctc.ctc_cmp_id', $cmpIds)
            ->get(['ctc.ctc_cmp_id', 'bcm.name'])
            ->groupBy('ctc_cmp_id');

        $portfolios = DB::table('business_portfolios')->whereIn('company_id', $cmpIds)
            ->where('is_published', 1)->get(['company_id', 'published_snapshot'])->keyBy('company_id');

        $rows = [];
        foreach ($cmpIds as $id) {
            $detail = $details->get($id);
            if (! $detail) {
                continue;
            }
            $companyOwners = $owners->get($id, collect());
            $activeOwners = $companyOwners->where('um_status', 2);
            $ownerNames = $activeOwners->map(fn ($o) => trim(($o->ud_first_name ?? '').' '.($o->ud_last_name ?? '')) ?: (string) $o->um_user_name)
                ->filter()->unique()->implode(', ');

            $snapshot = json_decode((string) optional($portfolios->get($id))->published_snapshot, true) ?: [];
            $lines = [];
            if (! empty($snapshot['tagline'])) {
                $lines[] = $this->clean($snapshot['tagline']);
            }
            $about = $snapshot['about'] ?? $detail->cmpd_description;
            if ($about && ! CompaniesDetail::isSetupDescription($about)) {
                $lines[] = Str::limit($this->clean($about), 2000, '');
            }
            foreach ($snapshot['items'] ?? [] as $item) {
                if (($item['is_active'] ?? true) && ! empty($item['title'])) {
                    $lines[] = self::ITEM.$this->clean($item['title']).(! empty($item['description']) ? ' — '.Str::limit($this->clean($item['description']), 300, '') : '');
                }
            }
            $keywords = $activeOwners->pluck('ud_keywords')->filter()->map(fn ($k) => $this->clean($k))->implode(', ');
            if ($keywords !== '') {
                $lines[] = self::KEYWORDS.Str::limit($keywords, 500, '');
            }

            $rows[] = [
                'cmp_id' => $id,
                'slug' => $detail->public_slug,
                'name' => Str::limit($this->clean((string) $detail->cmpd_name), 250, ''),
                'owners' => Str::limit($ownerNames, 495, ''),
                'categories' => Str::limit($categories->get($id, collect())->pluck('name')->filter()->unique()->implode(', '), 495, ''),
                'content' => implode("\n", array_filter($lines)),
                'logo' => $detail->cmpd_logo,
                'is_public' => (int) $detail->cmpd_status === 1 && $detail->public_slug && $activeOwners->isNotEmpty(),
                'is_sponsored' => (bool) $detail->cmpd_is_sponsored,
                'indexed_at' => now(),
            ];
        }

        $missing = array_diff($cmpIds, array_column($rows, 'cmp_id'));
        if ($missing) {
            DB::table(self::TABLE)->whereIn('cmp_id', $missing)->delete();
        }
        if ($rows) {
            DB::table(self::TABLE)->upsert($rows, ['cmp_id'], array_keys($rows[0]));
        }

        return count($rows);
    }

    /**
     * Make sure the index exists and is reasonably fresh. An empty index is
     * built right away (first run); a stale one is rebuilt after the response.
     */
    private function ready(): bool
    {
        if (! Schema::hasTable(self::TABLE)) {
            return false;
        }
        $builtAt = Cache::get(self::BUILT_KEY);
        if (! $builtAt && ! DB::table(self::TABLE)->exists()) {
            $this->rebuildAll();
        } elseif (! $builtAt || $builtAt < now()->subMinutes(self::REBUILD_EVERY_MINUTES)->timestamp) {
            if (Cache::add(self::LOCK_KEY, 1, 300)) {
                app()->terminating(function () {
                    try {
                        $this->rebuildAll();
                    } catch (\Throwable $e) {
                        Log::warning('Business search index rebuild failed', ['message' => $e->getMessage()]);
                    } finally {
                        Cache::forget(self::LOCK_KEY);
                    }
                });
            }
        }

        return true;
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                              */
    /* ------------------------------------------------------------------ */

    /** Lower-case words; FULLTEXT operators and punctuation are stripped. */
    private function tokens(string $query): array
    {
        $query = mb_strtolower(Str::limit(trim($query), 100, ''));
        preg_match_all('/[\p{L}\p{N}]+/u', $query, $m);

        return array_slice(array_values(array_unique($m[0])), 0, 6);
    }

    private function like(string $value, bool $prefixOnly = false): string
    {
        $value = addcslashes($value, '\\%_');

        return ($prefixOnly ? '' : '%').$value.'%';
    }

    private function clean(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['<br', '</p>'], [' <br', ' </p>'], $text)), ENT_QUOTES | ENT_HTML5)));
    }

    private function snippet(string $line, array $tokens): string
    {
        $lower = mb_strtolower($line);
        $pos = collect($tokens)->map(fn ($t) => mb_strpos($lower, $t))->filter(fn ($p) => $p !== false)->min() ?? 0;
        $start = max(0, $pos - 20);

        return ($start > 0 ? '…' : '').Str::limit(mb_substr($line, $start), 50);
    }

    private function initials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];

        return mb_strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1)) ?: 'B';
    }
}
