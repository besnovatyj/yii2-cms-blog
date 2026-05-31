<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\services\parse;

use common\components\upload\UploadFromUrl;
use Besnovatyj\Helpers\FilesystemHelper;
use Besnovatyj\Helpers\url\UrlHelper;
use DOMDocument;
use DOMElement;
use DOMException;
use DOMNode;
use DOMXPath;
use Exception;
use FilesystemIterator;
use Besnovatyj\Blog\entities\Post;
use Yii;

/**
 * Base class for article parsers with common functionality
 * Uses native PHP DOM extension (DOMDocument/DOMXPath) for HTML parsing
 *
 * Preview flow:
 * 1. Load HTML from URL
 * 2. Extract title, remove scripts/video
 * 3. Find all images, download each to temp folder
 * 4. Replace image URLs with local temp URLs
 * 5. Cache the result with local URLs
 * 6. Return content ready for preview (all images are local)
 *
 * Save flow:
 * 1. Load cached data
 * 2. Move images from temp to permanent folder
 * 3. Update URLs in content to permanent paths
 * 4. Save to database
 */
abstract class AbstractParser implements ParserInterface
{
    protected string $sourceUrl;
    protected string $tmpPath;
    protected string $tmpUrlBase;
    protected string $postImagesPath;
    protected ?ParsedArticle $parsedArticle = null;

    private ?DOMDocument $document = null;
    private ?DOMXPath $xpath = null;

    abstract protected function getConfig(): ParserConfig;

    public function __construct(string $url)
    {
        $this->sourceUrl = $url;
        $urlHash = md5($url);
        $this->tmpPath = Yii::getAlias('@static/cache/tmp') . '/' . $urlHash;
        $this->tmpUrlBase = Yii::getAlias('@staticHostName/cache/tmp') . '/' . $urlHash;
        $this->postImagesPath = '@static/origin/Blog';
    }

    /**
     * Fetch article, download ALL images locally, return content with local URLs
     * @throws \yii\base\Exception|DOMException
     */
    public function fetchArticle(): ParsedArticle
    {
        $config = $this->getConfig();

        $this->prepareTempDirectory();

        $this->loadDocument($this->sourceUrl, $config->encoding);

        $container = $this->querySelector($config->containerSelector);
        if (!$container) {
            throw new \yii\base\Exception('Container not found: ' . $config->containerSelector);
        }

        $body = $this->querySelector($config->bodySelector, $container);
        if (!$body) {
            throw new \yii\base\Exception('Body not found: ' . $config->bodySelector);
        }

        $title = $this->extractTitle($container, $config->titleSelector);

        $this->removeElements($body, $config->scriptSelector);

        if ($config->videoBlockSelector) {
            $this->removeElements($body, $config->videoBlockSelector);
        }

        // Download all images and replace with local URLs
        $downloadedImages = $this->downloadAndReplaceImages($body, $config);

        $content = $this->buildContent($body);

        if ($config->collapseWhitespace) {
            $content = $this->collapseWhitespace($content);
        }

        $this->parsedArticle = new ParsedArticle(
            sourceUrl: $this->sourceUrl,
            title: $title,
            content: $content,
            imageUrls: $downloadedImages
        );

        $this->saveParsedData();

        return $this->parsedArticle;
    }

    /**
     * @deprecated Not needed anymore - images are downloaded in fetchArticle()
     */
    public function downloadImage(string $imageUrl): string
    {
        return $this->downloadImageToTemp($imageUrl);
    }

    /**
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function populatePost(Post $post): Post
    {
        $this->loadParsedData();

        if (!$this->parsedArticle) {
            throw new \yii\base\Exception('No parsed data available. Call fetchArticle() first.');
        }

        // Move images from temp to permanent location and update URLs
        $content = $this->moveImagesAndUpdateContent($post);

        $post->title = $this->parsedArticle->title;
        $post->content = $content;

        return $post;
    }

    // ==================== Image Processing ====================

    /**
     * Download all images and replace URLs in DOM with local paths
     * @return array Map of original URL => local URL
     * @throws DOMException
     */
    protected function downloadAndReplaceImages(DOMElement $body, ParserConfig $config): array
    {
        $downloadedImages = [];

        if ($config->imageElementSelector) {
            $downloadedImages = $this->processNestedImages($body, $config);
        } else {
            $downloadedImages = $this->processDirectImages($body, $config);
        }

        return $downloadedImages;
    }

    /**
     * Process images inside container elements (like Pikabu)
     * @return array Map of original URL => local URL
     * @throws DOMException
     */
    protected function processNestedImages(DOMElement $body, ParserConfig $config): array
    {
        $downloadedImages = [];
        $blocks = $this->querySelectorAll($config->imageBlockSelector, $body);

        foreach ($blocks as $block) {
            $imgElement = $this->querySelector($config->imageElementSelector, $block);

            if (!$imgElement) {
                continue;
            }

            $srcUrl = $this->extractImageSrc($imgElement, $config->imageSrcAttributes);

            if ($srcUrl) {
                $localUrl = $this->downloadImageToTemp($srcUrl);

                if ($localUrl) {
                    $downloadedImages[$srcUrl] = $localUrl;

                    // Replace the whole block with a simple img tag with local URL
                    $newImg = $this->createElement('img', [
                        'src' => $localUrl,
                        'class' => 'img-fluid',
                        'data-original-url' => $srcUrl
                    ]);
                    $this->replaceElement($block, $newImg);
                } else {
                    // Failed to download - remove the block
                    $this->removeElement($block);
                }
            }
        }

        return $downloadedImages;
    }

    /**
     * Process direct img elements (like Habr)
     * @return array Map of original URL => local URL
     */
    protected function processDirectImages(DOMElement $body, ParserConfig $config): array
    {
        $downloadedImages = [];
        $images = $this->querySelectorAll($config->imageBlockSelector, $body);

        foreach ($images as $image) {
            $srcUrl = $this->extractImageSrc($image, $config->imageSrcAttributes);

            if ($srcUrl && UrlHelper::isUrl($srcUrl) && !str_contains($srcUrl, 'loader')) {
                $localUrl = $this->downloadImageToTemp($srcUrl);

                if ($localUrl) {
                    $downloadedImages[$srcUrl] = $localUrl;

                    // Update the img src to local URL
                    $image->setAttribute('src', $localUrl);
                    $image->setAttribute('data-original-url', $srcUrl);
                    $image->setAttribute('class', 'img-fluid');

                    // Remove srcset as we have local image now
                    $image->removeAttribute('srcset');
                    $image->removeAttribute('data-src');
                } else {
                    // Failed to download - remove the image
                    $this->removeElement($image);
                }
            } else {
                $this->removeElement($image);
            }
        }

        return $downloadedImages;
    }

    /**
     * Download single image to temp folder
     * @return string|null Local URL or null on failure
     */
    protected function downloadImageToTemp(string $imageUrl): ?string
    {
        if (empty($imageUrl)) {
            return null;
        }

        try {
            $file = UploadFromUrl::getInstancesByUrl($imageUrl);

            if (!$file->saveAs($this->tmpPath . DIRECTORY_SEPARATOR . $file->name)) {
                return null;
            }

            return $this->tmpUrlBase . '/' . $file->name;
        } catch (\Throwable $e) {
            // Log error but don't stop processing
            Yii::error('Failed to download image: ' . $imageUrl . ' - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Move images from temp to post directory and update content URLs
     * @throws Exception
     */
    protected function moveImagesAndUpdateContent(Post $post): string
    {
        $images = $this->getTempImageFiles();

        if (empty($images)) {
            return $this->parsedArticle->content;
        }

        $postImagesPath = Yii::getAlias($this->postImagesPath . '/' . $post->id);
        $postImagesUrl = Yii::getAlias('@staticHostName/origin/Blog/' . $post->id);

        FilesystemHelper::createDirectoryRecursively($postImagesPath);

        $content = $this->parsedArticle->content;

        foreach ($images as $filename) {
            $source = $this->tmpPath . '/' . $filename;
            $dest = $postImagesPath . '/' . $filename;

            if (rename($source, $dest)) {
                // Replace temp URL with permanent URL in content
                $oldUrl = $this->tmpUrlBase . '/' . $filename;
                $newUrl = $postImagesUrl . '/' . $filename;
                $content = str_replace($oldUrl, $newUrl, $content);
            }
        }

        // Clean up temp directory
        FilesystemHelper::deleteDirContents($this->tmpPath, true);

        return $content;
    }

    protected function extractImageSrc(DOMElement $element, array $attributes): ?string
    {
        foreach ($attributes as $attr) {
            $value = $element->getAttribute($attr);
            if (empty($value)) {
                continue;
            }

            // Normalize protocol-relative URLs (//example.com/...)
            if (str_starts_with($value, '//')) {
                $value = 'https:' . $value;
            }

            if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                return $value;
            }
        }

        return null;
    }

    protected function getTempImageFiles(): array
    {
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];

        if (!is_dir($this->tmpPath)) {
            return [];
        }

        $files = [];
        $iterator = new FilesystemIterator($this->tmpPath, FilesystemIterator::SKIP_DOTS);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, $extensions, true)) {
                    $files[] = $file->getFilename();
                }
            }
        }

        return $files;
    }

    // ==================== DOM Helper Methods ====================

    /**
     * @throws \yii\base\Exception
     */
    protected function loadDocument(string $url, string $encoding = 'UTF-8'): void
    {
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n" .
                    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
                    "Accept-Language: ru-RU,ru;q=0.9,en;q=0.8\r\n",
                'timeout' => 30,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $html = file_get_contents($url, false, $context);

        if ($html === false) {
            throw new \yii\base\Exception('Failed to load URL: ' . $url);
        }

        if (strtolower($encoding) !== 'utf-8') {
            $html = mb_convert_encoding($html, 'UTF-8', $encoding);
        }

        $this->document = new DOMDocument('1.0', 'UTF-8');
        $this->document->preserveWhiteSpace = true;
        $this->document->formatOutput = false;

        libxml_use_internal_errors(true);
        $this->document->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR
        );
        libxml_clear_errors();

        $this->xpath = new DOMXPath($this->document);
    }

    protected function loadHtmlString(string $html): void
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
        $this->document->preserveWhiteSpace = true;

        libxml_use_internal_errors(true);
        $this->document->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR
        );
        libxml_clear_errors();

        $this->xpath = new DOMXPath($this->document);
    }

    protected function cssToXPath(string $selector): string
    {
        $selector = trim($selector);

        if (str_contains($selector, ',')) {
            $parts = array_map(fn($s) => $this->cssToXPath(trim($s)), explode(',', $selector));
            return implode(' | ', $parts);
        }

        $selector = preg_replace('/\s*>\s*/', ' >', $selector);

        $parts = preg_split('/\s+/', $selector);
        $xpath = '.';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $isChild = str_starts_with($part, '>');
            if ($isChild) {
                $part = substr($part, 1);
            }

            $converted = $this->convertSingleSelector($part);
            $xpath .= ($isChild ? '/' : '//') . $converted;
        }

        return $xpath;
    }

    private function convertSingleSelector(string $selector): string
    {
        $element = '*';

        if (preg_match('/^([a-zA-Z][a-zA-Z0-9]*)?/', $selector, $matches) && !empty($matches[1])) {
            $element = $matches[1];
            $selector = substr($selector, strlen($matches[1]));
        }

        $conditions = [];

        if (preg_match('/#([a-zA-Z0-9_-]+)/', $selector, $matches)) {
            $conditions[] = "@id='{$matches[1]}'";
            $selector = str_replace('#' . $matches[1], '', $selector);
        }

        if (preg_match_all('/\.([a-zA-Z0-9_-]+)/', $selector, $matches)) {
            foreach ($matches[1] as $class) {
                $conditions[] = "contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')";
            }
        }

        if (preg_match_all('/\[([^\]]+)\]/', $selector, $matches)) {
            foreach ($matches[1] as $attr) {
                if (str_contains($attr, '=')) {
                    [$name, $value] = explode('=', $attr, 2);
                    $value = trim($value, '"\'');
                    $conditions[] = "@{$name}='{$value}'";
                } else {
                    $conditions[] = "@{$attr}";
                }
            }
        }

        $result = $element;
        if (!empty($conditions)) {
            $result .= '[' . implode(' and ', $conditions) . ']';
        }

        return $result;
    }

    protected function querySelector(string $selector, ?DOMNode $context = null): ?DOMElement
    {
        $xpath = $this->cssToXPath($selector);
        $context = $context ?? $this->document;
        $result = $this->xpath->query($xpath, $context);

        return ($result && $result->length > 0) ? $result->item(0) : null;
    }

    /**
     * @return DOMElement[]
     */
    protected function querySelectorAll(string $selector, ?DOMNode $context = null): array
    {
        $xpath = $this->cssToXPath($selector);
        $context = $context ?? $this->document;
        $result = $this->xpath->query($xpath, $context);

        $elements = [];
        if ($result) {
            foreach ($result as $node) {
                if ($node instanceof DOMElement) {
                    $elements[] = $node;
                }
            }
        }

        return $elements;
    }

    protected function getInnerHtml(DOMElement $element): string
    {
        $html = '';
        foreach ($element->childNodes as $child) {
            $html .= $this->document->saveHTML($child);
        }
        return $html;
    }

    /**
     * @throws DOMException
     */
    protected function createElement(string $tagName, array $attributes = []): DOMElement
    {
        $element = $this->document->createElement($tagName);
        foreach ($attributes as $name => $value) {
            $element->setAttribute($name, $value);
        }
        return $element;
    }

    protected function replaceElement(DOMElement $oldElement, DOMElement $newElement): void
    {
        $oldElement->parentNode->replaceChild($newElement, $oldElement);
    }

    protected function removeElement(DOMElement $element): void
    {
        $element->parentNode?->removeChild($element);
    }

    // ==================== Protected helpers ====================

    /**
     * @throws \yii\base\Exception
     * @throws Exception
     */
    protected function prepareTempDirectory(): void
    {
        FilesystemHelper::createDirectoryRecursively($this->tmpPath);
        FilesystemHelper::deleteDirContents($this->tmpPath);
    }

    /**
     * @throws \yii\base\Exception
     */
    protected function extractTitle(DOMElement $container, string $selector): string
    {
        $titleElement = $this->querySelector($selector, $container);

        if (!$titleElement) {
            throw new \yii\base\Exception('Title not found: ' . $selector);
        }

        return trim($this->getInnerHtml($titleElement));
    }

    protected function removeElements(DOMElement $body, string $selector): void
    {
        $elements = $this->querySelectorAll($selector, $body);

        foreach ($elements as $element) {
            $this->removeElement($element);
        }
    }

    protected function buildContent(DOMElement $body): string
    {
        $sourceLink = sprintf(
            '<p><a href="%s" target="_blank">Источник</a></p>',
            htmlspecialchars($this->sourceUrl)
        );

        return $sourceLink . $this->getInnerHtml($body);
    }

    protected function collapseWhitespace(string $text): string
    {
        return trim(mb_ereg_replace('\s\s+|\R+', ' ', $text));
    }

    // ==================== Persistence ====================

    protected function saveParsedData(): void
    {
        if (!$this->parsedArticle) {
            return;
        }

        $data = [
            'sourceUrl' => $this->parsedArticle->sourceUrl,
            'title' => $this->parsedArticle->title,
            'content' => $this->parsedArticle->content,
            'imageUrls' => $this->parsedArticle->imageUrls,
        ];

        $jsonPath = $this->tmpPath . '/article.json';
        file_put_contents($jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * @throws \yii\base\Exception
     */
    protected function loadParsedData(): void
    {
        $jsonPath = $this->tmpPath . '/article.json';

        if (!file_exists($jsonPath)) {
            throw new \yii\base\Exception('Parsed data not found. Call fetchArticle() first.');
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        $this->parsedArticle = new ParsedArticle(
            sourceUrl: $data['sourceUrl'],
            title: $data['title'],
            content: $data['content'],
            imageUrls: $data['imageUrls'] ?? []
        );
    }
}
