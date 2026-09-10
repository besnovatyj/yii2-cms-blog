<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\entities\queries;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;

/* @see \Besnovatyj\Blog\entities\taxonomy\Taxonomy */
class TaxonomyQuery extends ActiveQuery
{
    /**
     * Раздел опубликован сам по себе (без учёта дерева).
     *
     * Для фронтенда этого недостаточно: раздел внутри скрытого родителя тоже не должен быть
     * доступен — см. {@see visible()}.
     */
    public function active(?string $alias = null): static
    {
        return $this->andWhere([($alias ?? Taxonomy::tableName()) . '.status' => Taxonomy::STATUS_ACTIVE]);
    }

    /**
     * Раздел доступен анонимному посетителю: опубликован сам и не спрятан ни одним из предков.
     *
     * Скрытие родителя обязано скрывать всю ветку — иначе дочерний раздел остаётся открыт по
     * прямой ссылке, хотя из навигации он исчез. Проверка идёт по ключам Nested Sets одним
     * подзапросом: у видимого узла не должно существовать предка со снятой публикацией.
     *
     * Проверяются предки ЛЮБОГО уровня, включая корневые (`depth = 0`): корень — такой же
     * полноценный раздел с редактируемым статусом, а не служебный контейнер (прежнее исключение
     * корня было наследием старой схемы дерева и оставляло ветку скрытого корня видимой).
     */
    public function visible(?string $alias = null): static
    {
        $table = Taxonomy::tableName();
        $self = $alias ?? $table;

        $hiddenAncestor = (new Query())
            ->select(new Expression('1'))
            ->from(['anc' => $table])
            ->where(new Expression(
                "anc.[[tree]] = {$self}.[[tree]] AND anc.[[lft]] < {$self}.[[lft]] AND anc.[[rgt]] > {$self}.[[rgt]]",
            ))
            ->andWhere(['<>', 'anc.status', Taxonomy::STATUS_ACTIVE]);

        return $this->active($alias)->andWhere(['not exists', $hiddenAncestor]);
    }
}
