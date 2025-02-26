<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller {

    /**
     * Handles the download route for authorized users
     *
     * @param Request $request
     * @param string $file
     */
    public function download(Request $request, $type, $file)
    {
        $filePath = $file;
        if (!Storage::exists($filePath)) {
            $filePath = "/{$type}/{$file}";
        }
        if (!Storage::exists($filePath)) {
            $filePath = '/download/'. $file;
        }
        abort_unless(Storage::exists($filePath), 404);
        $ext = File::extension(storage_path($filePath));
        // It is assumed by convension that the url is kebab-cased
        $type = str_replace('-', '_', $type);
        return Storage::download(
            $filePath,
            $type.'_'.date('YmdHis').".$ext"
        );
    }

    /**
     * Handles the download route for authorized users
     *
     * @param Request $request
     * @param string $file
     */
    public function view(Request $request, $name, $file)
    {
        $filepath = $file;
        
        if (!Storage::exists($filepath)) {
            $filepath = "/{$name}/{$file}";
            $name = '';
        }

        abort_unless(Storage::exists($filepath), 404);

        $ext = File::extension(storage_path($filepath));

        // It is assumed by convention that the url is kebab-cased
        $filename = ($name ? str_replace('-', '_', $name) : pathinfo($filepath, PATHINFO_FILENAME)) . ".$ext";
        
        $headers = [
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ];
        
        return response()->file(Storage::path($filepath), $headers);
    }
}
