<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\repositories;

use Besnovatyj\Blog\entities\Tag;
use Besnovatyj\Blog\entities\TagAssignment;
use Besnovatyj\Blog\repositories\NotFoundException;
use RuntimeException;
use Throwable;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\Exception;
use yii\db\StaleObjectException;

class TagRepository
{
    public function get(int $id): Tag
    {
        if (!$tag = Tag::findOne($id)) {
            throw new NotFoundException('Tag is not found.');
        }
        return $tag;
    }

    public function findByName(string $name): ?Tag
    {
        return Tag::findOne(['name' => $name]);
    }

    public function findBySlug(string $slug): ?Tag
    {
        return Tag::findOne(['slug' => $slug]);
    }

    /**
     * @throws Exception
     */
    public function save(Tag $tag): void
    {
        if (!$tag->save()) {
            throw new RuntimeException('Saving error.');
        }
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(Tag $tag): void
    {
        if (!$tag->delete()) {
            throw new RuntimeException('Removing error.');
        }
    }

    public function searchEmptyTags(): DataProviderInterface
    {
        $subQuery = TagAssignment::find()->select(['tag_id']);
        $query = Tag::find()->where(['not in', 'id', $subQuery])->orderBy('id');
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ]
        ]);
    }

    public function deleteEmptyTags(): int
    {
        $subQuery = TagAssignment::find()->select(['tag_id']);
        return Tag::deleteAll(['not in', 'id', $subQuery]);
    }
}
