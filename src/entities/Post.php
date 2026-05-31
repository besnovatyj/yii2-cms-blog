<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\entities;

use Besnovatyj\Blog\entities\queries\PostQuery;
use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\entities\taxonomy\TaxonomyAssignment;
use Besnovatyj\Helpers\FilesystemHelper;
use Besnovatyj\Meta\Meta;
use Besnovatyj\Meta\MetaBehavior;
use Besnovatyj\Upload\heap\ThumbnailMode;
use Besnovatyj\Upload\heap\ThumbnailProfile;
use Besnovatyj\Upload\heap\UploadBehavior;
use DomainException;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * @property integer $id
 * @property integer $taxonomy_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $title
 * @property string $description
 * @property string $content
 * @property string $photo
 * @property integer $status
 * @property integer $comments_count
 * @property integer $views
 * @property integer $comments_allowed
 * @property integer $pinned
 *
 * @property Taxonomy $taxonomy
 * @property Taxonomy[] $taxonomies
 * @property TaxonomyAssignment[] $taxonomyAssignments
 * @property Meta $meta
 * @property TagAssignment[] $tagAssignments
 * @property Tag[] $tags
 * @property Comment[] $comments
 *
 * @mixin UploadBehavior
 */
class Post extends ActiveRecord
{
    public const int STATUS_DRAFT = 0;
    public const int STATUS_ACTIVE = 1;

    public const int COMMENTS_ALLOWED = 1;
    public const int COMMENTS_DISABLED = 0;

    public const int UNPINNED = 0;
    public const int PINNED = 1;

    public Meta $meta;

    public static function create($taxonomyId, $title, $description, $content, $created_at, $comments_allowed, Meta $meta): self
    {
        $post = new static();
        $post->taxonomy_id = $taxonomyId;
        $post->title = $title;
        $post->description = $description;
        $post->content = $content;
        $post->created_at = $created_at;
        $post->updated_at = $post->created_at;
        $post->comments_count = 0;
        $post->comments_allowed = $comments_allowed;
        $post->status = self::STATUS_DRAFT;
        $post->meta = $meta;
        return $post;
    }

    public function edit($title, $description, $content, $comments_allowed, $updated_at, Meta $meta): void
    {
        $this->title = $title;
        $this->description = $description;
        $this->content = $content;
        $this->comments_allowed = $comments_allowed;
        $this->updated_at = $updated_at;
        $this->meta = $meta;
    }

    public function changeMainTaxonomy($taxonomy_id): void
    {
        $this->taxonomy_id = $taxonomy_id;
    }

    // <editor-fold desc="Statuses and flags">

    public function activate(): void
    {
        if ($this->isActive()) {
            throw new DomainException('Пост уже активен.');
        }
        $this->status = self::STATUS_ACTIVE;
    }

    public function draft(): void
    {
        if ($this->isDraft()) {
            throw new DomainException('Пост уже не активен.');
        }
        $this->status = self::STATUS_DRAFT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function pinToggle(): void
    {
        if ($this->pinned === self::PINNED) {
            $this->pinned = self::UNPINNED;
        } else {
            $this->pinned = self::PINNED;
        }
    }

    public function isPinned(): bool
    {
        return $this->pinned === self::PINNED;
    }

    public function isCommentsAllowed(): int
    {
        return $this->comments_allowed;
    }

    public function disableComments(): void
    {
        $this->comments_allowed = static::COMMENTS_DISABLED;
    }

    public function allowComments(): void
    {
        $this->comments_allowed = static::COMMENTS_ALLOWED;
    }

    // </editor-fold>

    public function getSeoTitle(): string
    {
        return $this->meta->title ?: $this->title;
    }

    public function setPhoto(UploadedFile $photo): void
    {
        $this->photo = $photo;
    }

    public function refreshViewCounter(): void
    {
        $this->updateCounters(['views' => +1]);
    }

    public function updateCommentsCount(): void
    {
        $this->comments_count = (int)$this->getComments()
            ->andWhere(['active' => Comment::STATUS_ACTIVE])
            ->count();
    }

    // <editor-fold desc="Relations">

    public function getTagAssignments(): ActiveQuery
    {
        return $this->hasMany(TagAssignment::class, ['post_id' => 'id']);
    }

    public function getTags(): ActiveQuery
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->via('tagAssignments');
    }

    public function getComments(): ActiveQuery
    {
        return $this->hasMany(Comment::class, ['post_id' => 'id']);
    }

    public function getTaxonomy(): ActiveQuery
    {
        return $this->hasOne(Taxonomy::class, ['id' => 'taxonomy_id']);
    }

    public function getTaxonomyAssignments(): ActiveQuery
    {
        return $this->hasMany(TaxonomyAssignment::class, ['post_id' => 'id']);
    }

    public function getTaxonomies(): ActiveQuery
    {
        return $this->hasMany(Taxonomy::class, ['id' => 'taxonomy_id'])->via('taxonomyAssignments');
    }

    // </editor-fold>

    // <editor-fold desc="Events">

    /**
     * @throws \yii\base\Exception
     */
    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);
        FilesystemHelper::createDirectoryRecursively(Yii::getAlias('@static/origin/Blog/' . $this->id));
    }

    /**
     * @throws Exception
     */
    public function beforeDelete(): bool
    {
        if (parent::beforeDelete()) {
            if (is_dir($img_dir = \Yii::getAlias('@static/origin/Blog/' . $this->id))) {
                FilesystemHelper::deleteDirContents($img_dir, true);
            }
            return true;
        }
        return false;
    }

    // </editor-fold>

    public static function tableName(): string
    {
        return '{{%blog_posts}}';
    }

    public function behaviors(): array
    {
        return [
            MetaBehavior::class,
            'photoUpload' => [
                // TODO Сделать уже передачу размеров через параметры запроса изображения, или какое-нибудь конфигурирование, чтобы не лезть сюда в сущность каждый раз!!!
                'class' => UploadBehavior::class,
                'attribute' => 'photo',
                'pathTemplate' => 'origin/Blog/{pk}/{basename}',
                'thumbnails' => [
                    new ThumbnailProfile('admin', width: 100, height: 57, quality: 80, mode: ThumbnailMode::Crop),
                    new ThumbnailProfile('admin_view', width: 350, height: 350, mode: ThumbnailMode::Resize),
                    new ThumbnailProfile('blog_list', width: 350, height: 350, mode: ThumbnailMode::Crop),
                ],
                'thumbPathTemplate' => 'cache/Blog/{pk}/{filename}_{profile}.{extension}',
            ],
            ...parent::behaviors(),
        ];
    }

    public function transactions(): array
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public static function find(): PostQuery
    {
        return new PostQuery(static::class);
    }

}
