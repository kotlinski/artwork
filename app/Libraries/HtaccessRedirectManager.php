<?php

namespace App\Libraries;

class HtaccessRedirectManager
{
    private const START_MARKER = "\t# BEGIN AUTO RENAME REDIRECTS";
    private const END_MARKER = "\t# END AUTO RENAME REDIRECTS";

    private string $htaccessPath;

    public function __construct(?string $htaccessPath = null)
    {
        $this->htaccessPath = $htaccessPath ?? FCPATH . '.htaccess';
    }

    /**
     * @param array<int, array{from:string,to:string}> $redirects
     */
    public function addRedirects(array $redirects): void
    {
        if ($redirects === []) {
            return;
        }

        if (!is_file($this->htaccessPath) || !is_readable($this->htaccessPath) || !is_writable($this->htaccessPath)) {
            throw new \RuntimeException('Cannot access .htaccess at ' . $this->htaccessPath);
        }

        $content = file_get_contents($this->htaccessPath);
        if ($content === false) {
            throw new \RuntimeException('Failed to read .htaccess content.');
        }

        [$before, $managedBody, $after] = $this->splitManagedSection($content);
        $rules = $this->parseManagedRules($managedBody);

        foreach ($redirects as $redirect) {
            $from = $this->normalizePath($redirect['from'] ?? '');
            $to = $this->normalizePath($redirect['to'] ?? '');
            if ($from === '' || $to === '' || $from === $to) {
                continue;
            }

            $pattern = '^' . preg_quote(ltrim($from, '/'), '#') . '$';
            $rules[$pattern] = "\tRewriteRule {$pattern} {$to} [R=301,L,NC]";
        }

        if ($rules === []) {
            return;
        }

        ksort($rules);
        $newManagedBody = implode("\n", $rules);
        $newContent = $before . self::START_MARKER . "\n" . $newManagedBody . "\n" . self::END_MARKER . $after;

        if (file_put_contents($this->htaccessPath, $newContent, LOCK_EX) === false) {
            throw new \RuntimeException('Failed to write updated .htaccess.');
        }
    }

    /**
     * @return array{0:string,1:string,2:string}
     */
    private function splitManagedSection(string $content): array
    {
        $startPos = strpos($content, self::START_MARKER);
        $endPos = strpos($content, self::END_MARKER);

        if ($startPos !== false && $endPos !== false && $endPos > $startPos) {
            $before = substr($content, 0, $startPos);
            $bodyStart = $startPos + strlen(self::START_MARKER);
            $managedBody = trim((string) substr($content, $bodyStart, $endPos - $bodyStart));
            $after = substr($content, $endPos + strlen(self::END_MARKER));
            return [$before, $managedBody, $after];
        }

        $anchor = "\t# Redirect Trailing Slashes...";
        $anchorPos = strpos($content, $anchor);
        if ($anchorPos === false) {
            throw new \RuntimeException('Could not find redirect anchor in .htaccess.');
        }

        $before = substr($content, 0, $anchorPos);
        if (!str_ends_with($before, "\n")) {
            $before .= "\n";
        }
        $before .= "\n";
        $after = substr($content, $anchorPos);

        return [$before, '', $after];
    }

    /**
     * @return array<string,string>
     */
    private function parseManagedRules(string $managedBody): array
    {
        $rules = [];
        if ($managedBody === '') {
            return $rules;
        }

        $lines = preg_split('/\r\n|\r|\n/', $managedBody) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_starts_with($line, 'RewriteRule ')) {
                continue;
            }

            $parts = preg_split('/\s+/', $line, 4);
            if (!is_array($parts) || count($parts) < 3) {
                continue;
            }

            $pattern = $parts[1];
            $target = $parts[2];
            $rules[$pattern] = "\tRewriteRule {$pattern} {$target} [R=301,L,NC]";
        }

        return $rules;
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        $parsed = parse_url($path, PHP_URL_PATH);
        if (is_string($parsed) && $parsed !== '') {
            $path = $parsed;
        }

        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }
}

