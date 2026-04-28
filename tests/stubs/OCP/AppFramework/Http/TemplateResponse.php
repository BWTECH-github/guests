<?php

declare(strict_types=1);

namespace OCP\AppFramework\Http;

class TemplateResponse extends Response {
	protected string $templateName;
	protected array $params;
	protected string $renderAs;
	protected string $appName;
	
	public function __construct(string $appName, string $templateName, array $params = [], string $renderAs = 'user') {
		$this->appName = $appName;
		$this->templateName = $templateName;
		$this->params = $params;
		$this->renderAs = $renderAs;
	}
	
	public function getTemplateName(): string {
		return $this->templateName;
	}
	
	public function getParams(): array {
		return $this->params;
	}
}
