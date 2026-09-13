<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\repositories;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

class TaxonomyRepository
{

    public function get($id): Taxonomy
    {
        if (!$taxonomy = Taxonomy::findOne($id)) {
            throw new NotFoundException('Taxonomy is not found.');
        }
        return $taxonomy;
    }

    /**
     * @throws Exception
     */
    public function save(Taxonomy $taxonomy): void
    {
        if (!$taxonomy->save()) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(Taxonomy $taxonomy): void
    {
        if (!$taxonomy->delete()) {
            throw new \RuntimeException('Removing error.');
        }
    }

    /**
     * Все разделы, включая скрытые, — для админки и построения URL.
     *
     * Фронтовый аналог с фильтром видимости —
     * {@see \Besnovatyj\Blog\readModels\TaxonomyReadRepository::getAll()}.
     *
     * @return Taxonomy[]
     */
    public function getAll(): array
    {
        return Taxonomy::find()->orderBy('lft')->all();
    }

    /**
     * То же в виде массивов — для выпадающих списков админки.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllAsArray(): array
    {
        return Taxonomy::find()->orderBy('lft')->asArray()->all();
    }

    /**
     * Раздел по slug без учёта публикации.
     *
     * Нужен разбору и генерации ЧПУ: маршрут обязан строиться и для скрытого раздела, иначе в
     * админке не получить ссылку на него. Отсечение от анонимного посетителя выполняет фронтовый
     * {@see \Besnovatyj\Blog\readModels\TaxonomyReadRepository::findBySlug()} — он вернёт null,
     * и контроллер отдаст 404.
     */
    public function findBySlug(string $slug): ?Taxonomy
    {
        return Taxonomy::find()->andWhere(['slug' => $slug])->one();
    }

    /**
     * Раздел по id без учёта публикации — для админки и построения URL.
     */
    public function find(int $id): ?Taxonomy
    {
        return Taxonomy::find()->andWhere(['id' => $id])->one();
    }

}
