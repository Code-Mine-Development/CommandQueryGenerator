<?php
use CommandQueryGenerator\Controller\CommandController;
use CommandQueryGenerator\Controller\CommandControllerFactory;
use CommandQueryGenerator\Controller\QueryController;
use CommandQueryGenerator\Controller\QueryControllerFactory;

return [
    'controllers' => [
        'factories' => [
            CommandController::class => CommandControllerFactory::class,
            QueryController::class   => QueryControllerFactory::class,
        ],
    ],

    'service_manager' => [],

    'console' => [
        'router' => [
            'routes' => [
                'generate command' => [
                    'type'    => 'simple',
                    'options' => [
                        'route'    => 'generate command --module= --name= [--add-factory=]',
                        'defaults' => [
                            'controller' => CommandController::class,
                            'action'     => 'generateCommand',
                        ],
                    ],
                ],
                'generate query' => [
                    'type'    => 'simple',
                    'options' => [
                        'route'    => 'generate query --module= --name= [--add-factory=]',
                        'defaults' => [
                            'controller' => QueryController::class,
                            'action'     => 'generateQuery',
                        ],
                    ],
                ],

            ],
        ],
    ],
];