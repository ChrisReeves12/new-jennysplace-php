<?php
/**
* The SearchController class definition.
*
* This controller manages product search on the front end
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\Controller;

use Library\Controller\JPController;
use Library\Model\Page\Page;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\Settings;
use Zend\View\Model\ViewModel;

/**
 * Class SearchController
 * @package Frontend\Controller
 */
class SearchController extends JPController
{
    /**
     * The main page that represents the search results
     * @return ViewModel
     */
    public function indexAction()
    {
        // Set intermediate layout
        $main_layout = $this->layout()->getChildrenByCaptureTo('main_layout')[0];
        $main_layout->setTemplate('frontend/layout/shopping_layout');

        $keywords = isset($_GET['keywords']) ? $_GET['keywords'] : '';
        $first_result = isset($_GET['page']) ? $_GET['page']-1 : 0;
        $max_products = Settings::get('products_per_page');

        // Set up product list view
        $product_list = new ViewModel();
        $product_list->setTemplate('element/product/list');

        // Set page
        $page = new Page();
        $page->setTitle("Search Results for '{$keywords}'");
        $page->setPageType('search');
        $this->setPage($page);

        // Search products
        $em = EntityManagerSingleton::getInstance();

        if (!empty($keywords))
        {
            $products = $em->getRepository('Library\Model\Product\Product')->findByKeywords($keywords, $first_result, $max_products);
            $result_count = $em->getRepository('Library\Model\Product\Product')->findByKeywordsCount($keywords);
        }
        else
        {
            $products = [];
            $result_count = 0;
        }

        $product_views = [];

        if (count($products) > 0)
        {
            foreach ($products as $product)
            {
                // Get mini price display
                $mini_price_display = new ViewModel(['product' => $product]);
                $mini_price_display->setTemplate('element/product/mini_product_price');
                $buy_now_button = new ViewModel(['product' => $product]);
                $buy_now_button->setTemplate('element/product/buy_now_button');

                // Add product views to list
                $productView = new ViewModel(['product' => $product, 'mini_price_display' => $mini_price_display, 'buy_now_button' => $buy_now_button]);
                $productView->setTemplate('element/product/product_list_element');
                $product_views[] = $productView;
            }
        }

        $product_list->setVariable('product_views', $product_views);

        // Add variables to pagination
        $page_index = !isset($_GET['page']) ? 1 : $_GET['page'];
        $max_products = Settings::get('products_per_page');
        $pagination_view = new ViewModel(['product_qty' => $result_count, 'max_products' => $max_products, 'page' => $page_index]);
        $pagination_view->setTemplate('element/product/pagination');

        return compact(['result_count', 'keywords', 'product_list', 'pagination_view']);
    }
}