<?php

namespace Divido\Helpers\Paginator;

use Divido\ApiExceptions\InternalServerErrorException;
use Slim\Http\Request;

/**
 * Class Paginator
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class PaginatorHelper
{
    /** @var PaginatorModel */
    private $model;

    /** @var Request */
    private $request;

    public function __construct(
        Request $request,
        $defaultSortColumn,
        $defaultSortDirection,
        $filterableColumns = [['created_after']],
        $sortableColumns = ['created_at'],
        $replaceParameters = []
    ) {
        $this->request = $request;

        if (isset($replaceParameters[$defaultSortColumn])) {
            $defaultSortColumn = $replaceParameters[$defaultSortColumn];
        }

        $model = (new PaginatorModel())
            ->setFilterableColumns($filterableColumns)
            ->setSortableColumns($sortableColumns)
            ->setSortColumn($defaultSortColumn)
            ->setSortDirection($defaultSortDirection);

        $this->model = $model;

        $model->setPerPage((int) $request->getParam('per_page', 25));

        if ($request->getParam('page')) {
            $model->setPage($request->getParam('page'));
        }
        if (!empty($request->getParam('sort')['column'])) {
            $sortColumn = $request->getParam('sort')['column'];

            if (in_array($sortColumn, $model->getSortableColumns())) {

                if (isset($replaceParameters[$sortColumn])) {
                    $sortColumn = $replaceParameters[$sortColumn];
                }

                $model->setSortColumn($sortColumn);
            }
        }
        if (!empty($request->getParam('sort')['direction'])) {
            $sortDirection = $request->getParam('sort')['direction'];

            if (in_array(mb_strtolower($sortDirection), ['asc', 'desc'])) {
                $model->setSortDirection($sortDirection);
            }
        }
        if (!empty($request->getParam('filter'))) {
            $filters = [];
            $pdoParams = [];

            foreach ($request->getParam('filter') as $filterColumn => $filterValue) {
                if (!in_array($filterColumn, $filterableColumns)) {
                    continue;
                }

                if (array_key_exists($filterColumn, $replaceParameters) && !empty($replaceParameters[$filterColumn])) {
                    $filterColumn = $replaceParameters[$filterColumn];
                }

                $filterValueLength = mb_strlen($filterValue);

                if (mb_substr($filterColumn, -7, 7) == '_before') {
                    $filterColumn = mb_substr($filterColumn, 0, -7) . "_at";
                    $operator = "<=";
                    $discern = 'before';
                } else if (mb_substr($filterColumn, -6, 6) == '_after') {
                    $filterColumn = mb_substr($filterColumn, 0, -6) . "_at";
                    $operator = ">=";
                    $discern = 'after';
                } else if (mb_substr($filterValue, 0, 1) === '%' && mb_substr($filterValue, $filterValueLength - 1, 1) === '%') {
                    $operator = 'LIKE';
                } else {
                    $operator = (mb_substr($filterValue, 0, 1) == '!') ? "!=" : "=";
                }
                $filterColumn = "a." . $filterColumn;
                $filterColumnPdoName = preg_replace("/[^A-Za-z0-9]/", '', $filterColumn) . ($discern ?? '');

                $filters[] = $filterColumn . " " . $operator . " :" . $filterColumnPdoName;
                $pdoParams[":" . $filterColumnPdoName] = $filterValue;
            }

            $this->model->setFilters($filters);
            $this->model->setPdoParams($pdoParams);
        }
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'count' => $this->model->getCount(),
            'current_page' => $this->model->getPage(),
            'first_item' => $this->model->getFirst(),
            'has_more_pages' => ($this->model->getPage() < $this->model->getLastPage()) ? true : false,
            'last_page' => $this->model->getLastPage(),
            'per_page' => $this->model->getPerPage(),
            'total_exceeds_pagination_limit' => false
        ];
    }

    /**
     * @param \PDO $pdo
     * @param array $query
     * @return bool|\PDOStatement
     * @throws InternalServerErrorException
     */
    public function getPrepareStatement(\PDO $pdo, array $query)
    {
        if (!isset($query['from'])) {
            throw new InternalServerErrorException('No table provided');
        }
        if (!isset($query['select'])) {
            throw new InternalServerErrorException('No select columns provided');
        }

        $whereArray = (isset($query['where']) && is_array($query['where'])) ? $query['where'] : [];
        $whereArray = array_merge($this->model->getFilters(), $whereArray);

        if (isset($query['replace']) && is_array($query['replace'])) {
            foreach ($whereArray as $name => $value) {
                if (isset($query['replace'][$name])) {
                    $whereArray[$query['replace'][$name]] = $value;
                }
            }
        }

        $where = (count($whereArray) > 0) ? ' WHERE ' . implode(" AND ", $whereArray) : '';

        $pdoParams = (isset($query['params']) && is_array($query['params'])) ? $query['params'] : [];
        $pdoParams = array_merge($pdoParams, $this->model->getPdoParams());

        $orderBy = ($this->model->getSortColumn()) ? ' ORDER BY `' . $this->model->getSortColumn() . '` ' . $this->model->getSortDirection() : '';

        $countStatement = 'SELECT COUNT(*) AS rows FROM ' . $query['from'] . $where;

        $statement = $pdo->prepare($countStatement);

        $statement->execute($pdoParams);
        $row = $statement->fetch();
        $this->model->setCount($row->rows);

        $selectStatement = 'SELECT ' . $query['select'] . ' FROM ' . $query['from'] . $where . $orderBy . ' LIMIT ' . $this->model->getFirst() . ',' . $this->model->getPerPage();

        $statement = $pdo->prepare($selectStatement);
        $statement->execute($pdoParams);

        return $statement;

    }
}
