<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\readModels;

use Besnovatyj\Blog\entities\Tag;
use Besnovatyj\Blog\entities\TagAssignment;

class TagReadRepository
{
    public function find($id): ?Tag
    {
        return Tag::findOne($id);
    }

    public function findMostPopular(int $count): array
    {
        /**
         * Запрос:
         * $subQuery = TagAssignment::find()->select(['tag_id', 'COUNT(tag_id) AS `frequency`'])->groupBy('tag_id')->orderBy(['frequency' => 'SORT_DESC'])->limit($count);
         * $tags = Tag::find()->where(['in', 'id', $subQuery])->orderBy('id')->all();
         * То же самое в виде SQL:
         * SELECT * FROM `blog_tags` WHERE `id` IN (SELECT `tag_id`, COUNT(tag_id) AS `frequency` FROM `blog_tag_asgmt` GROUP BY `tag_id` ORDER BY `frequency` LIMIT 6) ORDER BY `id`
         * не прокатывает, потому что `SQLSTATE[42000]: Syntax error or access violation: 1235 This version of MySQL doesn't yet support 'LIMIT & IN/ALL/ANY/SOME subquery'`.
         * Поэтому используем JOIN:
         * SELECT * FROM `blog_tags` JOIN (SELECT `tag_id`, COUNT(tag_id) AS `frequency` FROM `blog_tag_asgmt` GROUP BY `tag_id` ORDER BY `frequency` DESC LIMIT 6) t ON `blog_tags`.`id`=`t`.`tag_id`
         */

        $subQuery = TagAssignment::find()->alias('ta')
            ->select(['tag_id', 'COUNT(tag_id) AS frequency'])
            ->groupBy('ta.tag_id')
            ->orderBy(['frequency' => SORT_DESC])
            ->limit($count);

        $query = Tag::find()->alias('t')
            ->innerJoin(['f' => $subQuery], '`t`.`id` = `f`.`tag_id`');

        return $query->all();
    }

    public function findBySlug($slug): ?Tag
    {
        return Tag::find()->andWhere(['slug' => $slug])->one();
    }
}
