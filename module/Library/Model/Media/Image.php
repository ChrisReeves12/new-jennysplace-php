<?php
/**
* The Image class definition.
*
* This class represents images.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Media;

use Doctrine\ORM\Mapping\OneToMany;
use Library\Model\AbstractModel;
use Library\Model\Relationship\ProductImage;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;

/**
 * Class Image
 * @package Library\Model\Media
 */

/**
 * @Entity
 * @Table(name="images")
 * @HasLifecycleCallbacks
 */
class Image extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="url", type="string", length=500, nullable=false)
     * @var string
     */
    protected $url;

    /**
     * @Column(name="inactive", type="boolean", nullable=false)
     * @var bool
     */
    protected $inactive;

    /**
     * @Column(name="alt_tag", type="string", length=500, nullable=true)
     * @var string
     */
    protected $alt;

    /**
     * @Column(name="title", type="string", length=500, nullable=true)
     * @var string
     */
    protected $title;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\ProductImage", mappedBy="image", cascade={"remove", "persist"})
     * @var ProductImage[]
     */
    protected $image_products;

    /**
     * @return ProductImage[]
     */
    public function getImageProducts()
    {
        return $this->image_products;
    }

    /**
     * @param ProductImage[] $image_products
     */
    public function setImageProducts($image_products)
    {
        $this->image_products = $image_products;
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
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
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * @param string $alt
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}