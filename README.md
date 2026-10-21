# Лабораторна робота №1

**Дисципліна:** Сучасні фреймворки веб-програмування  
**Студент:** Андрієвський Ярослав (група ІПЗ-23-5)  

---

## Опис проекту

У рамках даної лабораторної роботи створено єдиний репозиторій для виконання практичних завдань з використанням фреймворків **Symfony** та **Laravel**.

### Структура проекту:

- `symfony/` — проект на базі **Symfony 6.4**
  - Реалізовано контролер `TestController` (`src/Controller/TestController.php`)
  - Маршрут: `/test`
  - Представлення: `templates/test/index.html.twig`
- `laravel/` — проект на базі **Laravel 11**
  - Реалізовано контролер `TestController` (`app/Http/Controllers/TestController.php`)
  - Маршрут: `/test` (`routes/web.php`)
  - Метод повертає текстову відповідь `'test'`

---

## Запуск проектів

### 1. Symfony
```bash
cd symfony
composer install
symfony server:start
# або
php -S 127.0.0.1:8000 -t public
```
Перевірка тестового методу: `http://127.0.0.1:8000/test`

### 2. Laravel
```bash
cd laravel
composer install
php artisan serve
```
Перевірка тестового методу: `http://127.0.0.1:8000/test`

---

## Налаштування Xdebug

Встановлено та активовано Xdebug:
- Версія: **Xdebug v3.3.2**
- Режим (`xdebug.mode`): `debug,develop`
- Клієнт: `127.0.0.1:9003`
- `start_with_request`: `yes`
