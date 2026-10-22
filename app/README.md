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
php bin/import-media.php                         # old pictures → uploads/ with 480/960/1600 sizes
php bin/create-admin.php
```

## Run

```sh
php app/bin/publish.php                                          # static pages from the database → public/
php -d upload_max_filesize=20M -d post_max_size=64M -S 127.0.0.1:8000 -t public app/bin/dev-router.php   # site + /api + /admin
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
| `php bin/publish.php` | build, check links, swap into `public/` (same as the admin's Publish button) |
| `php bin/publish.php --rollback[=ID]` / `--list` | put a previous build back / list them |
| `php bin/import-media.php` | give pictures from the old site (assets/img) the upload sizes in `uploads/` (once, after seed) |
| `php bin/create-admin.php` | add a user (asks for the password, hidden) |
| `php bin/cleanup.php` | delete old rate limits, login attempts, spam (30 d) and logs (30 d) |
| `php bin/backup.php` | gzip dump to `storage/backups/`, keeps 14 |
| `php bin/backup.php --restore=FILE --db=NAME` | load a backup into another (existing) database |
| `composer test` | PHPUnit |

`public/<lang>/` comes only from the database through the Publisher. The old Python build (`build/`) was removed in P2-C11;
it is in git history before that commit. `scripts/optimize_media.py` stays (Python, run on a developer machine): WebP for
`public/assets/img/`, the hero video, and `--zh-font` to rebuild the Chinese font subset when the Publisher warns about
missing characters. The GitHub Pages preview is built from `database/seed/seed.json` (`.github/workflows/pages.yml`).
