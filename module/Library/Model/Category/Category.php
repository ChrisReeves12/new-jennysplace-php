<?php
/**
 * The Category class definition.
 *
 * This model represents categories that contain products.
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Model\Category;

use Library\Model\AbstractModel;
use Library\Model\Media\Image;
use Library\Model\Page\Page;
use Library\Model\Product\Product;
use Library\Model\Relationship\CategoryDiscount;
use Library\Model\Relationship\ProductCategory;
use Library\Model\Shop\ShopList\QueryList;
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
 * Class Category
 * @package Library\Model\Category
 */

/**
 * @Entity(repositoryClass="Library\Model\Repository\CategoryRepository")
 * @Table(name="categories")
 * @HasLifecycleCallbacks
 */
class Category extends AbstractModel
{
    use StandardModelTrait;

    const THEME_CATEGORY_ID = 135;

    /**
     * @Column(name="name", type="string", length=500, nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @Column(name="inactive", type="boolean", nullable=false)
     * @var bool
     */
    protected $inactive;

    /**
     * @ManyToOne(targetEntity="Library\Model\Category\Category", inversedBy="child_categories")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     * @var Category
     */
    protected $parent_category;

    /**
     * @OneToMany(targetEntity="Library\Model\Category\Category", mappedBy="parent_category", cascade={"persist", "remove"})
     * @var Category[]
     */
    protected $child_categories;

    /**
     * @OneToOne(targetEntity="Library\Model\Page\Page", cascade={"remove", "persist"})
     * @JoinColumn(name="page_id", referencedColumnName="id")
     * @var Page
     */
    protected $page;

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
     * @OneToOne(targetEntity="Library\Model\Media\Image", cascade={"remove", "persist"})
     * @JoinColumn(name="default_image", referencedColumnName="id")
     * @var Image
     */
    protected $default_image;

    /**
     * @Column(name="delete_later", type="boolean", nullable=true)
     * @var boolean
     */
    protected $delete_later;

    /**
     * @Column(name="sort_order", type="integer", nullable=true)
     * @var int
     */
    protected $sort_order;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\ProductCategory", mappedBy="category", cascade={"persist"})
     * @var ProductCategory[]
     */
    protected $category_products;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\CategoryDiscount", mappedBy="category", cascade={"persist", "remove"})
     * @var CategoryDiscount[]
     */
    protected $category_discounts;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\ShopList\QueryList")
     * @JoinColumn(name="query_list_id", referencedColumnName="id")
     * @var QueryList
     */
    protected $query_list;

    /**
     * @ManyToOne(targetEntity="Library\Model\Category\Category")
     * @JoinColumn(name="points_to_category_id", referencedColumnName="id")
     * @var Category
     */
    protected $points_to;

    /**
     * @return Category
     */
    public function getPointsTo()
    {
        return $this->points_to;
    }

    /**
     * @param Category $points_to
     */
    public function setPointsTo($points_to)
    {
        $this->points_to = $points_to;
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
     * @return Category
     */
    public function getParentCategory()
    {
        return $this->parent_category;
    }

    /**
     * @param Category $parent_category
     */
    public function setParentCategory($parent_category)
    {
        $this->parent_category = $parent_category;
    }

    /**
     * @return Category[]
     */
    public function getChildCategories()
    {
        return $this->child_categories;
    }

    /**
     * @param Category[] $child_categories
     */
    public function setChildCategories($child_categories)
    {
        $this->child_categories = $child_categories;
    }

    /**
     * @return boolean
     */
    public function isDeleteLater()
    {
        return $this->delete_later;
    }

    /**
     * @param boolean $delete_later
     */
    public function setDeleteLater($delete_later)
    {
        $this->delete_later = $delete_later;
    }

    /**
     * Add a product to the category
     *
     * @param Product $product
     * @param int $sort_order
     */
    public function addProduct(Product $product, $sort_order = 0)
    {
        $product_category = new ProductCategory();
        $product_category->setProduct($product);
        $product_category->setCategory($this);
        $product_category->setSortOrder($sort_order);

        // Add it to array
        $this->category_products[] = $product_category;
    }

    /**
     * @return CategoryDiscount[]
     */
    public function getCategoryDiscounts()
    {
        return $this->category_discounts;
    }

    /**
     * @param CategoryDiscount[] $category_discounts
     */
    public function setCategoryDiscounts($category_discounts)
    {
        $this->category_discounts = $category_discounts;
    }

    /**
     * @return ProductCategory[]
     */
    public function getCategoryProducts()
    {
        return $this->category_products;
    }

    /**
     * @param ProductCategory[] $category_products
     */
    public function setCategoryProducts($category_products)
    {
        $this->category_products = $category_products;
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
        $this->sort_order = empty($sort_order) ? 0 : intval($sort_order);
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
     * @return QueryList
     */
    public function getQueryList()
    {
        return $this->query_list;
    }

    /**
     * @param QueryList $query_list
     */
    public function setQueryList($query_list)
    {
        $this->query_list = $query_list;
    }
}