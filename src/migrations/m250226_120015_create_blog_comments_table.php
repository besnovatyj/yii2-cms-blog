<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<n>' */
class m250226_120015_create_blog_comments_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%blog_comments}}';

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
            'post_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор поста'),
            'user_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор пользователя'),
            'parent_id' => $this->integer(10)->null()
                ->comment('Идентификатор родительского комментария'),
            'created_at' => $this->dateTime()->null()->defaultExpression('NOW()')
                ->comment('Дата и время создания комментария'),
            'text' => $this->text()->notNull()
                ->comment('Содержимое комментария'),
            'active' => $this->smallInteger(1)->notNull()->defaultValue(0)
                ->comment('Статус комментария'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Комментарии блога');

        $this->createIndexes(static::TABLE_NAME, 'post_id');
        $this->createIndexes(static::TABLE_NAME, 'user_id');
        $this->createIndexes(static::TABLE_NAME, 'parent_id');

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
