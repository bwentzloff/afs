AltFantasySports
================

Code for [altfantasysports.com](https://altfantasysports.com/).

Install Dependencies for Mac OSX
--------------------------------

### Install Homebrew
```
/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
```

### Get more recent PHP version (and version manager)
```
brew install php72 phpunit node composer
brew tap wilmoore/formulae
brew install php-version
echo '[[ -e ~/.phpbrew/bashrc ]] && source ~/.phpbrew/bashrc' >> ~/.bash_profile
echo 'source "$(brew --prefix php-version)/php-version.sh" && php-version 7' >> ~/.bash_profile
```

NOTE: May need to change `.bash_profile` to `.zshrc` if using zshell which is
the new default shell on Catalina (untested).

*TODO: Document Dependencies for Windows/Linux*


Application Setup
-----------------

### Install Dependencies
```
composer install
npm install
```

### Initialize local database
```
touch database/database.sqlite
```

Or create a new empty file called `database.sqlite` in
`[PATH_TO_APP_ROOT_DIR]/database`.

### Modify .env to use local database
```
cp .env.example .env
APP_ROOT="$(pwd)"
sed -i '' -e 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i '' -e "s;DB_DATABASE=laravel;DB_DATABASE=$APP_ROOT/database/database.sqlite;" .env
```

Or rename `.env.example` to `.env` and make these changes:

```
DB_CONNECTION=sqlite
DB_DATABASE=[PATH_TO_APP_ROOT_DIR]/database/database.sqlite
```

### Run all database migrations
```
php artisan migrate:install
php artisan migrate
```

### Generate secrets
```
php artisan key:generate
php artisan jwt:secret
```

### Load necessary data
The sample data that can be seeded creates 4 users of the site: user1@afs.com,user2@afs.com,user3@afs.com, and user4@afs.com, all with the password of 'password'
```
php artisan db:seed
```


Running the Application
-----------------------

If not using apache or another web server, run the embedded server with:

```
php artisan serve --port=9090
```
- start node
```
npm run watch
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

### Run the test-suite
```
phpunit
```


Other Items of Note
-------------------

### List all available routes
```
php artisan route:list
```

### View the schema
```
php artisan schema:show
```

### JWT ttl/expire times (jwt.php)
- JWT_REFRESH_TTL (number of minutes refresh token is valid for, once
  invalid the user will have to re-authenticate/login)
- JWT_TTL (token stays valid for x number of minutes then app will try
  to refresh)

- optional lamp stack: https://bitnami.com/stack/lamp/installer and
  install the laravel module during install.
