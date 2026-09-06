<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\readModels;

use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use DomainException;
use Exception;
use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\entities\Tag;
use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\forms\frontend\search\SearchForm;
use Besnovatyj\Contracts\search\SearchDocument;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;

class PostReadRepository
{
    private TreeQueryScope $treeScope;

    public function __construct()
    {
        $this->treeScope = new TreeQueryScope(Taxonomy::class);
    }

    public function count(): int
    {
        return Post::find()->active()->count();
    }

    public function getAllByRange($offset, $limit): array
    {
        return Post::find()->active()->orderBy(['id' => SORT_ASC])->limit($limit)->offset($offset)->all();
    }

    public function getAll(): DataProviderInterface
    {
        $query = Post::find()->active()->orderBy(['pinned' => SORT_DESC])->with(['taxonomy', 'tags']);
        return $this->getProvider($query);
    }

    /**
     * Простой полнотекстовый поиск по активным постам (title/description/content) через LIKE.
     * Базовая реализация для фронтового поиска; пустой запрос возвращает все активные посты.
     * Значение идёт в параметризованный `like`-предикат (экранируется), длина ограничена формой.
     */
    public function search(SearchForm $form): DataProviderInterface
    {
        $query = Post::find()->active()->orderBy(['pinned' => SORT_DESC])->with(['taxonomy', 'tags']);

        $text = trim((string)$form->text);
        if ($text !== '') {
            $query->andWhere(['or',
                ['like', 'title', $text],
                ['like', 'description', $text],
                ['like', 'content', $text],
            ]);
        }

        return $this->getProvider($query);
    }

    public function getAllByTaxonomy(Taxonomy $taxonomy): DataProviderInterface
    {
        $query = Post::find()->alias('p')->active('p')->orderBy(['pinned' => SORT_DESC])->with('taxonomy');
        $ids = $this->treeScope->descendantIds($taxonomy, andSelf: true);
        $query->joinWith(['taxonomyAssignments ta'], false);
        $query->andWhere(['or', ['p.taxonomy_id' => $ids], ['ta.taxonomy_id' => $ids]]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

    public function getAllByTag(Tag $tag): DataProviderInterface
    {
        $query = Post::find()->alias('p')->with('taxonomy');
        $query->joinWith(['tagAssignments ta'], false);
        $query->andWhere(['ta.tag_id' => $tag->id]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

    public function getAllByOtherTaxonomy(Taxonomy $taxonomy): DataProviderInterface
    {
        // для backend ищем и неактивные тоже
        $query = Post::find()->alias('p')->with('taxonomy');
        $query->joinWith(['taxonomyAssignments ta'], false);
        $query->andWhere(['ta.taxonomy_id' => $taxonomy->id]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

    public function getLast($limit): array
    {
        return Post::find()->active()->with('taxonomy')->orderBy(['id' => SORT_DESC])->limit($limit)->all();
    }

    public function getLastUpdated($limit): array
    {
        return Post::find()->orderBy(['updated_at' => SORT_DESC])->limit($limit)->all();
    }

    public function getPopularByComments($limit): array
    {
        return Post::find()->active()->with('taxonomy')->orderBy(['comments_count' => SORT_DESC])->limit($limit)->all();
    }

    public function getPopularByViews($limit): array
    {
        return Post::find()->active()->orderBy(['views' => SORT_DESC])->limit($limit)->all();
    }

    public function getPinned(): array
    {
        return Post::find()->andWhere(['pinned' => Post::PINNED])->all();
    }

    public function getDrafted(): array
    {
        return Post::find()->andWhere(['status' => Post::STATUS_DRAFT])->all();
    }

    public function find($id): ?Post
    {
        $post = Post::find()->active()->andWhere(['id' => $id])->one();
        if ($post instanceof Post) {
            return $post;
        }
        return null;
    }

    /**
     * Опубликованные посты для сквозного поиска.
     *
     * В индекс идут только активные записи — то же, что видит анонимный посетитель. Теги и
     * название раздела кладутся в ключевые слова: по ним ищут, но в карточке выдачи они не нужны.
     * Связи подгружаются пачкой (`with`), иначе на каждый пост уходило бы по два лишних запроса.
     *
     * @return iterable<SearchDocument>
     */
    public function searchDocuments(): iterable
    {
        $query = Post::find()->active()
            ->with(['tags', 'taxonomy'])
            ->orderBy(['id' => SORT_ASC]);

        /** @var Post $post */
        foreach ($query->each(100) as $post) {
            $keywords = array_map(static fn (Tag $tag): string => (string)$tag->name, $post->tags);

            if ($post->taxonomy !== null) {
                $keywords[] = (string)$post->taxonomy->name;
            }

            yield new SearchDocument(
                type: 'blog.post',
                entityId: (int)$post->id,
                route: '/Blog/post/view',
                params: ['id' => (int)$post->id],
                title: (string)$post->title,
                text: (string)$post->content,
                keywords: implode(' ', $keywords),
                excerpt: $post->description,
                date: (int)$post->created_at,
                image: $post->getThumbUrl('photo', 'blog_list'),
                // Закреплённые посты и в поиске должны идти чуть выше при равной релевантности.
                boost: (int)$post->pinned === Post::PINNED ? 1.3 : 1.0,
            );
        }
    }

    private function getProvider(ActiveQuery $query): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $query,
//            'sort' => false,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ]
        ]);
    }
}
