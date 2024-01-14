<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait EnumListTrait
{
    public static function list(): Collection
    {
        return collect(self::cases())
            ->map(fn($item) => ['id' => $item->value, 'name' => $item->name]);
    }

    public static function findById($id): Collection
    {
        $data = collect(self::cases())
            ->map(fn($item) => ['id' => $item->value, 'name' => $item->name]);
        return $data->where('id', $id);
    }
}
