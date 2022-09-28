<?php

declare(strict_types=1);

namespace ThinLayer\Php2Curl;

class CurlBuilder
{
    private const HTTP_PORT = 80;
    private const HEADER_CONTENT_TYPE = 'content-type';
    private const HEADER_CONTENT_LENGTH = 'content-length';

    private const CONTENT_TYPE_FORM_DATA = 'multipart/form-data';
    private const CONTENT_TYPE_FORM_URL_ENCODED = 'application/x-www-form-urlencoded';
    private const CONTENT_TYPE_RAW = 'raw';

    private string $method;
    private string $scheme;
    private string $host;
    private int $port;
    private string $uri;
    private array $post;
    private array $headers;
    private ?string $input;

    public function __construct()
    {
        $this->headers = getallheaders();
        $this->input = file_get_contents('php://input');
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
        $this->host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $this->port = $_SERVER['SERVER_PORT'] ?? 80;
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->post = $_POST;
    }

    public function method(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function scheme(string $scheme): self
    {
        $this->scheme = $scheme;
        return $this;
    }

    public function host(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function port(int $port): self
    {
        $this->port = $port;
        return $this;
    }

    public function uri(string $uri): self
    {
        $this->uri = $uri;
        return $this;
    }

    public function post(array $post): self
    {
        $this->post = $post;
        return $this;
    }

    public function headers(array $headers): self
    {
        $this->headers = $this->processHeaders($headers);
        return $this;
    }

    public function input(string $input): self
    {
        $this->input = $input;
        return $this;
    }

    public function build(): string
    {
        $fullUrl = $this->getFullUrl();
        $headers = $this->getHeaders();
        $body = $this->getBody();
        return "curl --location --request {$this->method}{$fullUrl}{$headers}{$body}";
    }

    private function getFullUrl(): string
    {
        $port = $this->port !== self::HTTP_PORT ? ':' . $this->port : '';
        return " \"{$this->scheme}://{$this->host}{$port}{$this->uri}\"";
    }

    private function removeBoundaryFromContentType(array $headers): array
    {
        if (!empty($headers[self::HEADER_CONTENT_TYPE])) {
            $headers[self::HEADER_CONTENT_TYPE] = $this->removeBoundary($headers[self::HEADER_CONTENT_TYPE]);
        }

        unset($headers[self::HEADER_CONTENT_LENGTH]);
        return $headers;
    }

    private function guessContentType(array $headers): string
    {
        $contentType = $headers[self::HEADER_CONTENT_TYPE] ?? '';
        if (stripos($contentType, self::CONTENT_TYPE_FORM_DATA) !== false) {
            return self::CONTENT_TYPE_FORM_DATA;
        }

        if (stripos($contentType, self::CONTENT_TYPE_FORM_URL_ENCODED) !== false) {
            return self::CONTENT_TYPE_FORM_URL_ENCODED;
        }

        return self::CONTENT_TYPE_RAW;
    }

    private function getHeaders(): string
    {
        $result = '';
        foreach ($this->headers as $header => $value) {
            $result .= " --header '$header: $value'";
        }

        return $result;
    }

    private function getBody(): string
    {
        switch ($this->method) {
            case 'GET':
            case 'OPTIONS':
                return '';
        };

        $contentType = $this->guessContentType($this->headers);
        switch ($contentType) {
            case self::CONTENT_TYPE_FORM_DATA:
                return $this->buildFormDataBody();

            case self::CONTENT_TYPE_FORM_URL_ENCODED:
                return $this->buildUrlEncodedBody();

            case self::CONTENT_TYPE_RAW:
                return $this->buildRawBody();

            default:
                return '';
        }
    }

    private function processHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $header => $value) {
            $header = mb_strtolower($header);
            $value = is_array($value) ? implode(', ', $value) : $value;
            $result[$header] = $value;
        }

        return $this->removeBoundaryFromContentType($result);
    }

    private function buildFormDataBody(): string
    {
        if (!$this->post) {
            return '';
        }

        return (new FormDataBuilder())->post($this->post)->build();
    }

    private function buildUrlEncodedBody(): string
    {
        if (!$this->post) {
            return '';
        }

        $data = http_build_query($this->post, '', '&', PHP_QUERY_RFC3986);
        return " --data-urlencode '$data'";
    }

    private function buildRawBody(): string
    {
        if (!$this->input) {
            return '';
        }

        $body = $this->escape($this->input);
        return " --data-raw '$body'";
    }

    private function escape($parameter): string
    {
        return addcslashes($parameter, "'");
    }

    private function removeBoundary(string $value): string
    {
        return preg_replace('/; boundary=(-)+\d+$/', '', $value);
    }
}
