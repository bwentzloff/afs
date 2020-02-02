### environment setup
- rename .env.example to .env and run
```
php artisan key:generate
php artisan jwt:secret
```

- database
```
touch database/database.sqlite
```
  - .env 
```
DB_CONNECTION=sqlite
DB_DATABASE=$ROOT/database/database.sqlite
```
  - migrations
```
php artisan migrate:install
php artisan migrate
```

### run app
- if not using apache or another web server, you can run an embedded server with:
```
php artisan serve --port=9090
```
- start node
```
npm run watch
```

### other notes
- list all routes
```
php artisan route:list
```

- jwt ttl/expire times (jwt.php)
  - JWT_REFRESH_TTL (number of minutes refresh token is valid for, once invalid the user will have to re-authenticate/login)
  - JWT_TTL (token stays valid for x number of minutes then app will try to refresh)

- optional lamp stack: https://bitnami.com/stack/lamp/installer and install the laravel module during install.