<?php
/**
* The QueryList class definition.
*
* This class represents shop lists that are dynamically created through queries.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop\ShopList;

use Doctrine\ORM\Mapping\Column;
use Library\Model\Shop\ShopList;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="query_lists")
 * @HasLifecycleCallbacks
**/
class QueryList extends ShopList
{
    /**
     * @Column(name="query", type="text", nullable=true)
     * @var string
     */
    protected $query;

    /**
     * @Column(name="name", type="string", length=500, nullable=true)
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }


    public function calculateTotals()
    {
        // Not used in Query Lists
    }

    public function calculateWeight()
    {
        // Not used in Query Lists
    }
}