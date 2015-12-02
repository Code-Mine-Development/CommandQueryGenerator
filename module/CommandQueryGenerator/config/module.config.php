<?php
use CommandQueryGenerator\Controller\CommandController;
use CommandQueryGenerator\Controller\CommandControllerFactory;

return [
    'controllers' => [
        'factories' => [
            CommandController::class => CommandControllerFactory::class,
        ],
    ],

    'service_manager' => [],

    'console' => [
        'router' => [
            'routes' => [
                'oauth client add' => [
                    'type'    => 'simple',
                    'options' => [
                        'route'    => 'generate command --module= --name= [--add-factory=]',
                        'defaults' => [
                            'controller' => CommandController::class,
                            'action'     => 'generateCommand',
                        ],
                    ],
                ],

            ],
        ],
    ],
];