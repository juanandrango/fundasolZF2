<?php

return array(
    'doctrine' => array(
        'driver' => array(
            'investor_entities' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Investor/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Investor\Entity' => 'investor_entities'
                ),
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'investors' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/investors',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Investor\Controller',
                        'controller'    => 'Investor',
                        'action'        => 'showAll',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'Investor' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/Investor[/:action][/:investorId]',
                            'constraints' => array(
                                'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'investorId'  => '[0-9]*'
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
            'Investor\Controller\Investor' => 'Investor\Controller\InvestorController'
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'investor/investor/showAll'     => __DIR__ . '/../view/investor/investor/show-all.phtml',
            'investor/investor/show'        => __DIR__ . '/../view/investor/investor/show.phtml',
            'investor/investor/edit'        => __DIR__ . '/../view/investor/investor/show.phtml',
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
