<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\services\parse;

use Besnovatyj\Blog\entities\Post;

interface ParserInterface
{
    /**
     * Returns the host domain this parser handles (e.g., 'habr.com', 'pikabu.ru')
     */
    public static function getSupportedHost(): string;

    /**
     * Fetches and prepares article data from URL (first step - preview)
     */
    public function fetchArticle(): ParsedArticle;

    /**
     * Downloads a single image from URL to temp directory
     */
    public function downloadImage(string $imageUrl): string;

    /**
     * Parses prepared data into Post entity (second step - finalize)
     */
    public function populatePost(Post $post): Post;
}
