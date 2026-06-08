<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends Controller
{
    public function serve(string $path)
    {
        if (!$this->isAllowedPath($path)) {
            throw new NotFoundHttpException();
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            return response()->view('errors.file-not-found', [
                'filename' => basename($path),
            ], 404);
        }

        return $disk->response($path);
    }

    /**
     * Periksa apakah path berada di folder yang diizinkan.
     */
    protected function isAllowedPath(string $path): bool
    {
        $allowed = [
            'surat-keluar/pdf/',
            'surat-keluar/lampiran/',
            'tanda-tangan/',
        ];

        foreach ($allowed as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
