# Лабораторні роботи з курсу "Сучасні фреймворки вебпрограмування"

**Дисципліна:** Сучасні фреймворки веб-програмування  
**Студент:** Патлай Максим (група ВТ-23-1)  
**Репозиторій:** [https://github.com/JaroslavAnd/TEST1.git](https://github.com/JaroslavAnd/TEST1.git)  
**Гілка Лабораторної №3:** `lab3`

---

## Лабораторна робота №1

Створено репозиторій для роботи з фреймворками **Symfony 6.4** та **Laravel 11**:
- Налаштовано середовище (PHP 8.3, Composer, Xdebug v3.3.2).
- Ініціалізовано проекти в папках `symfony/` та `laravel/`.
- Реалізовано початкові контролери `TestController` з тестовим методом `/test`.
- Налаштовано коректне ігнорування прав доступу `git config core.fileMode false`.

---

## Лабораторна робота №2: Опис CRUD операцій

В обох проектах реалізовано базові CRUD-операції над сутністю `Product` згідно з методичними вказівками:
- `symfony/src/Controller/ProductController.php` (маршрути `/products`, `/products/{id}`).
- `laravel/app/Http/Controllers/ProductController.php` (маршрути `/products`, `/products/{id}`).

---

## Лабораторна робота №3: Проектування бази даних та CRUD зі зв'язками

### Обрана тема (Варіант 25)
> **Кредитна система банку:** база даних для зберігання інформації про клієнтів, кредити, виплати та банківські рахунки.

### 1. Налаштування MySQL:
- **Користувач:** `bank_user` (пароль: `bank_password`)
- **База даних Symfony:** `bank_credit_symfony`
- **База даних Laravel:** `bank_credit_laravel`

### 2. Спроектована структура бази даних (5 таблиць зі зв'язками):
1. **`clients`** (Клієнти банку)
   - `id`, `first_name`, `last_name`, `email` (unique), `phone`, `tax_id` (unique), `created_at`
2. **`bank_accounts`** (Банківські рахунки)
   - `id`, `client_id` (FK $\rightarrow$ `clients.id`), `account_number` (IBAN, unique), `balance`, `currency`, `status`, `created_at`
3. **`credit_types`** (Кредитні програми)
   - `id`, `name`, `interest_rate`, `min_amount`, `max_amount`, `term_months`
4. **`credits`** (Оформлені кредити)
   - `id`, `client_id` (FK $\rightarrow$ `clients.id`), `bank_account_id` (FK $\rightarrow$ `bank_accounts.id`), `credit_type_id` (FK $\rightarrow$ `credit_types.id`), `amount`, `remaining_amount`, `start_date`, `end_date`, `status`, `created_at`
5. **`payments`** (Виплати / платежі за кредитами)
   - `id`, `credit_id` (FK $\rightarrow$ `credits.id`), `amount`, `payment_date`, `payment_method`, `status`, `created_at`

### Зв'язки між сутностями:
- `clients` (1) $\longleftrightarrow$ (N) `bank_accounts`
- `clients` (1) $\longleftrightarrow$ (N) `credits`
- `bank_accounts` (1) $\longleftrightarrow$ (N) `credits`
- `credit_types` (1) $\longleftrightarrow$ (N) `credits`
- `credits` (1) $\longleftrightarrow$ (N) `payments`

---

### 3. Реалізація у Symfony 6.4:
Створено 5 Doctrine-сутностей (`src/Entity/`), 5 репозиторіїв (`src/Repository/`) та 5 окремих контролерів (`src/Controller/`):
- `ClientController`:
  - `GET /api/clients` — список клієнтів
  - `GET /api/clients/{id}` — деталі клієнта
  - `POST /api/clients` — створення клієнта
  - `PUT /api/clients/{id}` — оновлення
  - `DELETE /api/clients/{id}` — видалення
- `BankAccountController`:
  - `GET /api/bank-accounts` (підтримує фільтр `?clientId=...`)
  - `GET /api/bank-accounts/{id}`
  - `POST /api/bank-accounts` — створення рахунку з прив'язкою до клієнта (`clientId`)
  - `PUT /api/bank-accounts/{id}`
  - `DELETE /api/bank-accounts/{id}`
- `CreditTypeController`:
  - `GET /api/credit-types`, `GET /api/credit-types/{id}`, `POST /api/credit-types`, `PUT /api/credit-types/{id}`, `DELETE /api/credit-types/{id}`
- `CreditController`:
  - `GET /api/credits`, `GET /api/credits/{id}`
  - `POST /api/credits` — оформлення кредиту зі зв'язками (`clientId`, `bankAccountId`, `creditTypeId`, `amount`)
  - `PUT /api/credits/{id}`, `DELETE /api/credits/{id}`
- `PaymentController`:
  - `GET /api/payments`, `GET /api/payments/{id}`
  - `POST /api/payments` — здійснення виплати за кредитом (`creditId`, `amount`, `paymentMethod`), автоматичний перерахунок залишку боргу (`remaining_amount`)
  - `PUT /api/payments/{id}`, `DELETE /api/payments/{id}`

---

### 4. Реалізація у Laravel 11:
Створено 5 міграцій (`database/migrations/`), 5 Eloquent-моделей (`app/Models/`) з налаштованими зв'язками `hasMany`/`belongsTo`, 5 контролерів (`app/Http/Controllers/`):
- Маршрути зареєстровано через `Route::apiResource` у `routes/web.php` з префіксом `/api/`:
  - `/api/clients` (`ClientController`)
  - `/api/bank-accounts` (`BankAccountController`)
  - `/api/credit-types` (`CreditTypeController`)
  - `/api/credits` (`CreditController`)
  - `/api/payments` (`PaymentController`)
- Усі `/api/*` маршрути виведено з-під CSRF перевірки у `bootstrap/app.php` для тестування через REST-клієнти.
- Створено початковий сідер `DatabaseSeeder.php` для наповнення демонстраційними зв'язаними даними.

---

## Запуск та перевірка

### Symfony
```bash
cd symfony
symfony server:start
# або
php -S 127.0.0.1:8000 -t public
```
- Перевірка клієнтів: `GET http://127.0.0.1:8000/api/clients`
- Перевірка рахунків: `GET http://127.0.0.1:8000/api/bank-accounts`
- Перевірка кредитів: `GET http://127.0.0.1:8000/api/credits`
- Перевірка виплат: `GET http://127.0.0.1:8000/api/payments`

### Laravel
```bash
cd laravel
php artisan serve
```
- Перевірка клієнтів: `GET http://127.0.0.1:8000/api/clients`
- Перевірка рахунків: `GET http://127.0.0.1:8000/api/bank-accounts`
- Перевірка кредитів: `GET http://127.0.0.1:8000/api/credits`
- Перевірка виплат: `GET http://127.0.0.1:8000/api/payments`

---

## Налаштування Xdebug
- Версія: **Xdebug v3.3.2**
- `xdebug.mode = debug,develop`
- `xdebug.client_host = 127.0.0.1`
- `xdebug.client_port = 9003`
- `xdebug.start_with_request = yes`
