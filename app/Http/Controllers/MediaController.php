<?php

namespace App\Http\Controllers;

use App\Support\MediaStorage;

class MediaController extends Controller
{
    public function show(string $path)
    {
        if (str_contains($path, '..')) {
            abort(404);
        }

        $disk = MediaStorage::disk();

        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path);
    }
}
