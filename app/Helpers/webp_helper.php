<?php

/**
 * Generate a WebP file from a source image.
 * Tries Imagick first, then falls back to GD.
 *
 * @param string $sourcePath   Absolute path to the source image (jpg/png/webp)
 * @param string $outPath      Absolute path for the output .webp file
 * @param int    $quality      WebP quality (0-100)
 * @param int    $resizeHeight If > 0, resize to this height (maintaining aspect ratio)
 */
function generate_webp_variant(string $sourcePath, string $outPath, int $quality, int $resizeHeight = 0): void
{
    $encoded = false;

    // Try Imagick first.
    try {
        $image = \Config\Services::image('imagick');
        $image->withFile($sourcePath);
        $image->reorient();
        if ($resizeHeight > 0) {
            $image->resize(0, $resizeHeight, true);
        }
        $image->convert(IMAGETYPE_WEBP);
        $image->quality($quality);
        $image->save($outPath);
        $encoded = is_file($outPath) && filesize($outPath) > 0;
    } catch (\Throwable $e) {
        $encoded = false;
    }

    // Fallback: GD resize + imagewebp.
    if (!$encoded) {
        $tmpPng = tempnam(sys_get_temp_dir(), 'webp_') . '.png';
        try {
            if ($resizeHeight > 0) {
                $info = @getimagesize($sourcePath);
                $origW = $info ? $info[0] : 0;
                $origH = $info ? $info[1] : 0;
                $scale = ($origH > 0) ? $resizeHeight / $origH : 1.0;
                _gd_resize_fit($sourcePath, $tmpPng, (int) round($origW * $scale), $resizeHeight);
            } else {
                _gd_resize_fit($sourcePath, $tmpPng, 99999, 99999);
            }
            _gd_encode_webp($tmpPng, $outPath, $quality);
        } finally {
            if (is_file($tmpPng)) {
                @unlink($tmpPng);
            }
        }
    }
}

/**
 * Resize an image to fit within maxWidth × maxHeight, maintaining aspect ratio (no crop).
 */
function generate_webp_fit(string $sourcePath, string $outPath, int $maxWidth, int $maxHeight, int $quality): void
{
    $tmpPng  = tempnam(sys_get_temp_dir(), 'webp_') . '.png';
    $resized = false;

    // Try Imagick first.
    try {
        $image = \Config\Services::image('imagick');
        $image->withFile($sourcePath);
        $image->reorient();
        $image->resize($maxWidth, $maxHeight, true);
        $image->save($tmpPng);
        $resized = is_file($tmpPng) && filesize($tmpPng) > 0;
    } catch (\Throwable $e) {
        $resized = false;
    }

    // Fall back to GD.
    if (!$resized) {
        try {
            _gd_resize_fit($sourcePath, $tmpPng, $maxWidth, $maxHeight);
            $resized = is_file($tmpPng) && filesize($tmpPng) > 0;
        } catch (\Throwable $e) {
            if (is_file($tmpPng)) {
                @unlink($tmpPng);
            }
            throw new \RuntimeException('Image processing failed with both Imagick and GD: ' . $e->getMessage(), 0, $e);
        }
    }

    _webp_encode($tmpPng, $outPath, $quality);

    if (is_file($tmpPng)) {
        @unlink($tmpPng);
    }
}

/**
 * Center-crop an image to an exact square of $size × $size pixels.
 */
function generate_webp_square(string $sourcePath, string $outPath, int $size, int $quality): void
{
    $tmpPng  = tempnam(sys_get_temp_dir(), 'webp_') . '.png';
    $resized = false;

    // Try Imagick first.
    try {
        $image = \Config\Services::image('imagick');
        $image->withFile($sourcePath);
        $image->reorient();
        $image->fit($size, $size, 'center');
        $image->save($tmpPng);
        $resized = is_file($tmpPng) && filesize($tmpPng) > 0;
    } catch (\Throwable $e) {
        $resized = false;
    }

    // Fall back to GD.
    if (!$resized) {
        try {
            _gd_resize_fit($sourcePath, $tmpPng, $size, $size);
            $resized = is_file($tmpPng) && filesize($tmpPng) > 0;
        } catch (\Throwable $e) {
            if (is_file($tmpPng)) {
                @unlink($tmpPng);
            }
            throw new \RuntimeException('Image processing failed with both Imagick and GD: ' . $e->getMessage(), 0, $e);
        }
    }

    _webp_encode($tmpPng, $outPath, $quality);

    if (is_file($tmpPng)) {
        @unlink($tmpPng);
    }
}

/**
 * Internal: encode a PNG temp file to WebP via Imagick, then GD fallback.
 * (cwebp removed — shell_exec is disabled on the production server.)
 */
function _webp_encode(string $tmpPng, string $outPath, int $quality): void
{
    $encoded = false;

    // Try Imagick.
    try {
        $image = \Config\Services::image('imagick');
        $image->withFile($tmpPng);
        $image->convert(IMAGETYPE_WEBP);
        $image->quality($quality);
        $image->save($outPath);
        $encoded = is_file($outPath) && filesize($outPath) > 0;
    } catch (\Throwable $e) {
        $encoded = false;
    }

    // Fall back to GD's imagewebp().
    if (!$encoded) {
        _gd_encode_webp($tmpPng, $outPath, $quality);
    }
}

/**
 * Internal: resize source image to fit within maxWidth × maxHeight using GD.
 */
function _gd_resize_fit(string $sourcePath, string $outPng, int $maxWidth, int $maxHeight): void
{
    $info = @getimagesize($sourcePath);
    if (!$info) {
        throw new \RuntimeException('GD: cannot read image info for ' . $sourcePath);
    }

    $src = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
        IMAGETYPE_PNG  => @imagecreatefrompng($sourcePath),
        IMAGETYPE_WEBP => @imagecreatefromwebp($sourcePath),
        default        => false,
    };

    if ($src === false) {
        throw new \RuntimeException('GD: unsupported image type or failed to load: ' . $sourcePath);
    }

    $origW = imagesx($src);
    $origH = imagesy($src);

    // Auto-orient via EXIF if available.
    if (function_exists('exif_read_data') && $info[2] === IMAGETYPE_JPEG) {
        $exif        = @exif_read_data($sourcePath);
        $orientation = $exif['Orientation'] ?? 1;
        if ($orientation > 1) {
            $src   = _gd_apply_orientation($src, $orientation);
            $origW = imagesx($src);
            $origH = imagesy($src);
        }
    }

    // Compute fit-within dimensions.
    $scale = min($maxWidth / $origW, $maxHeight / $origH, 1.0);
    $newW  = max(1, (int) round($origW * $scale));
    $newH  = max(1, (int) round($origH * $scale));

    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
    imagedestroy($src);

    imagepng($dst, $outPng);
    imagedestroy($dst);
}

/**
 * Internal: apply EXIF orientation to a GD image resource.
 *
 * @param \GdImage $image
 * @return \GdImage
 */
function _gd_apply_orientation($image, int $orientation)
{
    $angle = match ($orientation) {
        3       => 180,
        6       => -90,
        8       => 90,
        default => 0,
    };
    if ($angle !== 0) {
        $rotated = imagerotate($image, $angle, 0);
        imagedestroy($image);
        return $rotated ?: $image;
    }
    return $image;
}

/**
 * Internal: encode a PNG file to WebP using GD's imagewebp().
 */
function _gd_encode_webp(string $sourcePng, string $outPath, int $quality): void
{
    $src = @imagecreatefrompng($sourcePng);
    if ($src === false) {
        throw new \RuntimeException('GD: failed to load PNG for WebP encoding: ' . $sourcePng);
    }

    imagealphablending($src, false);
    imagesavealpha($src, true);

    $result = imagewebp($src, $outPath, $quality);
    imagedestroy($src);

    if (!$result || !is_file($outPath) || filesize($outPath) === 0) {
        throw new \RuntimeException('GD: failed to encode WebP output: ' . $outPath);
    }
}
