<?php

return [

    'name' => 'tobbe/transifex-fetcher',
    
    'type' => 'extension',
    
    'main' => function($app) {},

    'autoload' => [
        'Tobbe\\TransifexFetcher\\' => 'src'
    ],

    'events' => [

        'console.init' => function ($event, $console) {
            $console->add(new Tobbe\TransifexFetcher\Commands\TransifexConfigCommand());
            $console->add(new Tobbe\TransifexFetcher\Commands\TransifexFetchCommand());
        }

    ]

];