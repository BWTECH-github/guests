<?php

declare(strict_types=1);

namespace OCP;

interface IDBConnection {
    public function getQueryBuilder();
    public function prepare(string $sql, ?int $limit = null, ?int $offset = null);
    public function executeQuery(string $sql, array $params = [], array $types = []);
    public function executeUpdate(string $sql, array $params = [], array $types = []): int;
    public function lastInsertId(?string $seqName = null): string;
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
}