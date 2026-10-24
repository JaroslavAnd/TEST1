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

## Налаштування Xdebug
- Версія: **Xdebug v3.3.2**
- `xdebug.mode = debug,develop`
- `xdebug.client_host = 127.0.0.1`
- `xdebug.client_port = 9003`
- `xdebug.start_with_request = yes`
