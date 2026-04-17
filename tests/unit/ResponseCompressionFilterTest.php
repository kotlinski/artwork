<?php

use App\Filters\ResponseCompressionFilter;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ResponseCompressionFilterTest extends CIUnitTestCase
{
    public function testCompressesLargeHtmlResponsesWhenClientAcceptsGzip(): void
    {
        $filter = new ResponseCompressionFilter();

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getHeaderLine')->willReturnCallback(static function (string $name): string {
            return strtolower($name) === 'accept-encoding' ? 'gzip, deflate, br' : '';
        });

        $capturedHeaders = [];
        $capturedBody = null;

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn(str_repeat('News page content. ', 120));
        $response->method('getHeaderLine')->willReturnCallback(static function (string $name): string {
            return match (strtolower($name)) {
                'content-encoding', 'vary' => '',
                'content-type' => 'text/html; charset=UTF-8',
                default => '',
            };
        });
        $response->method('setHeader')->willReturnCallback(static function (string $name, $value) use (&$capturedHeaders, $response) {
            $capturedHeaders[strtolower($name)] = $value;

            return $response;
        });
        $response->expects($this->once())
            ->method('removeHeader')
            ->with('Content-Length')
            ->willReturnSelf();
        $response->method('setBody')->willReturnCallback(static function ($body) use (&$capturedBody, $response) {
            $capturedBody = $body;

            return $response;
        });

        $filter->after($request, $response);

        $this->assertSame('gzip', $capturedHeaders['content-encoding'] ?? null);
        $this->assertSame('Accept-Encoding', $capturedHeaders['vary'] ?? null);
        $this->assertIsString($capturedBody);
        $this->assertNotSame('', $capturedBody);
        $this->assertSame(str_repeat('News page content. ', 120), gzdecode($capturedBody));
    }

    public function testSkipsCompressionWhenResponseIsAlreadyEncoded(): void
    {
        $filter = new ResponseCompressionFilter();

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getHeaderLine')->willReturn('gzip');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaderLine')->willReturnCallback(static function (string $name): string {
            return match (strtolower($name)) {
                'content-encoding' => 'br',
                'content-type' => 'text/html; charset=UTF-8',
                default => '',
            };
        });

        $response->expects($this->never())->method('setHeader');
        $response->expects($this->never())->method('removeHeader');
        $response->expects($this->never())->method('setBody');

        $filter->after($request, $response);

        $this->assertTrue(true);
    }
}
