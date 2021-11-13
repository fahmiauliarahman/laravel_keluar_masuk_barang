<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarangMasukDetail extends Model
{
    protected $table = 'barang_masuk_detail';
    protected $guarded = [];

    public function barang()
    {
        return $this->belongsTo('App\Barang', 'barang_id');
    }

    public function barang_masuk()
    {
        return $this->belongsTo('App\BarangMasuk', 'barang_masuk_id');
    }
}
