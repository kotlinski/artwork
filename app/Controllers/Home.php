<?php

namespace App\Controllers;

use App\Models\Startpage;
use CodeIgniter\HTTP\Files\UploadedFile;

class Home extends BaseController
{
    public function index(): string
    {
        $startpageModel = new Startpage();

        $startpage = $startpageModel->orderBy('id', 'DESC')->first();
        $startpage = is_array($startpage) ? $startpage : ['id' => null, 'text' => '', 'image_path' => null];

        $imageData = $this->resolveStartpageImageData((string) ($startpage['image_path'] ?? ''));
        $description = 'Official website of Swedish artist Anne Hamrin Simonsson. Discover conceptual art, paintings, and installations with unique artist insights.';

        $required = [
            'title' => 'Anne Hamrin Simonsson | Official Website',
            'selected_menu_item' => '',
            'body_class' => 'startpage',
            'description' => $description,
            'meta_keywords' => 'Anne Hamrin Simonsson, conceptual art, visual artist, Swedish artist, installation art, artwork, exhibitions',
            'og_image' => $imageData['full_url'] ?? base_url('anne-hamrin-simonsson-portrait.jpg'),
            'og_image_width' => (string) ($imageData['full_width'] ?? 320),
            'og_image_height' => (string) ($imageData['full_height'] ?? 320),
            'lcp_image_url' => $imageData['display_url'] ?? '',
        ];

        return $this->renderView('startpage_view', $required, [
            'startpage' => $startpage,
            'startpage_image' => $imageData,
            'startpage_jsonld' => generateStartpageJsonLd($description, $imageData),
        ]);
    }

    public function update()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $startpageModel = new Startpage();
        $rowId = (int) $this->request->getPost('id');
        $existing = $rowId > 0
            ? $startpageModel->find($rowId)
            : $startpageModel->orderBy('id', 'DESC')->first();

        $text = trim((string) $this->request->getPost('startpage_text'));
        if ($text === '' && is_array($existing)) {
            $text = (string) ($existing['text'] ?? '');
        }

        $mainImageFile = $this->request->getFile('startpage_image_file');
        $hasUpload = $this->hasUploadedFile($mainImageFile);

        $imagePath = (string) ($existing['image_path'] ?? '');
        if ($hasUpload) {
            $uploadError = $this->validateStartpageImageFile($mainImageFile);
            if ($uploadError !== null) {
                return redirect()->to('/')->with('error', $uploadError);
            }

            try {
                $savedImage = $this->saveStartpageImageVariants($mainImageFile);
                $newImagePath = (string) ($savedImage['image_path'] ?? '');
                if ($newImagePath !== '' && $imagePath !== '' && $imagePath !== $newImagePath) {
                    $this->deleteStartpageImageVariants($imagePath);
                }
                $imagePath = $newImagePath;
            } catch (\Throwable $e) {
                return redirect()->to('/')->with('error', 'Failed to process startpage image upload.');
            }
        }

        $payload = [
            'text' => $text,
            'image_path' => $imagePath !== '' ? $imagePath : null,
        ];

        if (is_array($existing) && isset($existing['id'])) {
            $startpageModel->update((int) $existing['id'], $payload);
        } else {
            $startpageModel->insert($payload);
        }

        return redirect()->to('/')->with('success', 'Startpage updated.');
    }

    protected function hasUploadedFile(?UploadedFile $file): bool
    {
        return $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE;
    }

    protected function validateStartpageImageFile(?UploadedFile $file): ?string
    {
        if ($file === null || !$file->isValid()) {
            return 'Startpage image upload failed.';
        }

        if ($file->getSize() > 20 * 1024 * 1024) {
            return 'Startpage image may not be larger than 20 MB.';
        }

        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return 'Startpage image must be jpg, jpeg, png, or webp.';
        }

        return null;
    }

    protected function resolveStartpageImageData(string $imagePath): array
    {
        $imagePath = trim($imagePath);
        if ($imagePath === '') {
            return [];
        }

        $fileName = basename($imagePath);
        if ($fileName === '' || $fileName === '.' || $fileName === '..') {
            return [];
        }

        $fullPath = str_starts_with($imagePath, 'media/startpage/')
            ? $imagePath
            : 'media/startpage/' . ltrim($imagePath, '/');

        $paths = [
            'display' => 'media/startpage/display/' . $fileName,
            'display2x' => 'media/startpage/display2x/' . $fileName,
            'small' => 'media/startpage/small/' . $fileName,
            'mobile' => 'media/startpage/mobile/' . $fileName,
            'medium' => 'media/startpage/medium/' . $fileName,
            'large' => 'media/startpage/large/' . $fileName,
            'x_large' => 'media/startpage/x-large/' . $fileName,
            'full' => $fullPath,
        ];

        $displayWidth = 380;
        $displayHeight = 280;
        $displayDims = @getimagesize(FCPATH . $paths['display']);
        if ($displayDims && isset($displayDims[0], $displayDims[1]) && $displayDims[0] > 0 && $displayDims[1] > 0) {
            $displayWidth = (int) $displayDims[0];
            $displayHeight = (int) $displayDims[1];
        }

        $fullWidth = 0;
        $fullHeight = 0;
        $fullDims = @getimagesize(FCPATH . $paths['full']);
        if ($fullDims && isset($fullDims[0], $fullDims[1])) {
            $fullWidth = (int) $fullDims[0];
            $fullHeight = (int) $fullDims[1];
        }

        $srcsetByUrl = [];
        if ($fullWidth > 0 && $fullHeight > 0) {
            $defs = [
                ['url' => $paths['small'], 'maxW' => 800, 'maxH' => 600],
                ['url' => $paths['mobile'], 'maxW' => 1024, 'maxH' => 768],
                ['url' => $paths['medium'], 'maxW' => 1280, 'maxH' => 960],
                ['url' => $paths['large'], 'maxW' => 1920, 'maxH' => 1440],
                ['url' => $paths['x_large'], 'maxW' => 2560, 'maxH' => 1920],
                ['url' => $paths['full'], 'maxW' => $fullWidth, 'maxH' => $fullHeight],
            ];

            foreach ($defs as $def) {
                $url = (string) ($def['url'] ?? '');
                if ($url === '') {
                    continue;
                }

                $scale = min($def['maxW'] / $fullWidth, $def['maxH'] / $fullHeight, 1.0);
                $variantW = max(1, (int) round($fullWidth * $scale));
                if (!isset($srcsetByUrl[$url]) || $variantW > $srcsetByUrl[$url]) {
                    $srcsetByUrl[$url] = $variantW;
                }
            }
        }

        $expandedSrcset = '';
        if (!empty($srcsetByUrl)) {
            asort($srcsetByUrl);
            $parts = [];
            foreach ($srcsetByUrl as $url => $w) {
                $parts[] = base_url($url) . ' ' . $w . 'w';
            }
            $expandedSrcset = implode(', ', $parts);
        }

        $expandedSizes = '96vw';
        if ($fullWidth > 0 && $fullHeight > 0) {
            $ratio = $fullWidth / $fullHeight;
            $ratioStr = rtrim(rtrim(number_format($ratio, 6, '.', ''), '0'), '.');
            $expandedSizes = 'min(calc(100vw - 40px), calc(92vh * ' . $ratioStr . '), 1400px, ' . $fullWidth . 'px)';
        }

        return [
            'file_name' => $fileName,
            'display' => $paths['display'],
            'display_2x' => $paths['display2x'],
            'display_url' => base_url($paths['display']),
            'display_2x_url' => base_url($paths['display2x']),
            'full' => $paths['full'],
            'full_url' => base_url($paths['full']),
            'small' => $paths['small'],
            'mobile' => $paths['mobile'],
            'medium' => $paths['medium'],
            'large' => $paths['large'],
            'x_large' => $paths['x_large'],
            'display_width' => $displayWidth,
            'display_height' => $displayHeight,
            'full_width' => $fullWidth,
            'full_height' => $fullHeight,
            'expanded_srcset' => $expandedSrcset,
            'expanded_sizes' => $expandedSizes,
        ];
    }

    protected function saveStartpageImageVariants(UploadedFile $file): array
    {
        $timestamp = date('Ymd-His');
        $random = strtolower(bin2hex(random_bytes(3)));
        $baseName = 'anne-hamrin-simonsson-startpage-' . $timestamp . '-' . $random;
        $origExt = strtolower($file->getClientExtension());
        $origName = $baseName . '.' . $origExt;
        $webpName = $baseName . '.webp';

        $startDir = FCPATH . 'media/startpage/';
        $originalDir = $startDir . 'original/';
        if (!is_dir($originalDir) && !mkdir($originalDir, 0775, true) && !is_dir($originalDir)) {
            throw new \RuntimeException('Failed to create startpage original directory.');
        }

        $file->move($originalDir, $origName, true);
        $origPath = $originalDir . $origName;

        $filesize = @filesize($origPath) ?: 0;
        if ($filesize > 3145728) {
            $quality = 43;
        } elseif ($filesize > 2097152) {
            $quality = 63;
        } elseif ($filesize > 1048576) {
            $quality = 73;
        } else {
            $quality = 87;
        }

        helper('webp');

        generate_webp_variant($origPath, $startDir . $webpName, $quality);

        $variants = [
            'display/' => ['maxW' => 380, 'maxH' => 3800, 'quality' => min($quality, 78)],
            'display2x/' => ['maxW' => 760, 'maxH' => 7600, 'quality' => min($quality, 82)],
            'small/' => ['maxW' => 800, 'maxH' => 600, 'quality' => min($quality, 75)],
            'mobile/' => ['maxW' => 1024, 'maxH' => 768, 'quality' => min($quality, 78)],
            'medium/' => ['maxW' => 1280, 'maxH' => 960, 'quality' => min($quality, 80)],
            'large/' => ['maxW' => 1920, 'maxH' => 1440, 'quality' => min($quality, 85)],
            'x-large/' => ['maxW' => 2560, 'maxH' => 1920, 'quality' => min($quality, 87)],
        ];

        foreach ($variants as $subdir => $opts) {
            $targetDir = $startDir . $subdir;
            if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
                throw new \RuntimeException('Failed to create startpage variant directory.');
            }
            generate_webp_fit($origPath, $targetDir . $webpName, $opts['maxW'], $opts['maxH'], $opts['quality']);
        }

        return [
            'image_path' => $webpName,
        ];
    }

    protected function deleteStartpageImageVariants(string $storedPath): void
    {
        $path = trim($storedPath);
        if ($path === '') {
            return;
        }

        $basename = basename($path);
        if ($basename === '' || $basename === '.' || $basename === '..') {
            return;
        }

        $startDir = FCPATH . 'media/startpage/';
        $variantDirs = ['', 'display/', 'display2x/', 'small/', 'mobile/', 'medium/', 'large/', 'x-large/'];
        foreach ($variantDirs as $subdir) {
            $candidate = $startDir . $subdir . $basename;
            if (is_file($candidate)) {
                @unlink($candidate);
            }
        }

        $nameNoExt = pathinfo($basename, PATHINFO_FILENAME);
        if ($nameNoExt === '') {
            return;
        }

        foreach (glob($startDir . 'original/' . $nameNoExt . '.*') ?: [] as $original) {
            if (is_file($original)) {
                @unlink($original);
            }
        }
    }
}

function generateStartpageJsonLd(string $description, array $imageData = []): string
{
    $baseUrl = rtrim((string) base_url('/'), '/');
    $pageUrl = $baseUrl . '/';
    $webPageId = $baseUrl . '/#webpage';
    $organizationId = $baseUrl . '/#organization';
    $logoId = $baseUrl . '/#publisher-logo';

    $graph = [
        [
            '@type' => 'WebSite',
            '@id' => $baseUrl . '/#website',
            'url' => $pageUrl,
            'name' => 'Anne Hamrin Simonsson',
            'publisher' => ['@id' => $baseUrl . '/#person'],
        ],
        [
            '@type' => 'Organization',
            '@id' => $organizationId,
            'name' => 'Anne Hamrin Simonsson',
            'url' => $pageUrl,
            'sameAs' => [
                'https://www.wikidata.org/wiki/Q137808007',
                'https://www.instagram.com/ahamrinsimonsson/',
                'https://www.linkedin.com/in/anne-hamrin-simonsson-1948aba5/',
                'https://www.konstikalmarlan.se/verksamhet/anne-hamrin-simonsson/',
                'https://www.smalandstriennalen.se/medverkande/anne-hamrin-simonsson',
                'https://www.kalmarkonstmuseum.se/exhibition/med-orat-mot-marken-och-blicken-utat/',
            ],
            'contactPoint' => [
                [
                    '@type' => 'ContactPoint',
                    'contactType' => 'artwork inquiries',
                    'url' => $baseUrl . '/contact',
                    'availableLanguage' => ['en', 'sv'],
                ],
            ],
            'logo' => ['@id' => $logoId],
        ],
        [
            '@type' => 'ImageObject',
            '@id' => $logoId,
            'url' => base_url('anne-hamrin-simonsson-portrait.jpg'),
            'contentUrl' => base_url('anne-hamrin-simonsson-portrait.jpg'),
            'width' => 320,
            'height' => 320,
        ],
        [
            '@type' => 'Person',
            '@id' => $baseUrl . '/#person',
            'name' => 'Anne Hamrin Simonsson',
            'url' => $baseUrl . '/about',
            'image' => [
                '@type' => 'ImageObject',
                '@id' => $logoId,
                'url' => base_url('anne-hamrin-simonsson-portrait.jpg'),
                'contentUrl' => base_url('anne-hamrin-simonsson-portrait.jpg'),
                'width' => 320,
                'height' => 320,
            ],
            'jobTitle' => 'Visual Artist',
            'description' => 'Anne Hamrin Simonsson is a Swedish conceptual and visual artist known for site-specific installations and objects.',
            'sameAs' => [
                'https://www.wikidata.org/wiki/Q137808007',
                'https://www.instagram.com/ahamrinsimonsson/',
                'https://www.linkedin.com/in/anne-hamrin-simonsson-1948aba5/',
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $webPageId,
            'url' => $pageUrl,
            'name' => 'Anne Hamrin Simonsson',
            'description' => $description,
            'isPartOf' => ['@id' => $baseUrl . '/#website'],
            'about' => ['@id' => $baseUrl . '/#person'],
            'mainEntity' => ['@id' => $baseUrl . '/#person'],
            'inLanguage' => 'en',
        ],
    ];

    $fullUrl = trim((string) ($imageData['full_url'] ?? ''));
    if ($fullUrl !== '') {
        $imageNodeId = $baseUrl . '/#startpage-image';
        $imageNode = [
            '@type' => 'ImageObject',
            '@id' => $imageNodeId,
            'url' => $fullUrl,
            'contentUrl' => $fullUrl,
            'name' => 'Startpage image',
        ];

        $displayUrl = trim((string) ($imageData['display_url'] ?? ''));
        if ($displayUrl !== '') {
            $imageNode['thumbnailUrl'] = $displayUrl;
        }

        $fullWidth = isset($imageData['full_width']) ? (int) $imageData['full_width'] : 0;
        $fullHeight = isset($imageData['full_height']) ? (int) $imageData['full_height'] : 0;
        if ($fullWidth > 0) {
            $imageNode['width'] = $fullWidth;
        }
        if ($fullHeight > 0) {
            $imageNode['height'] = $fullHeight;
        }

        foreach ($graph as $idx => $node) {
            if (($node['@id'] ?? null) === $webPageId) {
                $graph[$idx]['primaryImageOfPage'] = ['@id' => $imageNodeId];
                break;
            }
        }
        $graph[] = $imageNode;
    }

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => $graph,
    ];

    return json_encode(
        $jsonLd,
        JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE
        | JSON_PRETTY_PRINT
        | JSON_HEX_TAG
    );
}

