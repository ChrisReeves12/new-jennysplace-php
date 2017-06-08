<?php
/**
* The ShippingRange class definition.
*
* Represents a shipping rate range regarding weight or price
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class ShippingRange
 * @package Library\Model\Shop
 */

/**
 * @Entity
 * @Table(name="shipping_ranges")
 * @HasLifecycleCallbacks
 */
class ShippingRange extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\ShippingMethod")
     * @JoinColumn(name="shipping_method_id", referencedColumnName="id")
     * @var ShippingMethod
     */
    protected $shipping_method;

    /**
     * @Column(name="low_value", type="decimal", scale=2, nullable=false)
     * @var float
     */
    protected $low_value;

    /**
     * @Column(name="high_value", type="decimal", scale=2, nullable=false)
     * @var float
     */
    protected $high_value;

    /**
     * @Column(name="price", type="decimal", scale=2, nullable=false)
     * @var float
     */
    protected $price;

    /**
     * @return ShippingMethod
     */
    public function getShippingMethod()
    {
        return $this->shipping_method;
    }

    /**
     * @param ShippingMethod $shipping_method
     */
    public function setShippingMethod($shipping_method)
    {
        $this->shipping_method = $shipping_method;
    }

    /**
     * @return float
     */
    public function getLowValue()
    {
        return $this->low_value;
    }

    /**
     * @param float $low_value
     */
    public function setLowValue($low_value)
    {
        $this->low_value = $low_value;
    }

    /**
     * @return float
     */
    public function getHighValue()
    {
        return $this->high_value;
    }

    /**
     * @param float $high_value
     */
    public function setHighValue($high_value)
    {
        $this->high_value = $high_value;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }
}