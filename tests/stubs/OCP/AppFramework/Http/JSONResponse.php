<?php

declare(strict_types=1);

namespace OCP\AppFramework\Http;

class JSONResponse extends Response {
    protected $data;
    
    public function __construct($data = [], int $statusCode = 200) {
        $this->data = $data;
        $this->status = $statusCode;
    }
    
    public function getData() {
        return $this->data;
    }
}