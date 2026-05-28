# 🔐 PassManager v0.3

A secure, web-based **password manager** built in PHP using **Object-Oriented Programming** principles. Each user's passwords are encrypted with a unique key derived from their login credentials — meaning only they can ever read their own vault.

---

## ✨ Features

| Feature | Details |
|---|---|
| 🔑 User registration & login | Username/password auth with `password_hash()` / `password_verify()` |
| 🛡️ Per-user AES-256-CBC encryption | Every saved password is encrypted before it touches the database |
| 🔒 Encrypted key architecture | The encryption key itself is encrypted with the user's password |
| 🎲 Password generator | Configurable character sets (lowercase, uppercase, numbers, symbols) |
| 📋 Password vault dashboard | View, add, and delete saved passwords |
| 🔄 Password change | Re-encrypts the vault key atomically when the master password changes |
| 🚪 Secure logout | Full session destroy on logout |

---

## 🏗️ OOP Architecture

The project is built around **5 core classes**, each with a single, clear responsibility:

```
php_projektas/
├── classes/
│   ├── Database.php          ← Singleton PDO wrapper
│   ├── Encryptor.php         ← Static AES-256-CBC encrypt/decrypt
│   ├── PasswordEntry.php     ← Save, retrieve, delete password records
│   ├── PasswordGenerator.php ← Configurable random password generator
│   └── User.php              ← Register, login, logout, change password
├── views/
│   ├── login.php             ← Login page & form handler
│   ├── register.php          ← Registration page & form handler
│   ├── dashboard.php         ← Main vault view
│   ├── add_password.php      ← Add/generate passwords
│   ├── change_password.php   ← Change master password
│   ├── delete_password.php   ← Delete a password entry (POST only)
│   └── logout.php            ← Destroys session
├── assets/
│   └── style.css             ← Stylesheet
├── config.php                ← DB credentials (not committed)
└── config dot php example.txt ← Config template
```

---

## 📐 Class Reference

### `Database` — Singleton PDO Wrapper
> **Pattern: Singleton**

Ensures only one database connection exists for the lifetime of a request.

| Method | Signature | Description |
|---|---|---|
| `getInstance()` | `static → Database` | Returns the single shared instance |
| `query()` | `(string $sql, array $params) → array` | Runs a SELECT, returns all rows |
| `execute()` | `(string $sql, array $params) → bool` | Runs INSERT / UPDATE / DELETE |
| `lastInsertId()` | `→ string` | Returns the last auto-increment ID |

- Uses `PDO::ATTR_EMULATE_PREPARES => false` — real prepared statements, immune to SQL injection.
- Connection options set `ERRMODE_EXCEPTION` and `FETCH_ASSOC` by default.

---

### `Encryptor` — AES-256-CBC Encryption
> **Pattern: Static utility class**

All methods are `static` — no instantiation needed.

| Method | Signature | Description |
|---|---|---|
| `encrypt()` | `(string $plaintext, string $key) → string` | Encrypts and returns base64-encoded `IV + ciphertext` |
| `decrypt()` | `(string $encrypted, string $key) → string\|false` | Decodes, extracts IV, decrypts |
| `generateKey()` | `→ string` | Generates a 64-char hex key using `random_bytes(32)` |

**How it works:**
1. A random 16-byte **IV** is generated with `openssl_random_pseudo_bytes()` for every encryption.
2. The IV is prepended to the ciphertext before base64-encoding.
3. On decryption, the IV is sliced off the front and used to decrypt the rest.

---

### `PasswordGenerator` — Configurable Password Generator

Instantiated with counts for each character category.

| Method | Signature | Description |
|---|---|---|
| `__construct()` | `(int $lc=2, int $uc=2, int $num=2, int $spec=2)` | Sets minimum character counts per category |
| `generate()` | `→ string` | Builds and shuffles the password |
| `getLength()` | `→ int` | Returns total password length |

**Character pools:**
- Lowercase: `abcdefghijklmnopqrstuvwxyz`
- Uppercase: `ABCDEFGHIJKLMNOPQRSTUVWXYZ`
- Numbers: `0123456789`
- Specials: `!@#$%^&*()_+-=[]{}|;:,.<>?`

Uses `random_int()` (cryptographically secure) to pick characters, then `shuffle()` to randomise order.

---

### `PasswordEntry` — Vault Record Management

| Method | Signature | Description |
|---|---|---|
| `__construct()` | | Gets the `Database` singleton |
| `save()` | `(int $userId, string $title, string $plainPw, string $key) → bool` | Encrypts and inserts a new entry |
| `getAllForUser()` | `(int $userId, string $key) → array` | Fetches all entries and decrypts each `password` field |
| `delete()` | `(int $entryId, int $userId) → bool` | Deletes an entry, enforces ownership via `user_id` |

---

### `User` — Authentication & Session Management

| Method | Signature | Description |
|---|---|---|
| `register()` | `(string $username, string $password) → array` | Validates, hashes password, generates & encrypts vault key, inserts user |
| `login()` | `(string $username, string $password) → array` | Verifies hash, decrypts vault key, populates session |
| `changePassword()` | `(string $oldPw, string $newPw) → array` | Verifies old pw, re-encrypts vault key with new pw, updates DB atomically |
| `isLoggedIn()` | `static → bool` | Checks `$_SESSION['user_id']` |
| `requireLogin()` | `static → void` | Redirects to login if not authenticated |
| `logout()` | `static → void` | Clears and destroys session, redirects |

---

## 🔐 Security Design

### Zero-Knowledge Vault Key Architecture

This is the core security design of the application:

```
Registration:
  1. User picks a password P
  2. A random 64-char hex key K is generated (Encryptor::generateKey)
  3. K is encrypted with P  →  stored as encrypted_key in DB
  4. P is hashed with bcrypt  →  stored as password_hash in DB
  ⚠️  The plaintext K is NEVER stored

Login:
  1. P is verified against password_hash (password_verify)
  2. encrypted_key is decrypted with P  →  plaintext K retrieved
  3. K is stored only in $_SESSION['user_key'] for the session lifetime

Password Change:
  1. Old P verified
  2. K decrypted with old P
  3. K re-encrypted with new P  →  new encrypted_key
  4. New P hashed  →  new password_hash
  5. Both updated atomically in one SQL statement
  6. Session key updated so current session keeps working
```

### Other Security Measures

| Measure | Implementation |
|---|---|
| SQL Injection prevention | PDO prepared statements with `EMULATE_PREPARES => false` |
| XSS prevention | All output passed through `htmlspecialchars()` |
| Username enumeration prevention | Login returns the same error for wrong username OR wrong password |
| Session fixation prevention | `session_regenerate_id(true)` called on login |
| Integer injection prevention | Entry IDs cast to `(int)` before use in `delete_password.php` |
| Ownership enforcement | DELETE query always includes `AND user_id = ?` |

---

## ⚙️ Requirements

- **PHP** 8.1 or newer (uses typed properties, `string|false` union types)
- **MySQL** 5.7+ or **MariaDB** 10.3+
- **XAMPP** (or any Apache + PHP + MySQL stack)
- PHP extension: `openssl` (for encryption)
- PHP extension: `pdo_mysql` (for database)

---

## 🗄️ Database Schema

Run the following SQL to create the required tables:

```sql
CREATE DATABASE IF NOT EXISTS book CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE book;

CREATE TABLE users (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username       VARCHAR(50)  NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    encrypted_key  TEXT         NOT NULL,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE passwords (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id            INT UNSIGNED NOT NULL,
    title              VARCHAR(100) NOT NULL,
    encrypted_password TEXT         NOT NULL,
    created_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 🚀 Setup & Installation

### 1. Clone / Copy the project

Place the project folder inside your XAMPP web root:

```
C:\xampp\htdocs\php_projektas\
```

### 2. Configure the database

Copy the example config and fill in your credentials:

```bash
# Copy the example
copy "config dot php example.txt" config.php
```

Edit `config.php`:

```php
<?php
define('DB_HOST',    'localhost');
define('DB_NAME',    'book');          // must match the database you created
define('DB_USER',    'root');          // your MySQL username
define('DB_PASS',    '');              // your MySQL password (blank for default XAMPP)
define('DB_CHARSET', 'utf8mb4');
```

### 3. Create the database tables

Open **phpMyAdmin** (`http://localhost/phpmyadmin`) and run the SQL from the [Database Schema](#️-database-schema) section above.

### 4. Start XAMPP

Make sure both **Apache** and **MySQL** are running in the XAMPP Control Panel.

### 5. Open in browser

```
http://localhost/php_projektas/views/login.php
```

---

## 🖥️ Pages & Navigation

| URL | Description |
|---|---|
| `/views/login.php` | Login form — entry point |
| `/views/register.php` | Create a new account |
| `/views/dashboard.php` | View all saved passwords (vault) |
| `/views/add_password.php` | Generate and/or save a new password |
| `/views/change_password.php` | Change the master password |
| `/views/delete_password.php` | POST-only handler to delete an entry |
| `/views/logout.php` | Destroys session and redirects to login |

---

## 📦 Version History

| Version | Changes |
|---|---|
| v0.3 | Password change with atomic key re-encryption; session key update on password change |
| v0.2 | AES-256-CBC encryption for stored passwords; per-user vault key |
| v0.1 | Basic user auth, plaintext password storage (proof of concept) |

---

## 🎓 OOP Concepts Demonstrated

This project was built as an OOP exam project and demonstrates:

- ✅ **Encapsulation** — private properties in all classes, public methods only expose what's needed
- ✅ **Singleton pattern** — `Database` class uses a static `$instance` to prevent duplicate connections
- ✅ **Static methods** — `Encryptor` (utility), `User::isLoggedIn()`, `User::requireLogin()`, `User::logout()`
- ✅ **Constructor injection / dependency** — `PasswordEntry` and `User` receive the `Database` instance via constructor
- ✅ **Separation of concerns** — each class has one job; views handle presentation only
- ✅ **Type declarations** — typed properties (`private PDO $pdo`), parameter types, return types throughout
- ✅ **Union return types** — `decrypt(): string|false` (PHP 8+ syntax)
- ✅ **Prepared statements** — safe DB access via PDO in the `Database` class

---

## 📄 License

Academic project — for educational purposes.
