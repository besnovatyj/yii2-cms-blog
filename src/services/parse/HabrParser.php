<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\services\parse;

/**
 * Parser for habr.com articles
 */
final class HabrParser extends AbstractParser
{
    public static function getSupportedHost(): string
    {
        return 'habr.com';
    }

    protected function getConfig(): ParserConfig
    {
        return new ParserConfig(
            encoding: 'utf-8',
            containerSelector: 'article.tm-article-presenter__content',
            bodySelector: 'div.article-body',
            titleSelector: 'h1.tm-title > span',
            imageBlockSelector: 'figure',
            imageSrcAttributes: ['src', 'srcset'],
            imageElementSelector: 'img',
            videoBlockSelector: 'video',
            scriptSelector: 'script',
            collapseWhitespace: false,
        );
    }
}
