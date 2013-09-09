<?php
return array(
    'doctrine' => array(
        'driver' => array(
            'contributions_entities' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Contribution/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Contribution\Entity' => 'contributions_entities'
                ),
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'contributions' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/contributions',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Contribution\Controller',
                        'controller'    => 'Contribution',
                        'action'        => 'showAll',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'Contribution' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/Contribution[/:action][/:contributionId]',
                            'constraints' => array(
                                'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'contributionId'  => '[0-9]*'
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
            'Contribution\Controller\Contribution' => 'Contribution\Controller\ContributionController'
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'contribution/contribution/showAll'     => __DIR__ . '/../view/contribution/contribution/show-all.phtml',
            'contribution/contribution/show'        => __DIR__ . '/../view/contribution/contribution/show.phtml',
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
