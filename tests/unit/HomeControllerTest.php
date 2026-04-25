<?php

use App\Controllers\Home;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class HomeControllerTest extends CIUnitTestCase
{
    public function testResolveStartpageImageDataReturnsEmptyWhenNoImage(): void
    {
        $controller = new class extends Home {
            public function resolveStartpageImage(?array $image): array
            {
                return $this->resolveStartpageImageData($image);
            }
        };

        $this->assertSame([], $controller->resolveStartpageImage(null));
    }

    public function testResolveStartpageImageDataBuildsResponsiveMetadata(): void
    {
        $controller = new class extends Home {
            public function resolveStartpageImage(?array $image): array
            {
                return $this->resolveStartpageImageData($image);
            }
        };

        $image = [
            'id' => 22,
            'title' => 'Start image',
            'file_name' => 'example.webp',
            'width_px' => 2000,
            'height_px' => 1000,
        ];

        $resolved = $controller->resolveStartpageImage($image);

        $this->assertSame(22, $resolved['id']);
        $this->assertSame(2000, $resolved['full_width']);
        $this->assertSame(1000, $resolved['full_height']);
        $this->assertStringContainsString('media/startpage/display/example.webp', $resolved['display_url']);
        $this->assertStringContainsString('media/startpage/small/example.webp 800w', $resolved['expanded_srcset']);
        $this->assertStringContainsString('media/startpage/mobile/example.webp 1024w', $resolved['expanded_srcset']);
        $this->assertStringContainsString('media/startpage/medium/example.webp 1280w', $resolved['expanded_srcset']);
        $this->assertStringContainsString('media/startpage/large/example.webp 1920w', $resolved['expanded_srcset']);
    }
}

