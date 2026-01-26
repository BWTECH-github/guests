<?php

declare(strict_types=1);

namespace OCP\AppFramework\Http;

class DataResponse extends Response {
    protected $data;
    
    public function __construct($data = [], int $statusCode = 200, array $headers = []) {
        $this->data = $data;
        $this->status = $statusCode;
        $this->headers = $headers;
    }
    
    public function getData() {
        return $this->data;
    }
}