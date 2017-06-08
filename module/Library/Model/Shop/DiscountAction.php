<?php
/**
* The DiscountAction class definition.
*
* This class represents relationships between discounts and actions.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Relationship\DiscountDiscountAction;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class DiscountAction
 * @package Library\Model\Shop
 */

/**
 * @Entity
 * @Table(name="discount_actions")
 * @HasLifecycleCallbacks
 */
class DiscountAction extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="name", type="string", length=500, nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @Column(name="shiping_discount", type="decimal", precision=2, nullable=true)
     * @var float
     */
    protected $shipping_discount;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\ShippingMethod")
     * @JoinColumn(name="shipping_method_id", referencedColumnName="id")
     * @var ShippingMethod
     */
    protected $shipping_method;

    /**
     * @Column(name="ship_discount_type", type="string", length=500, nullable=true)
     * @var string
     */
    protected $ship_discount_type;

    /**
     * @Column(name="total_discount_type", type="string", length=500, nullable=true)
     * @var string
     */
    protected $total_discount_type;

    /**
     * @Column(name="total_discount", type="decimal", precision=2, nullable=true)
     * @var float
     */
    protected $total_discount;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\DiscountDiscountAction", mappedBy="discount_action")
     * @var DiscountDiscountAction[]
     */
    protected $discount_action_discounts;

    /**
     * @return DiscountDiscountAction[]
     */
    public function getDiscountActionDiscounts()
    {
        return $this->discount_action_discounts;
    }

    /**
     * @param DiscountDiscountAction[] $discount_action_discounts
     */
    public function setDiscountActionDiscounts($discount_action_discounts)
    {
        $this->discount_action_discounts = $discount_action_discounts;
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
     * @return float
     */
    public function getShippingDiscount()
    {
        return $this->shipping_discount;
    }

    /**
     * @param float $shipping_discount
     */
    public function setShippingDiscount($shipping_discount)
    {
        $this->shipping_discount = $shipping_discount;
    }

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
     * @return string
     */
    public function getShipDiscountType()
    {
        return $this->ship_discount_type;
    }

    /**
     * @param string $ship_discount_type
     */
    public function setShipDiscountType($ship_discount_type)
    {
        $this->ship_discount_type = $ship_discount_type;
    }

    /**
     * @return string
     */
    public function getTotalDiscountType()
    {
        return $this->total_discount_type;
    }

    /**
     * @param float $total_discount_type
     */
    public function setTotalDiscountType($total_discount_type)
    {
        $this->total_discount_type = $total_discount_type;
    }

    /**
     * @return float
     */
    public function getTotalDiscount()
    {
        return $this->total_discount;
    }

    /**
     * @param float $total_discount
     */
    public function setTotalDiscount($total_discount)
    {
        $this->total_discount = $total_discount;
    }
}