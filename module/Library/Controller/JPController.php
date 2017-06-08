<?php
/**
* The JPController class definition.
*
* This controller will act as the parent of all action controllers
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Controller;

use Doctrine\ORM\EntityManager;
use Library\Model\Category\Category;
use Library\Model\Page\Page;
use Library\Model\Page\Theme;
use Library\Model\Product\Product;
use Library\Service\DB\EntityManagerSingleton;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Class JPController
 * @package Library\Controller
 */
abstract class JPController extends AbstractActionController
{
    protected $page;
    protected $error_message;
    protected $success_message;

    /** @var  EntityManager */
    protected $entity_manager;

    /**
     * Gets a Doctrine PDO connection
     * @return \Doctrine\DBAL\Driver\PDOConnection
     */
    public function getDBConnection()
    {
        return $this->getServiceLocator()->get('db_connection');
    }

    /**
     * Returns the store settings.
     * @return array
     */
    public function getStoreSettings()
    {
        $config = $this->getServiceLocator()->get('config');
        return $config['store_settings'];
    }

    /**
     * Sets bread crumb session
     */
    private function _create_breadcrumb()
    {
        $em = EntityManagerSingleton::getInstance();
        $session_service = $this->getServiceLocator()->get('session');
        $breadcrumb_container = $session_service->getContainer('breadcrumb');
        $page_type = $page_id = $url = $title = null;
        $breadcrumb_container['first'] = [];

        // Parse title correctly
        if ($this->page->getPageType() == 'home')
        {
            $title = "<i class='fa fa-home'></i> Home";
            $page_type = 'home';
            $page_id = null;
            $url = '/';

            $breadcrumb_container['whence'] = [];
        }
        elseif ($this->page->getPageType() == 'search')
        {
            $title = "<i class='fa fa-search'></i> Search Results";
            $page_type = 'search';
            $page_id = null;
            $url = $_SERVER['REQUEST_URI'];

            $breadcrumb_container['whence'] =
                [
                    'title' => "<i class='fa fa-home'></i> Home",
                    'url' => '/',
                    'page_type' => 'home'
                ];
        }

        // Handle bread crumbs for categories
        elseif ($this->page->getPageType() == 'category')
        {
            // Check if this category has a parent
            $category = $em->getRepository('Library\Model\Category\Category')->findOneByPage($this->page);
            if ($category instanceof Category)
            {
                // Place the parent in the first part of the breadcrumb
                if (!is_null($category->getParentCategory()))
                {
                    $breadcrumb_container['whence'] =
                        [
                            'title' => $category->getParentCategory()->getPage()->getTitle(),
                            'url' => $category->getParentCategory()->getPage()->getFullUrl(),
                            'page_type' => 'category',
                            'page_id' => $category->getParentCategory()->getPage()->getId()
                    ];
                }
                else
                {
                    $breadcrumb_container['whence'] =
                        [
                            'title' => "<i class='fa fa-home'></i> Home",
                            'url' => '/',
                            'page_type' => 'home'
                        ];
                }

                $page_type = 'category';
                $page_id = $category->getPage()->getId();
                $title = $category->getPage()->getTitle();
                $url = $_SERVER['REQUEST_URI'];
            }
        }

        // Handle bread crumbs for products
        elseif ($this->page->getPageType() == 'product')
        {
            $product = $em->getRepository('Library\Model\Product\Product')->findOneByPage($this->page);
            if ($product instanceof Product)
            {
                if ($breadcrumb_container['current']['page_type'] == 'search' || $breadcrumb_container['current']['page_type'] == 'category')
                {
                    $breadcrumb_container['whence'] = $breadcrumb_container['current'];

                    // Place parent category in first position
                    if ($breadcrumb_container['whence']['page_type'] == 'category')
                    {
                        $parent_category = $em->getRepository('Library\Model\Category\Category')
                            ->findOneByPage($em->getReference('Library\Model\Page\Page', $breadcrumb_container['whence']['page_id']))->getParentCategory();

                        if (!is_null($parent_category))
                        {
                            $breadcrumb_container['first'] =
                                [
                                    'title' => $parent_category->getPage()->getTitle(),
                                    'url' => $parent_category->getPage()->getFullUrl(),
                                    'page_type' => 'category',
                                    'page_id' => $parent_category->getPage()->getId()
                                ];
                        }
                    }
                }
                elseif ($breadcrumb_container['current']['page_type'] == 'home' || $breadcrumb_container['current']['page_type'] == 'product')
                {
                    $breadcrumb_container['whence'] =
                        [
                            'title' => "New Arrivals",
                            'url' => $em->getReference('Library\Model\Category\Category', 3)->getPage()->getFullUrl(),
                            'page_type' => 'category',
                            'page_id' => $em->getReference('Library\Model\Category\Category', 3)->getPage()->getId(),
                        ];
                }

                $page_id = $this->page->getId();
                $page_type = 'product';
                $title = $this->page->getTitle();
                $url = $this->page->getFullUrl();
            }
        }

        // Add current page
        $breadcrumb_container['current'] = [
            'title' => $title,
            'url' => $url,
            'page_type' => $page_type,
            'page_id' => $page_id
        ];
    }

    /**
     * Sets the current page and adds the appropriate headers to the view
     * @param Page $page
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
        $this->getServiceLocator()->get('ViewRenderer')->headTitle()->append($page->getTitle());

        // Create breadcrumb
        $this->_create_breadcrumb();

        // Add stylesheets
        $stylesheets = json_decode($this->page->getStylesheets());
        if (!empty($stylesheets))
        {
            foreach($stylesheets as $stylesheet)
            {
                $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/css/' . trim($stylesheet));
            }
        }

        // Add javascript
        $scripts = json_decode($this->page->getHeadScripts());
        if (!empty($scripts))
        {
            foreach ($scripts as $script)
            {
                $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/' . trim($script));
            }
        }
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * @param mixed $error_message
     */
    public function setErrorMessage($error_message)
    {
        if (is_array($error_message))
        {
            $this->error_message = "<ul>";
            foreach ($error_message as $message)
            {
                $this->error_message .= "<li>{$message}</li>";
            }
            $this->error_message .= "</ul>";
        }
        else
        {
            $this->error_message = $error_message;
        }

        $this->layout()->setVariable('error_message', $error_message);
    }

    /**
     * @return mixed
     */
    public function getSuccessMessage()
    {
        return $this->success_message;
    }

    /**
     * @param mixed $success_message
     */
    public function setSuccessMessage($success_message)
    {
        if (is_array($success_message))
        {
            $this->success_message = "<ul>";
            foreach ($success_message as $message)
            {
                $this->success_message .= "<li>{$message}</li>";
            }
            $this->success_message .= "</ul>";
        }
        else
        {
            $this->success_message = $success_message;
        }

        $this->layout()->setVariable('success_message', $success_message);
    }

    /**
     * Get an instance of the theme currently being used
     * @return \Library\Model\Page\Theme
     */
    public function getTheme()
    {
        $frontend_theme = $this->getStoreSettings()['frontend_theme'];
        return Theme::findByFolder($frontend_theme);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entity_manager;
    }

    /**
     * @param EntityManager $entity_manager
     */
    public function setEntityManager($entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }
}