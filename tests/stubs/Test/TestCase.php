<?php

declare(strict_types=1);

namespace Test;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase {
	private static int $uniqueIdCounter = 0;
	
	protected function setUp(): void {
		parent::setUp();
	}
	
	protected function tearDown(): void {
		parent::tearDown();
	}
	
	/**
	 * Generate a unique ID for testing purposes
	 */
	protected function getUniqueID(string $prefix = ''): string {
		self::$uniqueIdCounter++;
		return $prefix . self::$uniqueIdCounter . '_' . uniqid();
	}
}
