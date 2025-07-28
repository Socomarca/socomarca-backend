<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class S3Controller extends Controller
{
    public function upload(Request $request)
    {
        Log::info('Intentando subir archivo', ['file' => $request->file('file')]);

        $request->validate([
            'file' => 'required|file|max:10240', 
            'folder' => 'nullable|string', 
        ]);

        $folder = $request->input('folder', 'test'); 
        $file = $request->file('file');
        
        
        $originalName = $file->getClientOriginalName();
        
        
        $path = Storage::disk('s3')->putFileAs($folder, $file, $originalName);

        Log::info('Resultado de putFileAs', ['path' => $path]);

        if (!$path) {
            return response()->json([
                'message' => 'No se pudo subir el archivo',
            ], 400);
        }

        return response()->json([
            'message' => 'Archivo subido correctamente',
            'path' => $path,
            'url' => config('filesystems.disks.s3.endpoint') . '/' . config('filesystems.disks.s3.bucket') . '/' . $path,
        ]);
    }

    public function list(Request $request)
    {
        $folder = $request->input('folder');
        
        // Si no se especifica carpeta, lista todos los archivos del bucket
        $files = $folder ? Storage::disk('s3')->files($folder) : Storage::disk('s3')->allFiles();
        
        return response()->json([
            'files' => $files,
            'folder' => $folder ?? 'root'
        ]);
    }
}