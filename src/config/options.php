<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// Все опции должны быть изначально определены при в конфигурации модуля при подключении в приложение.
return [
    'blog_comments_allowed' => [
        'path' => 'modules.Blog.params.comments_allowed',
        'label' => 'Comments allowed',
        'description' => '',
        'group' => '',
        'category' => 'Blog',
        'rules' => [
            ['required'],
            ['boolean']
        ],
        'inputOptions' => [
            'type' => 'dropdown',
            'items' => [
                1 => 'Allow',
                0 => 'Deny',
            ],
        ],
    ],
];
