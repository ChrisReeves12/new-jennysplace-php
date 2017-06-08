<?php
/**
 * The PageService class definition.
 *
 * This class handles various tasks on pages
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/
namespace Library\Service;

use Library\Model\Page\Menu;
use Library\Model\Page\MenuItem;
use Library\Service\DB\EntityManagerSingleton;
/**
 * Class PageService
 * @package Library\Service
 */
class PageService extends AbstractService
{
    /**
     * Creates a url handle from the passed in name for page objects
     *
     * @param string $name
     * @return string
     */
    public function create_handle($name = null)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $name = strtolower(trim($name));
        $clean_handle = str_replace(' ', '-', htmlspecialchars_decode($name));
        $clean_handle = preg_replace('/[^0-9a-zA-Z\-]*/', '', $clean_handle);
        $clean_handle = str_replace('--', '-', $clean_handle);
        $clean_handle = str_replace('---', '-', $clean_handle);
        $handle_found = false;
        $count = 1;
        while (false === $handle_found)
        {
            $pages = $em->getRepository('Library\Model\Page\Page')->findBy(['url_handle' => $clean_handle]);
            if (count($pages) > 0)
            {
                $count++;
                $clean_handle .= '-' . $count;
            }
            else
            {
                $handle_found = true;
            }
        }
        return $clean_handle;
    }
    /**
     * Saves a menu to the database
     *
     * @param $data
     * @param $menu
     *
     * @return Menu
     */
    public function save_menu($data, $menu)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        // Update or save menu
        if (!($menu instanceof Menu))
        {
            $menu = new Menu();
        }
        $menu_info = $data['info'];
        $menu->setLabel(trim($menu_info['label']));
        $menu->setCssClass(trim($menu_info['css_class_name']));
        $menu->setInactive(trim($menu_info['inactive']));
        // Delete all of the children to replace them
        $old_menu_items = $menu->getMenuItems();
        if (count($old_menu_items) > 0)
        {
            foreach ($old_menu_items as $old_menu_item)
            {
                $em->remove($old_menu_item);
            }
        }
        $em->flush();
        // Add menu items
        if (count($menu_info['menu_items']) > 0)
        {
            $counter = count($menu_info['menu_items']);
            foreach ($menu_info['menu_items'] as $menu_item)
            {
                $menu_item_obj = new MenuItem();
                $menu_item_obj->setLabel(trim($menu_item['item_label']));
                $menu_item_obj->setCssClass(trim($menu_item['menu_item_css']));
                $menu_item_obj->setUrl(trim($menu_item['menu_item_url']));
                $menu_item_obj->setMenu($menu);
                $menu_item_obj->setSortOrder($counter);
                $em->persist($menu_item_obj);
                $counter--;
            }
        }
        $em->persist($menu);
        return $menu;
    }
}