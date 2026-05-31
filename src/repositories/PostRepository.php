<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\repositories;

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\repositories\NotFoundException;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

class PostRepository
{
    public function get($id): Post
    {
        if (!$post = Post::findOne($id)) {
            throw new NotFoundException('Post is not found.');
        }
        return $post;
    }

    public function existsByTaxonomy($id): bool
    {
        return Post::find()->andWhere(['taxonomy_id' => $id])->exists();
    }

    /**
     * @throws Exception
     */
    public function save(Post $post): void
    {
        if (!$post->save()) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function existsByMainTaxonomy($id): bool
    {
        return Post::find()->andWhere(['taxonomy_id' => $id])->exists();
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function remove(Post $post): void
    {
        if (!$post->delete()) {
            throw new \RuntimeException('Removing error.');
        }
    }
}
