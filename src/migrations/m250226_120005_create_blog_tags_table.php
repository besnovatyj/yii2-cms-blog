<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<Name>' */
class m250226_120005_create_blog_tags_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%blog_tags}}';

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
            'name' => $this->string(255)->notNull()
                ->comment('Название тега'),
            'slug' => $this->string(255)->notNull()
                ->comment('Slug тега'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Теги поста');

        $this->createIndexes(static::TABLE_NAME, 'slug', false, true);

        parent::safeUp();
    }

}
