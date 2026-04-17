<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ResponseCompressionFilter implements FilterInterface
{
  /**
   * @var list<string>
   */
  private array $compressibleTypes = [
    'text/html',
    'text/plain',
    'text/css',
    'text/javascript',
    'application/javascript',
    'application/json',
    'application/xml',
    'image/svg+xml',
  ];

  public function before(RequestInterface $request, $arguments = null)
  {
    // No-op.
  }

  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {
    if (!function_exists('gzencode')) {
      return;
    }

    if (strtoupper($request->getMethod()) === 'HEAD') {
      return;
    }

    $statusCode = $response->getStatusCode();
    if ($statusCode < 200 || $statusCode >= 300) {
      return;
    }

    if ($response->getHeaderLine('Content-Encoding') !== '') {
      return;
    }

    $acceptEncoding = strtolower($request->getHeaderLine('Accept-Encoding'));
    if (!str_contains($acceptEncoding, 'gzip')) {
      return;
    }

    $contentType = strtolower(trim(explode(';', $response->getHeaderLine('Content-Type'))[0] ?? ''));
    if ($contentType !== '' && !in_array($contentType, $this->compressibleTypes, true)) {
      return;
    }

    $body = $response->getBody();
    if (!is_string($body) || $body === '' || strlen($body) < 1024) {
      return;
    }

    $compressedBody = gzencode($body, 6, ZLIB_ENCODING_GZIP);
    if (!is_string($compressedBody) || $compressedBody === '' || strlen($compressedBody) >= strlen($body)) {
      return;
    }

    $existingVary = trim($response->getHeaderLine('Vary'));
    if ($existingVary === '') {
      $response->setHeader('Vary', 'Accept-Encoding');
    } elseif (!str_contains(strtolower($existingVary), 'accept-encoding')) {
      $response->setHeader('Vary', $existingVary . ', Accept-Encoding');
    }

    $response->setHeader('Content-Encoding', 'gzip');
    $response->removeHeader('Content-Length');
    $response->setBody($compressedBody);
  }
}
