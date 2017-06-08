<?php
/**
* The Product class definition.
*
* This model represents products customers can purchase.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Product;

use Doctrine\ORM\Mapping\Index;
use Library\Model\AbstractModel;
use Library\Model\Media\Image;
use Library\Model\Page\Page;
use Library\Model\Relationship\ProductCategory;
use Library\Model\Relationship\ProductImage;
use Library\Model\Relationship\ProductVideo;
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
 * Class Product
 * @package Library\Model\Product
 */

/**
 * @Entity(repositoryClass="Library\Model\Repository\ProductRepository")
 * @Table(name="products", indexes={@Index(name="idx_product_code_search", columns={"product_code"}),
 * @Index(name="idx_product_stock_search", columns={"status_id"})})
 * @HasLifecycleCallbacks
 */
class Product extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="name", type="string", length=500, nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @Column(name="description", type="text", nullable=true)
     * @var string
     */
    protected $description;

    /**
     * @Column(name="keywords", type="text", nullable=true)
     * @var string
     */
    protected $keywords;

    /**
     * @Column(name="product_code", type="string", length=255, nullable=false)
     * @var string
     */
    protected $product_code;

    /**
     * @Column(name="show_more_caption", type="boolean", nullable=true)
     * @var boolean
     */
    protected $show_more_caption;

    /**
     * @Column(name="base_price", type="decimal", scale=2, nullable=false)
     * @var float
     */
    protected $base_price;

    /**
     * @Column(name="discount_price", type="decimal", scale=2, nullable=false)
     * @var float
     */
    protected $discount_price;

    /**
     * @Column(name="tax", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $tax;

    /**
     * @ManyToOne(targetEntity="Library\Model\Product\Status")
     * @JoinColumn(name="status_override_id", referencedColumnName="id")
     * @var Status
     */
    protected $status_override;

    /**
     * @ManyToOne(targetEntity="Library\Model\Product\Status")
     * @JoinColumn(name="status_id", referencedColumnName="id", nullable=false)
     * @var Status
     */
    protected $status;

    /**
     * @Column(name="base_weight", type="decimal", scale=2, nullable=false)
     * @var float
     */
    protected $base_weight;

    /**
     * @OneToOne(targetEntity="Library\Model\Media\Image", cascade={"remove", "persist"})
     * @JoinColumn(name="default_image_id", referencedColumnName="id")
     * @var Image
     */
    protected $default_image;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\ProductCategory", mappedBy="product", cascade={"remove"})
     * @var ProductCategory[]
     */
    protected $product_categories;

    /**
     * @OneToMany(targetEntity="Library\Model\Product\Sku", mappedBy="product", cascade={"remove"})
     * @var Sku[]
     */
    protected $skus;

    /**
     * @OneToOne(targetEntity="Library\Model\Page\Page", cascade={"remove", "persist"})
     * @JoinColumn(name="page_id", referencedColumnName="id")
     * @var Page
     */
    protected $page;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\ProductImage", mappedBy="product", cascade={"remove", "persist"})
     * @var ProductImage[]
     */
    protected $product_images;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\ProductVideo", mappedBy="video", cascade={"remove", "persist"})
     * @var ProductVideo[]
     */
    protected $product_videos;

    /**
     * @Column(name="sort_order", type="integer", nullable=true)
     * @var int
     */
    protected $sort_order;

    /**
     * @Column(name="important", type="boolean", nullable=true)
     * @var bool
     */
    protected $important;

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status ?? $this->getStatusOverride();
    }

    /**
     * @return boolean
     */
    public function shouldShowMoreCaption()
    {
        return $this->show_more_caption ?? false;
    }

    /**
     * @param boolean $show_more_caption
     */
    public function setShowMoreCaption($show_more_caption)
    {
        $this->show_more_caption = $show_more_caption ?? false;
    }

    /**
     * @param Status $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
        $this->sort_order = empty($sort_order) ? null : $sort_order;
    }

    /**
     * @return Image[]
     */
    public function getProductImages()
    {
        return $this->product_images;
    }

    /**
     * @return ProductVideo[]
     */
    public function getProductVideos()
    {
        return $this->product_videos;
    }

    /**
     * @param ProductVideo[] $product_videos
     */
    public function setProductVideos($product_videos)
    {
        $this->product_videos = $product_videos;
    }

    /**
     * @param ProductImage[] $product_images
     */
    public function setProductImages($product_images)
    {
        $this->product_images = $product_images;
    }

    /**
     * @return float
     */
    public function getDiscountPrice()
    {
        return $this->discount_price;
    }

    /**
     * @param float $discount_price
     */
    public function setDiscountPrice($discount_price)
    {
        $this->discount_price = empty($discount_price) ? 0 : floatval($discount_price);
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return Sku[]
     */
    public function getSkus()
    {
        return $this->skus;
    }

    /**
     * @param Sku[] $skus
     */
    public function setSkus($skus)
    {
        $this->skus = $skus;
    }

    /**
     * @return ProductCategory[]
     */
    public function getProductCategories()
    {
        return $this->product_categories;
    }

    /**
     * @return Status
     */
    public function getStatusOverride()
    {
        return $this->status_override;
    }

    /**
     * @param Status $status_override
     */
    public function setStatusOverride($status_override)
    {
        $this->status_override = $status_override;
    }

    /**
     * @param ProductCategory[] $product_categories
     */
    public function setProductCategories($product_categories)
    {
        $this->product_categories = $product_categories;
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
     * @return boolean
     */
    public function isImportant()
    {
        return $this->important;
    }

    /**
     * @param boolean $important
     */
    public function setImportant($important)
    {
        $this->important = $important;
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return string
     */
    public function getProductCode()
    {
        return $this->product_code;
    }

    /**
     * @param string $product_code
     */
    public function setProductCode($product_code)
    {
        $this->product_code = $product_code;
    }

    /**
     * @return float
     */
    public function getBasePrice()
    {
        return $this->base_price;
    }

    /**
     * @param float $base_price
     */
    public function setBasePrice($base_price)
    {
        $this->base_price = floatval($base_price);
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
        $this->tax = empty($tax) ? 0.00 : floatval($tax);
    }

    /**
     * @return float
     */
    public function getBaseWeight()
    {
        return $this->base_weight;
    }

    /**
     * @param float $base_weight
     */
    public function setBaseWeight($base_weight)
    {
        $this->base_weight = $base_weight ?? 0;
    }

    /**
     * @return Image
     */
    public function getDefaultImage()
    {
        return $this->default_image;
    }

    /**
     * @param Image $default_image
     */
    public function setDefaultImage($default_image)
    {
        $this->default_image = $default_image;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Add skus to product
     * @param Sku[] $skus
     */
    public function addSkus($skus)
    {
        foreach ($skus as &$sku)
        {
            if ($sku instanceof Sku)
            {
                $sku->setProduct($this);
            }
        }
    }

    /**
     * Get default sku of product
     * @return Sku
     */
    public function getDefaultSku()
    {
        if (count($this->skus) > 0)
        {
            foreach($this->skus as $sku)
            {
                if ($sku->getIsDefault())
                {
                    return $sku;
                }
            }
        }

        return null;
    }

    /**
     * Gets the total amount on hand of all the skus under the product
     * @return int
     */
    public function getQuantityFromSkus()
    {
        // Get the quantity from the skus
        $quantity = 0;
        $skus = $this->getSkus();
        if (count($skus) > 0)
        {
            foreach($skus as $sku)
            {
                $quantity += $sku->getQuantity();
            }
        }

        return $quantity;
    }

    /**
     * Add additional product images
     *
     * @param Image $image
     * @param int $sort_order
     */
    public function addAdditionalImage(Image $image, $sort_order = null)
    {
        $product_image = new ProductImage();
        $product_image->setImage($image);
        $product_image->setProduct($this);
        $product_image->setSortOrder($sort_order);
        $this->product_images[] = $product_image;
    }
}