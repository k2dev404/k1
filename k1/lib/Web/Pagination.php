<?php

namespace K1\Web;

class Pagination
{
    private int $total_records;
    private int $items_per_page;
    private int $current_page;
    private int $first_page = 1;
    private array $url_decode = [];
    private int $width;

    public function __construct(int $total_records, int $items_per_page, int $current_page, string $url = '', int $width = 7)
    {
        $this->setTotalRecords($total_records);
        $this->setItemsPerPage($items_per_page);
        $this->setCurrentPage($current_page);
        $this->setUrl($url);

        $this->width = $width;
    }

    public function setUrl(string $url = ''): Pagination
    {
        if (!$url) {
            $url = $_SERVER['REQUEST_URI'];
        }

        $parse = parse_url($url);

        $result = [
            'path' => $parse['path'],
            'query' => []
        ];

        if (isset($parse['query'])) {
            parse_str($parse['query'], $result['query']);
        }

        $this->url_decode = $result;

        return $this;
    }

    private function getModifyUrl(int $page)
    {
        $url = $this->url_decode;

        if ($page > 1) {
            $url['query']['page'] = $page;
        } else if (isset($url['query']['page'])) {
            unset($url['query']['page']);
        }

        $result = $url['path'];

        if ($url['query']) {
            $result .= '?' . http_build_query($url['query']);
        }

        return $result;
    }

    public function getTotalRecords(): int
    {
        return $this->total_records;
    }

    public function setTotalRecords(int $total_records): Pagination
    {
        $this->total_records = $total_records;

        return $this;
    }

    public function getItemsPerPage(): int
    {
        return $this->items_per_page;
    }

    public function setItemsPerPage(int $items_per_page): Pagination
    {
        $this->items_per_page = $items_per_page;

        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->current_page;
    }

    public function setCurrentPage(int $current_page): Pagination
    {
        if ($current_page < 1 || $current_page > $this->getTotalPages()) {
            $current_page = 1;
        }

        $this->current_page = $current_page;

        return $this;
    }

    public function getFirstPage(): int
    {
        return $this->first_page;
    }

    public function getNextPage(): int
    {
        if ($this->hasNext()) {
            return $this->current_page + $this->first_page;
        }

        return 0;
    }

    public function hasNext(): bool
    {
        return $this->current_page < $this->getLastPage();
    }

    public function getLastPage(): int
    {
        return $this->getTotalPages();
    }

    public function getTotalPages(): int
    {
        return ceil($this->total_records / $this->items_per_page);
    }

    public function getPrevPage(): int
    {
        if ($this->hasPrev()) {
            return $this->current_page - $this->first_page;
        }

        return 0;
    }

    public function hasPrev(): bool
    {
        return $this->current_page > $this->first_page;
    }

    public function getCalculate(): array
    {
        $total = $this->getTotalPages();

        if ($total < 2) {
            return [];
        }

        $result = [
            'first' => [],
            'last' => [],
            'prev' => [],
            'next' => [],
            'pages' => [],
        ];

        $current = $this->getCurrentPage();

        if ($first = $this->getFirstPage()) {
            $result['first'] = [
                'page' => $first,
                'url' => $this->getModifyUrl($first)
            ];
        }

        if ($last = $this->getLastPage()) {
            $result['last'] = [
                'page' => $last,
                'url' => $this->getModifyUrl($last)
            ];
        }

        if ($prev = $this->getPrevPage()) {
            $result['prev'] = [
                'page' => $prev,
                'url' => $this->getModifyUrl($prev)
            ];
        }

        if ($next = $this->getNextPage()) {
            $result['next'] = [
                'page' => $next,
                'url' => $this->getModifyUrl($next)
            ];
        }

        if ($last > 1) {
            $start = 1;
            $end = $this->width > 0 && $last > $this->width ? $this->width : $last;

            if ($this->width > 0) {
                $average = floor($this->width / 2);

                if ($current > $this->width - $average) {
                    if ($current + $average > $total) {
                        $end = $total;
                    } else {
                        $end = $current + $average;
                    }
                    $start = $end - $this->width + 1;
                }
            }

            foreach (range($start, $end) as $i) {
                $item = [
                    'page' => $i,
                    'url' => $this->getModifyUrl($i),
                ];

                if ($current == $i) {
                    $item['current'] = 1;
                }

                $result['pages'][] = $item;
            }
        }

        return $result;
    }
}