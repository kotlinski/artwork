<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RegenerateNewsImages extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'news:regenerate-images';
    protected $description = 'Regenerate all news image variants from originals.';

    public function run(array $params)
    {
        $newsDir = FCPATH . 'media/news/';
        $originalDir = $newsDir . 'original/';

        if (!is_dir($originalDir)) {
            CLI::error('No original directory found.');
            return;
        }

        $originals = glob($originalDir . '*.*') ?: [];
        if (empty($originals)) {
            CLI::write('No original images found.', 'yellow');
            return;
        }

        $cwebpBin = trim((string) shell_exec('which cwebp 2>/dev/null'));
        $convertBin = trim((string) shell_exec('which convert 2>/dev/null'));

        $variants = [
            'medium/' => ['maxW' => 380, 'maxH' => 280, 'quality' => 65],
            'large/'  => ['maxW' => 760, 'maxH' => 560, 'quality' => 72],
        ];

        foreach ($originals as $origPath) {
            $baseName = pathinfo($origPath, PATHINFO_FILENAME);
            $webpName = $baseName . '.webp';

            CLI::write("Processing: {$baseName}");

            // Root fullscreen file (reoriented, no resize)
            $this->generateRoot($origPath, $newsDir . $webpName, 87, $convertBin, $cwebpBin);

            foreach ($variants as $subdir => $opts) {
                $targetDir = $newsDir . $subdir;
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }

                $outPath = $targetDir . $webpName;

                $tmpPng  = tempnam(sys_get_temp_dir(), 'regen_') . '.png';
                $converted = false;

                if ($convertBin !== '') {
                    // fit within bounding box with ImageMagick
                    $cmd = sprintf(
                        '%s %s -auto-orient -resize %dx%d\> -quality 95 %s 2>/dev/null',
                        escapeshellarg($convertBin),
                        escapeshellarg($origPath),
                        $opts['maxW'],
                        $opts['maxH'],
                        escapeshellarg($tmpPng)
                    );
                    exec($cmd, $out, $rc);
                    $converted = ($rc === 0 && is_file($tmpPng));
                }

                if (!$converted) {
                    $this->fitWithGd($origPath, $tmpPng, $opts['maxW'], $opts['maxH']);
                    $converted = is_file($tmpPng);
                }

                if ($converted && $cwebpBin !== '') {
                    $cmd = sprintf(
                        '%s -q %d -o %s -- %s 2>/dev/null',
                        escapeshellarg($cwebpBin),
                        $opts['quality'],
                        escapeshellarg($outPath),
                        escapeshellarg($tmpPng)
                    );
                    exec($cmd);
                } elseif ($converted) {
                    // fallback: copy png as webp via GD
                    $this->pngToWebp($tmpPng, $outPath, $opts['quality']);
                }

                if (is_file($tmpPng)) {
                    @unlink($tmpPng);
                }

                $size = is_file($outPath) ? round(filesize($outPath) / 1024, 1) : '??';
                CLI::write("  {$subdir}{$webpName}: {$size} KB");
            }
        }

        CLI::write('Done!', 'green');
    }

    private function fitWithGd(string $src, string $dst, int $maxW, int $maxH): void
    {
        $info = getimagesize($src);
        if (!$info) return;

        [$srcW, $srcH, $type] = $info;

        // EXIF rotate for JPEG
        $exifAngle = 0;
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($src);
            $exifAngle = match ((int) ($exif['Orientation'] ?? 0)) {
                3 => 180, 6 => -90, 8 => 90, default => 0,
            };
            if (abs($exifAngle) === 90) [$srcW, $srcH] = [$srcH, $srcW];
        }

        $scale  = min($maxW / $srcW, $maxH / $srcH, 1.0);
        $dstW   = (int) round($srcW * $scale);
        $dstH   = (int) round($srcH * $scale);

        $srcImg = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_WEBP => imagecreatefromwebp($src),
            default        => null,
        };
        if (!$srcImg) return;

        if ($exifAngle !== 0) {
            $srcImg = imagerotate($srcImg, $exifAngle, 0);
        }

        $dstImg = imagecreatetruecolor($dstW, $dstH);
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $dstW, $dstH, imagesx($srcImg), imagesy($srcImg));
        imagepng($dstImg, $dst);
        imagedestroy($srcImg);
        imagedestroy($dstImg);
    }

    private function pngToWebp(string $src, string $dst, int $quality): void
    {
        $img = imagecreatefrompng($src);
        if (!$img) return;
        imagewebp($img, $dst, $quality);
        imagedestroy($img);
    }

    private function generateRoot(string $src, string $dst, int $quality, string $convertBin, string $cwebpBin): void
    {
        $tmpPng = tempnam(sys_get_temp_dir(), 'regen_') . '.png';
        $converted = false;

        if ($convertBin !== '') {
            $cmd = sprintf(
                '%s %s -auto-orient -quality 95 %s 2>/dev/null',
                escapeshellarg($convertBin),
                escapeshellarg($src),
                escapeshellarg($tmpPng)
            );
            exec($cmd, $out, $rc);
            $converted = ($rc === 0 && is_file($tmpPng));
        }

        if (!$converted) {
            $this->fitWithGd($src, $tmpPng, 99999, 99999);
            $converted = is_file($tmpPng);
        }

        if ($converted && $cwebpBin !== '') {
            $cmd = sprintf(
                '%s -q %d -o %s -- %s 2>/dev/null',
                escapeshellarg($cwebpBin),
                $quality,
                escapeshellarg($dst),
                escapeshellarg($tmpPng)
            );
            exec($cmd);
        } elseif ($converted) {
            $this->pngToWebp($tmpPng, $dst, $quality);
        }

        if (is_file($tmpPng)) {
            @unlink($tmpPng);
        }
    }
}
