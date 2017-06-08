<?php
/**
* The Tax class definition.
*
* This class represents a tax that adds a percentage to the total before shipping based on state
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class Tax
 * @package Library\Model\Shop
 */

/**
 * @Entity
 * @Table(name="taxes")
 * @HasLifecycleCallbacks
 */
class Tax extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="state", length=10, type="string", nullable=false, unique=true)
     * @var string
     */
    protected $state;

    /**
     * @Column(name="rate", type="decimal", scale=2, nullable=false)
     * @var float
     */
    protected $rate;

    /**
     * @Column(name="inactive", type="boolean", nullable=false)
     * @var bool
     */
    protected $inactive;

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param int $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    /**
     * @return bool
     */
    public function getInactive()
    {
        return $this->inactive;
    }

    /**
     * @param bool $inactive
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;
    }
}