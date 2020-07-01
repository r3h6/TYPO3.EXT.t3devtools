<?php

return [
    'fal:placeholders' => [
        'class' => \R3H6\T3devtools\Command\FalPlaceholdersCommand::class,
    ],
    'database:seed' => [
        'class' => \R3H6\T3devtools\Command\DatabaseSeedCommand::class,
    ],
    'deprecation:scan' => [
        'vendor' => 't3devtools',
        'class' => \R3H6\T3devtools\Command\DeprecationScanCommandController::class,
        'schedulable' => false,
        // 'controller' => \R3H6\T3devtools\Command\DeprecationScanCommandController::class,
        // 'controllerCommandName' => 'execute',
        'runLevel' => \Helhum\Typo3Console\Core\Booting\RunLevel::LEVEL_COMPILE,
    ],
];
