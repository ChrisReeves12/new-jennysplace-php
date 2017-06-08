<?php
/**
* The Sku class definition.
*
* Skus represent individual items under products, such as different sizes and colors of the product.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Product;

use Doctrine\ORM\Mapping\Index;
use Library\Model\AbstractModel;
use Library\Model\Media\Image;
use Library\Model\Relationship\OptionOptionValue;
use Library\Model\Relationship\SkuOptionOptionValue;
use Library\Model\Shop\ShopListElement;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * Class Sku
 * @package Library\Model\Product
 */

/**
 * @Entity(repositoryClass="Library\Model\Repository\SkuRepository")
 * @Table(name="skus", indexes={@Index(name="idx_sku_number_search", columns={"number"})})
 * @HasLifecycleCallbacks
 */
class Sku extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Product\Product", inversedBy="skus", cascade={"persist"})
     * @JoinColumn(name="product_id", referencedColumnName="id")
     * @var Product
     */
    protected $product;

    /**
     * @Column(name="number", type="string", length=255, nullable=true)
     * @var string
     */
    protected $number;

    /**
     * @OneToMany(targetEntity="Library\Model\Shop\ShopListElement", mappedBy="sku", cascade={"remove"})
     * @var ShopListElement[]
     */
    protected $shop_list_elements;

    /**
     * @Column(name="quantity", type="integer", nullable=false)
     * @var int
     */
    protected $quantity;

    /**
     * @Column(name="is_default", type="boolean", nullable=false)
     * @var bool
     */
    protected $is_default;

    /**
     * @ManyToOne(targetEntity="Library\Model\Product\Status")
     * @JoinColumn(name="status", referencedColumnName="id")
     * @var Status
     */
    protected $status;

    /**
     * @OneToOne(targetEntity="Library\Model\Media\Image", cascade={"remove", "persist"})
     * @JoinColumn(name="image_id", referencedColumnName="id")
     * @var Image
     */
    protected $image;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\SkuOptionOptionValue", mappedBy="sku", cascade={"remove", "persist"})
     * @var SkuOptionOptionValue[]
     */
    protected $sku_option_option_values;

    /**
     * @return SkuOptionOptionValue[]
     */
    public function getSkuOptionOptionValues()
    {
        return $this->sku_option_option_values;
    }

    /**
     * @param SkuOptionOptionValue[] $sku_option_option_values
     */
    public function setSkuOptionOptionValues($sku_option_option_values)
    {
        $this->sku_option_option_values = $sku_option_option_values;
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
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->is_default;
    }

    /**
     * @param bool $is_default
     */
    public function setIsDefault($is_default)
    {
        $this->is_default = $is_default;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        $status = ($this->getIsDefault()) ? ($this->getProduct()->getStatus() ?? $this->getProduct()->getStatusOverride()) : $this->status;
        return $status;
    }

    /**
     * Checks for status override on product first for status
     * @return Status
     */
    public function getRealStatus()
    {
        // Check for override
        $status = $this->product->getStatusOverride();
        if ($status instanceof Status)
        {
            return $status;
        }

        return $this->status ?? $this->getProduct()->getStatus();
    }

    /**
     * @param Status $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return ShopListElement[]
     */
    public function getShopListElements()
    {
        return $this->shop_list_elements;
    }

    /**
     * @param ShopListElement[] $shop_list_elements
     */
    public function setShopListElements($shop_list_elements)
    {
        $this->shop_list_elements = $shop_list_elements;
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
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @param OptionOptionValue $option_value_pair
     */
    public function addOptionValuePair(OptionOptionValue $option_value_pair)
    {
        $sku_option_option_value = new SkuOptionOptionValue();
        $sku_option_option_value->setSku($this);
        $sku_option_option_value->setOptionOptionValue($option_value_pair);
        $this->sku_option_option_values[] = $sku_option_option_value;
    }
}