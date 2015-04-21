<?php

return [
    'reginald' => [
        'name' => 'Reginald'
    ],
    'redbeard' => [
        'name' => 'Redbeard'
    ],
    'blackbeard' => [
        'name' => 'Edward Teach',
        'title' => function ($record) {
            return sprintf('%s the Pirate!', $record['name']);
        }
    ]
];
