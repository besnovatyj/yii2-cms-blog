<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Blog;

use yii\base\BootstrapInterface;

/**
 * Bootstrap блога.
 * Bootstrap глобальный (L2, гейт modman): выполняется во всех приложениях;
 */
final class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app): void {}
}
