<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Blog\blocks;

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\readModels\PostReadRepository;
use Besnovatyj\Contracts\block\BlockDataProvider;

/**
 * Посты для блока «Последние посты» ({@see LatestPostsBlockType}).
 *
 * Выборка не кэшируется, как и остальные ленты блога на фронте: пост виден, пока опубликован он сам
 * и вся цепочка его рубрик, и сбрасывать кэш пришлось бы на каждую правку поста и рубрики. Запрос
 * один (плюс `with('taxonomy')`) и идёт по индексу `created_at`.
 *
 * Создаётся DI-контейнером модулем блоков при выводе блока.
 */
final class LatestPostsData implements BlockDataProvider
{
    public function __construct(private readonly PostReadRepository $posts)
    {
    }

    /**
     * @param array{taxonomy:string, withDescendants:bool, limit:string} $data
     * @return array{posts: Post[]}
     */
    public function provide(array $data): array
    {
        $taxonomy = (string)($data['taxonomy'] ?? '');
        $limit = max(1, min(24, (int)($data['limit'] ?? 3)));

        return [
            'posts' => $this->posts->latest(
                $limit,
                ctype_digit($taxonomy) ? (int)$taxonomy : null,
                (bool)($data['withDescendants'] ?? true),
            ),
        ];
    }
}
