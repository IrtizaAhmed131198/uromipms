<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected $casts = [
        'business_location' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Category $category) {
            if (! $category->isDirty('business_location')) {
                return;
            }
            $ids = self::normalizeBusinessLocationIds($category->business_location);
            $category->business_location = empty($ids) ? null : array_values($ids);
        });
    }

    /**
     * Normalize stored business_location (JSON text, array, legacy serialized, single id) to location ids.
     *
     * @param  mixed  $value
     * @return array<int>
     */
    public static function normalizeBusinessLocationIds($value): array
    {
        if ($value === null || $value === '' || $value === [] || $value === false) {
            return [];
        }

        // Stored as plain 0 / "0" when nothing valid was saved — not a real location id.
        if (is_int($value) || is_float($value)) {
            $id = (int) $value;

            return $id > 0 ? [$id] : [];
        }

        if (is_string($value) && ctype_digit(trim((string) $value))) {
            $id = (int) $value;

            return $id > 0 ? [$id] : [];
        }

        if (is_array($value)) {
            $ids = [];
            foreach ($value as $v) {
                if ($v === null || $v === '' || $v === false) {
                    continue;
                }
                if (is_numeric($v)) {
                    $i = (int) $v;
                    if ($i > 0) {
                        $ids[] = $i;
                    }
                }
            }

            return array_values(array_unique($ids));
        }

        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '' || $trim === '[]') {
                return [];
            }

            $decoded = json_decode($trim, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return self::normalizeBusinessLocationIds($decoded);
            }

            if (str_starts_with($trim, 'a:')) {
                $un = @unserialize($trim);
                if (is_array($un)) {
                    return self::normalizeBusinessLocationIds($un);
                }
            }

            if (str_contains($trim, ',')) {
                return self::normalizeBusinessLocationIds(explode(',', $trim));
            }
        }

        return [];
    }

    /**
     * When the business has exactly one location, return its id — used for display/save when category row stores 0/NULL.
     *
     * @return array<int>
     */
    public static function singleBusinessLocationIdsOrEmpty(int $business_id): array
    {
        $ids = BusinessLocation::where('business_id', $business_id)->orderBy('id')->pluck('id');

        return $ids->count() === 1 ? [(int) $ids->first()] : [];
    }

    /**
     * Location labels for the category grid (matches Business Location dropdown wording).
     *
     * @param  array<int>  $locationIds
     */
    public static function formatLocationNamesForBusiness(int $business_id, array $locationIds): string
    {
        if (empty($locationIds)) {
            return '';
        }

        $locations = BusinessLocation::where('business_id', $business_id)
            ->whereIn('id', $locationIds)
            ->orderBy('name')
            ->get(['name', 'location_id']);

        return $locations->map(function ($loc) {
            return ! empty($loc->location_id)
                ? $loc->name.' ('.$loc->location_id.')'
                : $loc->name;
        })->implode(', ');
    }

    /**
     * Combines Category and sub-category
     *
     * @param  int  $business_id
     * @return array
     */
    public static function catAndSubCategories($business_id)
    {
        $all_categories = Category::where('business_id', $business_id)
                                ->where('category_type', 'product')
                                ->orderBy('name', 'asc')
                                ->get()
                                ->toArray();

        if (empty($all_categories)) {
            return [];
        }
        $categories = [];
        $sub_categories = [];

        foreach ($all_categories as $category) {
            if ($category['parent_id'] == 0) {
                $categories[] = $category;
            } else {
                $sub_categories[] = $category;
            }
        }

        $sub_cat_by_parent = [];
        if (! empty($sub_categories)) {
            foreach ($sub_categories as $sub_category) {
                if (empty($sub_cat_by_parent[$sub_category['parent_id']])) {
                    $sub_cat_by_parent[$sub_category['parent_id']] = [];
                }

                $sub_cat_by_parent[$sub_category['parent_id']][] = $sub_category;
            }
        }

        foreach ($categories as $key => $value) {
            if (! empty($sub_cat_by_parent[$value['id']])) {
                $categories[$key]['sub_categories'] = $sub_cat_by_parent[$value['id']];
            }
        }

        return $categories;
    }

    /**
     * Category Dropdown
     *
     * @param  int  $business_id
     * @param  string  $type category type
     * @return array
     */
    public static function forDropdown($business_id, $type)
    {
        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->where('category_type', $type)
                            ->select(DB::raw('IF(short_code IS NOT NULL, CONCAT(name, "-", short_code), name) as name'), 'id')
                            ->orderBy('name', 'asc')
                            ->get();

        $dropdown = $categories->pluck('name', 'id');

        return $dropdown;
    }

    public function sub_categories()
    {
        return $this->hasMany(\App\Category::class, 'parent_id');
    }

    /**
     * Scope a query to only include main categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyParent($query)
    {
        return $query->where('parent_id', 0);
    }
}
