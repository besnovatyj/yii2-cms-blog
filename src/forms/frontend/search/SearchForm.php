<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Blog\forms\frontend\search;

use Besnovatyj\Forms\BaseForm;

/**
 * Форма фронтового поиска по блогу.
 *
 * Наследует {@see BaseForm} (приведение typed-свойств из GET). `formName() === ''` — параметры
 * читаются из query как есть (`?text=...`), без префикса формы (как во фронтовом поиске Person).
 */
class SearchForm extends BaseForm
{
    public ?string $text = null;

    public function rules(): array
    {
        return [
            ['text', 'trim'],
            ['text', 'string', 'max' => 255],
        ];
    }

    public function formName(): string
    {
        return '';
    }

    public function attributeLabels(): array
    {
        return [
            'text' => 'Поиск по блогу',
        ];
    }
}
