<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer;

use Concrete\Core\Search\ItemList\Database\ItemList;
use Concrete\Core\Search\Pagination\Pagination;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\Manufacturer;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;

class ManufacturerList extends ItemList
{

    /**
     * Create base query
     */
    public function createQuery()
    {
        $this->query->select('m.mID')
            ->from('CommunityStoreManufacturer', 'm');
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        return $query->select('count(m.mID)')
            ->execute()
            ->fetchColumn();
    }
    /**
     * Gets the pagination object for the query.
     * @return Pagination
     */
    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {

        });
        $pagination = new Pagination($this, $adapter);
        return $pagination;
    }

    public function getResult($queryRow)
    {
        $ai = Manufacturer::getByID($queryRow['mID']);
        return $ai;
    }

    public static function getManufacturerList()
    {
        $em = dbORM::entityManager();
        $queryBuilder = $em->createQueryBuilder();

        return $queryBuilder->select('m')
            ->from('\Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\Manufacturer', 'm')
            ->orderBy('m.mName')
            ->getQuery()
            ->getResult();
    }

}