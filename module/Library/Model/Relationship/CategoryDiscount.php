<?php
/**
* The CategoryDiscount class definition.
*
* This class represents relationships between categories and discounts
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Relationship;

use Library\Model\AbstractModel;
use Library\Model\Category\Category;
use Library\Model\Shop\Discount;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;

/**
 * Class CategoryDiscount
 * @package Library\Model\Relationship
 */

/**
 * @Entity
 * @Table(name="assoc_categories_discounts")
 * @HasLifecycleCallbacks
 */
class CategoryDiscount extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Category\Category", inversedBy="category_discounts")
     * @JoinColumn(name="category_id", referencedColumnName="id")
     * @var Category
     */
    protected $category;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\Discount", inversedBy="discount_categories")
     * @JoinColumn(name="discount_id", referencedColumnName="id")
     * @var Discount
     */
    protected $discount;

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