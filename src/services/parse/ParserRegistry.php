<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\services\parse;

use InvalidArgumentException;

/**
 * Registry for article parsers.
 * Allows registering new parsers without modifying existing code.
 */
final class ParserRegistry
{
    /** @var array<string, class-string<ParserInterface>> */
    private array $parsers = [];

    /**
     * Register a parser class for a specific host
     *
     * @param class-string<ParserInterface> $parserClass
     */
    public function register(string $parserClass): void
    {
        if (!is_subclass_of($parserClass, ParserInterface::class)) {
            throw new InvalidArgumentException(
                sprintf('Parser class must implement %s', ParserInterface::class)
            );
        }

        $host = $parserClass::getSupportedHost();
        $this->parsers[$host] = $parserClass;
    }

    /**
     * Check if parser exists for given URL
     */
    public function hasParser(string $url): bool
    {
        $host = $this->extractHost($url);
        return isset($this->parsers[$host]);
    }

    /**
     * Get parser instance for URL
     */
    public function getParser(string $url): ParserInterface
    {
        $host = $this->extractHost($url);

        if (!isset($this->parsers[$host])) {
            throw new InvalidArgumentException(
                sprintf('No parser registered for host: %s', $host)
            );
        }

        $parserClass = $this->parsers[$host];
        return new $parserClass($url);
    }

    /**
     * Get list of supported hosts
     *
     * @return string[]
     */
    public function getSupportedHosts(): array
    {
        return array_keys($this->parsers);
    }

    private function extractHost(string $url): string
    {
        $parsed = parse_url($url);

        if (!isset($parsed['host'])) {
            throw new InvalidArgumentException('Invalid URL: ' . $url);
        }

        return $parsed['host'];
    }
}
