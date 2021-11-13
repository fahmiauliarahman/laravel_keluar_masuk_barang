<?php

namespace App\Http\Controllers\Api;


use App\Kategori;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $kategori = Kategori::paginate(10);
        return ResponseFormatter::success($kategori);
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
        $filter = $request->only('nama');

        $validator = Validator::make($request->all(), [
            'nama' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return ResponseFormatter::error(NULL, 419, $validator->messages());
        }

        $kategori = Kategori::create($filter);
        return ResponseFormatter::success($kategori);
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
        $kategori = Kategori::findOrFail($id);

        return ResponseFormatter::success($kategori);
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
        $filter = $request->only('nama');
        $kategori = Kategori::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return ResponseFormatter::error(NULL, 419, $validator->messages());
        }

        $kategori->update($filter);
        return ResponseFormatter::success($kategori);
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
        Kategori::destroy($id);
        return ResponseFormatter::success(true);
    }
}
