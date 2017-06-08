<?php
/**
* The MenuItem class definition.
*
* Represents a link in a menu
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Page;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Library\Model\Traits\StandardModelTrait;
use Library\Model\AbstractModel;
/**
 * Class MenuItem
 * @package Library\Model\Page
 */

/**
 * @Entity
 * @Table(name="menu_items")
 * @HasLifecycleCallbacks
 */
class MenuItem extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="label", type="string", length=500, nullable=false)
     */
    protected $label;

    /**
     * @Column(name="url", type="string", length=500, nullable=false)
     */
    protected $url;

    /**
     * @Column(name="css_class", type="string", length=500, nullable=true)
     */
    protected $css_class;

    /**
     * @Column(name="sort_order", type="integer", nullable=true)
     */
    protected $sort_order;

    /**
     * @ManyToOne(targetEntity="Library\Model\Page\Menu", inversedBy="menu_items")
     * @JoinColumn(name="menu_id", referencedColumnName="id")
     */
    protected $menu;

    /**
     * @return mixed
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param mixed $menu
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getCssClass()
    {
        return $this->css_class;
    }

    /**
     * @param mixed $css_class
     */
    public function setCssClass($css_class)
    {
        $this->css_class = $css_class;
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @param mixed $sort_order
     */
    public function setSortOrder($sort_order)
    {
        $this->sort_order = $sort_order;
    }

}