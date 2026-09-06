<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\repositories;

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\entities\Tag;
use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\repositories\NotFoundException;
use Throwable;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;
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

    /**
     * Посты тега — для админки: без фильтра публикации и видимости раздела.
     *
     * Фронтовый аналог — {@see \Besnovatyj\Blog\readModels\PostReadRepository::getAllByTag()},
     * он показывает только доступное анонимному посетителю. Методы намеренно разные: админка
     * обязана видеть скрытое, фронт — не должен.
     */
    public function getAllByTag(Tag $tag): DataProviderInterface
    {
        $query = Post::find()->alias('p')->with('taxonomy');
        $query->joinWith(['tagAssignments ta'], false);
        $query->andWhere(['ta.tag_id' => $tag->id]);
        $query->groupBy('p.id');

        return $this->getProvider($query);
    }

    /**
     * Посты, привязанные к разделу дополнительной связью (не основной таксономией) — для админки.
     */
    public function getAllByOtherTaxonomy(Taxonomy $taxonomy): DataProviderInterface
    {
        $query = Post::find()->alias('p')->with('taxonomy');
        $query->joinWith(['taxonomyAssignments ta'], false);
        $query->andWhere(['ta.taxonomy_id' => $taxonomy->id]);
        $query->groupBy('p.id');

        return $this->getProvider($query);
    }

    /**
     * Последние изменённые посты — плитка дашборда админки.
     *
     * @return Post[]
     */
    public function getLastUpdated(int $limit): array
    {
        return Post::find()->orderBy(['updated_at' => SORT_DESC])->limit($limit)->all();
    }

    /**
     * Закреплённые посты — плитка дашборда админки.
     *
     * @return Post[]
     */
    public function getPinned(): array
    {
        return Post::find()->andWhere(['pinned' => Post::PINNED])->all();
    }

    /**
     * Черновики — плитка дашборда админки.
     *
     * @return Post[]
     */
    public function getDrafted(): array
    {
        return Post::find()->andWhere(['status' => Post::STATUS_DRAFT])->all();
    }

    private function getProvider(ActiveQuery $query): DataProviderInterface
    {
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
        ]);
    }
}
