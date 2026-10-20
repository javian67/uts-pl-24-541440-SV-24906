<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillController extends Controller
{
    /**
     * Display a listing of bills for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $bills = $request->user()->bills()->latest()->get();

        return response()->json([
            'message' => 'Bills retrieved successfully',
            'data' => $bills,
        ], 200);
    }

    /**
     * Store a newly created bill in storage for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_tagihan' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'tanggal_jatuh_tempo' => 'required|date',
            'status' => 'nullable|in:Belum Dibayar,Sudah Dibayar',
            'catatan' => 'nullable|string',
        ]);

        $bill = $request->user()->bills()->create($validated);

        return response()->json([
            'message' => 'Tagihan berhasil dibuat',
            'data' => $bill,
        ], 201);
    }

    /**
     * Display the specified bill if it belongs to the authenticated user.
     */
    public function show(Request $request, string|int $id): JsonResponse
    {
        $bill = $request->user()->bills()->find($id);

        if (!$bill) {
            return response()->json([
                'message' => 'Tagihan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail tagihan berhasil diambil',
            'data' => $bill,
        ], 200);
    }

    /**
     * Update the specified bill if it belongs to the authenticated user.
     */
    public function update(Request $request, string|int $id): JsonResponse
    {
        $bill = $request->user()->bills()->find($id);

        if (!$bill) {
            return response()->json([
                'message' => 'Tagihan tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'nama_tagihan' => 'sometimes|required|string|max:255',
            'kategori' => 'sometimes|required|string|max:255',
            'jumlah' => 'sometimes|required|numeric|min:0',
            'tanggal_jatuh_tempo' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:Belum Dibayar,Sudah Dibayar',
            'catatan' => 'nullable|string',
        ]);

        $bill->update($validated);

        return response()->json([
            'message' => 'Tagihan berhasil diperbarui',
            'data' => $bill,
        ], 200);
    }

    /**
     * Remove the specified bill from storage if it belongs to the authenticated user.
     */
    public function destroy(Request $request, string|int $id): JsonResponse
    {
        $bill = $request->user()->bills()->find($id);

        if (!$bill) {
            return response()->json([
                'message' => 'Tagihan tidak ditemukan',
            ], 404);
        }

        $bill->delete();

        return response()->json([
            'message' => 'Tagihan berhasil dihapus',
        ], 200);
    }
}
