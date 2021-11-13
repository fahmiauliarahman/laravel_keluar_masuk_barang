<?php

namespace App\Http\Controllers\Api;

use App\BarangKeluar;
use App\BarangKeluarDetail;
use App\BarangMasuk;
use App\BarangMasukDetail;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LaporanController extends Controller
{
    public function report(Request $request)
    {
        $filter = $request->only('from', 'to');

        $validator = Validator::make($filter, [
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return ResponseFormatter::error(NULL, 419, $validator->messages());
        }

        if (Carbon::parse($filter['from'])->gt(Carbon::parse($filter['to']))) {
            return ResponseFormatter::error(NULL, 419, 'From date must be before to date');
        }


        $barang_masuk = BarangMasuk::whereBetween('tanggal', [$filter['from'], $filter['to']])->get()->toArray();
        for ($i = 0; $i < count($barang_masuk); $i++) {
            $barang_masuk_detail = BarangMasukDetail::with('barang')->where('barang_masuk_id', $barang_masuk[$i]['id'])->get();

            $barang_masuk[$i]['detail'] = $barang_masuk_detail;
        }

        $barang_keluar = BarangKeluar::whereBetween('tanggal', [$filter['from'], $filter['to']])->get()->toArray();
        for ($i = 0; $i < count($barang_keluar); $i++) {
            $barang_keluar_detail = BarangKeluarDetail::with('barang')->where('barang_keluar_id', $barang_keluar[$i]['id'])->get();

            $barang_keluar[$i]['detail'] = $barang_masuk_detail;
        }

        $data = [
            'barang_masuk' => $barang_masuk,
            'barang_keluar' => $barang_keluar,
        ];

        return ResponseFormatter::success($data);
    }

    public function stok()
    {
        $barang = DB::select("SELECT a.id, (SELECT x.nama_barang FROM barang x where a.id = x.id  ORDER BY x.id asc limit 1) AS nama_barang,  (SELECT x.kode_barang FROM barang x where a.id = x.id  ORDER BY x.id asc limit 1) AS kode_barang,  (SELECT x.stok FROM barang x where a.id = x.id  ORDER BY x.id asc limit 1) AS stok_barang_saat_ini, SUM(b.jumlah) as masuk, SUM(c.jumlah) as keluar FROM barang a LEFT JOIN barang_masuk_detail AS b ON b.barang_id = a.id LEFT JOIN barang_keluar_detail as c on c.id = a.id GROUP BY a.id");

        return ResponseFormatter::success($barang);
    }
}
