<?php

namespace App\Repositories;

use App\Database\Database;
use PDO;

#[\AllowDynamicProperties]
abstract class Model
{
    protected static string $table = '';
    protected static array $fillable = [];
    protected static string $primaryKey = 'id';
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::$table, Database::getInstance());
    }

    public static function find(int|string $id): ?static
    {
        $instance = new static();
        $stmt = $instance->db->prepare("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? $instance->fill($data) : null;
    }

    public static function all(array $conditions = [], string $orderBy = ''): array
    {
        $instance = new static();
        $sql = "SELECT * FROM " . static::$table;
        $params = [];
        
        if ($conditions) {
            $where = [];
            foreach ($conditions as $key => $value) {
                if (is_array($value)) {
                    if (empty($value)) {
                        $where[] = "1 = 0";
                    } else {
                        $ph = implode(',', array_fill(0, count($value), '?'));
                        $where[] = "{$key} IN ({$ph})";
                        $params = array_merge($params, array_values($value));
                    }
                } else {
                    $where[] = "{$key} = ?";
                    $params[] = $value;
                }
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $stmt = $instance->db->prepare($sql);
        $stmt->execute($params);
        return array_map(fn($row) => (new static())->fill($row), $stmt->fetchAll());
    }

    public static function first(array $conditions = []): ?static
    {
        $results = static::all($conditions);
        return $results[0] ?? null;
    }

    public static function create(array $data): static
    {
        $instance = new static();
        $columns = [];
        $placeholders = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, static::$fillable)) {
                $columns[] = $key;
                $placeholders[] = '?';
                $values[] = $value;
            }
        }
        
        $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute($values);
        
        $id = $instance->db->lastInsertId();
        return static::find($id);
    }

    public function update(array $data): bool
    {
        $set = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, static::$fillable)) {
                $set[] = "{$key} = ?";
                $values[] = $value;
            }
        }
        
        if (!$set) return false;
        
        $values[] = $this->{static::$primaryKey};
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $set) . " WHERE " . static::$primaryKey . " = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete(): bool
    {
        $stmt = $this->db->prepare("DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?");
        return $stmt->execute([$this->{static::$primaryKey}]);
    }

    protected function fill(array $data): static
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }

    public static function hydrate(array $data): static
    {
        return (new static())->fill($data);
    }

    public function toArray(): array
    {
        $data = [];
        foreach (static::$fillable as $field) {
            if (property_exists($this, $field)) {
                $data[$field] = $this->$field;
            }
        }
        $data[static::$primaryKey] = $this->{static::$primaryKey};
        return $data;
    }
}

class QueryBuilder
{
    private string $table;
    private PDO $db;
    private array $wheres = [];
    private array $params = [];
    private string $orderBy = '';
    private int $limit = 0;
    private int $offset = 0;
    private array $joins = [];
    private string $select = '*';

    public function __construct(string $table, PDO $db)
    {
        $this->table = $table;
        $this->db = $db;
    }

    public function select(string $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->wheres[] = "{$column} {$operator} ?";
        $this->params[] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            $this->wheres[] = "1 = 0";
            return $this;
        }
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = "{$column} IN ({$placeholders})";
        $this->params = array_merge($this->params, $values);
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "ORDER BY {$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = "SELECT {$this->select} FROM {$this->table}";
        
        if ($this->joins) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        
        if ($this->wheres) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }
        
        if ($this->orderBy) {
            $sql .= ' ' . $this->orderBy;
        }
        
        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        if ($this->offset) {
            $sql .= " OFFSET {$this->offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->params);
        return $stmt->fetchAll();
    }

    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if ($this->joins) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        
        if ($this->wheres) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->params);
        return (int)($stmt->fetch()['count'] ?? 0);
    }

    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $this->offset(($page - 1) * $perPage)->limit($perPage);
        $data = $this->get();
        $total = $this->count();
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
        ];
    }
}