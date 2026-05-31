<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\services\parse;

/**
 * Parser for pikabu.ru articles
 */
final class PikabuParser extends AbstractParser
{
    public static function getSupportedHost(): string
    {
        return 'pikabu.ru';
    }

    protected function getConfig(): ParserConfig
    {
        return new ParserConfig(
            encoding: 'windows-1251',
            containerSelector: '.story__main',
            bodySelector: '.story__content-inner',
            titleSelector: '.story__title-link',
            imageBlockSelector: '.story-block_type_image',
            imageSrcAttributes: ['data-large-image'],
            imageElementSelector: '.story-image__image',
            videoBlockSelector: '.story-block_type_video',
            scriptSelector: 'script',
            collapseWhitespace: true,
        );
    }
}
