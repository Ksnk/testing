# Обновление статуса АПИ #

Тестовое задание на понимание  и умение работать с laravel и так...

## Установка

Клонировать репозиторий-
```
git clone git@bitbucket.org:ksnk/testing.git
```

```
cd testing
```

```
composer install
```

Изменить настройки в .env, чтобы они указывали на базу данных(`DB_USERNAME`, `DB_PASSWORD`).

Then create a database named `todos` and then do a database migration using this command-
```
php artisan migrate
```

Then change permission of storage folder using thins command-
```
(sudo) chmod 777 -R storage
```

At last generate application key, which will be used for password hashing, session and cookie encryption etc.
```
php artisan key:generate
```

