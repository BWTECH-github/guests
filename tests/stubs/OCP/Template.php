<?php

declare(strict_types=1);

namespace OCP;

class Template {
    protected string $app;
    protected string $name;
    protected array $vars = [];
    
    public function __construct(string $app, string $name, string $renderAs = '') {
        $this->app = $app;
        $this->name = $name;
    }
    
    public function assign(string $key, $value): void {
        $this->vars[$key] = $value;
    }
    
    public function fetchPage(): string {
        return '';
    }
}