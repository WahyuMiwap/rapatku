<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    public static function defaultCategories(): array
    {
        return ['Umum', 'P2M', 'Humas', 'Brantas', 'Rehabilitasi'];
    }

    /**
     * Dapatkan daftar nama kategori untuk dropdown.
     * Jika tabel kosong, kembalikan default.
     */
    public static function getList(): array
    {
        $list = static::orderBy('name')->pluck('name')->toArray();

        return ! empty($list) ? $list : static::defaultCategories();
    }
}
