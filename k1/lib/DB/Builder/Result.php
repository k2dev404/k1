<?php

namespace K1\DB\Builder;

use K1\System\Exceptions\SystemException;
use mysqli;
use stdClass;

class Result
{
    private Query $queryBuilder;
    private mysqli $connect;
    private array $data;

    public function __construct(Query $queryBuilder, mysqli $connect, $data)
    {
        $this->queryBuilder = $queryBuilder;
        $this->connect = $connect;
        $this->data = $data;
    }

    private function escape($value, $quotes = null, $dot = false)
    {
        if ($value instanceof stdClass) {
            return $value->value;
        }

        if ($dot) {
            $exp = explode('.', $value);

            if (count($exp) > 1) {
                return $this->escape($exp[0], $quotes) . '.' . $this->escape($exp[1], $quotes);
            } else {
                return $this->escape($value, $quotes);
            }
        }

        $result = trim($this->connect->escape_string((string)$value));

        if (!is_null($quotes)) {
            $result = $quotes . $result . $quotes;
        }

        return $result;
    }

    public function implodeValues($values, $quotes = null): string
    {
        $result = [];
        foreach ($values as $value) {
            $result[] = $this->escape($value, $quotes);
        }

        return implode(', ', $result);
    }

    public function where(array $where): string
    {
        $result = '';

        $first = true;
        foreach ($where as $item) {
            if ($first) {
                $item[0] = '';
            }

            if (!isset($item[2]) && isset($item[1]) && $item[1] instanceof Query) {
                $result .= ($item[0] ? ' ' . $item[0] : '') . ' ( ' . $this->where($item[1]->where) . ' )';

                continue;
            }

            if (is_array($item[3])) {
                $item[3] = '(' . $this->implodeValues($item[3], "'") . ')';
            } else {
                $item[3] = $this->escape($item[3], '\'');
            }

            $item[1] = $this->escape($item[1], '`', true);

            if (!$item[0]) {
                unset($item[0]);
            }

            $result .= ' ' . implode(' ', $item);

            $first = false;
        }

        return $result;
    }

    public function orderBy(array $orderBy): string
    {
        $result = [];

        foreach ($orderBy as $item) {
            $result[] = $this->escape($item[0], '`', true) . ' ' . $item[1];
        }

        return implode(', ', $result);
    }

    public function table(array $items): string
    {
        $result = [];

        foreach ($items as $key => $value) {
            if (is_string($key)) {
                $result[] = $this->escape($key, '`', true) . ' ' . $this->escape($value, '`');
            } else {
                $result[] = $this->escape($value, '`');
            }
        }

        return implode(', ', $result);
    }

    public function select(array $items): string
    {
        $result = [];

        foreach ($items as $key => $value) {
            if (is_string($key)) {
                $result[] = $this->escape($key, '`', true) . ' as ' . $this->escape($value, '`');
            } else {
                $result[] = $this->escape($value, '`');
            }
        }

        return implode(', ', $result);
    }

    public function limit($offset, $limit)
    {
        if (is_null($offset)) {
            return (int)$limit;
        } else {
            return (int)$offset . ', ' . (int)$limit;
        }
    }

    /**
     * @throws SystemException
     */
    public function run(): string
    {
        $wheres = true;
        $orderBy = true;
        $limit = true;

        $operation = $this->data['operation'];

        if (!array_key_exists('tables', $this->data)) {
            throw new SystemException('Не задана таблица');
        }

        $result = [];
        if ($operation == 'SELECT') {
            $result[] = $operation;
            if (array_key_exists('num', $this->data)) {
                $result[] = 'SQL_CALC_FOUND_ROWS';
            }

            if (array_key_exists('selects', $this->data)) {
                $result[] = $this->select($this->data['selects']);
            } else {
                $result[] = '*';
            }

            $result[] = 'FROM ' . $this->table($this->data['tables']);
        }

        if ($operation == 'UPDATE') {
            $result[] = $operation . ' ' . $this->escape(current($this->data['tables']), '`') . ' SET ';

            if (!array_key_exists('updates', $this->data)) {
                throw new SystemException('Не заданы поля для обновления');
            }

            $update = [];

            foreach ($this->data['updates'] as $key => $value) {
                $update[] = $this->escape($key, '`') . ' = ' . $this->escape($value, '\'');
            }

            $result[] = implode(', ', $update);

            $orderBy = false;
            $limit = false;
        }

        if ($operation == 'INSERT') {
            if (!array_key_exists('inserts', $this->data)) {
                throw new SystemException('Не заданы поля для вставки');
            }

            $fields = $values = [];

            foreach ($this->data['inserts'] as $key => $value) {
                $fields[] = $key;
                $values[] = $value;
            }

            $result[] = $operation . ' INTO ' . $this->escape(current($this->data['tables']), '`') . ' (' . $this->implodeValues($fields, '`') . ') VALUES (' . $this->implodeValues($values, '\'') . ')';

            $wheres = false;
            $orderBy = false;
        }

        if ($operation == 'DELETE') {
            $result[] = $operation;

            $result[] = 'FROM ' . $this->table($this->data['tables']);
        }

        if ($wheres && array_key_exists('wheres', $this->data)) {
            $result[] = 'WHERE ' . $this->where($this->data['wheres']);
        }

        if ($orderBy && array_key_exists('orderBy', $this->data)) {
            $result[] = 'ORDER BY ' . $this->orderBy($this->data['orderBy']);
        }

        if ($limit && array_key_exists('limit', $this->data)) {
            $result[] = 'LIMIT ' . $this->limit($this->data['offset'], $this->data['limit']);
        }

        return implode(' ', $result);
    }
}