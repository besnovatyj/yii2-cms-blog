<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\readModels;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Contracts\search\SearchDocument;
use Besnovatyj\Contracts\sitemap\SitemapUrl;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\helpers\ArrayHelper;

/**
 * Чтение разделов блога ДЛЯ ФРОНТЕНДА.
 *
 * Отдаёт только видимые разделы: опубликованные и не спрятанные ни одним из предков
 * (см. {@see \Besnovatyj\Blog\entities\queries\TaxonomyQuery::visible()}). Скрытый раздел не
 * должен попадать ни в меню виджета, ни в выдачу поиска, ни открываться по прямой ссылке.
 *
 * Выборки без фильтра публикации, нужные админке и построению URL, — в
 * {@see \Besnovatyj\Blog\repositories\TaxonomyRepository}.
 *
 * Исключение — {@see pathTo()}: он собирает ЧПУ-путь по дереву и обязан работать для любого
 * раздела, иначе в админке не построить ссылку на скрытую страницу.
 */
class TaxonomyReadRepository
{
    private TreeQueryScope $treeScope;

    public function __construct()
    {
        $this->treeScope = new TreeQueryScope(Taxonomy::class);
    }

    /**
     * @return Taxonomy[]
     */
    public function getAll(): array
    {
        return Taxonomy::find()->visible()->orderBy('lft')->all();
    }

    public function getAllAsArray(): array
    {
        return Taxonomy::find()->visible()->orderBy('lft')->asArray()->all();
    }

    public function find(int $id): ?Taxonomy
    {
        return Taxonomy::find()->visible()->andWhere(['id' => $id])->one();
    }

    /**
     * @param string $slug
     * @return Taxonomy|null
     */
    public function findBySlug(string $slug): ?Taxonomy
    {
        return Taxonomy::find()->visible()->andWhere(['slug' => $slug])->one();
    }

    /**
     * ЧПУ-путь таксономии: слаги предков (без виртуального корня depth=0) и самого узла через «/».
     * Используется {@see \Besnovatyj\Blog\urls\TaxonomyUrlRule} для разбора/генерации ЧПУ-адресов.
     */
    public function pathTo(Taxonomy $taxonomy): string
    {
        $nodes = $this->treeScope->parentsQuery($taxonomy, andSelf: true)
            ->andWhere(['>', 'depth', 0])
            ->all();

        return implode('/', ArrayHelper::getColumn($nodes, 'slug'));
    }

    /**
     * Разделы блога для сквозного поиска.
     *
     * В индекс идут только видимые разделы — те же, что показывает фронтенд: раздел опубликован
     * и не спрятан ни одним из предков. Скрытый раздел не должен находиться поиском, иначе
     * снятие с публикации перестаёт что-либо значить.
     *
     * @return iterable<SearchDocument>
     */
    /**
     * Видимые разделы блога для карты сайта.
     *
     * Обход в порядке дерева (`tree`, `lft`) и глубина узла отдаются как есть: человеческая карта
     * рисует по ним отступ, а строить вложенные списки провайдеру не приходится — это забота
     * представления.
     *
     * Отпечатка свежести у разделов нет: колонок времени в дереве не заведено, а суррогат вроде
     * `MAX(rgt)` не заметил бы переименования. Разделов немного, полный обход дёшев.
     *
     * @return iterable<SitemapUrl>
     */
    public function sitemapUrls(): iterable
    {
        $query = Taxonomy::find()->visible()->orderBy(['tree' => SORT_ASC, 'lft' => SORT_ASC]);

        /** @var Taxonomy $taxonomy */
        foreach ($query->each(200) as $taxonomy) {
            yield new SitemapUrl(
                route: '/Blog/post/taxonomy',
                params: ['slug' => $taxonomy->slug],
                title: (string)$taxonomy->name,
                depth: (int)$taxonomy->depth,
            );
        }
    }

    public function searchDocuments(): iterable
    {
        $query = Taxonomy::find()->visible()->orderBy(['id' => SORT_ASC]);

        /** @var Taxonomy $taxonomy */
        foreach ($query->each(100) as $taxonomy) {
            yield new SearchDocument(
                type: 'blog.taxonomy',
                entityId: (int)$taxonomy->id,
                route: '/Blog/post/taxonomy',
                params: ['slug' => $taxonomy->slug],
                title: (string)$taxonomy->name,
                text: (string)$taxonomy->description,
            );
        }
    }

    public function getTreeWithSubsOf(?Taxonomy $taxonomy = null): array
    { // TODO - JOIN - blog_posts - count()
        $query = Taxonomy::find()->visible()->orderBy('lft');
        if ($taxonomy) {
            $parents = $this->treeScope->parentsQuery($taxonomy)->all();
            $criteria = ['or', ['depth' => 2]];
            foreach (ArrayHelper::merge([$taxonomy], $parents) as $item) {
                $criteria[] = ['and', ['>', 'lft', $item->lft], ['<', 'rgt', $item->rgt], ['depth' => $item->depth + 1]];
            }
            $query->andWhere($criteria);
        } else {
            $query->andWhere(['depth' => 1]);
        }

        return $query->all();
    }
}
