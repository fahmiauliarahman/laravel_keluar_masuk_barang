<?php

namespace App\Http\Controllers\Api;

use App\Barang;
use App\BarangMasukDetail;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\BarangMasuk;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BarangMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $barang_masuk = BarangMasuk::paginate(10);
        return ResponseFormatter::success($barang_masuk);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $filter = $request->only('dari', 'no_faktur', 'resi_img', 'barang_id', 'jumlah', 'tanggal');

        if (count($request->input('barang_id')) !== count($request->input('jumlah'))) {
            return ResponseFormatter::error('Tipe barang dan jumlah barang tidak sama');
        }

        $validator = Validator::make($filter, [
            'dari' => 'required',
            'no_faktur' => 'required',
            'tanggal' => 'required|date_format:Y-m-d',
            'resi_img' => 'required|file|image|mimes:jpeg,png,jpg|max:2048',
            "barang_id" => "required|array",
            "barang_id.*" => "required|exists:barang,id",
            "jumlah" => "required|array",
            "jumlah.*" => "required|min:0",
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return ResponseFormatter::error(NULL, 419, $validator->messages());
        }

        // menyimpan data file yang diupload ke variabel $file
        $file = $request->file('resi_img');

        $nama_file = time() . "_" . $file->getClientOriginalName();

        // isi dengan nama folder tempat kemana file diupload
        $tujuan_upload = 'a930f5a435d7dfacf3ab12e3b5539cbf1c1ad81d'; //sha1 dari faktur_images
        $file->move($tujuan_upload, $nama_file);

        $barang_masuk = BarangMasuk::create([
            'dari' => $filter['dari'],
            'no_faktur' => $filter['no_faktur'],
            'resi_img' => $nama_file,
            'tanggal' => $filter['tanggal'],
        ]);

        for ($i = 0; $i < count($request->input('barang_id')); $i++) {
            try {
                $barang = Barang::findOrFail($request->input('barang_id')[$i]);
                $barang_masuk_detail = BarangMasukDetail::create([
                    'barang_masuk_id' => $barang_masuk->id,
                    'barang_id' => $request->input('barang_id')[$i],
                    'jumlah' => $request->input('jumlah')[$i],
                ]);

                $barang->update([
                    'stok' => $barang->stok + $request->input('jumlah')[$i],
                ]);
            } catch (Exception $e) {
                return ResponseFormatter::error(__LINE__, 500, $e->getMessage());
            }
        }

        return ResponseFormatter::success([$barang_masuk, $barang_masuk_detail]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $barang_masuk = BarangMasuk::findOrFail($id);
        $barang_masuk_detail = BarangMasukDetail::with('barang')->where('barang_masuk_id', $id)->get();

        $data = [
            'barang_masuk' => $barang_masuk,
            'barang_masuk_detail' => $barang_masuk_detail,
        ];
        return ResponseFormatter::success($data);
    }

    /**
     * Update the specified resource in storage.
     * ini belum 100% fixed, skip dulu karena waktu nya mepet, mungkin after akan saya perbaiki
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $barang_masuk = BarangMasuk::findOrFail($id);
        $filter = $request->only('dari', 'no_faktur', 'resi_img', 'barang_id', 'jumlah');

        return ResponseFormatter::success($filter);

        if (count($request->input('barang_id')) !== count($request->input('jumlah'))) {
            return ResponseFormatter::error('Tipe barang dan jumlah barang tidak sama');
        }

        $validator = Validator::make($filter, [
            'dari' => 'required',
            'no_faktur' => 'required',
            'tanggal' => 'required|date_format:Y-m-d',
            'resi_img' => 'file|image|mimes:jpeg,png,jpg|max:2048',
            "barang_id" => "required|array",
            "barang_id.*" => "required|exists:barang,id",
            "jumlah" => "required|array",
            "jumlah.*" => "required|min:0",
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return ResponseFormatter::error(NULL, 419, $validator->messages());
        }

        $to_be_updated = [
            'dari' => $filter['dari'],
            'no_faktur' => $filter['no_faktur'],
            'tanggal' => $filter['tanggal'],
        ];


        if ($request->hasFile('resi_img')) {
            // menyimpan data file yang diupload ke variabel $file
            $file = $request->file('resi_img');
            $nama_file = time() . "_" . $file->getClientOriginalName();
            $to_be_updated['resi_img'] = $nama_file;
            // isi dengan nama folder tempat kemana file diupload
            $tujuan_upload = 'a930f5a435d7dfacf3ab12e3b5539cbf1c1ad81d'; //sha1 dari faktur_images
            $file->move($tujuan_upload, $nama_file);
            unlink(public_path() . '/' . $tujuan_upload . '/' . $barang_masuk->resi_img);
        }


        $barang_masuk->update($to_be_updated);

        for ($i = 0; $i < count($request->input('barang_id')); $i++) {
            try {
                $barang = Barang::findOrFail($request->input('barang_id')[$i]);

                $barang_masuk_detail = BarangMasukDetail::where([
                    'barang_masuk_id' => $barang_masuk->id,
                    'barang_id' => $request->input('barang_id')[$i],
                ])->first();
                $barang->update([
                    'stok' => ($barang->stok - $barang_masuk_detail->jumlah) + $request->input('jumlah')[$i],
                ]);

                $barang_masuk_detail->update([
                    'jumlah' => $request->input('jumlah')[$i],
                ]);
            } catch (Exception $e) {
                return ResponseFormatter::error(__LINE__, 500, $e->getMessage());
            }
        }

        return ResponseFormatter::success([$barang_masuk, $barang_masuk_detail]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        BarangMasuk::destroy($id);
        return ResponseFormatter::success(true);
    }
}
