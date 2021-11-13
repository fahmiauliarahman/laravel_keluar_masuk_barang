<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarangKeluarDetail extends Model
{
    protected $table = 'barang_keluar_detail';
    protected $guarded = [];

    public function barang()
    {
        return $this->belongsTo('App\Barang', 'barang_id');
    }

    public function barang_keluar()
    {
        return $this->belongsTo('App\BarangKeluar', 'barang_keluar_id');
    }
}
