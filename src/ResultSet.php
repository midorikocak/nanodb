<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use midorikocak\querymaker\QueryInterface;

use function array_column;
use function array_shift;
use function ceil;
use function preg_match;
use function reset;

class ResultSet
{
    private int $total;
    private int $pageSize;
    private int $page;
    private ?int $offset;
    private ?int $limit;
    private ?string $orderBy;
    private ?string $order;
    private string $tableName;

    /**
     * @return static
     */
    public static function getResultSet(
        Database $db,
        QueryInterface $query
    ): self {
        $counterQuery = $query->count();
        $db->query($counterQuery);
        $db->execute();

        $counted = array_column($db->fetchAll(), 'COUNT(*)');

        $resultSet = new self();
        $resultSet->total = (int) reset($counted);

        $queryString = $query->getQuery();
        preg_match('/FROM (\S*)( WHERE)?/', $queryString, $tableMatch);

        preg_match('/OFFSET (\d+)/', $queryString, $offsetMatch);
        preg_match('/LIMIT (\d+)/', $queryString, $limitMatch);
        preg_match('/ORDER BY (\S+)( ASC| DESC)/', $queryString, $orderMatch);

        array_shift($offsetMatch);
        array_shift($limitMatch);
        array_shift($orderMatch);

        $resultSet->limit = (int) (reset($limitMatch) ?? 0);
        $resultSet->offset = (int) (reset($offsetMatch) ?? 0);
        $resultSet->orderBy = reset($orderMatch) !== false ? reset($orderMatch) : null;
        $resultSet->order = $orderMatch[1] ?? null;
        $resultSet->tableName = $tableMatch[1] ?? '';

        $resultSet->pageSize = $resultSet->limit ?? $resultSet->total;

        $resultSet->page = (int) ceil($resultSet->offset / $resultSet->pageSize);
        return $resultSet;
    }

    public static function getResultArray(Database $db, QueryInterface $query): array
    {
        return self::getResultSet($db, $query)->toArray();
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'pageSize' => $this->pageSize,
            'page' => $this->page,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'orderBy' => $this->orderBy,
            'tableName' => $this->tableName,
        ];
    }
}
