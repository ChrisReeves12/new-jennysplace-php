<?php
/**
* The Video class definition.
*
* This class describes video media
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Media;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Relationship\ProductVideo;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class Video
 * @package Library\Model\Media
 */

/**
 * @Entity
 * @Table(name="videos")
 * @HasLifecycleCallbacks
 */
class Video extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="url", type="string", nullable=false)
     * @var string
     */
    protected $url;

    /**
     * @Column(name="type", type="string", nullable=false)
     * @var string
     */
    protected $type;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\ProductVideo", mappedBy="product", cascade={"remove", "persist"})
     * @var ProductVideo[]
     */
    protected $product_videos;

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * Gets the embed code of the video
     *
     * @param int $width
     * @param int $height
     *
     * @return string
     */
    public function getEmbedCode($width = 560, $height = 315)
    {
        $url = $this->url;
        $pieces = explode('=', $url);
        $query = end($pieces);

        if ($this->type == 'youtube')
        {
            return '<iframe width="'.$width.'" height="'.$height.'" src="https://www.youtube.com/embed/'.$query.'" frameborder="0" allowfullscreen></iframe>';
        }

        return '';
    }
}