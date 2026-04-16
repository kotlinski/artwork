<?php

/**
 * Generate a WebP file from a source image using cwebp for efficient compression.
 * Falls back to ImageMagick/Imagick if cwebp is not available.
 *
 * @param string $sourcePath  Absolute path to the source image (jpg/png/webp)
 * @param string $outPath     Absolute path for the output .webp file
 * @param int    $quality     WebP quality (0-100)
 * @param int    $resizeHeight If > 0, resize to this height (maintaining aspect ratio)
 */
function generate_webp_variant(string $sourcePath, string $outPath, int $quality, int $resizeHeight = 0): void
{
    // Step 1: Prepare a temporary PNG for cwebp input (resize + auto-orient via Imagick)
    $needsResize = $resizeHeight > 0;

    if ($needsResize) {
        // Use Imagick to resize, output a temp PNG, then encode with cwebp
        $tmpPng = tempnam(sys_get_temp_dir(), 'webp_') . '.png';
        $image = \Config\Services::image('imagick');
        $image->withFile($sourcePath);
        $image->reorient();
        $image->resize(0, $resizeHeight, true);
        $image->save($tmpPng);
        $cwebpSource = $tmpPng;
    } else {
        // For root (no resize), auto-orient via Imagick to temp PNG
        $tmpPng = tempnam(sys_get_temp_dir(), 'webp_') . '.png';
        $image = \Config\Services::image('imagick');
        $image->withFile($sourcePath);
        $image->reorient();
        $image->save($tmpPng);
        $cwebpSource = $tmpPng;
    }

    // Step 2: Try cwebp for efficient WebP encoding
    $cwebpBin = trim((string) shell_exec('which cwebp 2>/dev/null'));
    $encoded = false;

    if ($cwebpBin !== '' && is_file($cwebpSource)) {
        $cmd = sprintf(
            '%s -q %d -o %s -- %s 2>/dev/null',
            escapeshellarg($cwebpBin),
            $quality,
            escapeshellarg($outPath),
            escapeshellarg($cwebpSource)
        );
        exec($cmd, $output, $exitCode);
        $encoded = ($exitCode === 0 && is_file($outPath) && filesize($outPath) > 0);
    }

    // Step 3: Fallback to Imagick if cwebp failed or unavailable
    if (!$encoded) {
        $image = \Config\Services::image('imagick');
        $image->withFile($sourcePath);
        $image->reorient();
        $image->convert(IMAGETYPE_WEBP);
        $image->quality($quality);
        if ($resizeHeight > 0) {
            $image->resize(0, $resizeHeight, true);
        }
        $image->save($outPath);
    }

    // Cleanup temp file
    if (isset($tmpPng) && is_file($tmpPng)) {
        @unlink($tmpPng);
    }
}

