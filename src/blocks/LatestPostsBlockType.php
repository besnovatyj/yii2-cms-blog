<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Blog\blocks;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Contracts\block\BlockType;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;

/**
 * Тип блока «Последние посты» для модуля блоков (`Blog:latest`).
 *
 * Блок хранит выбор: рубрика (или весь блог), сколько постов и в сколько колонок, ссылка «все
 * новости». Посты выбирает поставщик данных {@see LatestPostsData} на каждый показ — лента живая.
 *
 * Список рубрик для поля выбора ленивый (замыкание): его загружают только форма блока и проверка
 * при сохранении, вывод блока на сайте рубрики не перечитывает. Рубрика хранится по id: slug
 * меняется вместе с адресом раздела, id — нет.
 *
 * Шаблон по умолчанию — `src/views/blockTypes/latest.php`; тема перекрывает его overlay-ем
 * представлений модуля: `themes/{тема}/modules/Blog/views/blockTypes/latest.php`.
 */
final class LatestPostsBlockType
{
    /** Локальный id типа; полный — `Blog:latest`. */
    public const string ID = 'latest';

    public function definition(): BlockType
    {
        return new BlockType(
            id: self::ID,
            label: 'Последние посты',
            view: dirname(__DIR__) . '/views/blockTypes/latest.php',
            fields: [
                'heading' => ['kind' => 'text', 'label' => 'Заголовок раздела', 'maxLength' => 120,
                    'hint' => 'Пусто — без заголовка.'],
                'taxonomy' => ['kind' => 'select', 'label' => 'Рубрика', 'default' => '',
                    'options' => static fn (): array => ['' => 'Все рубрики']
                        + (new TreeQueryScope(Taxonomy::class))->dropdownTree(indent: '— '),
                    'hint' => 'Пост попадает в рубрику, если она у него основная или дополнительная. '
                        . 'Скрытые рубрики и черновики на сайт не выводятся.'],
                'withDescendants' => ['kind' => 'checkbox', 'label' => 'Вместе с подрубриками', 'default' => true],
                'limit' => ['kind' => 'select', 'label' => 'Сколько постов', 'default' => '3', 'options' => [
                    '3' => '3', '4' => '4', '6' => '6', '8' => '8', '9' => '9', '12' => '12',
                ]],
                'columns' => ['kind' => 'select', 'label' => 'В ряду на широком экране', 'default' => '3', 'options' => [
                    '2' => '2', '3' => '3', '4' => '4',
                ]],
                'more' => ['kind' => 'link', 'label' => 'Ссылка «все новости»', 'hint' => 'Пусто — без ссылки.'],
                'moreLabel' => ['kind' => 'text', 'label' => 'Подпись ссылки', 'default' => 'Все новости', 'maxLength' => 40],
            ],
            hint: 'Свежие посты блога плитками: весь блог или одна рубрика.',
            dataProvider: LatestPostsData::class,
        );
    }
}
