Редактирование полей Directory в списке (без перехода на редактирование ресурса).

#### Установка:
выполнить в папке core команду

```php -d="memory_limit=-1" artisan package:installrequire webber12/evocms-directory-editor "*"```

затем, независимо от способа установки, в этой же папке выполнить (если пакет ранее не устанавливался)

```php artisan vendor:publish --provider="EvolutionCMS\EvoDirectoryEditor\EvoDirectoryEditorServiceProvider"```


#### Использование:

Для использования необходимо
1. чтобы строка Directory имела поле id
2. чтобы у ячейки Directory был класс class="editable" (просто добавить данные классы в конфиге Directory к нужным полям)

Редактирование происходит по dblclick


