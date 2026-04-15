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
        $this->assertSame(1200, $normalized[0]['main_image_width']);
        $this->assertSame(800, $normalized[0]['main_image_height']);
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
        $this->assertSame('media/news/example-without-dimensions.webp', $normalized[0]['main_image_medium']);
        $this->assertSame('media/news/example-without-dimensions.webp', $normalized[0]['main_image_large']);
        $this->assertArrayNotHasKey('main_image_width', $normalized[0]);
        $this->assertArrayNotHasKey('main_image_height', $normalized[0]);
    }
}

