<?php
/**
 * The BulkEditController class definition.
 *
 * General controller used for modifying multiple items
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Backend\Controller;

use Doctrine\Common\Collections\Criteria;
use Library\Controller\JPController;
use Library\Model\Category\Category;
use Library\Model\Media\Image;
use Library\Service\DB\EntityManagerSingleton;
use Library\Model\Product\Product;
use Library\Service\Settings;
use Zend\View\Model\JsonModel;

/**
 * Class BulkEditController
 * @package Backend\Controller
 */
class BulkEditController extends JPController
{
    const MAX_RESULTS_SHOWN = 100;

    public function productsAction()
    {
        // Handle various Ajax requests
        $response = $this->_handle_ajax_requests();
        if (!empty($response))
        {
            return new JsonModel($response);
        }

        // Get categories
        $em = EntityManagerSingleton::getInstance();

        $product_status_info[] = ['id' => 0, 'name' => 'None'];
        $category_listings = $em->getRepository('Library\Model\Category\Category')->findAllWithHierarchy();


        foreach($category_listings as $listing)
        {
            // Construct listing name
            $listing_name = "";
            if (count($listing['ancestors']) > 0)
            {
                foreach ($listing['ancestors'] as $ancestor)
                {
                    $listing_name .= $ancestor['name'] . " >> ";
                }
            }

            $listing_name .= $listing['name'];
            $category_options[] = ['id' => $listing['id'], 'name' => $listing_name];
        }

        // Sort categories
        if (isset($category_options))
        {
            usort($category_options, function ($a, $b)
            {
                return strcmp($a['name'], $b['name']);
            });
        }

        // Get product statuses
        $product_statuses = $em->getRepository('Library\Model\Product\Status')->findAll();
        foreach ($product_statuses as $product_status)
        {
            $product_status_info[] = ['id' => $product_status->getId(), 'name' => $product_status->getName()];
        }

        // Get main categories for dialog
        $main_categories = $em->getRepository('Library\Model\Category\Category')->findBy(['parent_category' => null]);
        $main_category_array = [];
        $sub_categories_array = [];
        $main_category_dictionary = [];
        $sub_category_dictionary = [];

        /** @var Category $main_category */
        foreach ($main_categories as $main_category)
        {
            $main_category_dictionary[$main_category->getId()] = $main_category->getName();
            $main_category_array[] = [$main_category->getId(), $main_category->getName()];

            /** @var Category[] $sub_categories */
            $sub_categories = $em->getRepository('Library\Model\Category\Category')->findBy(['parent_category' => $main_category]);

            if (!isset($sub_categories_array[$main_category->getId()]))
                $sub_categories_array[$main_category->getId()] = [];

            // Add subcategories to table
            if (count($sub_categories) > 0)
            {
                /** @var Category $sub_category */
                foreach ($sub_categories as $sub_category)
                {
                    if (!isset($sub_category_dictionary[$main_category->getId()][$sub_category->getId()]))
                        $sub_category_dictionary[$main_category->getId()][$sub_category->getId()] = [];

                    $sub_category_dictionary[$main_category->getId()][$sub_category->getId()] = $sub_category->getName();

                    $sub_categories_array[$main_category->getId()][] = [
                        $sub_category->getId(),
                        $sub_category->getName()
                    ];
                }
            }
        }

        // Get data to send to view
        $json_data = json_encode([
            'category_map' => $category_map ?? null,
            'main_category_dictionary' => $main_category_dictionary,
            'sub_category_dictionary' => $sub_category_dictionary,
            'main_categories' => $main_category_array,
            'sub_category_data' => $sub_categories_array,
            'categories' => $category_options ?? null,
            'product_statuses' => $product_status_info,
            'headers' => ['', 'Important' ,'Id', 'Image', 'Product Code', 'Name', 'Base Price', 'Status Override', 'Date Added'],
            'rows' => $this->_getProductData()
        ]);

        return compact(['json_data']);
    }

    /**
     * Handles various ajax requests
     * @return array
     */
    private function _handle_ajax_requests()
    {
        $product_service = $this->getServiceLocator()->get('product');

        if (!empty($_GET))
        {
            $task = $_GET['task'];
            unset($_GET['task']);

            switch ($task)
            {
                case 'view_more':

                    $page = $_GET['page'] + 1;
                    $category = $_GET['category'];
                    $filter = $_GET['filter'];

                    $rows_info = $this->_getProductData($category, $page, $filter);

                    return ['error' => false, 'page' => $page, 'rows' => $rows_info];
                    break;

                case 'refresh':

                    $category = $_GET['category'];
                    $filter = $_GET['filter'];

                    $rows_info = $this->_getProductData($category, 0, $filter);

                    return ['error' => false, 'rows' => $rows_info];
                    break;
            }
        }

        if (!empty($_POST))
        {
            $task = $_POST['task'];
            unset($_POST['task']);

            switch ($task)
            {
                case 'delete':

                    // Delete the products
                    if (!empty($_POST['ids']))
                    {
                        $product_service->deleteByIds($_POST['ids'], new Product());
                    }

                    EntityManagerSingleton::getInstance()->flush();

                    return ['error' => false, 'ids' => $_POST['ids']];
                    break;

                case 'update_sort_order':

                    // Update sort order
                    $product_ids = $_POST['product_ids'];
                    $category = $_POST['category'];

                    // We must have a category selected to sort
                    if ($category == 0)
                        break;

                    $em = EntityManagerSingleton::getInstance();

                    // First get how many relationships we have with this category
                    $qb = $em->createQueryBuilder();
                    $qb->select($qb->expr()->count('pc'))->from('Library\Model\Relationship\ProductCategory', 'pc');
                    $qb->where($qb->expr()->eq('pc.category', $category));
                    $count = $qb->getQuery()->getSingleScalarResult();

                    // Set sort order from highest count and count down from there
                    foreach ($product_ids as $product_id)
                    {
                        $product_rel = $em->getRepository('Library\Model\Relationship\ProductCategory')->findOneBy(['product' => $product_id, 'category' => $category]);
                        $product_rel->setSortOrder($count);
                        $em->flush();
                        $em->clear();
                        $count--;
                    }

                    return ['error' => false];
                    break;

                case 'update_important':

                    // Update the products important status
                    $product_service->updateImportantByIds($_POST['ids_to_make_important'], $_POST['ids_to_make_normal']);

                    EntityManagerSingleton::getInstance()->flush();

                    return ['error' => false];
                    break;

                case 'update_categories':

                    // Update categories for all products selected
                    $product_service->updateCategoriesByIds($_POST['product_ids'], $_POST['category_ids'], $_POST['save_method']);

                    EntityManagerSingleton::getInstance()->flush();

                    return ['error' => false];
                    break;

                case 'status_change':

                    // Update product statuses
                    if (!empty($_POST['ids']))
                    {
                        $product_status = $product_service->updateStatusesByIds($_POST['ids'], $_POST['status']);
                    }

                     EntityManagerSingleton::getInstance()->flush();

                    return ['error' => false, 'status_name' => isset($product_status) ? $product_status->getName() : "None"];
                    break;

                case 'date_change':

                    // Update product statuses
                    if (!empty($_POST['ids']))
                    {
                        $product_service->updateDateAddedByIds($_POST['ids'], $_POST['date']);
                    }

                    EntityManagerSingleton::getInstance()->flush();

                    return ['error' => false];
                    break;
            }
        }

        return null;
    }

    /**
     * @param int $category
     * @param int $first_page
     *
     * @param string $filter
     *
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    private function _getProductData($category = null, $first_page = 0, $filter = 'all')
    {
        $em = EntityManagerSingleton::getInstance();

        // Create dictionary to map filters to status
        $status_dictionary = [
            'in_stock' => 1,
            'out_of_stock' => 2,
            'disabled' => 3
        ];

        $criteria = new Criteria();
        $criteria->setMaxResults(self::MAX_RESULTS_SHOWN)->setFirstResult($first_page * self::MAX_RESULTS_SHOWN)->orderBy(['sort_order' => 'DESC']);

        // All categories
        if (is_null($category) || $category == 0)
        {
            // Add filter
            if ($filter != 'all')
                $criteria->where($criteria->expr()->eq('status', $em->getReference('Library\Model\Product\Status', $status_dictionary[$filter])));

            $results = $em->getRepository('Library\Model\Product\Product')->matching($criteria);
        }

        // Specific category
        else
        {
            if ($filter != 'all')
                $results = $em->getRepository('Library\Model\Product\Product')->findByCategoryAndStatus($em->getReference('Library\Model\Category\Category',
                    $category), $em->getReference('Library\Model\Product\Status', $status_dictionary[$filter]), $first_page * self::MAX_RESULTS_SHOWN,
                    self::MAX_RESULTS_SHOWN);
            else
            {
                $criteria->where($criteria->expr()->eq('category', $em->getReference('Library\Model\Category\Category', $category)));
                $results = $em->getRepository('Library\Model\Relationship\ProductCategory')->matching($criteria);
            }
        }

        $rows = [];
        $image_path = Settings::get('image_path');
        foreach ($results as $result)
        {
            $product = ($result instanceof Product) ? $result : $result->getProduct();
            if ($product->getDefaultImage() instanceof Image)
            {
                $image = $image_path . '/product_images/' . $product->getDefaultImage()->getUrl();
            }
            else
            {
                $image = '/img/layout_images/no_photo.jpg';
            }

            $status = is_null($product->getStatus()) ? "None" : $product->getStatus()->getName();

            $row = [
                'fields' => [
                    ['id' => 'id', 'type' => 'checkbox', 'value' => $product->getId()],
                    ['id' => 'important', 'type' => 'checkbox', 'value' => $product->getId(), 'status' => $product->isImportant() ? true : false],
                    ['value' => $product->getId(), 'type' => 'literal'],
                    ['value' => $image, 'type' => 'image'],
                    ['value' => $product->getProductCode(), 'type' => 'literal'],
                    ['value' => $product->getName(), 'type' => 'href', 'href' => "/admin/product/single?id={$product->getId()}"],
                    ['value' => '$' . $product->getBasePrice(), 'type' => 'literal'],
                    ['value' => $status, 'type' => 'literal'],
                    ['value' => $product->getDateCreated()->format('m/d/Y'), 'type' => 'literal']
                ]
            ];

            $rows[] = $row;
        }

        return $rows;
    }
}