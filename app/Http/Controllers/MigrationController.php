<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use App\Models\PenerimaSurat;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrationController extends Controller
{
    private string $oldConn = 'plo_old';

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole('super-admin'), 403, 'Hanya super-admin yang dapat mengakses migrasi.');
    }

    /**
     * Step 1: Overview — tampilkan statistik data lama.
     */
    public function index()
    {
        $this->authorizeSuperAdmin();
        $insert_unit_kerja = $insert_user = DB::connection($this->oldConn)
            ->table('unit_kerjas')
            ->get()
            ->map(fn($unit_kerja) => [
                'id'         => $unit_kerja->id_unit_kerja,
                'kode'       => $unit_kerja->id_unit_kerja,
                'nama'       => $unit_kerja->unit_kerja,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();

        UnitKerja::insertOrIgnore($insert_unit_kerja);

        $insert_user = DB::connection($this->oldConn)
            ->table('users')
            ->get()
            ->skip(1)
            ->map(fn($user) => [
                'id'             => $user->id,
                'name'           => $user->nama_lengkap,
                'email'          => $user->email,
                'password'       => $user->password,
                'unit_kerja_id'  => $user->unit_kerja_id,
                'created_at'     => now(),
                'updated_at'     => now(),
            ])
            ->values()
            ->all();

        User::insertOrIgnore($insert_user);

        $array_disposisi = DB::connection($this->oldConn)
            ->table('disposisi')
            ->get()
            ->groupBy('surat_id')
            ->toArray();

        $disposisi = collect($array_disposisi)
            ->map(function ($items) {
                return collect($items)
                    ->map(fn($item) => [
                        'surat_keluar_id'   => $item->surat_id,
                        'pengguna_id'       => $item->ke_user_id,
                        'pengirim_id'       => $item->dari_user_id,
                        'keterangan'        => $item->catatan,
                        'alasan'            => '',
                        'status'            => 'diteruskan',
                        'dibaca'            => 1,
                        'created_at'        => $item->created_at,
                        'updated_at'        => $item->updated_at,
                    ])
                    ->all();
            })
            ->all();

        $penerima_status = [
            'baru'      => 'diteruskan',
            'selesai'   => 'diterima',
            'diproses' => 'diteruskan'
        ];

        $array_penerima_surat = DB::connection($this->oldConn)
            ->table('surat')
            ->get()
            ->groupBy('id')
            ->toArray();

        $penerima_surat = collect($array_penerima_surat)
            ->map(function ($items) use ($penerima_status) {
                return collect($items)
                    ->map(fn($penerima) => [
                        'user_id'           => $penerima->tujuan_user_id,
                        'dibaca'            => 1,
                        'dibaca_at'         => now(),
                        'created_at'        => $penerima->created_at,
                        'updated_at'        => $penerima->updated_at,
                        'status'            => $penerima_status[$penerima->status_surat] ?? 'diteruskan',
                    ])
                    ->all();
            })
            ->all();


        $surat_status = [
            'draft'     => 'd',
            'selesai'   => 'e',
            'diproses' => 's'
        ];

        $insert_surat = DB::connection($this->oldConn)
            ->table('surat')
            ->get()
            ->map(function ($surat) use ($disposisi, $penerima_surat, $surat_status) {

                $uuid = Str::uuid()->toString();

                $file = 'surat-keluar/pdf/' . basename($surat->lampiran);
                $lampiran=null;

                if ($surat->pdf_file_path) {
                    $lampiran = 'surat-keluar/lampiran/' . basename($surat->pdf_file_path);
                }

                return [
                    'id_old'            => $surat->id,
                    'id'                => $uuid,
                    'user_id'           => $surat->created_by,
                    'nomor_surat'       => $surat->nomor_surat,
                    'perihal'           => $surat->judul,
                    'tanggal_surat'     => $surat->tanggal_surat,
                    'jenis_surat'       => $surat->jenis_surat,
                    'metode_surat'      => 'upload',
                    'file_pdf'          => $file,
                    'lampiran'          => $lampiran,
                    'status'            => $surat_status[$surat->status_surat] ?? 's',
                    'sent_at'           => null,
                    'created_at'        => $surat->created_at,
                    'updated_at'        => $surat->updated_at,
                    'disposisi'         => collect($disposisi[$surat->id] ?? [])
                        ->map(fn($item) => array_merge($item, [
                            'id' => Str::uuid()->toString(),
                            'surat_keluar_id' => $uuid,
                        ]))
                        ->all(),
                    'penerima'          => collect($penerima_surat[$surat->id] ?? [])
                        ->map(fn($item) => array_merge($item, [
                            'surat_keluar_id' => $uuid,
                        ]))
                        ->all(),
                ];
            })
            ->values()
            ->all();

        $suratRows = [];
        $disposisiRows = [];
        $penerimaRows = [];
        foreach ($insert_surat as $surat) {

            $suratRows[] = collect($surat)
                ->except(['id_old', 'disposisi', 'penerima'])
                ->toArray();

            foreach ($surat['disposisi'] as $disposisi) {
                $disposisiRows[] = $disposisi;
            }

            foreach ($surat['penerima'] as $penerima) {
                if (!$penerima['user_id']) {
                    continue;
                }
                $penerimaRows[] = $penerima;
            }
        }

        DB::transaction(function () use (
            $suratRows,
            $disposisiRows,
            $penerimaRows
        ) {

            SuratKeluar::insert($suratRows);

            Disposisi::insert($disposisiRows);

            PenerimaSurat::insert($penerimaRows);
        });
    }
}
