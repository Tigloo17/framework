<?php
declare(strict_types=1);

namespace Tigloo\Http;

use GuzzleHttp\Psr7\MimeType;
use GuzzleHttp\Psr7\Response as GuzzleHttpResponse;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Tigloo\Interfaces\ServerInterface;

class Response extends GuzzleHttpResponse implements ResponseInterface
{
    public function __construct(
        ?string $body = null,
        array $headers = [],
        int $status = 200
    ) {
        parent::__construct($status, $headers, Utils::streamFor($body));
    }

    public function prepare(ServerInterface $request): ResponseInterface
    {
        $response = clone $this;

        if (! $response->hasHeader('Content-Type')) {
            $response = $response->withHeader('Content-Type', MimeType::fromExtension('html').';charset=UTF-8');
        }

        if ($response->hasHeader('Transfer-Encoding')) {
            $response = $response->withoutHeader('Content-Length');
        }
        
        if (! $response->hasHeader('Content-Language')) {
            $response = $response->withHeader('Content-Language', 'fr-FR');
        }

        $response = $response->withHeader('Last-Modified', gmdate(DATE_RFC7231));
        $response = $response->withHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $response = $response->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        
        return $response;
    }

    public function send(): ResponseInterface
    {
        $this->sendHeaders();
        $this->sendBody();
        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        return $this;
    }

    public function sendHeaders()
    {
        if (headers_sent()) {
            return $this;
        }

        foreach ($this->getHeaders() as $name => $values) {
            $replace = ($name === 'Content-Type') ? true : false;
            foreach ($values as $value) {
                header(sprintf('%s:%s', $name, $value), $replace, $this->getStatusCode());
            }
        }
        // @todo envoyer cookie.
        
        header(sprintf(
            'HTTP/%s %s %s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()),
            true,
            $this->getStatusCode()
        );
    }

    public function sendBody()
    {
        $body = $this->getBody();
        if (! $body->isSeekable()) {
            echo $body;
            return;
        }

        $body->rewind();
        while (!$body->eof()) {
            echo $body->read(8192);
        }
    }
}