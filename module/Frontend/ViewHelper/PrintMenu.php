<?php
/**
* The PrintMenu class definition.
*
* This view helper prints a menu and its elements
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\ViewHelper;

use Library\Model\Page\Menu;
use Zend\Form\View\Helper\AbstractHelper;

/**
 * Class PrintMenu
 * @package Frontend\ViewHelper
 */
class PrintMenu extends AbstractHelper
{
    public function __invoke($menu)
    {
        if ($menu instanceof Menu && $menu->getInactive() == false)
        {
            ob_start();

            // Load the menu links
            $menu_items = $menu->getMenuItems();
            if (count($menu_items) > 0)
            {
                echo "<ul ".(!empty($menu->getCssClass()) ? "class='".$menu->getCssClass()."'" : '').">";
                foreach ($menu_items as $menu_item)
                {
                    echo "<li ".(!empty($menu_item->getCssClass()) ? "class='".$menu_item->getCssClass()."'" : '')."><a href='".$menu_item->getUrl()."'>".$menu_item->getLabel()."</a></li>";
                }
                echo "</ul>";
            }
        }

        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}