<?php

return array(
    'doctrine' => array(
        'driver' => array(
            'client_entities' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Client/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Client\Entity' => 'client_entities'
                ),
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'clients' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/clients',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Client\Controller',
                        'controller'    => 'Client',
                        'action'        => 'showAll',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'Client' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/Client[/:action][/:clientId]',
                            'constraints' => array(
                                'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'clientId'  => '[0-9]*'
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
            'Client\Controller\Client' => 'Client\Controller\ClientController'
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'client/client/showAll'     => __DIR__ . '/../view/client/client/show-all.phtml',
            'client/client/show'        => __DIR__ . '/../view/client/client/show.phtml',
            'client/client/edit'        => __DIR__ . '/../view/client/client/show.phtml',
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
