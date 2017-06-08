<?php
/**
* The ListController class definition.
*
* This controller handles the listing of different entities.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Model\AbstractModel;
use Library\Model\ViewStrategy\Backend\GenericListViewStrategy;
use Library\Model\ViewStrategy\IViewStrategy;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\ServiceInterface;
use Zend\Server\Reflection;
use Zend\View\Model\JsonModel;

/**
 * Class ListController
 * @package Backend\Controller
 */
class ListController extends JPController
{
    protected $entity_table;
    protected $entity;
    protected $view_strategy;

    // Initialize variables
    public function __construct()
    {
        // Set default view strategy
        $this->view_strategy = new GenericListViewStrategy();

        // Create entity table
        $this->entity_table = [
            'products' => [
                'name' => 'Products',
                'view_strategy' => 'Library\Model\ViewStrategy\Backend\ProductListViewStrategy',
                'single_url' => '/admin/product/single',
                'entity' => 'Library\Model\Product\Product',
                'service' => 'product',
                'parameters' => [
                    'id' => 'Id',
                    'product_code' => 'Product Code',
                    'name' => 'Name',
                    'base_price' => 'Price',
                    'status' => 'Status'
                ],
                'sort' => ['name', 'ASC']
            ],

            'shipping_methods' => [
                'name' => 'Shipping_Methods',
                'single_url' => '/admin/shipping-method/single',
                'entity' => 'Library\Model\Shop\ShippingMethod',
                'service' => 'ShippingMethod',
                'parameters' => [
                    'id' => 'Id',
                    'name' => 'Name',
                    'carrier' => 'Carrier',
                    'carrier_id' => 'Carrier ID'
                ],
                'sort' => ['carrier', 'ASC']
            ],

            'categories' => [
                'name' => 'Categories',
                'view_strategy' => 'Library\Model\ViewStrategy\Backend\CategoryListViewStrategy',
                'single_url' => '/admin/category/single',
                'entity' => 'Library\Model\Category\Category',
                'service' => 'category',
                'parameters' => [
                    'id' => 'Id',
                    'name' => 'Name'
                ]
            ],

            'email_campaigns' => [
                'name' => 'Email_Campaigns',
                'single_url' => '/admin/mailer/campaign',
                'entity' => 'Library\Model\Mail\Campaign',
                'service' => 'mailer',
                'parameters' => [
                    'id' => 'Id',
                    'name' => 'Name'
                ]
            ],

            'taxes' => [
                'name' => 'Taxes',
                'single_url' => '/admin/tax/single',
                'entity' => 'Library\Model\Shop\Tax',
                'service' => 'tax',
                'parameters' => [
                    'id' => 'Id',
                    'state' => 'State',
                    'rate' => 'Rate'
                ]
            ],

            'options' => [
                'name' => 'Options',
                'single_url' => '/admin/option/single',
                'entity' => 'Library\Model\Product\Option',
                'service' => 'option',
                'parameters' => [
                    'id' => 'Id',
                    'name' => 'Name'
                ]
            ],

            'discounts' => [
                'name' => 'Discounts',
                'single_url' => '/admin/discount/single',
                'entity' => 'Library\Model\Shop\Discount',
                'service' => 'order',
                'parameters' => [
                    'id' => 'Id',
                    'name' => 'Name',
                    'code' => 'Code'
                ]
            ],

            'menus' => [
                'name' => 'Menus',
                'single_url' => '/admin/menu/single',
                'entity' => 'Library\Model\Page\Menu',
                'service' => 'page',
                'parameters' => [
                    'id' => 'Id',
                    'label' => 'Label',
                    'date_created' => 'Date Created'
                ]
            ],

            'returns' => [
                'name' => 'Returns',
                'view_strategy' => 'Library\Model\ViewStrategy\Backend\ReturnListViewStrategy',
                'single_url' => '/admin/return/single',
                'entity' => 'Library\Model\Shop\ProductReturn',
                'service' => 'order',
                'hide_create' => true,
                'parameters' => [
                    'id' => 'Id',
                    'first_name' => 'First Name',
                    'last_name' => 'Last Name',
                    'status' => 'Status',
                    'order_number' => 'Order Number',
                    'date_created' => 'Date Created'
                ]
            ],

            'content_blocks' => [
                'name' => 'Content_Blocks',
                'view_strategy' => 'Library\Model\ViewStrategy\Backend\ContentBlockListViewStrategy',
                'single_url' => '/admin/content-block/single',
                'service' => 'contentBlock',
                'entity' => 'Library\Model\Page\ContentBlock',
                'parameters' => [
                    'handle' => 'Handle'
                ]
            ],

            'banners' => [
                'name' => 'Banners',
                'single_url' => '/admin/banner/single',
                'entity' => 'Library\Model\Page\Banner',
                'service' => 'banner',
                'parameters' => [
                    'id' => 'Id',
                    'label' => 'Label',
                    'date_created' => 'Date Created'
                ]
            ],

            'custom_pages' => [
                'name' => 'Custom_Pages',
                'view_strategy' => 'Library\Model\ViewStrategy\Backend\CustomPageListViewStrategy',
                'single_url' => '/admin/custom-page/single',
                'entity' => 'Library\Model\Page\CustomPage',
                'service' => 'customPage',
                'parameters' => [
                    'id' => 'Id',
                    'title' => 'Title',
                    'url_handle' => 'URL Handle',
                    'date_created' => 'Date Created'
                ]
            ],

            'shipping_ranges' => [
                'name' => 'Shipping_Ranges',
                'view_strategy' => 'Library\Model\ViewStrategy\Backend\ShippingRangeListViewStrategy',
                'single_url' => '/admin/shipping-range/single',
                'entity' => 'Library\Model\Shop\ShippingRange',
                'service' => 'shippingrange',
                'parameters' => [
                    'id' => 'Id',
                    'shipping_method' => 'Shipping Method',
                    'low_value' => 'Low Value',
                    'high_value' => 'High Value',
                    'date_created' => 'Date Created'
                ]
            ],

            'mail_list_subscribers' => [
                'name' => 'Mail_List_Subscribers',
                'single_url' => '/admin/mailsubs/single',
                'entity' => 'Library\Model\Subscription\MailSubscription',
                'service' => 'user',
                'hide_create' => true,
                'hide_edit' => true,
                'parameters' => [
                    'id' => 'Id',
                    'name' => 'Name',
                    'email' => 'Email',
                    'date_created' => 'Date Created'
                ],
                'sort' => ['name', 'ASC']
            ],

            'emails' => [
                'name' => 'Emails',
                'single_url' => '/admin/mailer/email',
                'entity' => 'Library\Model\Mail\Email',
                'service' => 'mailer',
                'parameters' => [
                    'id' => 'Id',
                    'subject' => 'Subject',
                    'completed' => 'Completed',
                    'date_created' => 'Date Created'
                ],
                'sort' => ['subject', 'ASC']
            ],

            'users' => [
                'name' => 'Users',
                'single_url' => '/admin/user/single',
                'entity' => 'Library\Model\User\User',
                'service' => 'user',
                'parameters' => [
                    'id' => 'Id',
                    'first_name' => 'First Name',
                    'last_name' => 'Last Name',
                    'email' => 'Email'
                ],
                'sort' => ['last_name', 'DESC']
            ],

            'orders' => [
                'name' => 'Orders',
                'view_strategy' => 'Library\Model\ViewStrategy\Backend\OrderListViewStrategy',
                'single_url' => '/admin/order/single',
                'entity' => 'Library\Model\Shop\ShopList\Order',
                'service' => 'order',
                'parameters' => [
                    'id' => 'Id',
                    'first_name' => 'First Name',
                    'last_name' => 'Last Name',
                    'status' => 'Status',
                    'order_number' => 'Order Number',
                    'payment_method' => 'Pay Method',
                    'date_created' => 'Date',
                    'sub_total' => 'Sub-Total',
                    'total' => 'Total'
                ]
            ]
        ];
    }

    /**
     * The main page that shows the listings
     * @return array
     * @throws \Exception
     */
    public function indexAction()
    {
        // Get the entity we are making the list of
        $this->entity = $this->params()->fromRoute('entity');
        if (!isset($this->entity))
        {
            return $this->getResponse()->setStatusCode(404);
        }

        // Get information about the entity to display to view
        if (empty($info = $this->entity_table[$this->entity]))
        {
            return false;
        }

        // Change the view strategy if it is set to use a different one
        if (!empty($info['view_strategy']))
        {
            $view_strategy_class = $info['view_strategy'];
            if (!class_exists($view_strategy_class))
            {
                throw new \Exception("The view strategy class being used to render this page cannot be found.");
            }

            $view_strategy = new $view_strategy_class;
            if (!($view_strategy instanceof IViewStrategy))
            {
                throw new \Exception("The view strategy being used must implement the IViewStrategy interface.");
            }

            $this->setViewStrategy($view_strategy);
        }

        // Render the list using the strategy given
        $view_results = $this->view_strategy->render($this);
        if (false === $view_results)
        {
            return $this->getResponse()->setStatusCode(404);
        }

        // Attach javascript
        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/backend/list.js');

        return $view_results;
    }

    /**
     * Handle post requests
     * @return JsonModel
     * @throws \Exception
     */
    public function handle_post()
    {
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $task = $data['task'];

            // Perform action based on task
            switch ($task)
            {
                // Delete item
                case 'delete':

                    // Find element to delete
                    $element_model = $this->entity_table[$data['element']]['entity'];
                    $element_service_name = $this->entity_table[$data['element']]['service'];
                    $element_service = $this->getServiceLocator()->get($element_service_name);
                    if (!($element_service instanceof ServiceInterface))
                        throw new \Exception("The element service in the list controller must implement Library\\Service\\ServiceInterface.");

                    $element_service->deleteByIds($data['ids'], new $element_model);

                    EntityManagerSingleton::getInstance()->flush();

                    return new JsonModel(['error' => false]);
                    break;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getEntityTable()
    {
        return $this->entity_table;
    }

    /**
     * @return IViewStrategy
     */
    public function getViewStrategy()
    {
        return $this->view_strategy;
    }

    /**
     * @param IViewStrategy $view_strategy
     */
    public function setViewStrategy(IViewStrategy $view_strategy)
    {
        $this->view_strategy = $view_strategy;
    }

    /**
     * @return AbstractModel
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param AbstractModel $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}