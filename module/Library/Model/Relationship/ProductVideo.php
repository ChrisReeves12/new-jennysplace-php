<?php
/**
* The ProductVideo class definition.
*
* This class represents the relationship between Videos and products.
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
use Library\Model\Media\Video;
use Library\Model\Product\Product;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class ProductVideo
 * @package Library\Model\Relationship
 */

/**
 * @Entity
 * @Table(name="assoc_products_video")
 * @HasLifecycleCallbacks
 */
class ProductVideo extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Product\Product", inversedBy="product_videos")
     * @JoinColumn(name="product_id", referencedColumnName="id")
     * @var Product
     */
    protected $product;

    /**
     * @ManyToOne(targetEntity="Library\Model\Media\Video", inversedBy="video_products")
     * @JoinColumn(name="video_id", referencedColumnName="id")
     * @var Video
     */
    protected $video;

    /**
     * @Column(name="sort_order", type="string", length=500, nullable=true)
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
        $this->sort_order = $sort_order;
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
     * @return Video
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @param Video $video
     */
    public function setVideo($video)
    {
        $this->video = $video;
    }
}