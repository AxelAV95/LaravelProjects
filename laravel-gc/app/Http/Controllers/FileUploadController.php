<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Storage\StorageClient;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        // Validar que el archivo es un PDF
        $request->validate([
            'file' => 'required|mimes:pdf|max:2048',
        ]);

        // Obtener el archivo
        $file = $request->file('file');

        // Configurar Google Cloud Storage
        $projectId = env('GOOGLE_CLOUD_PROJECT_ID');
        $bucketName = env('GOOGLE_CLOUD_STORAGE_BUCKET');
        $storage = new StorageClient([
            'projectId' => $projectId,
            'keyFilePath' => env('GOOGLE_APPLICATION_CREDENTIALS'),
        ]);

        $bucket = $storage->bucket($bucketName);

        // Generar un nombre único para el archivo
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Subir el archivo a Google Cloud Storage
        $bucket->upload(
            file_get_contents($file->getRealPath()),
            [
                'name' => $fileName,
            ]
        );

        // Retornar la respuesta
        return response()->json([
            'message' => 'Archivo subido exitosamente',
            'file_name' => $fileName,
        ], 200);
    }
}