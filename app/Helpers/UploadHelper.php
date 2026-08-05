<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadHelper
{
    public static function upload(UploadedFile $file, string $directory = 'uploads', string $disk = 'public'): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($directory, $filename, $disk);
    }

    public static function delete(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->exists($path) && Storage::disk($disk)->delete($path);
    }
}