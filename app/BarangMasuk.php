<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';
    protected $guarded = [];

    public function barang_masuk_detail()
    {
        return $this->hasMany(BarangMasukDetail::class);
    }
}
