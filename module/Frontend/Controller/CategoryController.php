<?php
/**
* The CategoryController class definition.
*
* This controller handles all of the frontend logic for categories
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\Controller;

use Library\Controller\JPController;
use Library\Model\Category\Category;
use Library\Model\Page\Page;
use Library\Model\Product\Product;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\Settings;
use Zend\View\Model\ViewModel;

/**
 * Class CategoryController
 * @package Frontend\Controller
 */
class CategoryController extends JPController
{

    /**
     * The main category page that shows all of the products
     * @return ViewModel
     * @throws \Exception
     */
    public function indexAction()
    {
        // Set intermediate layout
        $main_layout = $this->layout()->getChildrenByCaptureTo('main_layout')[0];
        $main_layout->setTemplate('frontend/layout/shopping_layout');

        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Get page index
        $page_index = !isset($_GET['page']) ? 1 : $_GET['page'];

        $handle = $this->params()->fromRoute('handle');
        $page = $em->getRepository('Library\Model\Page\Page')->findOneBy(['url_handle' => $handle, 'inactive' => false]);

        if (!($page instanceof Page))
        {
            return ['error' => 'This category cannot be found.'];
        }

        $this->setPage($page);

        // Set up the view models
        $product_list = new ViewModel();
        $product_list->setTemplate('element/product/list');

        // Find the category
        $category = $em->getRepository('Library\Model\Category\Category')->findOneByPage($page);
        if (!($category instanceof Category))
        {
            throw new \Exception("The category associated with the page cannot be found.");
        }

        // Get sub categories
        $sub_categories = $em->getRepository('Library\Model\Category\Category')->findSubCategories($category);

        $max_products = Settings::get('products_per_page');
        $pagination_view = null;

        // Get products, if there is a "points to", use that category as an alias instead of the category
        $category_to_get_products_from = ($category->getPointsTo() instanceof Category) ? $category->getPointsTo() : $category;

        $total_product_amount = $em->getRepository('Library\Model\Product\Product')->getProductCount($category_to_get_products_from);
        $products = $em->getRepository('Library\Model\Product\Product')->findByCategoryInOrder($category_to_get_products_from, ($page_index - 1) * $max_products, $max_products);

        if (count($products) > 0)
        {
            $product_views = [];

            /** @var Product $product */
            foreach ($products as $product)
            {
                // Check if product should have 'show more selections' caption
                $show_more_caption = false;
                if (count($product->getSkus()) > 1)
                {
                    if ($product->shouldShowMoreCaption())
                    {
                        $show_more_caption = true;
                    }

                }

                // Get mini price display
                $mini_price_display = new ViewModel(['product' => $product]);
                $mini_price_display->setTemplate('element/product/mini_product_price');
                $buy_now_button = new ViewModel(['product' => $product]);
                $buy_now_button->setTemplate('element/product/buy_now_button');

                // Add product views to list
                $productView = new ViewModel(['product' => $product, 'show_more_caption' => $show_more_caption, 'mini_price_display' => $mini_price_display, 'buy_now_button' => $buy_now_button]);
                $productView->setTemplate('element/product/product_list_element');
                $product_views[] = $productView;
            }

            // Add variables to pagination
            $pagination_view = new ViewModel(['product_qty' => $total_product_amount, 'max_products' => $max_products, 'page' => $page_index]);
            $pagination_view->setTemplate('element/product/pagination');

            $product_list->setVariables(['product_views' => $product_views]);
        }

        return compact(['category', 'pagination_view', 'sub_categories', 'product_list']);
    }
}