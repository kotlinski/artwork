<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RegenerateArtworkImages extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'artwork:regenerate-images';
    protected $description = 'Regenerate all artwork (konst) image variants from originals.';

    public function run(array $params)
    {
        $konstDir    = FCPATH . 'konst/';
        $originalDir = $konstDir . 'original/';

        if (!is_dir($originalDir)) {
            CLI::error('No konst/original directory found.');
            return;
        }

        $originals = glob($originalDir . '*.*') ?: [];
        if (empty($originals)) {
            CLI::write('No original images found.', 'yellow');
            return;
        }

        $cwebpBin   = trim((string) shell_exec('which cwebp 2>/dev/null'));
        $convertBin = trim((string) shell_exec('which convert 2>/dev/null'));

        // square/ and square2x/ — center-cropped squares
        $squareVariants = [
            'square/'   => 122,
            'square2x/' => 244,
        ];

        // thumb/ and thumb2x/ — fit within bounding box
        $fitVariants = [
            'thumb/'   => [122, 122],
            'thumb2x/' => [244, 244],
        ];

        $total = count($originals);
        foreach ($originals as $i => $origPath) {
            $baseName = pathinfo($origPath, PATHINFO_FILENAME);
            $webpName = $baseName . '.webp';

            CLI::write('[' . ($i + 1) . "/{$total}] {$baseName}");

            // Square-crop variants
            foreach ($squareVariants as $subdir => $size) {
                $targetDir = $konstDir . $subdir;
                if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);
                $outPath = $targetDir . $webpName;
                $this->generateSquare($origPath, $outPath, $size, 65, $convertBin, $cwebpBin);
                $kb = is_file($outPath) ? round(filesize($outPath) / 1024, 1) : '??';
                CLI::write("  {$subdir}{$webpName}: {$kb} KB");
            }

            // Fit-within variants
            foreach ($fitVariants as $subdir => [$maxW, $maxH]) {
                $targetDir = $konstDir . $subdir;
                if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);
                $outPath = $targetDir . $webpName;
                $this->generateFit($origPath, $outPath, $maxW, $maxH, 65, $convertBin, $cwebpBin);
                $kb = is_file($outPath) ? round(filesize($outPath) / 1024, 1) : '??';
                CLI::write("  {$subdir}{$webpName}: {$kb} KB");
            }

            // Root — full reoriented image
            $rootOut = $konstDir . $webpName;
            $this->generateFull($origPath, $rootOut, 87, $convertBin, $cwebpBin);
            $kb = is_file($rootOut) ? round(filesize($rootOut) / 1024, 1) : '??';
            CLI::write("  (root) {$webpName}: {$kb} KB");
        }

        CLI::write('Done!', 'green');
    }

    private function generateSquare(string $src, string $dst, int $size, int $quality,
                                    string $convert, string $cwebp): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'regen_') . '.png';

        if ($convert !== '') {
            // ImageMagick: resize so shortest side = $size, then center-crop
            $cmd = sprintf(
                '%s %s -auto-orient -resize %dx%d^ -gravity center -extent %dx%d -quality 95 %s 2>/dev/null',
                escapeshellarg($convert),
                escapeshellarg($src),
                $size, $size, $size, $size,
                escapeshellarg($tmp)
            );
            exec($cmd, $out, $rc);
        } else {
            $rc = 1;
        }

        if ($rc !== 0 || !is_file($tmp)) {
            $this->squareWithGd($src, $tmp, $size);
        }

        $this->encodeWebp($tmp, $dst, $quality, $cwebp);
        if (is_file($tmp)) @unlink($tmp);
    }

    private function generateFit(string $src, string $dst, int $maxW, int $maxH, int $quality,
                                 string $convert, string $cwebp): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'regen_') . '.png';

        if ($convert !== '') {
            $cmd = sprintf(
                '%s %s -auto-orient -resize %dx%d\> -quality 95 %s 2>/dev/null',
                escapeshellarg($convert),
                escapeshellarg($src),
                $maxW, $maxH,
                escapeshellarg($tmp)
            );
            exec($cmd, $out, $rc);
        } else {
            $rc = 1;
        }

        if ($rc !== 0 || !is_file($tmp)) {
            $this->fitWithGd($src, $tmp, $maxW, $maxH);
        }

        $this->encodeWebp($tmp, $dst, $quality, $cwebp);
        if (is_file($tmp)) @unlink($tmp);
    }

    private function generateFull(string $src, string $dst, int $quality,
                                  string $convert, string $cwebp): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'regen_') . '.png';

        if ($convert !== '') {
            $cmd = sprintf(
                '%s %s -auto-orient -quality 95 %s 2>/dev/null',
                escapeshellarg($convert),
                escapeshellarg($src),
                escapeshellarg($tmp)
            );
            exec($cmd, $out, $rc);
        } else {
            $rc = 1;
        }

        if ($rc !== 0 || !is_file($tmp)) {
            // Just copy via GD
            $this->fitWithGd($src, $tmp, 99999, 99999);
        }

        $this->encodeWebp($tmp, $dst, $quality, $cwebp);
        if (is_file($tmp)) @unlink($tmp);
    }

    private function encodeWebp(string $tmp, string $dst, int $quality, string $cwebp): void
    {
        if ($cwebp !== '' && is_file($tmp)) {
            $cmd = sprintf('%s -q %d -o %s -- %s 2>/dev/null',
                escapeshellarg($cwebp), $quality, escapeshellarg($dst), escapeshellarg($tmp));
            exec($cmd, $out, $rc);
            if ($rc === 0 && is_file($dst) && filesize($dst) > 0) return;
        }
        // Fallback: GD
        $img = @imagecreatefrompng($tmp);
        if ($img) { imagewebp($img, $dst, $quality); imagedestroy($img); }
    }

    private function squareWithGd(string $src, string $dst, int $size): void
    {
        $info = @getimagesize($src);
        if (!$info) return;
        [$w, $h, $type] = $info;
        $srcImg = $this->loadGd($src, $type);
        if (!$srcImg) return;

        // Scale so shorter side = $size
        if ($w < $h) {
            $scale = $size / $w;
        } else {
            $scale = $size / $h;
        }
        $scaledW = (int) round($w * $scale);
        $scaledH = (int) round($h * $scale);
        $scaled = imagecreatetruecolor($scaledW, $scaledH);
        imagecopyresampled($scaled, $srcImg, 0, 0, 0, 0, $scaledW, $scaledH, $w, $h);
        imagedestroy($srcImg);

        // Center crop
        $x = (int) (($scaledW - $size) / 2);
        $y = (int) (($scaledH - $size) / 2);
        $dst_img = imagecreatetruecolor($size, $size);
        imagecopy($dst_img, $scaled, 0, 0, $x, $y, $size, $size);
        imagedestroy($scaled);
        imagepng($dst_img, $dst);
        imagedestroy($dst_img);
    }

    private function fitWithGd(string $src, string $dst, int $maxW, int $maxH): void
    {
        $info = @getimagesize($src);
        if (!$info) return;
        [$w, $h, $type] = $info;
        $scale = min($maxW / $w, $maxH / $h, 1.0);
        $dstW  = (int) round($w * $scale);
        $dstH  = (int) round($h * $scale);
        $srcImg = $this->loadGd($src, $type);
        if (!$srcImg) return;
        $dstImg = imagecreatetruecolor($dstW, $dstH);
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $dstW, $dstH, $w, $h);
        imagepng($dstImg, $dst);
        imagedestroy($srcImg);
        imagedestroy($dstImg);
    }

    private function loadGd(string $src, int $type)
    {
        return match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_WEBP => imagecreatefromwebp($src),
            default        => null,
        };
    }
}

