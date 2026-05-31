<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\forms\backend;

use Besnovatyj\Helpers\StringHelper;
use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Forms\CompositeForm;
use Besnovatyj\Meta\MetaForm;
use Besnovatyj\Blog\forms\backend\TagsForm;
use Besnovatyj\Blog\forms\backend\TaxonomiesForm;
use yii\web\UploadedFile;

/**
 * @property TaxonomiesForm $taxonomies
 * @property MetaForm $meta
 * @property TagsForm $tags
 */
class PostForm extends CompositeForm
{
    public $title;
    public $description;
    public $content;
    public $photo;
    public $comments_allowed;

    public function __construct(?Post $post = null, $config = [])
    {
        if ($post) {
            $this->title = $post->title;
            $this->description = $post->description;
            $this->content = $post->content;
            $this->comments_allowed = $post->comments_allowed;
            $this->taxonomies = new TaxonomiesForm($post);
            $this->meta = new MetaForm($post->meta);
            $this->tags = new TagsForm($post);
        } else {
            $this->taxonomies = new TaxonomiesForm();
            $this->meta = new MetaForm();
            $this->tags = new TagsForm();
            $this->comments_allowed = Post::COMMENTS_DISABLED;
        }
        parent::__construct($config);
    }

    public function beforeValidate(): bool
    {
        $this->title = StringHelper::spaceReplace($this->title);
        $this->description = StringHelper::spaceReplace($this->description);
        if (parent::beforeValidate()) {
            $this->photo = UploadedFile::getInstance($this, 'photo');
            return true;
        }
        return false;
    }

    public function rules(): array
    {
        return [
            [['title', 'comments_allowed'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['comments_allowed'], 'integer'],
            [['description', 'content'], 'string'],
            // TODO Добавить проверку MIME-типа - MimeCheckHelper
            [['photo'], 'image',
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg, jpeg, webp',
                'minWidth' => '100',
                'maxWidth' => '5000',
                'minHeight' => '100',
                'maxHeight' => '5000',
            ],
        ];
    }

    protected function internalForms(): array
    {
        return ['meta', 'tags', 'taxonomies'];
    }

}
