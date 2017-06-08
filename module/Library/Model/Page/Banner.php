<?php
/**
* The Banner class definition.
*
* This class represents static and animating banners
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Page;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;

/**
 * @Entity
 * @Table(name="banners")
 */
class Banner extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="label", type="string", length=500, nullable=false)
     * @var string
     */
    protected $label;

    /**
     * @Column(name="animation_speed", type="integer", nullable=true)
     * @var int
     */
    protected $animation_speed;

    /**
     * @Column(name="delay_time", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $delay_time;

    /**
     * @Column(name="animation_type", type="string", length=500, nullable=true)
     * @var string
     */
    protected $animation_type;

    /**
     * @OneToMany(targetEntity="Library\Model\Page\BannerSlide", mappedBy="banner", cascade={"remove", "persist"})
     * @var BannerSlide[]
     */
    protected $banner_slides;

    /**
     * @Column(name="show_navigation", type="boolean", nullable=true)
     * @var bool
     */
    protected $show_navigation;

    /**
     * @Column(name="slide_direction", type="string", nullable=true)
     * @var string
     */
    protected $slide_direction;

    /**
     * @Column(name="show_arrows", type="boolean",nullable=true)
     * @var bool
     */
    protected $show_arrows;

    /**
     * @return bool
     */
    public function getShowNavigation()
    {
        return $this->show_navigation;
    }

    /**
     * @param bool $show_navigation
     */
    public function setShowNavigation($show_navigation)
    {
        $this->show_navigation = $show_navigation;
    }

    /**
     * @return string
     */
    public function getSlideDirection()
    {
        return $this->slide_direction;
    }

    /**
     * @param string $slide_direction
     */
    public function setSlideDirection($slide_direction)
    {
        $this->slide_direction = $slide_direction;
    }

    /**
     * @return bool
     */
    public function getShowArrows()
    {
        return $this->show_arrows;
    }

    /**
     * @param bool $show_arrows
     */
    public function setShowArrows($show_arrows)
    {
        $this->show_arrows = $show_arrows;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getAnimationSpeed()
    {
        return $this->animation_speed;
    }

    /**
     * @param int $animation_speed
     */
    public function setAnimationSpeed($animation_speed)
    {
        $this->animation_speed = $animation_speed;
    }

    /**
     * @return float
     */
    public function getDelayTime()
    {
        return $this->delay_time;
    }

    /**
     * @param float $delay_time
     */
    public function setDelayTime($delay_time)
    {
        $this->delay_time = $delay_time;
    }

    /**
     * @return string
     */
    public function getAnimationType()
    {
        return $this->animation_type;
    }

    /**
     * @param string $animation_type
     */
    public function setAnimationType($animation_type)
    {
        $this->animation_type = $animation_type;
    }

    /**
     * @return BannerSlide[]
     */
    public function getBannerSlides()
    {
        return $this->banner_slides;
    }

    /**
     * @param BannerSlide[] $banner_slides
     */
    public function setBannerSlides($banner_slides)
    {
        $this->banner_slides = $banner_slides;
    }
}