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
use Besnovatyj\Contracts\sitemap\SitemapUrl;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;

/**
 * Чтение постов ДЛЯ ФРОНТЕНДА.
 *
 * Каждый метод обязан отдавать только то, что доступно анонимному посетителю: пост опубликован
 * и лежит в видимом разделе (см. {@see \Besnovatyj\Blog\entities\queries\PostQuery::visible()}).
 * Выборки для админки, которым положено видеть черновики и скрытые разделы, живут в
 * {@see \Besnovatyj\Blog\repositories\PostRepository} — смешивать их здесь нельзя: один и тот же
 * метод на два контекста рано или поздно утекает скрытым контентом на публичную страницу.
 */
class PostReadRepository
{
    private TreeQueryScope $treeScope;

    public function __construct()
    {
        $this->treeScope = new TreeQueryScope(Taxonomy::class);
    }

    public function count(): int
    {
        return Post::find()->visible()->count();
    }

    public function getAllByRange($offset, $limit): array
    {
        return Post::find()->visible()->orderBy(['id' => SORT_ASC])->limit($limit)->offset($offset)->all();
    }

    public function getAll(): DataProviderInterface
    {
        $query = Post::find()->visible()->orderBy(['pinned' => SORT_DESC])->with(['taxonomy', 'tags']);
        return $this->getProvider($query);
    }

    /**
     * Простой полнотекстовый поиск по активным постам (title/description/content) через LIKE.
     * Базовая реализация для фронтового поиска; пустой запрос возвращает все активные посты.
     * Значение идёт в параметризованный `like`-предикат (экранируется), длина ограничена формой.
     */
    public function search(SearchForm $form): DataProviderInterface
    {
        $query = Post::find()->visible()->orderBy(['pinned' => SORT_DESC])->with(['taxonomy', 'tags']);

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
        $query = Post::find()->alias('p')->visible('p')->orderBy(['pinned' => SORT_DESC])->with('taxonomy');
        $ids = $this->treeScope->descendantIds($taxonomy, andSelf: true);
        $query->joinWith(['taxonomyAssignments ta'], false);
        $query->andWhere(['or', ['p.taxonomy_id' => $ids], ['ta.taxonomy_id' => $ids]]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

    public function getAllByTag(Tag $tag): DataProviderInterface
    {
        $query = Post::find()->alias('p')->visible('p')->with('taxonomy');
        $query->joinWith(['tagAssignments ta'], false);
        $query->andWhere(['ta.tag_id' => $tag->id]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

    public function getAllByOtherTaxonomy(Taxonomy $taxonomy): DataProviderInterface
    {
        $query = Post::find()->alias('p')->visible('p')->with('taxonomy');
        $query->joinWith(['taxonomyAssignments ta'], false);
        $query->andWhere(['ta.taxonomy_id' => $taxonomy->id]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

    public function getLast($limit): array
    {
        return Post::find()->visible()->with('taxonomy')->orderBy(['id' => SORT_DESC])->limit($limit)->all();
    }

    public function getLastUpdated($limit): array
    {
        return Post::find()->visible()->orderBy(['updated_at' => SORT_DESC])->limit($limit)->all();
    }

    public function getPopularByComments($limit): array
    {
        return Post::find()->visible()->with('taxonomy')->orderBy(['comments_count' => SORT_DESC])->limit($limit)->all();
    }

    public function getPopularByViews($limit): array
    {
        return Post::find()->visible()->orderBy(['views' => SORT_DESC])->limit($limit)->all();
    }

    public function getPinned(): array
    {
        return Post::find()->visible()->andWhere(['pinned' => Post::PINNED])->all();
    }

    public function find($id): ?Post
    {
        $post = Post::find()->visible()->andWhere(['id' => $id])->one();
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
    /**
     * Опубликованные посты для карты сайта.
     *
     * Тот же инвариант, что у поиска, — только публичное. Отличается набор полей: карте нужна дата
     * ИЗМЕНЕНИЯ (`updated_at`), по которой краулер решает, перечитывать ли страницу, а поиску — текст
     * и дата публикации. Поэтому два тонких метода поверх одной выборки, а не один «универсальный».
     *
     * Свежие посты идут первыми: если карта раздела не поместится в один файл, в первой части
     * окажется самое новое — краулер увидит его раньше.
     *
     * @return iterable<SitemapUrl>
     */
    public function sitemapUrls(): iterable
    {
        $query = Post::find()->visible()->orderBy(['id' => SORT_DESC]);

        /** @var Post $post */
        foreach ($query->each(200) as $post) {
            yield new SitemapUrl(
                route: '/Blog/post/view',
                params: ['id' => (int)$post->id],
                title: (string)$post->title,
                // updated_at — колонка DATETIME, а контракт ждёт Unix-timestamp.
                lastModified: $post->updated_at === null ? null : (strtotime((string)$post->updated_at) ?: null),
                // Закреплённый пост — витрина блога, ему уместен вес выше рядового.
                priority: (int)$post->pinned === Post::PINNED ? 0.8 : null,
            );
        }
    }

    /**
     * Отпечаток состояния постов для карты сайта: сколько их и когда правили последний раз.
     *
     * Одного `MAX(updated_at)` мало — он не замечает удаления поста, а удалённый пост обязан
     * исчезнуть из карты. Пара «сколько + когда» это закрывает и стоит одного запроса.
     */
    public function sitemapRevision(): string
    {
        $row = Post::find()->visible()
            ->select(['total' => 'COUNT(*)', 'latest' => 'MAX(updated_at)'])
            ->asArray()
            ->one();

        return ((string)($row['total'] ?? '0')) . ':' . ((string)($row['latest'] ?? ''));
    }

    public function searchDocuments(): iterable
    {
        $query = Post::find()->visible()
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
                // `created_at` — колонка DATETIME, а контракт ждёт Unix-timestamp. Приведение
                // (int) молча давало год («2020-05-14 12:00:00» → 2020), то есть 1 января 1970-го
                // в каждой карточке выдачи и бессмысленную сортировку по свежести.
                date: $post->created_at === null ? null : (strtotime((string)$post->created_at) ?: null),
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
