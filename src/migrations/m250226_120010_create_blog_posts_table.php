<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<n>' */
class m250226_120010_create_blog_posts_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%blog_posts}}';

    /**
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        parent::safeUp();

        if ($this->existTable(static::TABLE_NAME)) {
            return;
        }

        $this->createTable(static::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'taxonomy_id' => $this->integer(10)->null()
                ->comment('Идентификатор категории поста'),
            'created_at' => $this->dateTime()->null()->defaultExpression('NOW()')
                ->comment('Дата создания поста'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('NOW()')->append('ON UPDATE NOW()')
                ->comment('Дата последнего редактирования поста'),
            'title' => $this->string(255)->null()->defaultValue("Введите название поста")
                ->comment('Название поста'),
            'description' => $this->text()->null()
                ->comment('Краткое описание поста'),
            'content' => 'LONGTEXT NULL DEFAULT NULL',
            'photo' => $this->string(255)->null()
                ->comment('Главное изображение поста'),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(0)
                ->comment('Статус отображения поста'),
            'meta_json' => $this->text()->null()
                ->comment('JSON meta'),
            'comments_count' => $this->integer(10)->null()
                ->comment('Количество комментариев поста'),
            'comments_allowed' => $this->smallInteger(1)->notNull()->defaultValue(0)
                ->comment('Статус включения комментариев к посту'),
            'views' => $this->integer(10)->null()
                ->comment('Количество просмотров поста'),
            'pinned' => $this->smallInteger(1)->notNull()->defaultValue(0)
                ->comment('Статус закрепления поста'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Посты блога');
        $this->addCommentOnColumn(static::TABLE_NAME, 'content', 'Контент страницы');

        $this->createIndexes(static::TABLE_NAME, 'taxonomy_id');
        $this->createIndexes(static::TABLE_NAME, 'status');
        $this->createIndexes(static::TABLE_NAME, 'views');
        $this->createIndexes(static::TABLE_NAME, 'created_at');
        $this->createIndexes(static::TABLE_NAME, 'pinned');

        parent::safeUp();
    }

}
