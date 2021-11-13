<?php

namespace App\Http\Controllers\Api;

use App\BarangKeluar;
use App\BarangMasuk;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        $data_barang_masuk = BarangMasuk::whereBetween('created_at', [$filter['from'], $filter['to']])->get();
        $data_barang_keluar = BarangKeluar::whereBetween('created_at', [$filter['from'], $filter['to']])->get();

        return ResponseFormatter::success([$data_barang_masuk, $data_barang_keluar]);
    }
}
