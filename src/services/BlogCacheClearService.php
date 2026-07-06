<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Blog\services;

use Besnovatyj\Helpers\FilesystemHelper;
use Exception;

/**
 * Сервис получения и очистки кеша изображений блога (директория @static/cache/Blog).
 *
 * Директория создаётся при установке модуля (см. {@see \Besnovatyj\Blog\Module::directories()}),
 * но на случай её отсутствия защищаемся: пустой/несуществующий кеш — не ошибка
 * (нечего показывать и нечего чистить).
 *
 * Путь к кешу приходит извне (см. config/container.php) — класс не завязан на Yii::getAlias
 * и остаётся чистым для DI и тестов.
 */
class BlogCacheClearService
{
    /**
     * @param string $cacheDir Абсолютный путь к директории кеша изображений блога
     */
    public function __construct(private readonly string $cacheDir)
    {
    }

    /**
     * Возвращает отформатированный размер кеша изображений блога.
     *
     * @return string
     * @throws Exception
     */
    public function getData(): string
    {
        if (!is_dir($this->cacheDir)) {
            return $this->formatBytes(0);
        }

        $size = FilesystemHelper::getDirSize($this->cacheDir, true);
        return $this->formatBytes($size);
    }

    /**
     * Очищает содержимое директории кеша изображений блога, сохраняя .gitignore.
     *
     * @return bool
     * @throws Exception
     */
    public function clearData(): bool
    {
        if (!is_dir($this->cacheDir)) {
            // Нечего очищать — считаем операцию успешной.
            return true;
        }

        return FilesystemHelper::deleteDirContents($this->cacheDir, false, ['.gitignore']);
    }

    /**
     * Форматирует размер в байтах в читаемый формат.
     *
     * @param int $bytes Размер в байтах
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ'];
        $size = (float)$bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return sprintf('%.2f %s', $size, $units[$unitIndex]);
    }
}
