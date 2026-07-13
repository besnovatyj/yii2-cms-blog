<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use Besnovatyj\Blog\Module;

/**
 * Yii2-конфиг модуля для движка yiisoft/config (группа `common` — общий для всех приложений).
 *
 * Объявляется через `extra.config-plugin`, собирается modman в merge-plan и мёржится в рантайме.
 * Содержит регистрацию модуля. Меню (adminMenu) и миграции остаются вкладами modman. Значения берутся
 * из статических методов {@see Module} — единый источник, без дублирования.
 *
 * URL-правила фронтенда — вклад в именованный компонент `frontendUrlManager`, а НЕ в захардкоженный
 * список ядра. Почему группа `common`, а не `app-frontend`: компонент `frontendUrlManager` определён и
 * в backend, и во frontend (межприложенческая генерация ссылок через `UrlManagerHelperTrait` —
 * админка строит фронтовые URL сущностей блога). Группа `common` мёржится во все приложения, поэтому
 * правила получают обе копии компонента. `RecursiveMerge` конкатенирует списки `rules`: вклад модуля
 * (vendor-слой) встаёт ПЕРЕД правилами root, а catch-all ядра остаётся последним. Правила гейтятся
 * modman — деактивация модуля убирает их из сборки (в отличие от прежнего хардкода в ядре).
 *
 * Роут капитализирован под реальный id модуля ('Blog'); публичный URL (левая часть) остаётся строчным.
 */
return [
    'modules' => [
        Module::moduleId() => array_merge(
            ['class' => Module::class],
            Module::moduleConfig(),
            ['version' => Module::moduleVersion()],
        ),
    ],
    'components' => [
        'frontendUrlManager' => [
            'rules' => [
                'blog'                                    => 'Blog/post/index',
                'blog/search'                             => 'Blog/search/index',
                'blog/tag/<slug:[\w\-]+>/<page:\d+>'      => 'Blog/post/tag', // <page> — пагинация
                'blog/tag/<slug:[\w\-]+>'                 => 'Blog/post/tag',
                'blog/<id:\d+>'                           => 'Blog/post/view',
                'blog/<id:\d+>/comment'                   => 'Blog/post/comment',
                'blog/<slug:[\w\-]+>/<page:\d+>'          => 'Blog/post/taxonomy', // <page> — пагинация
                'blog/<slug:[\w\-]+>'                     => 'Blog/post/taxonomy',
            ],
        ],
    ],
];
