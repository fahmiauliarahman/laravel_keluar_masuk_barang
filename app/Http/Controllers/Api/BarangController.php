<?php

namespace App\Http\Controllers\Api;

use App\Barang;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $barang = Barang::with('kategori')->select('id', 'kode_barang', 'nama_barang', 'stok', 'kategori_id')->paginate(10);
        return ResponseFormatter::success($barang);
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
        $filter = $request->only('kode_barang', 'nama_barang', 'kategori_id', 'stok', 'keterangan');

        $validator = Validator::make($filter, [
            'kode_barang' => 'required|unique:barang',
            'nama_barang' => 'required|string',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'numeric|min:0',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return ResponseFormatter::error(NULL, 419, $validator->messages());
        }

        $barang = Barang::create($filter);
        return ResponseFormatter::success($barang);
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
        $barang = Barang::with('kategori')->findOrFail($id);

        return ResponseFormatter::success($barang);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $filter = $request->only('kode_barang', 'nama_barang', 'kategori_id', 'stok', 'keterangan');
        $barang = Barang::findOrFail($id);
        $validator = Validator::make($filter, [
            'kode_barang' => 'required|unique:barang,kode_barang,' . $id,
            'nama_barang' => 'required|string',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'numeric|min:0',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return ResponseFormatter::error(NULL, 419, $validator->messages());
        }

        $barang->update($filter);
        return ResponseFormatter::success($barang);
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
        Barang::destroy($id);
        return ResponseFormatter::success(true);
    }
}
