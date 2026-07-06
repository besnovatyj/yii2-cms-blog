<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\services\BlogCacheClearService;
use Besnovatyj\Meta\Meta;
use Besnovatyj\TreeManager\Manager\entities\Node;
use Besnovatyj\TreeManager\Manager\forms\TreeNodeFormInterface;
use Besnovatyj\TreeManager\Manager\TreeManager;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;

/**
 * Конфигурация DI контейнера для модуля Blog
 */
return function (\yii\di\Container $container): void {
    // Сервис очистки кеша изображений блога: путь к кешу резолвим здесь, в composition root,
    // чтобы сам сервис не зависел от Yii::getAlias и внедрялся контейнером в ClearController.
    $container->setSingleton(BlogCacheClearService::class, static function (): BlogCacheClearService {
        return new BlogCacheClearService(\Yii::getAlias('@static/cache/Blog'));
    });

    // TreeManager для таксономий Blog
    $container->setSingleton('blog.tree.manager', function () use ($container) {
        return new TreeManager(
            modelClass: Taxonomy::class,
            entityFactory: function (TreeNodeFormInterface $form): Taxonomy {
                return Taxonomy::create(
                    $form->name,
                    $form->slug,
                    $form->description,
                    new Meta(
                        $form->meta->title,
                        $form->meta->description,
                        $form->meta->keywords,
                    ),
                );
            },
            entityUpdater: function (Node $node, TreeNodeFormInterface $form): Node {
                /** @var Taxonomy $node */
                $node->edit(
                    $form->name,
                    $form->slug,
                    $form->description,
                    new Meta(
                        $form->meta->title,
                        $form->meta->description,
                        $form->meta->keywords,
                    ),
                );
                return $node;
            },
        );
    });
    // TreeQueryScope для чтения дерева таксономий Blog
    $container->setSingleton('blog.tree.scope', function () use ($container) {
        return new TreeQueryScope(Taxonomy::class);
    });
};
