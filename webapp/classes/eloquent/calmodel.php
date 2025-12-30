<?php
namespace Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/*
 Ezt önmagában sehol nem használjuk. 
 A különféle "cal"-al kezdődő eloquent modellek használják.
 Programozástörténeti hagyaták. Át lehetne alakítani.
 
*/

class CalModel extends Model
{
    protected array $excludeFromArray = ['created_at', 'updated_at'];

    public function toArray()
    {
        $original = parent::toArray();

        return collect($original)->reject(function ($value, $key) {
            return in_array($key, $this->excludeFromArray);
        })->mapWithKeys(function ($value, $key) {
            return [Str::camel($key) => $value];
        })->toArray();
    }

    public function fill(array $attributes)
    {
        // camelCase → snake_case
        $snakeCased = [];
        foreach ($attributes as $key => $value) {
            $snakeCased[Str::snake($key)] = $value;
        }

        return parent::fill($snakeCased);
    }

    static function arrayKeysToSnakeCase(array $array): array
    {
        return collect($array)->mapWithKeys(function ($value, $key) {
            return [Str::snake($key) => $value];
        })->toArray();
    }
}

