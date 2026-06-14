<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog;

use common\components\module\CmsModule;
use modules\modmanNew\contract\DeclaresModule;
use modules\modmanNew\contract\ProvidesAdminMenu;
use modules\modmanNew\contract\ProvidesDependencies;
use modules\modmanNew\contract\ProvidesDirectories;
use modules\modmanNew\contract\ProvidesMigrations;
use modules\modmanNew\contract\ProvidesOptions;
use Yii;

class Module extends CmsModule implements
    DeclaresModule, ProvidesAdminMenu,
    ProvidesDependencies, ProvidesDirectories,
    ProvidesMigrations, ProvidesOptions
{
    public const bool EDITABLE = true;
    public const string VERSION = '1.0.0';
    public const string MODULE_ID = 'Blog';

    public function init(): void
    {
        parent::init();

        if (!isset(Yii::$app->i18n->translations['Blog'])) {
            Yii::$app->i18n->translations['Blog'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en',
                'basePath' => '@Besnovatyj/Blog/messages'
            ];
        }
    }

    public static function moduleId(): string { return self::MODULE_ID; }
    public static function moduleVersion(): string { return self::VERSION; }
    public static function isEditable(): bool { return self::EDITABLE; }
    public static function adminMenu(): array { return require __DIR__ . '/config/adminMenu.php'; }
    public static function moduleConfig(): array { return require __DIR__ . '/config/config.php'; }
    public static function options(): array { return require __DIR__ . '/config/options.php'; }
    public static function dependencies(): array { return require __DIR__ . '/config/dependencies.php'; }
    public static function directories(): array { return ['@static/origin/Blog', '@static/cache/Blog']; }
    public static function migrationPath(): string       { return __DIR__.'/migrations'; }
    public static function migrationNamespace(): ?string { return __NAMESPACE__.'\\migrations'; }

}
