<?php
/**
* The Menu class definition.
*
* Represents a menu that is populated by menu items
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Page;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class Menu
 * @package Library\Model\Page
 */

/**
 * @Entity
 * @Table(name="menus")
 * @HasLifecycleCallbacks
 */
class Menu extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="label", type="string", length=500, nullable=false)
     * @var string
     */
    protected $label;

    /**
     * @Column(name="inactive", type="boolean", nullable=false)
     * @var bool
     */
    protected $inactive;

    /**
     * @Column(name="css_class", type="string", length=500, nullable=true)
     * @var string
     */
    protected $css_class;

    /**
     * @OneToMany(targetEntity="MenuItem", mappedBy="menu", cascade={"persist", "remove"})
     * @var MenuItem[]
     */
    protected $menu_items;

    /**
     * @return MenuItem[]
     */
    public function getMenuItems()
    {
        return $this->menu_items;
    }

    /**
     * @param MenuItem[] $menu_items
     */
    public function setMenuItems($menu_items)
    {
        $this->menu_items = $menu_items;
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
    public function getCssClass()
    {
        return $this->css_class;
    }

    /**
     * @param string $css_class
     */
    public function setCssClass($css_class)
    {
        $this->css_class = $css_class;
    }
}