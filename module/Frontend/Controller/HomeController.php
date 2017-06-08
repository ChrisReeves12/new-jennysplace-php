<?php

namespace Frontend\Controller;

use Doctrine\ORM\EntityManager;
use Library\Controller\JPController;
use Library\Model\Category\Category;
use Library\Model\Page\Page;
use Library\Service\Settings;
use Zend\View\Model\ViewModel;

/**
 * Class HomeController
 * @package Frontend\Controller
 */
class HomeController extends JPController
{
    /**
     * The home page action
     * @return ViewModel
     * @throws \Exception
     */
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getServiceLocator()->get('entity_manager');

        // Set intermediate layout
        $main_layout = $this->layout()->getChildrenByCaptureTo('main_layout')[0];
        $main_layout->setTemplate('frontend/layout/home_layout');

        // Set up the view models
        $product_list = new ViewModel();
        $product_list->setTemplate('element/product/list');

        // Create page object
        $page = new Page();

        // Get store settings to popuplate page object
        $page->setTitle('Home');
        $page->setPageType('home');
        $this->setPage($page);

        // Get the home page category
        $home_category = $em->getRepository('Library\Model\Category\Category')->findOneById(Settings::get('home_page_category'));
        if (!($home_category instanceof Category))
        {
            throw new \Exception("The home page category cannot be found in the database.");
        }

        // Get products from category
        $max_products = Settings::get('products_per_page');
        $products = $em->getRepository('Library\Model\Product\Product')->findByCategoryInOrder($home_category, 0, $max_products);

        if (count($products) > 0)
        {
            $product_views = [];

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

                // Add product views to list
                $productView = new ViewModel(['product' => $product]);
                $productView->setTemplate('element/product/product_list_element');

                // Create the view for the price display
                $mini_price_display = new ViewModel(['product' => $product]);
                $mini_price_display->setTemplate('element/product/mini_product_price');

                // Create the view for the buy now button display
                $buy_now_button = new ViewModel(['product' => $product]);
                $buy_now_button->setTemplate('element/product/buy_now_button');

                $productView->setVariables(compact(['mini_price_display', 'show_more_caption', 'buy_now_button']));
                $product_views[] = $productView;
            }

            $product_list->setVariables(compact(['product_views']));
        }

        return compact(['home_category', 'product_list']);
    }
}
