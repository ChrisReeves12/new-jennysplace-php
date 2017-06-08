<?php
/**
 * The Frontend module represents the customer facing side of the application.
 */

namespace Frontend;

use Library\Form\Auth\Login;
use Library\Form\Subscription\MailistSignup;
use Library\Model\User\User;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\Settings;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // Set up theme and banners
        $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, [$this, 'set_layout_variables'], 200);
        $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, [$this, 'set_layout_assets'], 100);
        $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, [$this, 'set_controller_assets'], 100);
    }

    public function getConfig()
    {
        return include __DIR__ . '/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__
                ]
            ]
        ];
    }

    /**
     * Sets up additional elements
     * @param MvcEvent $e
     */
    public function set_layout_assets(MvcEvent $e)
    {
        // Create mail list signup form
        $maillist_form = new MailistSignup();
        $mail_list_view = new ViewModel();
        $mail_list_view->setVariables(['maillist_form' => $maillist_form]);
        $mail_list_view->setTemplate('element/subscription/maillist');
        $e->getViewModel()->setVariable('maillist_form_view', $mail_list_view);
    }

    /**
     * Responsible for setting up the theme and banner if we are in the frontend module
     * @param MvcEvent $e
     */
    public function set_controller_assets(MvcEvent $e)
    {
        $session_service = $e->getApplication()->getServiceManager()->get('session');
        $this->setup_view($e);
        $route_match = $e->getRouteMatch();

        if ($route_match)
        {
            // Set whence location in case we need it
            $session_service->getContainer('navigation')['whence'] = $_SERVER['REQUEST_URI'];

            $controller = $route_match->getParam('controller');
            if (strpos($controller, __NAMESPACE__) !== false)
            {
                $this->setup_view($e);
            }
        }
    }

    /**
     * Sets up the active theme and view of the store by altering the template map and stack.
     * @param MvcEvent $e
     */
    public function setup_view(MvcEvent $e)
    {
        $em = EntityManagerSingleton::getInstance();
        $route_match = $e->getRouteMatch();
        $controller = (!$route_match) ? null : $route_match->getParam('controller');
        $action = (!$route_match) ? null : $route_match->getParam('action');

        $theme = Settings::get('frontend_theme');
        $e->getViewModel()->setVariables(['theme' => $theme]);

        // Set up banners
        $theme_config = json_decode(file_get_contents(getcwd() . '/public/themes/' . $theme . '/config.json'), true);
        $banner_info = $theme_config['banners'];

        if (!empty($banner_info))
        {
            $banners = [];

            // Get banners that are on the layout
            $layout_banners_labels = $banner_info['Default'];
            $layout_banners = $em->getRepository('Library\Model\Page\Banner')->findBy(['label' => $layout_banners_labels]);

            // Get banners from controller
            if ((!is_null($controller)) && isset($banner_info[$controller][$action]))
            {
                $banner_labels = $banner_info[$controller][$action];
                $banners = $em->getRepository('Library\Model\Page\Banner')->findBy(['label' => $banner_labels]);
            }

            $e->getViewModel()->setVariables(['banners' => $banners, 'layout_banners' => $layout_banners]);
        }

        // Set up CSS
        $css_info = $theme_config['css'];
        if (!empty($css_info))
        {
            // Get stylesheets from controller
            if (!is_null($controller) && isset($css_info[$controller][$action]))
            {
                $css_files = $css_info[$controller][$action];
                if (count($css_files) > 0)
                {
                    $headLink_function = $e->getApplication()->getServiceManager()->get('ViewHelperManager')->get('headLink');

                    foreach ($css_files as $css_file)
                    {
                        $headLink_function->appendStylesheet('/themes/' . $theme . '/css/' . $css_file);
                    }
                }
            }
        }

        // Set up scripts
        $js_info = $theme_config['js'];
        if (!empty($js_info))
        {
            // Get scripts from controller
            if (!is_null($controller) && isset($js_info[$controller][$action]))
            {
                $js_files = $js_info[$controller][$action];
                if (count($js_files) > 0)
                {
                    $headScript_function = $e->getApplication()->getServiceManager()->get('ViewHelperManager')->get('headScript');

                    foreach ($js_files as $js_file)
                    {
                        $headScript_function->appendFile('/themes/' . $theme . '/js/' . $js_file);
                    }
                }
            }
        }
    }

    /**
     * Sets layout variables to be used
     *
     * @param MvcEvent $e
     */
    public function set_layout_variables(MvcEvent $e)
    {
        $em = EntityManagerSingleton::getInstance();
        $user_service = $e->getApplication()->getServiceManager()->get('user');
        $session_service = $e->getApplication()->getServiceManager()->get('session');

        $store_settings = Settings::getAll();
        $theme = $store_settings['frontend_theme'];
        $categories = $em->getRepository('Library\Model\Category\Category')->findBy(['inactive' => false, 'parent_category' => null], ['sort_order' => 'DESC']);
        $user = $user_service->getIdentity();

        // Check if the prices should be shown
        $show_prices = $store_settings['show_prices'];
        $hide_prices = $show_prices == "0" ? true : false;
        $admin_logged_in = false;

        if ($user instanceof User)
        {
            $hide_prices = false;
            if ($user->getRole() == 'administrator' || $user->getRole() == 'superuser')
            {
                $admin_logged_in = true;
            }
        }

        // Load  main navigation
        $main_navigation = $em->getRepository('Library\Model\Page\Menu')->findOneByLabel('Main Site Navigation');

        // Get shopping cart
        if ($user instanceof User)
        {
            $cart = $user->getSavedCart();
        }
        else
        {
            $cart = null;
        }

        $breadcrumb_data = $session_service->getContainer('breadcrumb');

        // Load login form
        $login_form = new Login();
        $login_form->setAttribute('action', '/auth');
        $login_form->get('email')->setAttribute('class', 'form-control');
        $login_form->get('password')->setAttribute('class', 'form-control');
        $login_form->get('submit')->setAttributes(['class' => 'btn btn-default', 'value' => 'Login']);

        // Set up main template
        $e->getViewModel()->setTemplate('frontend/default');

        // Set up the main layout
        $main_layout = new ViewModel($e->getViewModel()->getVariables());
        $main_layout->setTemplate('frontend/layout/layout');
        $e->getViewModel()->addChild($main_layout, 'main_layout');

        $e->getViewModel()->setVariables(compact(['hide_prices', 'breadcrumb_data', 'admin_logged_in', 'store_settings', 'categories', 'main_navigation', 'login_form', 'theme', 'user', 'cart']));
    }
}
