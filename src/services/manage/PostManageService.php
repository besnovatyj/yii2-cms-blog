<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\services\manage;

use DateTimeImmutable;
use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\entities\Tag;
use Besnovatyj\Blog\entities\TagAssignment;
use Besnovatyj\Blog\entities\taxonomy\TaxonomyAssignment;
use Besnovatyj\Blog\forms\backend\PostForm;
use Besnovatyj\Blog\repositories\PostRepository;
use Besnovatyj\Blog\repositories\TagRepository;
use Besnovatyj\Blog\repositories\TaxonomyRepository;
use Besnovatyj\Meta\Meta;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

class PostManageService
{
    private PostRepository $posts;
    private TaxonomyRepository $taxonomies;
    private TagRepository $tags;

    public function __construct(
        PostRepository     $posts,
        TaxonomyRepository $taxonomies,
        TagRepository      $tags
    ) {
        $this->posts = $posts;
        $this->taxonomies = $taxonomies;
        $this->tags = $tags;
    }

    /**
     * @param PostForm $form
     * @return Post
     * @throws Exception
     * @throws Throwable
     */
    public function create(PostForm $form): Post
    {
        // Подтверждение того что такая категория существует.
        $mainTaxonomy = $this->taxonomies->get($form->taxonomies->main);

        $post = Post::create(
            $mainTaxonomy->id,
            $form->title,
            $form->description,
            $form->content,
            new DateTimeImmutable()->format('Y.m.d H:i:s'),
            $form->comments_allowed,
            new Meta(
                $form->meta->title,
                $form->meta->description,
                $form->meta->keywords
            )
        );

        if ($form->photo) {
            $post->setPhoto($form->photo);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->posts->save($post);

            $this->assignTaxonomies($post, $form->taxonomies->others);
            $this->assignTags($post, $form->tags->newTagsNames);

            $transaction->commit();
            return $post;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function edit(Post $post, PostForm $form): void
    {
        $taxonomy = $this->taxonomies->get($form->taxonomies->main);

        $post->edit(
            $form->title,
            $form->description,
            $form->content,
            $form->comments_allowed,
            new DateTimeImmutable()->format('Y.m.d H:i:s'),
            new Meta(
                $form->meta->title,
                $form->meta->description,
                $form->meta->keywords
            )
        );

        $post->changeMainTaxonomy($taxonomy->id);

        if ($form->photo) {
            $post->setPhoto($form->photo);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->posts->save($post);

            $this->revokeTaxonomies($post);
            $this->revokeTags($post);

            $this->assignTaxonomies($post, $form->taxonomies->others);
            $this->assignTags($post, $form->tags->newTagsNames);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function activate($id): void
    {
        $post = $this->posts->get($id);
        $post->activate();
        $this->posts->save($post);
    }

    /**
     * @throws Exception
     */
    public function draft($id): void
    {
        $post = $this->posts->get($id);
        $post->draft();
        $this->posts->save($post);
    }

    /**
     * @throws Throwable
     */
    public function remove($id): void
    {
        $post = $this->posts->get($id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->revokeTaxonomies($post);
            $this->revokeTags($post);
            $this->removeComments($post);

            $this->posts->remove($post);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function pinToggle(int $id): void
    {
        $post = $this->posts->get($id);
        $post->pinToggle();
        $this->posts->save($post);
    }

    // ==================== Private methods ====================

    /**
     * @throws Exception
     */
    private function assignTaxonomies(Post $post, array $taxonomyIds): void
    {
        $existingIds = ArrayHelper::getColumn($post->taxonomyAssignments, 'taxonomy_id');

        foreach ($taxonomyIds as $taxonomyId) {
            if (in_array($taxonomyId, $existingIds, true)) {
                continue;
            }

            $this->taxonomies->get($taxonomyId); // Проверка существования сущности

            $assignment = new TaxonomyAssignment();
            $assignment->post_id = $post->id;
            $assignment->taxonomy_id = $taxonomyId;

            if (!$assignment->save()) {
                throw new Exception('Failed to save taxonomy assignment.');
            }
        }
    }

    private function revokeTaxonomies(Post $post): void
    {
        TaxonomyAssignment::deleteAll(['post_id' => $post->id]);
    }

    /**
     * @throws Exception
     */
    private function assignTags(Post $post, array $tagNames): void
    {
        foreach ($tagNames as $tagName) {
            $slug = Inflector::slug($tagName);

            $tag = $this->tags->findBySlug($slug);
            if (!$tag) {
                $tag = Tag::create($tagName, $slug);
                $this->tags->save($tag);
            }

            $exists = TagAssignment::find()
                ->andWhere(['post_id' => $post->id, 'tag_id' => $tag->id])
                ->exists();

            if ($exists) {
                continue;
            }

            $assignment = new TagAssignment();
            $assignment->post_id = $post->id;
            $assignment->tag_id = $tag->id;

            if (!$assignment->save()) {
                throw new Exception('Failed to save tag assignment.');
            }
        }
    }

    private function revokeTags(Post $post): void
    {
        TagAssignment::deleteAll(['post_id' => $post->id]);
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    private function removeComments(Post $post): void
    {
        foreach ($post->comments as $comment) {
            $comment->delete();
        }
    }
}
