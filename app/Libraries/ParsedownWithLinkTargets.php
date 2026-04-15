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
        if ($href === '' || !$this->shouldOpenInNewTab($href)) {
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

    protected function shouldOpenInNewTab(string $href): bool
    {
        return (bool) preg_match('#^https?://#i', $href);
    }
}

