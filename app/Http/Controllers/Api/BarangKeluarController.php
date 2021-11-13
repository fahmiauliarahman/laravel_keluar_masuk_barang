<?php

namespace App\Http\Controllers\Api;

use App\Barang;
use App\BarangKeluar;
use App\BarangKeluarDetail;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BarangKeluarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $barang_keluar = BarangKeluar::paginate(10);
        return ResponseFormatter::success($barang_keluar);
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
        $filter = $request->only('kepada', 'no_faktur', 'resi_img', 'barang_id', 'jumlah', 'tanggal');

        if (count($request->input('barang_id')) !== count($request->input('jumlah'))) {
            return ResponseFormatter::error('Tipe barang dan jumlah barang tidak sama');
        }

        $validator = Validator::make($filter, [
            'kepada' => 'required',
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

        $barang_keluar = BarangKeluar::create([
            'kepada' => $filter['kepada'],
            'no_faktur' => $filter['no_faktur'],
            'resi_img' => $nama_file,
            'tanggal' => $filter['tanggal'],
        ]);

        for ($i = 0; $i < count($request->input('barang_id')); $i++) {
            try {
                $barang = Barang::findOrFail($request->input('barang_id')[$i]);
                if ($barang->stok < $request->input('jumlah')[$i]) {
                    return ResponseFormatter::error('Stok barang ' . $barang->nama_barang . ' tidak mencukupi');
                }
                $barang_keluar_detail = BarangKeluarDetail::create([
                    'barang_keluar_id' => $barang_keluar->id,
                    'barang_id' => $request->input('barang_id')[$i],
                    'jumlah' => $request->input('jumlah')[$i],
                ]);

                $barang->stok -= $request->input('jumlah')[$i];
                $barang->save();
            } catch (Exception $e) {
                return ResponseFormatter::error(__LINE__, 500, $e->getMessage());
            }
        }

        return ResponseFormatter::success([$barang_keluar, $barang_keluar_detail]);
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
        $barang_keluar = BarangKeluar::findOrFail($id);
        $barang_keluar_detail = BarangKeluarDetail::with('barang')->where('barang_keluar_id', $id)->get();

        $data = [
            'barang_keluar' => $barang_keluar,
            'barang_keluar_detail' => $barang_keluar_detail,
        ];
        return ResponseFormatter::success($data);
    }

    /**
     * Update the specified resource in storage.
     * ini belum 100% fixed, skip dulu karena waktu nya mepet, mungkin after akan saya perbaiki.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $barang_keluar = BarangKeluar::findOrFail($id);
        $filter = $request->only('kepada', 'no_faktur', 'resi_img', 'barang_id', 'jumlah');

        if (count($request->input('barang_id')) !== count($request->input('jumlah'))) {
            return ResponseFormatter::error('Tipe barang dan jumlah barang tidak sama');
        }

        $validator = Validator::make($filter, [
            'kepada' => 'required',
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
            'kepada' => $filter['kepada'],
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
            unlink(public_path() . '/' . $tujuan_upload . '/' . $barang_keluar->resi_img);
        }

        $barang_keluar->update($to_be_updated);

        for ($i = 0; $i < count($request->input('barang_id')); $i++) {
            try {
                $barang = Barang::findOrFail($request->input('barang_id')[$i]);
                $barang_keluar_detail = BarangKeluarDetail::where([
                    'barang_keluar_id' => $barang_keluar->id,
                    'barang_id' => $request->input('barang_id')[$i],
                ])->first();
                $barang->update([
                    'stok' => ($barang->stok + $barang_keluar_detail->jumlah) - $request->input('jumlah')[$i],
                ]);

                $barang_keluar_detail->update([
                    'jumlah' => $request->input('jumlah')[$i],
                ]);
            } catch (Exception $e) {
                return ResponseFormatter::error(__LINE__, 500, $e->getMessage());
            }
        }

        return ResponseFormatter::success([$barang_keluar, $barang_keluar_detail]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * mungkin ini belum 100% fixed, skip dulu karena waktu nya mepet, mungkin after akan saya perbaiki.
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        BarangKeluar::destroy($id);
        return ResponseFormatter::success(true);
    }
}
