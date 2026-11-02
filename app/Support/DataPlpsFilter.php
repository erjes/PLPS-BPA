<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Single source of truth for the dashboard filter rules.
 *
 * The Blade dashboard, the AJAX table, the PDF export and the queued Excel
 * export all have to filter the exact same way, so the logic lives here
 * instead of being duplicated (or copied into the export class).
 */
class DataPlpsFilter
{
    /**
     * Filter keys accepted from the dashboard / query string.
     */
    public const FILTER_KEYS = [
        'program_id',
        'sub_program_id',
        'fakultas_id',
        'prodi_id',
        'penyelenggara',
        'mitra_id',
        'semester_ta',
        'tahun_ajaran',
        'search_nama',
        'search_nim',
    ];

    /**
     * Apply the dashboard filters to an Eloquent DataPlps query.
     *
     * @param  Builder  $query
     * @param  array  $filters
     * @return Builder
     */
    public static function apply(Builder $query, array $filters): Builder
    {
        if (self::filled($filters, 'program_id')) {
            $query->whereIn('data_plps.program_id', self::expandIds($filters['program_id']));
        }

        if (self::filled($filters, 'sub_program_id')) {
            $query->whereIn('data_plps.sub_program_id', self::expandIds($filters['sub_program_id']));
        }

        if (self::filled($filters, 'mitra_id')) {
            $query->whereIn('data_plps.mitra_id', self::expandIds($filters['mitra_id']));
        }

        if (self::filled($filters, 'penyelenggara')) {
            $query->whereIn('data_plps.penyelenggara', (array) $filters['penyelenggara']);
        }

        if (self::filled($filters, 'fakultas_id')) {
            $ids = self::expandIds($filters['fakultas_id']);
            $query->whereHas('mahasiswa.prodi', fn ($q) => $q->whereIn('fakultas_id', $ids));
        }

        if (self::filled($filters, 'prodi_id')) {
            $ids = self::expandIds($filters['prodi_id']);
            $query->whereHas('mahasiswa', fn ($q) => $q->whereIn('prodi_id', $ids));
        }

        if (self::filled($filters, 'semester_ta')) {
            $query->whereIn('data_plps.semester_ta', (array) $filters['semester_ta']);
        }

        if (self::filled($filters, 'tahun_ajaran')) {
            $query->whereIn('data_plps.tahun_ajaran', (array) $filters['tahun_ajaran']);
        }

        // Free text search from the data table (kept so an export always
        // contains the same rows the user is currently looking at).
        if (!empty($filters['search_nama'])) {
            $query->whereHas('mahasiswa', fn ($q) => $q->where('nama', 'like', '%' . $filters['search_nama'] . '%'));
        }

        if (!empty($filters['search_nim'])) {
            $query->where('data_plps.nim', 'like', '%' . $filters['search_nim'] . '%');
        }

        return $query;
    }

    /**
     * Only keep the keys that are used for filtering, so request data such as
     * "_token" never ends up in the stored export filters.
     *
     * @param  array  $input
     * @return array
     */
    public static function only(array $input): array
    {
        return array_intersect_key($input, array_flip(self::FILTER_KEYS));
    }

    /**
     * Does the given input contain at least one active filter?
     *
     * @param  array  $filters
     * @return bool
     */
    public static function hasAny(array $filters): bool
    {
        foreach (self::FILTER_KEYS as $key) {
            if (self::filled($filters, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Expand comma separated ID values from multi-select checkboxes.
     * e.g. ["1,2", "3"] => ["1", "2", "3"]
     *
     * @param  mixed  $input
     * @return array
     */
    public static function expandIds($input): array
    {
        $expanded = [];

        foreach ((array) $input as $value) {
            foreach (explode(',', (string) $value) as $id) {
                $id = trim($id);

                if ($id !== '') {
                    $expanded[] = $id;
                }
            }
        }

        return array_values(array_unique($expanded));
    }

    /**
     * @param  array  $filters
     * @param  string  $key
     * @return bool
     */
    private static function filled(array $filters, string $key): bool
    {
        return !empty(array_filter((array) ($filters[$key] ?? [])));
    }
}
