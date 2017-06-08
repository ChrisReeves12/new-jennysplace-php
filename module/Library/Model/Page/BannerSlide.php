<?php
/**
* The BannerSlide class definition.
*
* Represents a slide of a static or animating banner
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Page;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Media\Image;
use Library\Model\Traits\StandardModelTrait;


/**
 * @Entity
 * @Table(name="banner_slides")
 */
class BannerSlide extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Media\Image")
     * @JoinColumn(name="image_id", referencedColumnName="id")
     * @var Image
     */
    protected $image;

    /**
     * @ManyToOne(targetEntity="Library\Model\Page\Banner", inversedBy="banner_slides")
     * @JoinColumn(name="banner_id", referencedColumnName="id")
     * @var Banner
     */
    protected $banner;

    /**
     * @Column(name="url", type="string", length=500, nullable=true)
     * @var string
     */
    protected $url;

    /**
     * @Column(name="sort_order", type="integer", nullable=true)
     * @var int
     */
    protected $sort_order;

    /**
     * @Column(name="open_new_window", type="boolean", nullable=true)
     * @var bool
     */
    protected $open_new_window;

    /**
     * @return string
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
    public function getOpenNewWindow()
    {
        return $this->open_new_window;
    }

    /**
     * @param bool $open_new_window
     */
    public function setOpenNewWindow($open_new_window)
    {
        $this->open_new_window = $open_new_window;
    }

    /**
     * @return Banner
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * @param Banner $banner
     */
    public function setBanner($banner)
    {
        $this->banner = $banner;
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
}