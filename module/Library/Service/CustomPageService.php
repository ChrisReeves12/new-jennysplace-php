<?php
/**
* The CustomPageService class definition.
*
* Has various services for updating and creating custom pages
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Page\CustomPage;
use Library\Model\Page\Page;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class CustomPageService
 * @package Library\Service
 */
class CustomPageService extends AbstractService
{
    /**
     * Saves or updates custom pages
     *
     * @param array $data
     * @param CustomPage $custom_page
     *
     * @return CustomPage
     * @throws \Exception
     */
    public function save($data, $custom_page)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Check the custom page for validity
        if (!($custom_page instanceof CustomPage))
        {
            $custom_page = new CustomPage();

            // Check if a page with the handle exists
            if (!is_null($em->getRepository('Library\Model\Page\Page')->findOneBy(['url_handle' => strtolower($data['url_handle'])])))
            {
                throw new \Exception("There is already a page that is using this URL Handle, '".strtolower($data['url_handle'])."'. Please use another URL Handle.");
            }
        }

        // Set custom page attributes
        $page = $custom_page->getPage();
        if (!($page instanceof Page))
        {
            $page = new Page();
        }

        $page->setTitle($data['title']);
        $page->setUrlHandle(strtolower($data['url_handle']));
        $page->setKeywords($data['meta_keywords']);
        $page->setDescription($data['meta_description']);
        $page->setInactive($data['inactive']);
        $page->setAccess(1);
        $page->setPageType('custom');

        $custom_page->setContent($data['content']);
        $custom_page->setPage($page);

        $em->persist($page);
        $em->persist($custom_page);

        return $custom_page;
    }
}