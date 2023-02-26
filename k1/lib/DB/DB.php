<?php

namespace K1\DB;

use K1\DB\Builder\Query;
use K1\System\Application;
use K1\System\Config;
use K1\System\Exceptions\SystemException;

class DB
{
    private static ?DB $instance = null;
    private ?\mysqli $connect = null;
    private array $data = [];
    private bool $debug = false;
    private bool $log = false;
    private int $page = 1;
    private bool $isSetPage = false;
    private bool $imitation = false;
    private bool $imitationNewLine = true;

    public int $maxOffset = 100000000;

    private function __construct()
    {

    }

    public static function getInstance(): ?DB
    {
        if (self::$instance === null) {
            self::$instance = new DB();
        }

        return self::$instance;
    }

    /**
     * @throws SystemException
     */
    private function setConnect()
    {
        $config = Config::get('db');

        if ($config['debug'] || $config['log']) {
            $this->debug = true;

            if ($config['log']) {
                $this->log = $config['log'];
            }
        }

        $this->connect = new \mysqli($config['host'], $config['user'], $config['password'], $config['database']);
    }

    /**
     * @throws SystemException
     */
    public function getConnect(): ?\mysqli
    {
        if (!$this->connect) {
            $this->setConnect();
        }

        return $this->connect;
    }

    public function setImitation(bool $value = true, bool $addNewLine = false)
    {
        $this->imitation = $value;
        $this->imitationNewLine = $addNewLine;
    }

    public function setCurrentPage($value)
    {
        if ($value < 0) {
            $value = 1;
        }

        if ($value > $this->maxOffset) {
            $value = $this->maxOffset;
        }

        $this->isSetPage = true;
        $this->page = (int)$value;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @throws SystemException
     */
    private function query(string $sql, int $size = 0)
    {
        $result = null;

        $connect = $this->getConnect();

        if ($this->debug) {
            $time = microtime(1);

            if (!array_key_exists('stat', $this->data)) {
                $this->data['stat'] = [
                    'total' => 0
                ];
            }
        }

        if ($size) {
            if (!$this->isSetPage) {
                $this->setCurrentPage($_REQUEST['page'] ?? 1);
            }

            $start = 0;

            if ($this->page > 1) {
                $start = $this->page * $size - $size;
            }

            $sqlReplace = preg_replace("#^(select)#i", "\\1 SQL_CALC_FOUND_ROWS", $sql);
            $sqlReplace .= ' LIMIT ' . $start . ', ' . $size;

            $result = $this->query($sqlReplace);

            if (!$this->imitation) {
                $total = $this->query('SELECT FOUND_ROWS()')->fetch_assoc()['FOUND_ROWS()'];

                if ($total && $start >= $total) {
                    $this->setCurrentPage(1);

                    return $this->query($sql, $size);
                }

                $this->data['page'] = [
                    'total' => $total,
                    'size' => $size,
                    'current' => $this->page
                ];
            }
        } else {
            if (!$this->imitation) {
                $result = $connect->query($sql);
            } else {
                echo $sql;
                if ($this->imitationNewLine) {
                    echo PHP_EOL;
                }
            }

            if ($this->debug) {
                $now = sprintf('%f', microtime(1) - $time);
                $backtrace = debug_backtrace();

                $this->data['stat']['total'] += $now;
                $this->data['stat']['queries'][] = [
                    'query' => $sql,
                    'time' => $now,
                    'backtrace' => $backtrace
                ];

                if ($this->log) {
                    $log = new Logger();
                    $log->add([
                        'query' => $sql,
                        'time' => $now,
                        'backtrace' => $backtrace
                    ]);
                }
            }
        }

        if ($connect->error) {
            throw new SystemException($connect->error . '<hr>Query: ' . $sql);
        }

        return $result;
    }

    /**
     * @throws SystemException
     */
    public function getList(string $table, array $options = []): ?array
    {
        $builder = new Query($this->getConnect());

        $builder->select($table);

        if (array_key_exists('select', $options)) {
            $builder->from($options['select']);
        }

        if (array_key_exists('filter', $options)) {
            foreach ($options['filter'] as $key => $value) {
                $parse = $this->parse($key, $value);

                $builder->where($parse['key'], $parse['operation'], $parse['value']);
            }
        }

        if (array_key_exists('orderBy', $options)) {
            foreach ($options['orderBy'] as $key => $value) {
                $builder->orderBy($key, $value);
            }
        }

        $size = 0;

        if (array_key_exists('size', $options)) {
            $size = $options['size'];
        } else {
            if (array_key_exists('limit', $options)) {
                if (is_array($options['limit'])) {
                    $builder->limit($options['limit'][0], $options['limit'][1]);
                } else {
                    $builder->limit($options['limit']);
                }
            }
        }

        return $this->getListRaw($builder->build(), $size);
    }

    /**
     * @throws SystemException
     */
    public function getListRaw(string $sql, int $size = 0): ?array
    {
        $result = $this->query($sql, $size);

        if ($result instanceof \mysqli_result) {
            $list = [];
            while ($row = $result->fetch_assoc()) {
                $list[] = $row;
            }

            $result->free();

            return $list;
        }

        return null;
    }

    /**
     * @throws SystemException
     */
    public function getChecksum(string $table): string
    {
        $sql = [];
        foreach ($this->getListRaw("CHECKSUM TABLE `" . $table . "`") as $row) {
            $sql[] = $row['Checksum'];
        }

        return implode(', ', $sql);
    }

    /**
     * @throws SystemException
     */
    public function update(string $table, array $fields, array $where = [])
    {
        $builder = new Query($this->getConnect());

        foreach ($fields as $key => $value) {
            if (is_null($value)) {
                $fields[$key] = DB::raw('null');
            }
        }

        $builder->update($table, $fields);

        foreach ($where as $key => $value) {
            $parse = $this->parse($key, $value);

            $builder->where($parse['key'], $parse['operation'], $parse['value']);
        }

        $this->query($builder->build());

        return $this->getConnect()->affected_rows;
    }

    /**
     * @throws SystemException
     */
    public function insert(string $table, array $fields = [])
    {
        $builder = new Query($this->getConnect());

        $builder->insert($table, $fields);

        $this->query($builder->build());

        return $this->getConnect()->insert_id;
    }

    /**
     * @throws SystemException
     */
    public function delete(string $table, array $where = [])
    {
        $builder = new Query($this->getConnect());

        $builder->delete($table);

        foreach ($where as $key => $value) {
            $parse = $this->parse($key, $value);

            $builder->where($parse['key'], $parse['operation'], $parse['value']);
        }

        $this->query($builder->build());

        return $this->getConnect()->affected_rows;
    }

    private function getParseResult($field, $operation, $values): array
    {
        if (is_array($values)) {
            if ($operation == '!=') {
                $operation = 'not in';
            } else {
                $operation = 'in';
            }

            $newValues = [];
            foreach ($values as $value) {
                if (is_null($value)) {
                    $newValues[] = Query::raw('NULL');
                } else {
                    $newValues[] = $value;
                }
            }

            $values = $newValues;

            return [
                'key' => $field,
                'operation' => $operation,
                'value' => $values
            ];
        }

        if (in_array($operation, ['%', '%%'])) {
            $operation = 'like';
            $values = '%' . $values . '%';
        }

        if ($operation == '%#') {
            $operation = 'like';
            $values = $values . '%';
        }

        if ($operation == '#%') {
            $operation = 'like';
            $values = '%' . $values;
        }

        if (is_null($values)) {
            $values = Query::raw('NULL');
        }

        return [
            'key' => $field,
            'operation' => $operation,
            'value' => $values
        ];
    }

    private function parse($field, $value): array
    {
        $operations = ['!=', '>=', '<=', '=', '<>', '>', '<', '%%', '%#', '#%', '%'];

        foreach ($operations as $operation) {
            if (strpos($field, $operation) !== false) {
                $field = str_replace($operation, '', $field);

                return $this->getParseResult($field, $operation, $value);
            }
        }

        return $this->getParseResult($field, '=', $value);
    }

    /**
     * @throws SystemException
     */
    public static function escape($text): string
    {
        $db = self::getInstance();

        return trim($db->getConnect()->escape_string((string)$text));
    }

    public static function raw($value): \stdClass
    {
        $ob = new \stdClass();
        $ob->value = $value;

        return $ob;
    }

    /**
     * @throws SystemException
     */
    public function close()
    {
        $this->getConnect()->close();
    }

    public function pagination($component = 'system/pagination')
    {
        $data = $this->getData();

        if (!empty($data['page'])) {
            $app = Application::getInstance();
            return $app->component($component, $data['page'], true);
        }

        return '';
    }
}