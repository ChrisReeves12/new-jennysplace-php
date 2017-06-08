<?php

return [

    // Controllers
    'controllers' => [
        'invokables' => [
            'Console\Controller\DataFix' => 'Console\Controller\DataFixController',
            'Console\Controller\Subscription' => 'Console\Controller\SubscriptionController',
        ]
    ],

    // Routes
    'console' => [
        'router' => [
            'routes' => [

                // Syncs users to newsletter list
                'sync-users-to-newsletter' => [
                    'options' => [
                        'route' => 'sync users to newsletter',
                        'defaults' => [
                            'controller' => 'Console\Controller\Subscription',
                            'action' => 'syncuserstonewsletter'
                        ]
                    ]
                ],

                // Syncs contacts on the newsletter list to the remote newsletter handling API
                'subscription-sync-to-remote' => [
                    'options' => [
                        'route' => 'sync newsletter to remote',
                        'defaults' => [
                            'controller' => 'Console\Controller\Subscription',
                            'action' => 'syncnewslettertoremote'
                        ]
                    ]
                ],

                // Runs scheduld email campaigns
                'run-scheduled-email-campaigns' => [
                    'options' => [
                        'route' => 'run scheduled email campaigns',
                        'defaults' => [
                            'controller' => 'Console\Controller\Subscription',
                            'action' => 'runemailcampaigns'
                        ]
                    ]
                ],

                // Updates the database schema
                'db-update-schema' => [
                    'options' => [
                        'route' => 'update database schema',
                        'defaults' => [
                            'controller' => 'Console\Controller\DataFix',
                            'action' => 'updatedatabaseschema'
                        ]
                    ]
                ],

                // Updates the product option cache
                'update-product-option-cache' => [
                    'options' => [
                        'route' => 'update product option cache',
                        'defaults' => [
                            'controller' => 'Console\Controller\DataFix',
                            'action' => 'updateproductoptioncache'
                        ]
                    ]
                ],

                // Updates the database schema
                'db-update-statuses' => [
                    'options' => [
                        'route' => 'update product status',
                        'defaults' => [
                            'controller' => 'Console\Controller\DataFix',
                            'action' => 'updateproductstatus'
                        ]
                    ]
                ],

                // Deletes duplicates in the product category associations
                'db-delete-category-duplicates' => [
                    'options' => [
                        'route' => 'delete category duplicate associations',
                        'defaults' => [
                            'controller' => 'Console\Controller\DataFix',
                            'action' => 'deleteduplicatecategoryassociations'
                        ]
                    ]
                ],

                // Moves products from one category to the other
                'category-product-move' => [
                    'options' => [
                        'route' => 'move products from category [--copy|-c] <source_cat> <dest_cat>',
                        'defaults' => [
                            'controller' => 'Console\Controller\DataFix',
                            'action' => 'categoryproductmove'
                        ]
                    ]
                ],

                'move-orphaned-products' => [
                    'options' => [
                        'route' => 'move orphaned products',
                        'defaults' => [
                            'controller' => 'Console\Controller\DataFix',
                            'action' => 'moveorphanedproducts'
                        ]
                    ]
                ],

                'convert-settings-file' => [
                    'options' => [
                        'route' => 'convert settings file',
                        'defaults' => [
                            'controller' => 'Console\Controller\DataFix',
                            'action' => 'convertsettingsfile'
                        ]
                    ]
                ],

                // Updates query lists
                'update-query-lists' => [
                    'options' => [
                        'route' => 'update query lists',
                        'defaults' => [
                            'controller' => 'Console\Controller\DataFix',
                            'action' => 'updatequerylists'
                        ]
                    ]
                ],

                // Repairs sku statuses
                'update-sku-status' => [
                    'options' => [
                        'route' => 'fix sku status',
                        'defaults' => [
                            'controller' => 'Console\Controller\DataFix',
                            'action' => 'fixskustatus'
                        ]
                    ]
                ]
            ]
        ]
    ]

];