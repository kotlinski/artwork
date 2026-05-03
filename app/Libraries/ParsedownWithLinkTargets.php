<?php

namespace App\Libraries;

class ParsedownWithLinkTargets extends \Parsedown
{
    protected function inlineLink($Excerpt)
    {
        $Link = parent::inlineLink($Excerpt);

        if (!is_array($Link) || !isset($Link['element']['attributes']['href'])) {
            return $Link;
        }

        $href = trim((string) $Link['element']['attributes']['href']);
        if ($href === '') {
            return $Link;
        }

        if (!empty($Link['element']['attributes']['title'])) {
            $Link['element']['attributes']['title'] = $this->sanitizeTitleText((string) $Link['element']['attributes']['title']);
        }

        if (empty($Link['element']['attributes']['title'])) {
            $fallbackTitle = $this->buildFallbackLinkTitle($Link, $href);
            if ($fallbackTitle !== '') {
                $Link['element']['attributes']['title'] = $fallbackTitle;
            }
        }

        if (!$this->shouldOpenInNewTab($href)) {
            return $Link;
        }

        $Link['element']['attributes']['target'] = '_blank';
        $existingRel = trim((string) ($Link['element']['attributes']['rel'] ?? ''));
        $splitRels = preg_split('/\s+/', $existingRel);
        $rels = $existingRel === '' ? [] : ($splitRels ?: []);

        if (!in_array('noopener', $rels, true)) {
            $rels[] = 'noopener';
        }
        if (!in_array('noreferrer', $rels, true)) {
            $rels[] = 'noreferrer';
        }

        $Link['element']['attributes']['rel'] = implode(' ', $rels);

        return $Link;
    }

    protected function inlineImage($Excerpt)
    {
        $Image = parent::inlineImage($Excerpt);

        if (!is_array($Image) || !isset($Image['element']['attributes']['src'])) {
            return $Image;
        }

        $src = trim((string) $Image['element']['attributes']['src']);
        if ($src === '') {
            return $Image;
        }

        $existingAlt = trim((string) ($Image['element']['attributes']['alt'] ?? ''));
        $existingTitle = trim((string) ($Image['element']['attributes']['title'] ?? ''));
        $fallbackText = $this->buildFallbackImageText($existingAlt !== '' ? $existingAlt : $existingTitle, $src);

        if ($existingAlt === '' && $fallbackText !== '') {
            $Image['element']['attributes']['alt'] = $fallbackText;
        }

        if ($existingTitle === '' && $fallbackText !== '') {
            $Image['element']['attributes']['title'] = $fallbackText;
        }

        return $Image;
    }

    protected function shouldOpenInNewTab(string $href): bool
    {
        return (bool) preg_match('#^https?://#i', $href);
    }

    protected function buildFallbackLinkTitle(array $link, string $href): string
    {
        $text = '';
        if (isset($link['element']['text']) && is_string($link['element']['text'])) {
            $text = $this->sanitizeTitleText((string) $link['element']['text']);
        }

        if ($text !== '') {
            return $text;
        }

        return $this->sanitizeTitleText($href);
    }

    protected function sanitizeTitleText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = str_replace('*', '', $text);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        return trim($text);
    }

    protected function buildFallbackImageText(string $text, string $src): string
    {
        $text = trim(strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if ($text !== '') {
            return $text;
        }

        $path = (string) parse_url($src, PHP_URL_PATH);
        $basename = pathinfo($path !== '' ? $path : $src, PATHINFO_FILENAME);
        $basename = str_replace(['-', '_'], ' ', (string) $basename);
        $basename = preg_replace('/\s+/', ' ', $basename) ?? $basename;

        return trim($basename);
    }
}

