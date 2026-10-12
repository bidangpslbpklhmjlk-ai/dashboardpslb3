<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ArmadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ubah sesuai policy/permission
    }

    public function rules(): array
    {
        $armadaId = $this->route('armada')?->id;

        return [
            'nama' => 'required|string|max:150|unique:armadas,nama,' . $armadaId,
            'tipe' => 'required|in:perusahaan,dinas',
            'pembayaran' => 'required|in:gratis,bayar',
            'nomor_polisi' => 'nullable|string|max:20',
            'jenis_kendaraan' => 'nullable|string|max:50',
            'tahun_pengadaan' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'kondisi' => 'nullable|in:baik,rusak_ringan,rusak_berat,tidak_beroperasi',
            'foto' => 'nullable|image|max:2048',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'aktif' => 'sometimes|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('tipe') === 'dinas') {
            $this->merge(['pembayaran' => 'gratis']);
        }

        if ($this->has('nomor_polisi')) {
            $this->merge([
                'nomor_polisi' => strtoupper(trim($this->input('nomor_polisi')))
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama armada wajib diisi.',
            'nama.unique' => 'Nama armada sudah terdaftar.',
            'tipe.required' => 'Tipe armada wajib dipilih.',
            'tipe.in' => 'Tipe armada tidak valid.',
            'pembayaran.required' => 'Jenis pembayaran wajib dipilih.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
            'tahun_pengadaan.min' => 'Tahun pengadaan tidak valid.',
            'tahun_pengadaan.max' => 'Tahun pengadaan tidak boleh melebihi tahun depan.',
        ];
    }
}