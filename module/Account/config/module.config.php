<?php
return array(
    'doctrine' => array(
        'driver' => array(
            'accounts_entities' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Account/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Account\Entity' => 'accounts_entities'
                ),
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'accounts' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/accounts',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Account\Controller',
                        'controller'    => 'Account',
                        'action'        => 'showAll',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'Account' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/Account[/:action][/:accountId]',
                            'constraints' => array(
                                'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'accountId'  => '[0-9]*'
                            ),
                            'defaults' => array(
                                'action'        => 'showAll',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Account\Controller\Account' => 'Account\Controller\AccountController'
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'account/account/showAll'       => __DIR__ . '/../view/account/account/show-all.phtml',
            'account/account/showRequests'  => __DIR__ . '/../view/account/account/show-requests.phtml',
            'account/account/show'          => __DIR__ . '/../view/account/account/show.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
);
