<?php
/**
* The ProductCategory class definition.
*
* This class represents relationships between products and categories
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Relationship;

use Library\Model\AbstractModel;
use Library\Model\Category\Category;
use Library\Model\Product\Product;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;

/**
 * Class ProductCategory
 * @package Library\Model\Relationship
 */

/**
 * @Entity
 * @Table(name="assoc_products_categories")
 * @HasLifecycleCallbacks
 */
class ProductCategory extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Product\Product", inversedBy="product_categories")
     * @JoinColumn(name="product_id", referencedColumnName="id")
     * @var Product
     */
    protected $product;

    /**
     * @ManyToOne(targetEntity="Library\Model\Category\Category", inversedBy="category_products")
     * @JoinColumn(name="category_id", referencedColumnName="id")
     * @var Category
     */
    protected $category;

    /**
     * @Column(name="sort_order", type="integer", nullable=true)
     * @var int
     */
    protected $sort_order;


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
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
}