<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

return [
    // Posts
    [
        'label' => 'Posts',
        'iconClass' => 'bi bi-list-columns me-1',
        'url' => ['/Blog/backend/post/index'],
        'active' => static function () {
            return str_contains(Yii::$app->request->url, '/Blog/backend/post');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Blog',
                    'groupIcon' => 'bi bi-book',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],

    // Taxonomy
    [
        'label' => 'Taxonomy',
        'iconClass' => 'bi bi-diagram-3 me-1',
        'url' => ['/Blog/backend/taxonomy/index'],
        'active' => static function () {
            return (bool)preg_match('#/Blog/backend/taxonomy/(index|create|update|view)#', Yii::$app->request->url);
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Blog',
                    'groupIcon' => 'bi bi-book',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],

    // Comments
    [
        'label' => 'Comments',
        'iconClass' => 'bi bi-chat-left me-1',
        'url' => ['/Blog/backend/comment/index'],
        'active' => static function () {
            return str_contains(Yii::$app->request->url, '/Blog/backend/comment');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Blog',
                    'groupIcon' => 'bi bi-book',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],

    // Tags
    [
        'label' => 'Tags',
        'iconClass' => 'bi bi-tags me-1',
        'url' => ['/Blog/backend/tag/index'],
        'active' => static function () {
            return str_contains(Yii::$app->request->url, '/Blog/backend/tag');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Blog',
                    'groupIcon' => 'bi bi-book',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],

    // Parser
    [
        'label' => 'Parser',
        'iconClass' => 'bi bi-journal-arrow-down me-1',
        'url' => ['/Blog/backend/parse/index'],
        'active' => static function () {
            return str_contains(Yii::$app->request->url, '/Blog/backend/parse');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Blog',
                    'groupIcon' => 'bi bi-book',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],
];
