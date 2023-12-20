# UpChecker

Laravel application to manage & run automated scripts that check that remote servers are up 

## :warning: Work in progress

This project is under development. Please wait to use it in production.

## Useful commands

### Run after git clone
```shell 
composer update;
composer run-script post-root-package-install;
composer run-script post-create-project-cmd;
php artisan migrate --seed;
npm update;
npm run build;
```

You can now log in with credientials :
Login : `dev@test.com`
Password : `password`

### Run during work
```shell 
npm run dev;
```

### Run before commit
```shell
./vendor/bin/pint;
./vendor/bin/pint -v; # verbose
./vendor/bin/pint --test; # inspect without changes
```
