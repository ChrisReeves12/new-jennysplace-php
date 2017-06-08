<?php
/**
* The DiscountDiscountAction class definition.
*
* This class represents the relationship between discounts and discount actions.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Relationship;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Shop\Discount;
use Library\Model\Shop\DiscountAction;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class DiscountDiscountAction
 * @package Library\Model\Relationship
 */

/**
 * @Entity
 * @Table(name="assoc_discounts_discount_actions")
 * @HasLifecycleCallbacks
 */
class DiscountDiscountAction extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\Discount", inversedBy="discount_discount_actions")
     * @JoinColumn(name="discount_id", referencedColumnName="id")
     * @var Discount
     */
    protected $discount;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\DiscountAction", inversedBy="discount_action_discounts")
     * @JoinColumn(name="discount_action_id", referencedColumnName="id")
     * @var DiscountAction
     */
    protected $discount_action;

    /**
     * @Column(name="opp_order", type="integer", nullable=true)
     * @var int
     */
    protected $operation_order;

    /**
     * @return Discount
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param Discount $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return DiscountAction
     */
    public function getDiscountAction()
    {
        return $this->discount_action;
    }

    /**
     * @param DiscountAction $discount_action
     */
    public function setDiscountAction($discount_action)
    {
        $this->discount_action = $discount_action;
    }

    /**
     * @return int
     */
    public function getOperationOrder()
    {
        return $this->operation_order;
    }

    /**
     * @param int $operation_order
     */
    public function setOperationOrder($operation_order)
    {
        $this->operation_order = $operation_order;
    }

}