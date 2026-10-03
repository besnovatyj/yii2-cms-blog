<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Contracts\adminMenu\AdminMenuLocation;
use Besnovatyj\Contracts\adminMenu\AdminMenuPlacement;

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
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Blog',
                    groupIcon: 'bi bi-book',
                    groupPriority: 100,
                    priority: 100,
                ),
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
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Blog',
                    groupIcon: 'bi bi-book',
                    groupPriority: 100,
                    priority: 100,
                ),
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
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Blog',
                    groupIcon: 'bi bi-book',
                    groupPriority: 100,
                    priority: 100,
                ),
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
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Blog',
                    groupIcon: 'bi bi-book',
                    groupPriority: 100,
                    priority: 100,
                ),
            ],
        ],
    ],
];
