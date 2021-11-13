<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Barang;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $guarded = [];

    public function barang()
    {
        return $this->hasMany(Barang::class);
    }
}
