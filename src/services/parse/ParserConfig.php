<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\services\parse;

/**
 * Configuration DTO for parser selectors
 */
final readonly class ParserConfig
{
    public function __construct(
        public string  $encoding,
        public string  $containerSelector,
        public string  $bodySelector,
        public string  $titleSelector,
        public string  $imageBlockSelector,
        public array   $imageSrcAttributes,
        public ?string $imageElementSelector = null,
        public ?string $videoBlockSelector = null,
        public string  $scriptSelector = 'script',
        public bool    $collapseWhitespace = false,
    ) {
    }
}
