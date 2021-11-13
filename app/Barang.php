<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Kategori;

class Barang extends Model
{
    protected $table = 'barang';
    protected $guarded = [];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
}
