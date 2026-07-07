<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\forms\backend;

use Besnovatyj\Helpers\StringHelper;
use Besnovatyj\Validators\SlugValidator;
use Besnovatyj\Blog\entities\Tag;
use yii\base\Model;
use yii\helpers\Inflector;

class TagForm extends Model
{
    public $name;
    public $slug;

    private $_tag;

    public function __construct(?Tag $tag = null, $config = [])
    {
        if ($tag) {
            $this->name = $tag->name;
            $this->slug = $tag->slug;
            $this->_tag = $tag;
        }
        parent::__construct($config);
    }

    public function beforeValidate(): bool
    {
        $this->name = StringHelper::spaceReplace($this->name);
        $this->slug = $this->slug ?: Inflector::slug($this->name);
        return parent::beforeValidate();
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name', 'slug'], 'string', 'max' => 255],
            ['slug', SlugValidator::class],
            [['name', 'slug'], 'unique', 'targetClass' => Tag::class, 'filter' => $this->_tag ? ['<>', 'id', $this->_tag->id] : null]
        ];
    }
}
