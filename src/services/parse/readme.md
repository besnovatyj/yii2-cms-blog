Контроллер использует deprecated методы — они сохранены для обратной совместимости, так что всё будет работать.

● Парсинг переработан

Используется нативный PHP DOM (DOMDocument + DOMXPath) — никаких внешних зависимостей.
Frontend переписан на нативный JavaScript (без jQuery).

Новая структура

      Besnovatyj/Blog/services/parse/
      ├── ParserInterface.php      # Интерфейс для всех парсеров
      ├── ParserConfig.php         # DTO с конфигурацией селекторов
      ├── ParsedArticle.php        # DTO с результатом парсинга
      ├── ParserRegistry.php       # Реестр парсеров (расширяемость)
      ├── AbstractParser.php       # Базовый класс с общей логикой
      ├── HabrParser.php           # Парсер habr.com (28 строк!)
      ├── PikabuParser.php         # Парсер pikabu.ru (28 строк!)
      └── ParseService.php         # Сервис для контроллера

Как добавить новый парсер

// 1. Создать класс

```php
final class VcRuParser extends AbstractParser
{
   public static function getSupportedHost(): string
   {
     return 'vc.ru';
   }

   protected function getConfig(): ParserConfig
   {
    return new ParserConfig(
        encoding: 'utf-8',
        containerSelector: 'article.content',
        bodySelector: '.content__body',
        titleSelector: 'h1.content-title',
        imageBlockSelector: 'img',
        imageSrcAttributes: ['src', 'data-src'],
    );
   }
}
```

// 2. Зарегистрировать в ParseService::createRegistry()
`$registry->register(VcRuParser::class);`

ParserConfig — все селекторы в одном месте

```php
new ParserConfig(
   encoding: 'utf-8',                           // Кодировка страницы
   containerSelector: 'article.content',        // Контейнер статьи
   bodySelector: '.body',                       // Тело статьи
   titleSelector: 'h1',                         // Заголовок
   imageBlockSelector: 'img',                   // Селектор изображений
   imageSrcAttributes: ['src', 'data-src'],     // Атрибуты с URL картинки
   imageElementSelector: null,                  // Для вложенных структур (Pikabu)
   videoBlockSelector: 'video',                 // Видео (будут удалены)
   scriptSelector: 'script',                    // Скрипты (будут удалены)
   collapseWhitespace: false,                   // Убирать лишние пробелы
);
```

AbstractParser.php - полностью переписан на нативные средства PHP:
- DOMDocument для загрузки и работы с HTML
- DOMXPath для поиска элементов
- Встроенный CSS→XPath конвертер для поддержки CSS-селекторов
- Корректная обработка кодировок (windows-1251 → UTF-8)

Ключевые методы:
- querySelector() / querySelectorAll() — поиск по CSS-селекторам
- getInnerHtml() / getOuterHtml() — получение HTML
- createElement() / replaceElement() / removeElement() — манипуляции с DOM

Логика парсинга сохранена:

1. Preview → загрузка статьи, кэширование в JSON
2. Загрузка изображений → параллельно для каждого блока
3. Save → создание поста из кэша без повторной загрузки

Обратная совместимость

Старые методы getStartData() и parseToPost() сохранены как deprecated — контроллер будет работать без изменений.
