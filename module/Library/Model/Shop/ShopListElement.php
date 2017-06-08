<?php
/**
* The ShopListElement class definition.
*
* This class represents one of the line elements of shop lists
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Media\Image;
use Library\Model\Product\Sku;
use Library\Model\Product\Status;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class ShopListElement
 * @package Library\Model\Shop
 */

/**
 * @Entity
 * @Table(name="shop_list_elements")
 * @HasLifecycleCallbacks
 */
class ShopListElement extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\ShopList", inversedBy="shop_list_elements")
     * @JoinColumn(name="shop_list_id", referencedColumnName="id")
     * @var ShopList
     */
    protected $shop_list;

    /**
     * @OneToOne(targetEntity="Library\Model\Shop\ProductReturn", mappedBy="shop_list_element", cascade={"persist", "remove"})
     * @var ProductReturn
     */
    protected $product_return;

    /**
     * @Column(name="number", type="string", length=500, nullable=true)
     * @var string
     */
    protected $number;

    /**
     * @Column(name="sort_order", type="integer", nullable=true)
     * @var int
     */
    protected $sort_order;

    /**
     * @ManyToOne(targetEntity="Library\Model\Product\Sku", inversedBy="shop_list_elements")
     * @JoinColumn(name="sku_id", referencedColumnName="id")
     * @var Sku
     */
    protected $sku;

    /**
     * @Column(name="price", type="decimal", scale=2, nullable=false)
     * @var float
     */
    protected $price;

    /**
     * @Column(name="weight", type="decimal", scale=2, nullable=false)
     * @var float
     */
    protected $weight;

    /**
     * @Column(name="name", type="string", length=500, nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @Column(name="tax", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $tax;

    /**
     * @Column(name="total", type="decimal", scale=2, nullable=false)
     * @var float
     */
    protected $total;

    /**
     * @ManyToOne(targetEntity="Library\Model\Media\Image")
     * @JoinColumn(name="image_id", referencedColumnName="id")
     * @var Image
     */
    protected $image;

    /**
     * @Column(name="quantity", type="integer", nullable=false)
     * @var int
     */
    protected $quantity;

    /**
     * @ManyToOne(targetEntity="Library\Model\Product\Status")
     * @JoinColumn(name="status", referencedColumnName="id")
     * @var Status
     */
    protected $status;

    /**
     * @Column(name="notes", type="text", nullable=true)
     * @var string
     */
    protected $notes;

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
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @param int $sort_order
     */
    public function setSortOrder($sort_order)
    {
        $this->sort_order = $sort_order;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param float $tax
     */
    public function setTax($tax)
    {
        $this->tax = empty($tax) ? 0 : floatval($tax);
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

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param Image $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param Status $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return Sku
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param Sku $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return ProductReturn
     */
    public function getProductReturn()
    {
        return $this->product_return;
    }

    /**
     * @param ProductReturn $product_return
     */
    public function setProductReturn($product_return)
    {
        $this->product_return = $product_return;
    }

    /**
     * Calculate the total of the line item
     */
    public function calculateTotal()
    {
        $this->total = number_format((($this->price + $this->tax) * $this->quantity), 2, '.', '');
    }

    /**
     * Converts a sku's attributes to the element
     * @param Sku $sku
     * @param int $qty
     */
    public function convertSkuToElement(Sku $sku, $qty)
    {
        $this->sku = $sku;
        $this->name = $sku->getProduct()->getName();

        $image = $sku->getImage();
        if (!empty($image))
        {
            $this->image = $sku->getImage();
        }
        else
        {
            $this->image = $sku->getProduct()->getDefaultImage();
        }

        $this->tax = $sku->getProduct()->getTax();
        $this->weight = $sku->getProduct()->getBaseWeight();

        $sku_number = $sku->getNumber();
        if (empty($sku_number))
            $this->number = $sku->getProduct()->getProductCode();
        else
            $this->number = $sku->getNumber();

        $this->status = $sku->getStatus();
        $this->quantity = $qty;

        $discount_price = $sku->getProduct()->getDiscountPrice();
        if (!empty($discount_price) && $discount_price > 0)
        {
            $this->price = $discount_price;
        }
        else
        {
            $this->price = $sku->getProduct()->getBasePrice();
        }

        // Calculate total
        $this->total = ($this->price * $this->quantity) + $this->tax;
    }
}