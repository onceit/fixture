<?php

return [
    'reginald' => [
        'name' => 'Reginald',
        'catchphrases' => [
            'batten' => [
                'position' => 5
            ],
            'fishes' => [
                'position' => 4
            ],
            'blow' => [
                'position' => 3
            ]
        ]
    ],
    'redbeard' => [
        'name' => 'Redbeard'
    ],
    'blackbeard' => [
        'name' => 'Edward Teach',
        'title' => function ($record) {
            return sprintf('%s the Pirate!', $record->name);
        },
        'catchphrases' => ['batten', 'fishes', 'blow']
    ]
];
