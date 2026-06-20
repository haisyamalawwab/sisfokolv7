---
description: how to run PHP commands (artisan, scripts) for this repo using PHP 7.4
---

# PHP 7.4 Execution Rule for massetyppiwm

This repo requires **PHP 7.4** (Laravel 5.8 compatibility).  
Always use the full path to PHP 7.4 binary instead of the system `php` command.

## PHP 7.4 Binary Path

```
D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe
```

## Running php artisan

// turbo
Replace `php artisan` with the full path:

```powershell
& "D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe" artisan <command>
```

**Examples:**
```powershell
# Run migrations
& "D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe" artisan migrate

# Run seeder
& "D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe" artisan db:seed

# Run specific seeder
& "D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe" artisan db:seed --class=BrandSeeder

# Clear cache
& "D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe" artisan cache:clear
& "D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe" artisan config:clear
& "D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe" artisan view:clear

# Run tests
& "D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe" artisan test

# Tinker
& "D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe" artisan tinker
```

## Running PHP Scripts Directly

```powershell
& "D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe" <script.php>
```

## Working Directory

Always run from the `core` directory:
```
d:\laragon\www\massetyppiwm\core
```
