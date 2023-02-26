<?php

namespace K1\DB\Builder;

use K1\System\Exceptions\SystemException;

class Query
{
    private \mysqli $connect;

    private array $data = [];

    public function __construct(\mysqli $connection)
    {
        $this->connect = $connection;
        $this->data['operation'] = 'SELECT';
    }

    public static function raw($value): \stdClass
    {
        $ob = new \stdClass();
        $ob->value = $value;

        return $ob;
    }

    public function table($tables): Query
    {
        if (is_array($tables)) {
            foreach ($tables as $key => $item) {
                $this->data['tables'][$key] = $item;
            }
        } else {
            $this->data['tables'][] = $tables;
        }

        return $this;
    }

    public function select($table, $column = ''): Query
    {
        $this->data['operation'] = 'SELECT';

        $this->table($table);
        $this->from($column);

        return $this;
    }

    public function from($column): Query
    {
        if (is_array($column)) {
            foreach ($column as $key => $item) {
                $this->data['selects'][$key] = $item;
            }
        } else if (strlen($column)) {
            $this->data['selects'][] = $column;
        }

        return $this;
    }

    public function insert($table, array $fields): Query
    {
        $this->data['operation'] = 'INSERT';

        $this->table($table);

        foreach ($fields as $key => $value) {
            $this->data['inserts'][$key] = $value;
        }

        return $this;
    }

    public function update($table, array $fields): Query
    {
        $this->data['operation'] = 'UPDATE';

        $this->table($table);

        foreach ($fields as $key => $value) {
            $this->data['updates'][$key] = $value;
        }

        return $this;
    }

    public function delete($table): Query
    {
        $this->data['operation'] = 'DELETE';

        $this->table($table);

        return $this;
    }

    public function where($column, $param1 = null, $param2 = null, $divider = 'and'): Query
    {
        if (is_callable($column)) {
            $q = new Query($this->connect);

            call_user_func_array($column, [&$q]);

            $this->data['wheres'][] = [$divider, $q];

            return $this;
        }

        if (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->data['wheres'][] = [$divider, $key, '=', $value];
            }

            return $this;
        }

        if (!is_null($param1) && !is_null($param2)) {
            $this->data['wheres'][] = [$divider, $column, $param1, $param2];

            return $this;
        }

        if (!is_null($param1)) {
            $this->data['wheres'][] = [$divider, $column, '=', $param1];

            return $this;
        }

        return $this;
    }

    public function andWhere($column, $param1 = null, $param2 = null): Query
    {
        return $this->where($column, $param1, $param2);
    }

    public function orWhere($column, $param1 = null, $param2 = null): Query
    {
        return $this->where($column, $param1, $param2, 'or');
    }

    public function whereIn($column, array $values): Query
    {
        return $this->where($column, 'in', $values);
    }

    public function whereNotIn($column, array $values): Query
    {
        return $this->where($column, 'not in', $values);
    }

    public function whereNull($column): Query
    {
        return $this->where($column, 'is', self::raw('NULL'));
    }

    public function whereNotNull($column): Query
    {
        return $this->where($column, 'is not', self::raw('NULL'));
    }

    public function orWhereNull($column): Query
    {
        return $this->orWhere($column, 'is', self::raw('NULL'));
    }

    public function orWhereNotNull($column): Query
    {
        return $this->orWhere($column, 'is not', self::raw('NULL'));
    }

    /**
     * @throws SystemException
     */
    function orderBy($column, string $method = 'asc'): Query
    {
        if (!in_array($method, ['asc', 'desc'])) {
            throw new SystemException('Значение сортировки может быть asc либо desc');
        }

        $this->data['orderBy'][] = [$column, $method];

        return $this;
    }

    public function limit($limit, $limit2 = null): Query
    {
        if (!is_null($limit2)) {
            $this->data['offset'] = $limit;
            $this->data['limit'] = $limit2;
        } else {
            $this->data['offset'] = null;
            $this->data['limit'] = $limit;
        }

        return $this;
    }

    /**
     * @throws SystemException
     */
    public function build(): string
    {
        $build = new Result($this, $this->connect, $this->data);
        return $build->run();
    }
}