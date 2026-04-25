<?php

use App\Controllers\News;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class NewsControllerTest extends CIUnitTestCase
{
    public function testNormalizeMainImagePathsUsesStoredDimensions(): void
    {
        $controller = new class extends News {
            public function normalizePaths(array $items): array
            {
                return $this->normalizeMainImagePaths($items);
            }
        };

        $items = [[
            'main_image' => 'news/example.webp',
            'width_px'   => '1200',
            'height_px'  => '800',
        ]];

        $normalized = $controller->normalizePaths($items);

        $this->assertSame('media/news/example.webp', $normalized[0]['main_image']);
        $this->assertSame('media/news/example.webp', $normalized[0]['main_image_thumb']);
        $this->assertSame('media/news/example.webp', $normalized[0]['main_image_thumb2x']);
        // Thumb fits within 122×122: scale = 122/1200 ≈ 0.10167 → 122×81
        $this->assertSame(122, $normalized[0]['main_image_width']);
        $this->assertSame(81, $normalized[0]['main_image_height']);
    }

    public function testNormalizeMainImagePathsKeepsLegacyFallbackWithoutStoredDimensions(): void
    {
        $controller = new class extends News {
            public function normalizePaths(array $items): array
            {
                return $this->normalizeMainImagePaths($items);
            }
        };

        $items = [[
            'main_image' => 'news/example-without-dimensions.webp',
        ]];

        $normalized = $controller->normalizePaths($items);

        $this->assertSame('media/news/example-without-dimensions.webp', $normalized[0]['main_image']);
        $this->assertSame('media/news/example-without-dimensions.webp', $normalized[0]['main_image_thumb']);
        $this->assertSame('media/news/example-without-dimensions.webp', $normalized[0]['main_image_thumb2x']);
        $this->assertArrayNotHasKey('main_image_width', $normalized[0]);
        $this->assertArrayNotHasKey('main_image_height', $normalized[0]);
    }

    public function testResolveNewsMainImageBasenameFromStoredPathSupportsCurrentFormats(): void
    {
        $controller = new class extends News {
            public function resolveBasename(string $path): string
            {
                return $this->resolveNewsMainImageBasenameFromStoredPath($path);
            }
        };

        $this->assertSame('example.webp', $controller->resolveBasename('media/news/example.webp'));
        $this->assertSame('example.webp', $controller->resolveBasename('news/example.webp'));
        $this->assertSame('example.webp', $controller->resolveBasename('/tmp/example.webp'));
    }
}

