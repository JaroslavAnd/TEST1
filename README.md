# Лабораторні роботи з курсу "Сучасні фреймворки вебпрограмування"

**Дисципліна:** Сучасні фреймворки веб-програмування  
**Студент:** Патлай Максим (група ВТ-23-1)  
**Репозиторій:** [https://github.com/JaroslavAnd/TEST1.git](https://github.com/JaroslavAnd/TEST1.git)

---

## Лабораторна робота №1

Створено репозиторій для роботи з фреймворками **Symfony 6.4** та **Laravel 11**:
- Налаштовано середовище (PHP 8.3, Composer, Xdebug v3.3.2).
- Ініціалізовано проекти в папках `symfony/` та `laravel/`.
- Реалізовано початкові контролери `TestController` з тестовим методом `/test`.
- Налаштовано коректне ігнорування прав доступу `git config core.fileMode false`.

---

## Лабораторна робота №2: Опис CRUD операцій

В обох проектах реалізовано CRUD-операції над сутністю `Product` згідно з вимогами.

### 1. Symfony (`symfony/src/Controller/ProductController.php`)
- **GET** `/products` (`getProducts`) — отримати список усіх товарів (JSON, `HTTP 200 OK`).
- **GET** `/products/{id}` (`getProductItem`) — отримати товар за ідентифікатором (JSON, `HTTP 200 OK` або `HTTP 404 NOT FOUND`).
- **POST** `/products` (`createProduct`) — створити новий товар (JSON, `HTTP 201 CREATED`).
- **PUT / PATCH** `/products/{id}` (`updateProduct`) — оновити існуючий товар (JSON, `HTTP 200 OK` або `HTTP 404 NOT FOUND`).
- **DELETE** `/products/{id}` (`deleteProduct`) — видалити товар за ідентифікатором (JSON, `HTTP 200 OK` або `HTTP 404 NOT FOUND`).

### 2. Laravel (`laravel/app/Http/Controllers/ProductController.php`)
Маршрути зареєстровано у `laravel/routes/web.php`:
- **GET** `/products` — отримати список товарів (`ProductController@getProducts`).
- **GET** `/products/{id}` — отримати товар за ID (`ProductController@getProductItem`).
- **POST** `/products` — створення товару (`ProductController@createProduct`).
- **PUT / PATCH** `/products/{id}` — оновлення товару (`ProductController@updateProduct`).
- **DELETE** `/products/{id}` — видалення товару (`ProductController@deleteProduct`).
- У `laravel/bootstrap/app.php` маршрути `/products*` додано у винятки CSRF перевірки для зручного тестування через Postman / curl.

---

## Запуск та перевірка

### Symfony
```bash
cd symfony
symfony server:start
# або
php -S 127.0.0.1:8000 -t public
```
- Перевірка GET: `http://127.0.0.1:8000/products`
- Перевірка GET за ID: `http://127.0.0.1:8000/products/1`

### Laravel
```bash
cd laravel
php artisan serve
```
- Перевірка GET: `http://127.0.0.1:8000/products`
- Перевірка GET за ID: `http://127.0.0.1:8000/products/1`

---

## Налаштування Xdebug
- Версія: **Xdebug v3.3.2**
- `xdebug.mode = debug,develop`
- `xdebug.client_host = 127.0.0.1`
- `xdebug.client_port = 9003`
- `xdebug.start_with_request = yes`
