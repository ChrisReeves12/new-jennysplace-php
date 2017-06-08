<?php

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
            'admin_home' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/admin',
                    'defaults' => [
                        'controller' => 'Backend\Controller\Home',
                        'action'     => 'index'
                    ]
                ]
            ],

            'admin_list' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/list[/][:entity]',
                    'constraints' => [
                        'entity' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\List',
                        'action'     => 'index'
                    ]
                ]
            ],

            'admin_mail' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/mailer/:action',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\Mailer'
                    ]
                ]
            ],

            'admin_bulk_edit' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/bulk-edit[/][:entity]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\BulkEdit',
                        'action'     => 'products'
                    ]
                ]
            ],

            'admin_discount' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/discount[/][:action]',
                    'defaults' => [
                        'controller' => 'Backend\Controller\Discount',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_product' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/product[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\Product',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_custom_page' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/custom-page[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\CustomPage',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_category' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/category[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\Category',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_user' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/user[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\User',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_option' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/option[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\Option',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_order' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/order[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\Order',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_tax' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/tax[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\Tax',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_return' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/return[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\Return',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_banner' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/banner[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\Banner',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_shipping_range' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/shipping-range[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\ShippingRange',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_shipping_method' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/shipping-method[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\ShippingMethod',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_content_blocks' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/content-block[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\ContentBlock',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_menu' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin/menu[/][:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z_]*'
                    ],
                    'defaults' => [
                        'controller' => 'Backend\Controller\Menu',
                        'action'     => 'single'
                    ]
                ]
            ],

            'admin_settings' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/admin/settings',
                    'defaults' => [
                        'controller' => 'Backend\Controller\StoreSettings',
                        'action'     => 'index'
                    ]
                ]
            ]
        ]
    ],

    // View Helpers
    'view_helpers'    => [
        'invokables' => [
            'print_sku_dialog'  => 'Backend\ViewHelper\PrintSkuDialog',
            'print_address'  => 'Backend\ViewHelper\PrintAddress',
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
            __DIR__ . '/View'
        ]
    ]

];