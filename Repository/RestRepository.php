<?php

namespace Pgs\RestfonyBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Michał Sikora
 */
abstract class RestRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    abstract public function getBaseQuery();

    public function getListQuery(array $sortConfiguration, $sortQuery)
    {
        $query = $this->getBaseQuery();
        $sorts = $this->prepareRequestSort($sortConfiguration, $sortQuery);
        foreach ($sorts as $sort) {
            $query->addOrderBy($sort['column'], $sort['order']);
        }

        return $query;
    }

    private function prepareRequestSort(array $sortConfiguration, $sortQuery)
    {
        $result = [];
        $columns = explode("|", $sortQuery);

        foreach ($columns as $column) {
            if ($column) {
                $sortOrder = 'asc';

                if ($column{0} === '-') {
                    $sortOrder = 'desc';
                    $column = ltrim($column, '-');
                }

                if (isset($sortConfiguration[$column])) {
                    $result[] = [
                        'column' => $sortConfiguration[$column],
                        'order' => $sortOrder,
                    ];
                }
            }
        }

        return $result;
    }
}
