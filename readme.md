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
brew install php72 node composer
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
```

### Generate secrets
```
php artisan key:generate
php artisan jwt:secret
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


Other Items of Note
-------------------

List all routes
```
php artisan route:list
```

- jwt ttl/expire times (jwt.php)
  - JWT_REFRESH_TTL (number of minutes refresh token is valid for, once invalid the user will have to re-authenticate/login)
  - JWT_TTL (token stays valid for x number of minutes then app will try to refresh)

- optional lamp stack: https://bitnami.com/stack/lamp/installer and install the laravel module during install.
