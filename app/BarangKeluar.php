<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    protected $table = 'barang_keluar';
    protected $guarded = [];

    public function barang_keluar_detail()
    {
        return $this->hasMany(BarangKeluarDetail::class);
    }
}
