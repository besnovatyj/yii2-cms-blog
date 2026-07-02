<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<n>' */
class m250226_120020_create_blog_tag_asgmt_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%blog_tag_asgmt}}';

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
            'post_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор поста'),
            'tag_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор тега'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Связь поста с тегом');

        $this->createIndexes(static::TABLE_NAME, 'post_id');
        $this->createIndexes(static::TABLE_NAME, 'tag_id');
        $this->createIndexes(static::TABLE_NAME, ['post_id', 'tag_id'], true);

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
