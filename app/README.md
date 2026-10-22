# app/ — PHP backend (contact API + admin)

Code outside the docroot. Plan: [docs/plan/03-backend.md](../docs/plan/03-backend.md), layout: [05-folder-structure.md](../docs/plan/05-folder-structure.md).

## Local setup (macOS, once)

```sh
brew install php@8.3 mysql@8.0 composer mailpit
# composer also installs the newest PHP as `php`; put 8.3 first so it matches the server
echo 'export PATH="/opt/homebrew/opt/php@8.3/bin:/opt/homebrew/opt/mysql@8.0/bin:$PATH"' >> ~/.zshrc
brew services start mysql@8.0
brew services start mailpit          # SMTP 127.0.0.1:1025, inbox http://127.0.0.1:8025

# database + user (same names as on the server)
mysql -uroot -e "CREATE DATABASE xtklaos_xtk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'xtklaos_app'@'localhost' IDENTIFIED BY '<password>';
  GRANT ALL ON xtklaos_xtk.* TO 'xtklaos_app'@'localhost';"

cd app
composer install
cp config/config.example.php config/config.php   # fill in db.pass and hmac_secret
php bin/migrate.php
php bin/seed.php                                 # first content (divisions, products, news) from database/seed/seed.json
php bin/create-admin.php
```

## Run

```sh
python3 build/build.py                                           # static pages → public/
php -S 127.0.0.1:8000 -t public app/bin/dev-router.php           # site + /api + /admin
```

- Site: http://127.0.0.1:8000/lo/contact.html
- Admin: http://127.0.0.1:8000/admin
- Emails: http://127.0.0.1:8025 (Mailpit)

## Commands

| Command | What it does |
|---|---|
| `php bin/migrate.php [--status]` | apply new `database/migrations/*.sql` |
| `php bin/seed.php` | load `database/seed/seed.json` into an empty database (refuses if content exists) |
| `php bin/build-site.php --out=DIR` | render every page from the database into DIR (`--seed=FILE` renders from a seed file instead) |
| `php bin/create-admin.php` | add a user (asks for the password, hidden) |
| `php bin/cleanup.php` | delete old rate limits, login attempts, spam (30 d) and logs (30 d) |
| `php bin/backup.php` | gzip dump to `storage/backups/`, keeps 14 |
| `php bin/backup.php --restore=FILE --db=NAME` | load a backup into another (existing) database |
| `composer test` | PHPUnit |
