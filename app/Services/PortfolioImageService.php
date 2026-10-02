<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class PortfolioImageService
{
    public const MAX_BYTES = 204800;

    public function store(UploadedFile $file, int $companyId, string $group = 'gallery'): string
    {
        $bytes = file_get_contents($file->getRealPath());
        $source = $bytes === false ? false : @imagecreatefromstring($bytes);
        if (!$source) {
            throw new RuntimeException('The selected file is not a valid image.');
        }

        $source = $this->orient($source, $file);
        $width = imagesx($source);
        $height = imagesy($source);
        if ($width * $height > 40000000) {
            imagedestroy($source);
            throw new RuntimeException('The image dimensions are too large. Please choose a smaller photo.');
        }
        $scale = min(1, 1800 / max($width, $height));
        $targetWidth = max(320, (int) round($width * $scale));
        $targetHeight = max(200, (int) round($height * $scale));
        $encoded = null;

        for ($attempt = 0; $attempt < 8; $attempt++) {
            $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

            foreach ([82, 72, 62, 52, 42, 34] as $quality) {
                ob_start();
                imagejpeg($canvas, null, $quality);
                $candidate = ob_get_clean();
                if (is_string($candidate) && strlen($candidate) <= self::MAX_BYTES) {
                    $encoded = $candidate;
                    break 2;
                }
            }

            imagedestroy($canvas);
            $targetWidth = max(320, (int) ($targetWidth * .82));
            $targetHeight = max(200, (int) ($targetHeight * .82));
        }

        imagedestroy($source);
        if ($encoded === null) {
            throw new RuntimeException('This image could not be reduced below 200 KB. Please choose another image.');
        }

        $directory = "uploads/portfolio/{$companyId}/{$group}";
        File::ensureDirectoryExists(public_path($directory));
        $relativePath = $directory.'/'.Str::uuid().'.jpg';
        if (file_put_contents(public_path($relativePath), $encoded) === false) {
            throw new RuntimeException('The image could not be saved. Please try again or contact the administrator.');
        }

        return $relativePath;
    }

    public function delete(?string $path): void
    {
        if (!$path || !str_starts_with($path, 'uploads/portfolio/')) {
            return;
        }
        File::delete(public_path($path));
    }

    private function orient($image, UploadedFile $file)
    {
        if (!function_exists('exif_read_data') || !in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }
        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
        if ($rotated !== $image) {
            imagedestroy($image);
        }
        return $rotated;
    }
}
