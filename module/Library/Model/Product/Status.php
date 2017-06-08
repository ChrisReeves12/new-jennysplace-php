<?php
/**
* The Status class definition.
*
* This class represents product statuses.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Product;

use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;

/**
 * Class Status
 * @package Library\Model\Product
 */

/**
 * @Entity
 * @Table(name="product_status")
 * @HasLifecycleCallbacks
 */
class Status extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="name", type="string", length=500, nullable=false)
     * @var string
     */
    protected $name;

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

}