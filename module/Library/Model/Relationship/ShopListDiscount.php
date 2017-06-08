<?php
/**
* The ShopListDiscount class definition.
*
* Represents the relationship between shopping shop_lists and discounts.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Relationship;

use Library\Model\AbstractModel;
use Library\Model\Shop\Discount;
use Library\Model\Shop\ShopList;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;

/**
 * Class ShopListDiscount
 * @package Library\Model\Relationship
 */

/**
 * @Entity
 * @Table(name="assoc_shop_lists_discounts")
 * @HasLifecycleCallbacks
 */
class ShopListDiscount extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\ShopList", inversedBy="shop_list_discounts")
     * @JoinColumn(name="shop_list_id", referencedColumnName="id")
     * @var ShopList
     */
    protected $shop_list;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\Discount", inversedBy="discount_shop_lists")
     * @JoinColumn(name="discount_id", referencedColumnName="id")
     * @var Discount
     */
    protected $discount;

    /**
     * @return ShopList
     */
    public function getShopList()
    {
        return $this->shop_list;
    }

    /**
     * @param ShopList $shop_list
     */
    public function setShopList($shop_list)
    {
        $this->shop_list = $shop_list;
    }

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
}