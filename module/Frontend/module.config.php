<?php
/**
 * The main config
 */

use Library\Service\Settings;

// Get theme to be used
$theme = Settings::get('frontend_theme');

return [
    // Controllers
    'controllers' => [
        'abstract_factories' => [
            'Library\Controller\AbstractControllerFactory'
        ]
    ],

    // Routes
    'router' => [
        'routes' => [
            'home' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => 'Frontend\Controller\Home',
                        'action'     => 'index'
                    ]
                ]
            ],

            'cart' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/shopping-cart[/][:action][/][:order_number]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Frontend\Controller\Cart',
                        'action'     => 'index'
                    ]
                ]
            ],

            'product' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/product/:handle',
                    'constraints' => [
                        'handle' => '[0-9a-zA-Z_-]*'
                    ],
                    'defaults' => [
                        'controller' => 'Frontend\Controller\Product',
                        'action'     => 'index'
                    ]
                ]
            ],

            'category' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/category/:handle',
                    'constraints' => [
                        'handle' => '[0-9a-zA-Z_-]*'
                    ],
                    'defaults' => [
                        'controller' => 'Frontend\Controller\Category',
                        'action'     => 'index'
                    ]
                ]
            ],

            'custom_page' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/page/:handle',
                    'constraints' => [
                        'handle' => '[0-9a-zA-Z_-]*'
                    ],
                    'defaults' => [
                        'controller' => 'Frontend\Controller\CustomPage',
                        'action'     => 'index'
                    ]
                ]
            ],

            'search' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/search',
                    'defaults' => [
                        'controller' => 'Frontend\Controller\Search',
                        'action'     => 'index'
                    ]
                ]
            ],

            'auth' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/auth[/][:action][/:token]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*',
                        'token' => '[0-9a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Frontend\Controller\Auth',
                        'action'     => 'index'
                    ]
                ]
            ],

            'user' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/user[/][:action][/][:param1]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*',
                        'param1' => '[a-zA-Z0-9_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Frontend\Controller\User',
                        'action'     => 'register'
                    ]
                ]
            ],

            'mailsignup' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/subscription/:action',
                    'defaults' => [
                        'controller' => 'Frontend\Controller\Subscription',
                        'action' => 'mail'
                    ]
                ]
            ]
        ]
    ],

    // View Manager
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_path_stack'      => [
            __DIR__ . '/../../public/themes/' . $theme,
            __DIR__ . '/../../public/themes/' . $theme . '/frontend',
        ],
        'strategies' => [
            'ViewJsonStrategy'
        ]
    ],

    // View Helpers
    'view_helpers'    => [
        'invokables' => [
            'render_banner'  => 'Frontend\ViewHelper\RenderBanner',
            'print_menu'  => 'Frontend\ViewHelper\PrintMenu',
            'print_product_options'  => 'Frontend\ViewHelper\PrintProductOptions',
            'print_content_block'  => 'Frontend\ViewHelper\PrintContentBlock',
            'print_breadcrumb'  => 'Frontend\ViewHelper\PrintBreadcrumb',
            'money' => 'Frontend\ViewHelper\Money',
            'product_photo' => 'Frontend\ViewHelper\ProductPhoto',
            'product_name' => 'Frontend\ViewHelper\ProductName'
        ]
    ]
];
