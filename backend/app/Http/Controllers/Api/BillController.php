<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

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
        $validator = Validator::make($request->all(), [
            'nama_tagihan' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'tanggal_jatuh_tempo' => 'required|date',
            'status' => 'nullable|in:Belum Dibayar,Sudah Dibayar',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validasi penyimpanan tagihan baru gagal', [
                'user' => $request->user()?->id ?? 'guest',
                'aksi' => 'simpan_tagihan',
                'sebab' => 'Field input tagihan tidak valid: ' . implode(', ', array_keys($validator->errors()->toArray())),
                'invalid_fields' => array_keys($validator->errors()->toArray()),
            ]);

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $bill = $request->user()->bills()->create($validator->validated());

            Log::info('Data tagihan baru berhasil disimpan', [
                'user' => $request->user()->id,
                'aksi' => 'simpan_tagihan',
                'sebab' => 'Tagihan ID ' . $bill->id . ' (' . $bill->nama_tagihan . ') berhasil ditambahkan ke database',
            ]);

            return response()->json([
                'message' => 'Tagihan berhasil dibuat',
                'data' => $bill,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Gagal menyimpan tagihan ke basis data', [
                'user' => $request->user()?->id ?? 'guest',
                'aksi' => 'simpan_tagihan',
                'sebab' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan sistem atau gangguan koneksi basis data.',
            ], 500);
        }
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
            Log::warning('Upaya pembaruan tagihan tidak ditemukan', [
                'user' => $request->user()->id,
                'aksi' => 'update_tagihan',
                'sebab' => 'Tagihan dengan ID ' . $id . ' tidak ditemukan atau bukan milik pengguna',
            ]);

            return response()->json([
                'message' => 'Tagihan tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_tagihan' => 'sometimes|required|string|max:255',
            'kategori' => 'sometimes|required|string|max:255',
            'jumlah' => 'sometimes|required|numeric|min:0',
            'tanggal_jatuh_tempo' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:Belum Dibayar,Sudah Dibayar',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validasi pembaruan tagihan gagal', [
                'user' => $request->user()->id,
                'aksi' => 'update_tagihan',
                'sebab' => 'Field input perbaruan tidak valid: ' . implode(', ', array_keys($validator->errors()->toArray())),
                'invalid_fields' => array_keys($validator->errors()->toArray()),
            ]);

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $bill->update($validator->validated());

            Log::info('Data tagihan berhasil diperbarui', [
                'user' => $request->user()->id,
                'aksi' => 'update_tagihan',
                'sebab' => 'Perubahan tagihan ID ' . $bill->id . ' berhasil disimpan ke basis data',
            ]);

            return response()->json([
                'message' => 'Tagihan berhasil diperbarui',
                'data' => $bill,
            ], 200);
        } catch (Throwable $e) {
            Log::error('Gagal memperbarui tagihan ke basis data', [
                'user' => $request->user()->id,
                'aksi' => 'update_tagihan',
                'sebab' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan sistem atau gangguan koneksi basis data.',
            ], 500);
        }
    }

    /**
     * Remove the specified bill from storage if it belongs to the authenticated user.
     */
    public function destroy(Request $request, string|int $id): JsonResponse
    {
        $bill = $request->user()->bills()->find($id);

        if (!$bill) {
            Log::warning('Upaya penghapusan tagihan tidak ditemukan', [
                'user' => $request->user()->id,
                'aksi' => 'hapus_tagihan',
                'sebab' => 'Tagihan dengan ID ' . $id . ' tidak ditemukan atau bukan milik pengguna',
            ]);

            return response()->json([
                'message' => 'Tagihan tidak ditemukan',
            ], 404);
        }

        try {
            $billId = $bill->id;
            $bill->delete();

            Log::info('Data tagihan berhasil dihapus', [
                'user' => $request->user()->id,
                'aksi' => 'hapus_tagihan',
                'sebab' => 'Tagihan ID ' . $billId . ' berhasil dihapus dari basis data',
            ]);

            return response()->json([
                'message' => 'Tagihan berhasil dihapus',
            ], 200);
        } catch (Throwable $e) {
            Log::error('Gagal menghapus tagihan dari basis data', [
                'user' => $request->user()->id,
                'aksi' => 'hapus_tagihan',
                'sebab' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat menghapus tagihan.',
            ], 500);
        }
    }
}
