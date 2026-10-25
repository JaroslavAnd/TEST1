# Лабораторні роботи з курсу "Сучасні фреймворки вебпрограмування"

**Дисципліна:** Сучасні фреймворки веб-програмування  
**Студент:** Патлай Максим (група ВТ-23-1)  
**Репозиторій:** [https://github.com/JaroslavAnd/TEST1.git](https://github.com/JaroslavAnd/TEST1.git)  
**Гілка Лабораторної №3:** `lab3`  
**Гілка Лабораторної №4:** `lab4`

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

Команди для створення користувача та баз даних у MySQL:
```sql
CREATE USER IF NOT EXISTS 'bank_user'@'localhost' IDENTIFIED BY 'bank_password';
CREATE USER IF NOT EXISTS 'bank_user'@'%' IDENTIFIED BY 'bank_password';
GRANT ALL PRIVILEGES ON *.* TO 'bank_user'@'localhost';
GRANT ALL PRIVILEGES ON *.* TO 'bank_user'@'%';
FLUSH PRIVILEGES;

CREATE DATABASE IF NOT EXISTS bank_credit_symfony CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS bank_credit_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

### 2. Спроектована структура бази даних (5 таблиць зі зв'язками):
1. **`clients`** (Клієнти банку)
   - `id`, `first_name`, `last_name`, `email` (unique), `phone`, `tax_id` (unique), `created_at`
2. **`bank_accounts`** (Банківські рахунки)
   - `id`, `client_id` (FK $\rightarrow$ `clients.id`), `account_number` (IBAN, unique), `balance`, `currency`, `status`, `created_at`
3. **`credit_types`** (Кредитні програми)
   - `id`, `name` (unique), `interest_rate`, `min_amount`, `max_amount`, `term_months`
4. **`credits`** (Оформлені кредити)
   - `id`, `client_id` (FK $\rightarrow$ `clients.id`), `bank_account_id` (FK $\rightarrow$ `bank_accounts.id`), `credit_type_id` (FK $\rightarrow$ `credit_types.id`), `amount`, `remaining_amount`, `start_date`, `end_date`, `status`, `created_at`
5. **`payments`** (Виплати / платежі за кредитами)
   - `id`, `credit_id` (FK $\rightarrow$ `credits.id`), `amount`, `payment_date`, `payment_method`, `status`, `created_at`

#### Зв'язки між сутностями:
- `clients` (1) $\longleftrightarrow$ (N) `bank_accounts`
- `clients` (1) $\longleftrightarrow$ (N) `credits`
- `bank_accounts` (1) $\longleftrightarrow$ (N) `credits`
- `credit_types` (1) $\longleftrightarrow$ (N) `credits`
- `credits` (1) $\longleftrightarrow$ (N) `payments`

---

### 3. Валідація та обробка конфліктів цілісності (HTTP Status Codes)
В обох фреймворках реалізовано однаковий стандартизований підхід:
1. **Валідація унікальності полів (`email`, `taxId`, `accountNumber`, `name`)**:
   - При спробі створити або оновити запис з існуючим значенням унікального поля повертається **HTTP 422 Unprocessable Entity** з детальним описом помилки (замість SQL Exception 500).
2. **Захист від некоректного видалення зв'язаних даних (Foreign Key Constraints)**:
   - При спробі видалити `BankAccount` або `CreditType`, які прив'язані до існуючих кредитів, система повертає **HTTP 409 Conflict** із поясненням причини (наприклад: *"Неможливо видалити банківський рахунок, оскільки до нього прив'язані оформлені кредити"*), запобігаючи падінню сервера з помилкою 500.

---

## Інструкція з розгортання та запуску

### 1. Symfony 6.4

```bash
cd symfony

# 1. Встановлення залежностей
composer install

# 2. Створення бази даних (якщо ще не створена)
php bin/console doctrine:database:create --if-not-exists

# 3. Виконання міграцій для створення таблиць
php bin/console doctrine:migrations:migrate --no-interaction
# (або пряме оновлення схеми: php bin/console doctrine:schema:update --force)

# 4. Заповнення початковими демо-даними (клієнти, рахунки, програми, кредити, виплати)
php bin/console app:seed-bank-data

# 5. Запуск локального веб-сервера
symfony server:start
# або вбудованим сервером PHP:
php -S 127.0.0.1:8000 -t public
```

#### Перевірка ендпоінтів Symfony:
- **Клієнти:** `GET http://127.0.0.1:8000/api/clients`
- **Рахунки:** `GET http://127.0.0.1:8000/api/bank-accounts`
- **Програми кредитування:** `GET http://127.0.0.1:8000/api/credit-types`
- **Кредити:** `GET http://127.0.0.1:8000/api/credits`
- **Платежі:** `GET http://127.0.0.1:8000/api/payments`

---

### 2. Laravel 11

```bash
cd laravel

# 1. Встановлення залежностей
composer install

# 2. Копіювання конфігураційного файлу оточення
cp .env.example .env
# (у Windows PowerShell: Copy-Item .env.example .env)

# 3. Генерація ключа додатку
php artisan key:generate

# 4. Виконання міграцій створення таблиць та заповнення даними
php artisan migrate --seed

# 5. Запуск серверу додатку
php artisan serve
```

#### Перевірка ендпоінтів Laravel:
- **Клієнти:** `GET http://127.0.0.1:8000/api/clients`
- **Рахунки:** `GET http://127.0.0.1:8000/api/bank-accounts`
- **Програми кредитування:** `GET http://127.0.0.1:8000/api/credit-types`
- **Кредити:** `GET http://127.0.0.1:8000/api/credits`
- **Платежі:** `GET http://127.0.0.1:8000/api/payments`

---

## Лабораторна робота №4: Пагінація та фільтрація

Розширено всі 5 ендпоінтів GET-списку в обох фреймворках: додано **пагінацію** та **фільтрацію за кожним полем таблиці**.

### Параметри пагінації (для всіх ендпоінтів)

| Параметр | Псевдонім | Тип | За замовчуванням | Опис |
|---|---|---|---|---|
| `page` | — | int | 1 | Номер сторінки |
| `itemsPerPage` | `per_page`, `limit` | int | 10 | Кількість записів на сторінці |

### Формат відповіді

```json
{
  "data": [ /* масив об'єктів */ ],
  "pagination": {
    "currentPage": 1,
    "itemsPerPage": 10,
    "totalItems": 42,
    "totalPages": 5,
    "hasNextPage": true,
    "hasPreviousPage": false
  }
}
```

---

### Параметри фільтрації по ендпоінтах

#### `GET /api/clients`

| Параметр | Тип | Опис |
|---|---|---|
| `id` | int | Точний збіг ID |
| `firstName` / `first_name` | string | Пошук підрядком (LIKE) |
| `lastName` / `last_name` | string | Пошук підрядком (LIKE) |
| `email` | string | Пошук підрядком (LIKE) |
| `phone` | string | Пошук підрядком (LIKE) |
| `taxId` / `tax_id` | string | Пошук підрядком (LIKE) |
| `createdFrom` / `created_from` | date | Дата створення від (включно) |
| `createdTo` / `created_to` | date | Дата створення до (включно) |

**Приклад:** `GET /api/clients?page=1&itemsPerPage=5&firstName=Іван`

---

#### `GET /api/bank-accounts`

| Параметр | Тип | Опис |
|---|---|---|
| `id` | int | Точний збіг ID |
| `clientId` / `client_id` | int | ID клієнта (точний) |
| `accountNumber` / `account_number` | string | Пошук підрядком (LIKE) |
| `currency` | string | Точний збіг валюти (напр. `UAH`) |
| `status` | string | Точний збіг статусу (`active`/`closed`) |
| `balance` | float | Точна сума балансу |
| `minBalance` / `min_balance` | float | Баланс від |
| `maxBalance` / `max_balance` | float | Баланс до |
| `createdFrom` / `created_from` | date | Дата створення від |
| `createdTo` / `created_to` | date | Дата створення до |

**Приклад:** `GET /api/bank-accounts?currency=UAH&status=active&minBalance=1000`

---

#### `GET /api/credit-types`

| Параметр | Тип | Опис |
|---|---|---|
| `id` | int | Точний збіг ID |
| `name` | string | Пошук підрядком (LIKE) |
| `interestRate` / `interest_rate` | float | Точна процентна ставка |
| `minInterestRate` / `min_interest_rate` | float | Ставка від |
| `maxInterestRate` / `max_interest_rate` | float | Ставка до |
| `minAmount` / `min_amount` | float | Мінімальна сума кредиту від |
| `maxAmount` / `max_amount` | float | Максимальна сума кредиту до |
| `termMonths` / `term_months` | int | Строк у місяцях (точний) |

**Приклад:** `GET /api/credit-types?minInterestRate=5&maxInterestRate=15`

---

#### `GET /api/credits`

| Параметр | Тип | Опис |
|---|---|---|
| `id` | int | Точний збіг ID |
| `clientId` / `client_id` | int | ID клієнта (точний) |
| `bankAccountId` / `bank_account_id` | int | ID рахунку (точний) |
| `creditTypeId` / `credit_type_id` | int | ID типу кредиту (точний) |
| `status` | string | Статус (`active`/`closed`/`overdue`) |
| `amount` | float | Точна сума |
| `minAmount` / `min_amount` | float | Сума від |
| `maxAmount` / `max_amount` | float | Сума до |
| `minRemainingAmount` / `min_remaining_amount` | float | Залишок від |
| `maxRemainingAmount` / `max_remaining_amount` | float | Залишок до |
| `startDate` / `start_date` | date | Дата початку від |
| `endDate` / `end_date` | date | Дата закінчення до |
| `createdFrom` / `created_from` | date | Дата створення від |
| `createdTo` / `created_to` | date | Дата створення до |

**Приклад:** `GET /api/credits?status=active&minAmount=10000&clientId=1`

---

#### `GET /api/payments`

| Параметр | Тип | Опис |
|---|---|---|
| `id` | int | Точний збіг ID |
| `creditId` / `credit_id` | int | ID кредиту (точний) |
| `amount` | float | Точна сума платежу |
| `minAmount` / `min_amount` | float | Сума від |
| `maxAmount` / `max_amount` | float | Сума до |
| `paymentMethod` / `payment_method` | string | Спосіб оплати (`bank_transfer`/`cash`/`card`) |
| `status` | string | Статус (`pending`/`completed`/`failed`) |
| `paymentDateFrom` / `payment_date_from` | date | Дата платежу від |
| `paymentDateTo` / `payment_date_to` | date | Дата платежу до |
| `createdFrom` / `created_from` | date | Дата створення від |
| `createdTo` / `created_to` | date | Дата створення до |

**Приклад:** `GET /api/payments?status=completed&paymentMethod=card&page=2&itemsPerPage=20`

---

### Реалізація

**Symfony 6.4:**
- `symfony/src/Service/PaginationService.php` — загальний пагінатор на Doctrine `Paginator`
- `symfony/src/Repository/*Repository.php` — методи `getFilteredQueryBuilder(array $filters)` у кожному репозиторії
- Усі 5 контролерів: метод `list()` передає `$request->query->all()` до репозиторію та пагінатора

**Laravel 11:**
- `laravel/app/Services/PaginationService.php` — загальний пагінатор на Eloquent `paginate()`
- `laravel/app/Models/*.php` — `scopeFilter(Builder $query, array $filters)` у кожній моделі
- Усі 5 контролерів: `Model::filter($request->query())->paginate()`

---

## Налаштування Xdebug
- Версія: **Xdebug v3.3.2**
- `xdebug.mode = debug,develop`
- `xdebug.client_host = 127.0.0.1`
- `xdebug.client_port = 9003`
- `xdebug.start_with_request = yes`
