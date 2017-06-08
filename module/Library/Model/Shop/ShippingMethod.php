<?php
/**
* The ShippingMethod class definition.
*
* This class defines shipping methods.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Index;
use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;

/**
 * Class ShippingMethod
 * @package Library\Model\Shop
 */

/**
 * @Entity
 * @Table(name="shipping_methods", indexes={@Index(name="idx_carrier_carrier_id", columns={"carrier", "carrier_id"})})
 * @HasLifecycleCallbacks
 */
class ShippingMethod extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="name", type="string", nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @Column(name="carrier", type="string", nullable=false)
     * @var string
     */
    protected $carrier;

    /**
     * @Column(name="carrier_id", type="string", length=100, nullable=false)
     * @var string
     */
    protected $carrier_id;

    /**
     * @Column(name="inactive", type="boolean", nullable=false)
     * @var bool
     */
    protected $inactive;

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
     * @return string
     */
    public function getCarrier()
    {
        return $this->carrier;
    }

    /**
     * @param string $carrier
     */
    public function setCarrier($carrier)
    {
        $this->carrier = $carrier;
    }

    /**
     * @return string
     */
    public function getCarrierId()
    {
        return $this->carrier_id;
    }

    /**
     * @param string $carrier_id
     */
    public function setCarrierId($carrier_id)
    {
        $this->carrier_id = $carrier_id;
    }

    /**
     * @return bool
     */
    public function isInactive()
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