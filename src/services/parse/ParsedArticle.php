<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\services\parse;

/**
 * DTO containing parsed article data
 */
final readonly class ParsedArticle
{
    public function __construct(
        public string $sourceUrl,
        public string $title,
        public string $content,
        public array  $imageUrls = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'sourceUrl' => $this->sourceUrl,
            'title' => $this->title,
            'content' => $this->content,
            'imageUrls' => $this->imageUrls,
        ];
    }
}
