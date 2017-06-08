<?php
/**
 * The CategoryService class definition.
 *
 * This service handles all of the updating, saving and deleting of categories
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Service;

use Library\Model\Category\Category;
use Library\Model\Media\Image;
use Library\Model\Page\Page;
use Library\Model\Shop\ShopList\QueryList;

/**
 * Class CategoryService
 * @package Library\Service
 */
class CategoryService extends AbstractService
{
    /**
     * Saves or updates category
     *
     * @param array $data
     * @param int $category_id
     *
     * @return Category
     * @throws \Exception
     */
    public function save($data, $category_id = null)
    {
        $em = $this->getServiceManager()->get('entity_manager');
        $page_service = $this->getServiceManager()->get('page');

        // Check to see if data is present
        if (!empty($data))
        {
            // If no category ID was given, create a new category
            if (is_null($category_id))
            {
                $category = new Category();
            }
            else
            {
                // Attempt to locate the category we need to edit
                $category = $em->getRepository('Library\Model\Category\Category')->findOneById($category_id);
                if (is_null($category))
                {
                    throw new \Exception("The category being edited no longer exists in the database.");
                }
            }

            // Setup page for the category
            $page = is_null($category_id) ? new Page() : $category->getPage();
            if (!($page instanceof Page))
            {
                $page = new Page();
            }

            $page->setTitle($data['category_name']);
            $page->setPageType('category');
            $page->setDescription($data['meta_description']);
            $page->setKeywords($data['keywords']);
            $page->setInactive($data['inactive']);
            $page->setAccess(1);

            // Create a handle if one hasn't been made
            $url_handle = $page->getUrlHandle();
            if (empty($url_handle))
            {
                $page->setUrlHandle($page_service->create_handle($data['category_name']));
            }

            $em->persist($page);

            // Setup the image for the category
            if ($data['image']['size'] > 0)
            {
                $image_service = $this->getServiceManager()->get('image');
                $image = new Image();
                $image->setTitle($data['category_name']);
                $image->setAlt($data['category_name']);
                $image->setInactive(false);
                $image->setUrl($image_service->getFileNameFromTempName($data['image']['tmp_name']));
                $em->persist($image);
            }

            // Check for parent category
            if ($data['parent'] > 0)
            {
                $parent_category = $em->getReference('Library\Model\Category\Category', $data['parent']);
            }
            else
            {
                $parent_category = null;
            }

            if ($parent_category == $category)
            {
                throw new \Exception("A category cannot be a parent of itself.");
            }

            // Check for points to category
            if ($data['points_to'] > 0)
            {
                $points_to_category = $em->getReference('Library\Model\Category\Category', $data['points_to']);
            }
            else
            {
                $points_to_category = null;
            }

            if ($points_to_category == $category)
            {
                throw new \Exception("A category cannot point to itself.");
            }

            // Update parameters of the category
            $category->setInactive($data['inactive']);
            if ($data['query_list'] > 0)
            {
                $query_list = $em->getRepository('Library\Model\Shop\ShopList\QueryList')->findOneById($data['query_list']);
                if (!($query_list instanceof QueryList))
                {
                    throw new \Exception("The query list for dynamic product loading in category cannot be found in database.");
                }

                $category->setQueryList($query_list);
            }
            else
            {
                $category->setQueryList(null);
            }

            $category->setDescription($data['description']);
            $category->setName($data['category_name']);
            $category->setKeywords($data['keywords']);
            $category->setPage($page);
            $category->setSortOrder($data['sort_order']);
            $category->setParentCategory($parent_category);
            $category->setPointsTo($points_to_category);

            if (isset($image))
            {
                $category->setDefaultImage($image);
            }
        }
        else
        {
            // No data given
            throw new \Exception("No categories were created.");
        }

        // Save the category
        $em->persist($category);
        return $category;
    }
}