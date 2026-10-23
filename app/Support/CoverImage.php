<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CoverImage
{
    public const MAX_WIDTH = 1200;

    public const DISK = 'public';

    /**
     * Resize and re-encode an uploaded image as JPEG and store it. Returns the stored path.
     */
    public static function store(UploadedFile $file, string $directory = 'covers', int $maxWidth = self::MAX_WIDTH): string
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($source === false) {
            throw new RuntimeException('Unreadable image.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $targetWidth = min($width, $maxWidth);
        $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagefill($canvas, 0, 0, (int) imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($canvas, null, 82);
        $contents = (string) ob_get_clean();

        $path = $directory.'/'.Str::uuid().'.jpg';
        Storage::disk(self::DISK)->put($path, $contents);

        return $path;
    }

    public static function delete(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
