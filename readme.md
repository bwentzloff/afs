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

- view the schema
```
php artisan schema:show
```

Testing
-------

### Initialize test database
```
touch database/test.sqlite
```

Or create a new empty file called `test.sqlite` in
`[PATH_TO_APP_ROOT_DIR]/database`.

### Modify .env.test to use test database
```
cp .env.test.example .env.test
APP_ROOT="$(pwd)"
sed -i '' -e "s;DB_DATABASE=laravel;DB_DATABASE=$APP_ROOT/database/test.sqlite;" .env.test
```

Or rename `.env.test.example` to `.env.test` and make these changes:

```
DB_DATABASE=[PATH_TO_APP_ROOT_DIR]/database/test.sqlite
```
