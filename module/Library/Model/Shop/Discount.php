<?php
/**
* The Discount class definition.
*
* This class represents discounts.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop;

use Doctrine\ORM\Mapping\Column;
use Library\Model\AbstractModel;
use Library\Model\Relationship\CategoryDiscount;
use Library\Model\Relationship\DiscountDiscountAction;
use Library\Model\Relationship\ShopListDiscount;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * Class Discount
 * @package Library\Model\Shop
 */

/**
 * @Entity
 * @Table(name="discounts")
 * @HasLifecycleCallbacks
 */
class Discount extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="name", type="string", length=500, nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @Column(name="script_name", type="string", nullable=true)
     * @var string
     */
    protected $script_name;

    /**
     * @Column(name="dollar_hurdle", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $dollar_hurdle;

    /**
     * @Column(name="code", type="string", length=20, nullable=false, unique=true)
     * @var string
     */
    protected $code;

    /**
     * @Column(name="start_date", type="datetime", nullable=false)
     * @var \DateTime
     */
    protected $start_date;

    /**
     * @Column(name="end_date", type="datetime", nullable=false)
     * @var \DateTime
     */
    protected $end_date;

    /**
     * @Column(name="is_inactive", type="boolean", nullable=false)
     * @var bool
     */
    protected $is_inactive;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\ShopListDiscount", mappedBy="discount", cascade={"remove"})
     * @var ShopListDiscount[]
     */
    protected $discount_shop_lists;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\CategoryDiscount", mappedBy="discount", cascade={"remove"})
     * @var CategoryDiscount[]
     */
    protected $discount_categories;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\DiscountDiscountAction", mappedBy="discount", cascade={"remove", "persist"})
     * @var DiscountDiscountAction[]
     */
    protected $discount_discount_actions;

    /**
     * @return string
     */
    public function getScriptName()
    {
        return $this->script_name;
    }

    /**
     * @param string $script_name
     */
    public function setScriptName($script_name)
    {
        $this->script_name = $script_name;
    }

    /**
     * @return DiscountDiscountAction[]
     */
    public function getDiscountDiscountActions()
    {
        return $this->discount_discount_actions;
    }

    /**
     * @param DiscountDiscountAction[] $discount_discount_actions
     */
    public function setDiscountDiscountActions($discount_discount_actions)
    {
        $this->discount_discount_actions = $discount_discount_actions;
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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @param \DateTime $start_date
     */
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * @param \DateTime $end_date
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }

    /**
     * @return bool
     */
    public function getIsInactive()
    {
        return $this->is_inactive;
    }

    /**
     * @param bool $is_inactive
     */
    public function setIsInactive($is_inactive)
    {
        $this->is_inactive = $is_inactive;
    }

    /**
     * @return CategoryDiscount[]
     */
    public function getDiscountCategories()
    {
        return $this->discount_categories;
    }

    /**
     * @param CategoryDiscount[] $discount_categories
     */
    public function setDiscountCategories($discount_categories)
    {
        $this->discount_categories = $discount_categories;
    }

    /**
     * @return ShopListDiscount[]
     */
    public function getDiscountShopLists()
    {
        return $this->discount_shop_lists;
    }

    /**
     * @param ShopListDiscount[] $discount_shop_lists
     */
    public function setDiscountShopLists($discount_shop_lists)
    {
        $this->discount_shop_lists = $discount_shop_lists;
    }

    /**
     * @return float
     */
    public function getDollarHurdle()
    {
        return $this->dollar_hurdle;
    }

    /**
     * @param float $dollar_hurdle
     */
    public function setDollarHurdle($dollar_hurdle)
    {
        $this->dollar_hurdle = $dollar_hurdle;
    }

    /**
     * @param DiscountAction $discount_action
     */
    public function addDiscountAction(DiscountAction $discount_action)
    {
        $discount_discount_action = new DiscountDiscountAction();
        $discount_discount_action->setDiscount($this);
        $discount_discount_action->setDiscountAction($discount_action);

        $this->discount_discount_actions[] = $discount_discount_action;
    }
}