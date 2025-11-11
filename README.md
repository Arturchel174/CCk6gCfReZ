Установка docker
---------------------------------
Выполнить команду для того, чтобы собрать образ

```
docker compose up --build -d
```
Теперь нужно провалиться в контейнер php

```
docker exec -it site_php sh
```
После выполнить команду для установки пакетов 

```
cd site && composer install
```
Выполнить команду для инициализации приложения 

```
php init
```
Пример подключения к бд в файле www/site/config/db.php

```
    'components' => [
        ...
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'mysql:host=site_mysql;dbname=site',
            'username' => 'root',
            'password' => 'site',
            'charset' => 'utf8',
        ],
        ...
    ],
```

