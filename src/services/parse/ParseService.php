<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\services\parse;

use DateTimeImmutable;
use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\repositories\PostRepository;
use Besnovatyj\Meta\Meta;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\helpers\Url;

/**
 * Service for parsing articles from external sources
 */
class ParseService
{
    private PostRepository $posts;
    private ParserRegistry $registry;

    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
        $this->registry = $this->createRegistry();
    }

    /**
     * Get preview data from URL (first step)
     *
     * @return array{sourceUrl: string, title: string, content: string, imageUrls: string[]}
     * @throws Exception
     */
    public function preview(string $url): array
    {
        $parser = $this->getParser($url);
        $article = $parser->fetchArticle();

        return $article->toArray();
    }

    /**
     * Download a single image (called from frontend for each image)
     *
     * @throws Exception
     */
    public function downloadImage(string $articleUrl, string $imageUrl): string
    {
        $parser = $this->getParser($articleUrl);

        return $parser->downloadImage($imageUrl);
    }

    /**
     * Create post from parsed data (second step)
     *
     * @return string URL to the created post
     * @throws Exception
     */
    public function createPost(string $url): string
    {
        $parser = $this->getParser($url);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = $this->createEmptyPost();
            $post = $parser->populatePost($post);

            if (empty($post->title) || empty($post->content)) {
                $this->posts->remove($post);
                throw new Exception('Parsing failed: empty title or content.');
            }

            $this->posts->save($post);

            $transaction->commit();

            return Url::to(['/Blog/backend/post/view', 'id' => $post->id]);
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw new Exception('Parsing failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if URL is supported
     */
    public function isSupported(string $url): bool
    {
        return $this->registry->hasParser($url);
    }

    /**
     * Get list of supported hosts
     *
     * @return string[]
     */
    public function getSupportedHosts(): array
    {
        return $this->registry->getSupportedHosts();
    }

    // ==================== Backward compatibility ====================

    /**
     * @throws Exception
     * @deprecated Use preview() instead
     */
    public function getStartData(string $url): array
    {
        return $this->preview($url);
    }

    /**
     * @throws Exception
     * @deprecated Use createPost() instead
     */
    public function parseToPost(string $url): string
    {
        return $this->createPost($url);
    }

    // ==================== Private methods ====================

    /**
     * @throws Exception
     */
    private function getParser(string $url): ParserInterface
    {
        if (!$this->registry->hasParser($url)) {
            throw new Exception(sprintf(
                'Unsupported host. Supported: %s',
                implode(', ', $this->registry->getSupportedHosts())
            ));
        }

        return $this->registry->getParser($url);
    }

    private function createRegistry(): ParserRegistry
    {
        $registry = new ParserRegistry();

        // Register built-in parsers
        $registry->register(HabrParser::class);
        $registry->register(PikabuParser::class);

        return $registry;
    }

    /**
     * @throws \yii\db\Exception
     */
    private function createEmptyPost(): Post
    {
        $post = Post::create(
            taxonomyId: null,
            title: '',
            description: '',
            content: '',
            created_at: new DateTimeImmutable()->format('Y.m.d H:i:s'),
            comments_allowed: Post::COMMENTS_DISABLED,
            meta: new Meta('', '', '')
        );

        $this->posts->save($post);

        return $post;
    }
}
