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
            'mini/'   => ['height' => 70,   'quality' => 55],
            'thumb/'  => ['height' => 280,  'quality' => 60],
            'medium/' => ['height' => 560,  'quality' => 65],
            'large/'  => ['height' => 1120, 'quality' => 72],
        ];

        foreach ($originals as $origPath) {
            $baseName = pathinfo($origPath, PATHINFO_FILENAME);
            $webpName = $baseName . '.webp';

            CLI::write("Processing: {$baseName}");

            foreach ($variants as $subdir => $opts) {
                $targetDir = $newsDir . $subdir;
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }

                $outPath = $targetDir . $webpName;
                $height = $opts['height'];
                $quality = $opts['quality'];

                // Use ImageMagick convert to resize, then cwebp to encode
                $tmpPng = tempnam(sys_get_temp_dir(), 'regen_') . '.png';

                if ($convertBin !== '') {
                    $cmd = sprintf(
                        '%s %s -auto-orient -resize x%d -quality 95 %s 2>/dev/null',
                        escapeshellarg($convertBin),
                        escapeshellarg($origPath),
                        $height,
                        escapeshellarg($tmpPng)
                    );
                    exec($cmd, $out, $rc);
                } else {
                    // Fallback to GD
                    $this->resizeWithGd($origPath, $tmpPng, $height);
                    $rc = 0;
                }

                if ($rc === 0 && is_file($tmpPng) && $cwebpBin !== '') {
                    $cmd = sprintf(
                        '%s -q %d -o %s -- %s 2>/dev/null',
                        escapeshellarg($cwebpBin),
                        $quality,
                        escapeshellarg($outPath),
                        escapeshellarg($tmpPng)
                    );
                    exec($cmd);
                }

                if (is_file($tmpPng)) {
                    @unlink($tmpPng);
                }

                $size = is_file($outPath) ? round(filesize($outPath) / 1024, 1) : '??';
                CLI::write("  {$subdir}{$webpName}: {$size} KB");
            }

            // Remove old root-level webp
            $rootWebp = $newsDir . $webpName;
            if (is_file($rootWebp)) {
                unlink($rootWebp);
                CLI::write("  Removed old root: {$webpName}");
            }
        }

        CLI::write('Done!', 'green');
    }

    private function resizeWithGd(string $src, string $dst, int $targetHeight): void
    {
        $info = getimagesize($src);
        if (!$info) return;

        $srcW = $info[0];
        $srcH = $info[1];
        $type = $info[2];
        $scale = $targetHeight / $srcH;
        $dstW = (int) round($srcW * $scale);
        $dstH = $targetHeight;

        $srcImg = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_WEBP => imagecreatefromwebp($src),
            default        => null,
        };
        if (!$srcImg) return;

        $dstImg = imagecreatetruecolor($dstW, $dstH);
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagepng($dstImg, $dst);
        imagedestroy($srcImg);
        imagedestroy($dstImg);
    }
}

