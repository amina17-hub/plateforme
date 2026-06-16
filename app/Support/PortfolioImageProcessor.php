<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PortfolioImageProcessor
{
    public function storeWatermarkedImage(UploadedFile $file, string $artisanName): string
    {
        $image = $this->createImageResource($file);

        if (! $image) {
            throw new RuntimeException('Impossible de traiter cette image.');
        }

        $image = $this->applyOrientation($image, $file);
        $image = $this->normalizeCanvas($image);
        $image = $this->resizeImage($image, 1600, 1600);
        $this->applyWatermark($image, $artisanName);

        $path = 'portfolio/' . Str::uuid() . '.jpg';

        ob_start();
        imagejpeg($image, null, 80);
        $binary = ob_get_clean();

        if ($binary === false) {
            imagedestroy($image);
            throw new RuntimeException('Impossible de finaliser cette image.');
        }

        Storage::disk('local')->put($path, $binary);
        imagedestroy($image);

        return $path;
    }

    protected function createImageResource(UploadedFile $file)
    {
        $mimeType = strtolower((string) $file->getMimeType());
        $realPath = $file->getRealPath();

        if (! $realPath) {
            return false;
        }

        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($realPath),
            'image/png' => imagecreatefrompng($realPath),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($realPath) : false,
            default => false,
        };
    }

    protected function applyOrientation($image, UploadedFile $file)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $realPath = $file->getRealPath();
        $mimeType = strtolower((string) $file->getMimeType());

        if (! $realPath || ! in_array($mimeType, ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }

        $exif = @exif_read_data($realPath);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $white = imagecolorallocate($image, 255, 255, 255);

        return match ($orientation) {
            3 => imagerotate($image, 180, $white),
            6 => imagerotate($image, -90, $white),
            8 => imagerotate($image, 90, $white),
            default => $image,
        };
    }

    protected function normalizeCanvas($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($canvas, 255, 255, 255);

        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);

        return $canvas;
    }

    protected function resizeImage($image, int $maxWidth, int $maxHeight)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxWidth && $height <= $maxHeight) {
            return $image;
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($resized, 255, 255, 255);

        imagefill($resized, 0, 0, $white);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }

    protected function applyWatermark($image, string $artisanName): void
    {
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);
        $label = trim(Str::limit($artisanName, 28, '') . ' | artisan skikda');
        $fontPath = $this->watermarkFontPath();

        if ($fontPath && function_exists('imagettftext')) {
            $angle = -32;
            $fontSize = max(16, min(38, (int) round($width * 0.027)));
            $box = imagettfbbox($fontSize, $angle, $fontPath, $label);

            if ($box === false) {
                return;
            }

            $textWidth = max($box[0], $box[2], $box[4], $box[6]) - min($box[0], $box[2], $box[4], $box[6]);
            $textHeight = max($box[1], $box[3], $box[5], $box[7]) - min($box[1], $box[3], $box[5], $box[7]);
            $stepX = max(130, (int) round($textWidth * 0.95));
            $stepY = max(58, (int) round($textHeight * 1.75));
            $textColor = imagecolorallocatealpha($image, 255, 255, 255, 72);
            $shadowColor = imagecolorallocatealpha($image, 15, 23, 42, 106);

            for ($y = -$stepY; $y < $height + $stepY; $y += $stepY) {
                $rowOffset = ((int) round($y / $stepY) % 2) === 0 ? 0 : (int) round($stepX / 2);

                for ($x = -$stepX; $x < $width + $stepX; $x += $stepX) {
                    imagettftext($image, $fontSize, $angle, $x + $rowOffset + 2, $y + 2, $shadowColor, $fontPath, $label);
                    imagettftext($image, $fontSize, $angle, $x + $rowOffset, $y, $textColor, $fontPath, $label);
                }
            }

            return;
        }

        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($label);
        $textHeight = imagefontheight($font);
        $stepX = max(130, (int) round($textWidth * 0.95));
        $stepY = max(46, (int) round($textHeight * 2.4));
        $shadowColor = imagecolorallocatealpha($image, 15, 23, 42, 106);
        $textColor = imagecolorallocatealpha($image, 255, 255, 255, 72);

        for ($y = 0; $y < $height; $y += $stepY) {
            for ($x = 0; $x < $width; $x += $stepX) {
                imagestring($image, $font, $x + 2, $y + 2, $label, $shadowColor);
                imagestring($image, $font, $x, $y, $label, $textColor);
            }
        }
    }

    protected function watermarkFontPath(): ?string
    {
        $paths = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/Library/Fonts/Arial Bold.ttf',
            '/System/Library/Fonts/SFNS.ttf',
            '/System/Library/Fonts/SFCompact.ttf',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
