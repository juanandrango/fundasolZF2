<?php

return array(
    'doctrine' => array(
        'driver' => array(
            'payments_entities' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Payment/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Payment\Entity' => 'payments_entities'
                ),
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'payments' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/payments',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Payment\Controller',
                        'controller'    => 'Payment',
                        'action'        => 'showAll',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'Payment' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/Payment[/:action][/:paymentId]',
                            'constraints' => array(
                                'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'paymentId'  => '[0-9]*'
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
            'Payment\Controller\Payment' => 'Payment\Controller\PaymentController'
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
