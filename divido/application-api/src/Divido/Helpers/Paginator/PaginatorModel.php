<?php

namespace Divido\Helpers\Paginator;

/**
 * Class PaginatorModel
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class PaginatorModel
{
    /** @var int */
    private $count;

    /** @var int */
    private $page;

    /** @var int */
    private $perPage;

    /** @var array */
    private $filters;

    /** @var array */
    private $pdoParams;

    /** @var string */
    private $sortColumn;

    /** @var string */
    private $sortDirection;

    /** @var array */
    private $filterableColumns;

    /** @var array */
    private $sortableColumns;

    /**
     * @return float|int
     */
    public function getFirst()
    {
        if ($this->getPage() > 0) {
            return ($this->getPage()-1)*$this->getPerPage();
        }

        return 0;
    }

    /**
     * @return float
     */
    public function getLastPage()
    {
        return ceil($this->getCount()/$this->getPerPage());
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count ?? 0;
    }

    /**
     * @param int $count
     * @return PaginatorModel
     */
    public function setCount(int $count): PaginatorModel
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return ($this->page > 1) ? $this->page:1;
    }

    /**
     * @param int $page
     * @return PaginatorModel
     */
    public function setPage(int $page): PaginatorModel
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return ((int) $this->perPage > 0) ? $this->perPage:25;
    }

    /**
     * @param int $perPage
     * @return PaginatorModel
     */
    public function setPerPage(int $perPage): PaginatorModel
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return (!empty($this->filters) > 0) ? $this->filters:[];
    }

    /**
     * @param array $filters
     * @return PaginatorModel
     */
    public function setFilters(array $filters): PaginatorModel
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return array
     */
    public function getPdoParams(): array
    {
        return (!empty($this->pdoParams) > 0) ? $this->pdoParams:[];
    }

    /**
     * @param array $pdoParams
     * @return PaginatorModel
     */
    public function setPdoParams(array $pdoParams): PaginatorModel
    {
        $this->pdoParams = $pdoParams;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortColumn(): string
    {
        return $this->sortColumn;
    }

    /**
     * @param string $sortColumn
     * @return PaginatorModel
     */
    public function setSortColumn(string $sortColumn): PaginatorModel
    {
        $this->sortColumn = $sortColumn;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    /**
     * @param string $sortDirection
     * @return PaginatorModel
     */
    public function setSortDirection(string $sortDirection): PaginatorModel
    {
        $this->sortDirection = $sortDirection;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilterableColumns(): array
    {
        return $this->filterableColumns;
    }

    /**
     * @param array $filterableColumns
     * @return PaginatorModel
     */
    public function setFilterableColumns(array $filterableColumns): PaginatorModel
    {
        $this->filterableColumns = $filterableColumns;

        return $this;
    }

    /**
     * @return array
     */
    public function getSortableColumns(): array
    {
        return $this->sortableColumns;
    }

    /**
     * @param array $sortableColumns
     * @return PaginatorModel
     */
    public function setSortableColumns(array $sortableColumns): PaginatorModel
    {
        $this->sortableColumns = $sortableColumns;

        return $this;
    }
}
