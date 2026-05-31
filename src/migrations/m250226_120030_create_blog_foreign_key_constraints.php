<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\migrations;

use common\components\migration\BaseMigration;
use Yii;
use yii\db\Exception;

class m250226_120030_create_blog_foreign_key_constraints extends BaseMigration
{

    /**
     * @throws Exception
     */
    public function safeUp(): void
    {
        parent::safeUp();

        Yii::$app->getDb()->createCommand("SET foreign_key_checks = 0")->execute();

        // Посты
        $this->createFKs(
            m250226_120010_create_blog_posts_table::TABLE_NAME,
            'taxonomy_id',
            m250226_120000_create_blog_taxonomy_table::TABLE_NAME,
            'id',
            'SET NULL');

        // Комментарии
        $this->createFKs(
            m250226_120015_create_blog_comments_table::TABLE_NAME,
            'parent_id',
            m250226_120015_create_blog_comments_table::TABLE_NAME,
            'id');
        $this->createFKs(
            m250226_120015_create_blog_comments_table::TABLE_NAME,
            'post_id',
            m250226_120010_create_blog_posts_table::TABLE_NAME,
            'id');
        $this->createFKs(
            m250226_120015_create_blog_comments_table::TABLE_NAME,
            'user_id',
            '{{%user_users}}',
            'id',
            'CASCADE');

        // Связь поста с тегами
        $this->createFKs(
            m250226_120020_create_blog_tag_asgmt_table::TABLE_NAME,
            'post_id',
            m250226_120010_create_blog_posts_table::TABLE_NAME,
            'id',
            'CASCADE');
        $this->createFKs(
            m250226_120020_create_blog_tag_asgmt_table::TABLE_NAME,
            'tag_id',
            m250226_120005_create_blog_tags_table::TABLE_NAME,
            'id',
            'CASCADE');

        // Связь поста с категориями
        $this->createFKs(
            m250226_120025_create_blog_taxonomy_asgmt_table::TABLE_NAME,
            'post_id',
            m250226_120010_create_blog_posts_table::TABLE_NAME,
            'id',
            'CASCADE');
        $this->createFKs(
            m250226_120025_create_blog_taxonomy_asgmt_table::TABLE_NAME,
            'taxonomy_id',
            m250226_120000_create_blog_taxonomy_table::TABLE_NAME,
            'id',
            'CASCADE');

        Yii::$app->db->createCommand('SET foreign_key_checks = 1')->execute();

    }

    public function safeDown(): void
    {
        // Отменяем действия по умолчанию,
        // так как \common\components\migration\BaseMigration::safeDown() вызывает static::TABLE_NAME,
        // которого в данной миграции не существует.
        // Так же, \common\components\migration\BaseMigration::safeDown() при удалении таблиц сам удалит у них все индексы и внешние ключи.

        // parent::safeDown();
    }

}
