# 07 — Deploy ແລະ ການດູແລລະບົບ

## 1. ສະພາບແວດລ້ອມ
| ຊື່ | URL | ໃຊ້ສຳລັບ |
|---|---|---|
| Local | `http://localhost:8000` (`php -S localhost:8000 -t public`) | ພັດທະນາ |
| Preview | `https://new.xtklaos.com` (ຊື່ສຸດທ້າຍລໍລູກຄ້າເລືອກ) | ລູກຄ້າເບິ່ງ ແລະ ທົດສອບ. `noindex` |
| Production | `https://xtklaos.com` | ຫຼັງເຟດ 4 ເທົ່ານັ້ນ |

ທັງ preview ແລະ production ໃຊ້ໂຄດ ແລະ DB ຊຸດດຽວກັນ. ໃນເຟດ 4, ໂດເມນຫຼັກຖືກຊີ້ມາບ່ອນດຽວກັນ (§6).

## 2. ຕັ້ງຄ່າ server ຄັ້ງທຳອິດ (ເຟດ S)

### 2.1 ໃນ cPanel
1. **Domains → Create a New Domain**: `new.xtklaos.com`, ບໍ່ໝາຍ "Share document root", Document Root = `xtk/public`.
2. **MultiPHP Manager**: ຕັ້ງ `new.xtklaos.com` ເປັນ `ea-php83`.
3. **MultiPHP INI Editor** (PHP 8.3): `display_errors = Off`, `log_errors = On`, `upload_max_filesize = 20M`, `post_max_size = 64M`, `memory_limit = 512M`, `max_execution_time = 120` (ຄືກັບ `public/admin/.user.ini`; GD ຕ້ອງໃຊ້ໜ່ວຍຄວາມຈຳຫຼາຍຕອນຫຍໍ້ຮູບຂະໜາດໃຫຍ່), `session.cookie_secure = On`, `session.cookie_httponly = On`, `expose_php = Off`.
4. **SSL/TLS Status**: ກວດວ່າ AutoSSL ຄອບຄຸມ subdomain (ໃບ wildcard ທີ່ມີຢູ່ຄວນຄອບຄຸມແລ້ວ).
5. **SSH Access → Manage SSH Keys**: Import public key ຈາກເຄື່ອງນັກພັດທະນາ ແລ້ວ **Authorize**. ທົດສອບ: `ssh -p 32 xtklaos@xtklaos.com`.
6. **MySQL Database Wizard** (ເຟດ 1): DB `xtklaos_xtk`, user `xtklaos_app`, ລະຫັດສຸ່ມ 32 ຕົວ, ສິດ: `SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES, LOCK TABLES`.
7. **Email Accounts** (ເຟດ 1): `noreply@xtklaos.com`, ລະຫັດສຸ່ມ.
8. **Email Deliverability**: ກົດ Repair/Install ໃຫ້ SPF, DKIM ຂຶ້ນ Valid. ເພີ່ມ DMARC `v=DMARC1; p=none; rua=mailto:keokhounma@xtklaos.com` ໃນ Zone Editor.

### 2.2 ໃນ server (ຜ່ານ SSH)
```bash
mkdir -p ~/xtk/app/storage/{logs,sessions,build,backups}
chmod 700 ~/xtk/app/storage
cp ~/xtk/app/config/config.example.php ~/xtk/app/config/config.php
chmod 600 ~/xtk/app/config/config.php
nano ~/xtk/app/config/config.php        # ໃສ່ຄ່າຈິງ
PHP=/opt/cpanel/ea-php83/root/usr/bin/php
$PHP ~/xtk/app/bin/migrate.php
$PHP ~/xtk/app/bin/create-admin.php
```
PHP CLI ທີ່ຄຳສັ່ງ `php` ທຳມະດາເອີ້ນອາດບໍ່ແມ່ນ 8.3. **ໃຊ້ path ເຕັມສະເໝີ** (ກວດ path ຈິງໃນ P0-A1).

## 3. `config.example.php`
```php
<?php
return [
    'env'      => 'production',                 // production | local
    'base_url' => 'https://new.xtklaos.com',
    'db' => [
        'dsn'  => 'mysql:host=localhost;dbname=xtklaos_xtk;charset=utf8mb4',
        'user' => 'xtklaos_app',
        'pass' => 'CHANGE_ME',
    ],
    'mail' => [
        'host' => 'mail.xtklaos.com', 'port' => 465, 'secure' => 'ssl',
        'user' => 'noreply@xtklaos.com', 'pass' => 'CHANGE_ME',
        'from' => 'noreply@xtklaos.com', 'from_name' => 'XTK Website',
    ],
    'hmac_secret' => 'CHANGE_ME_64_RANDOM_CHARS', // ສຳລັບ token ເວລາຂອງຟອມ
    'session_name' => 'xtk_admin',
];
```

## 4. Deploy

### 4.1 `scripts/deploy.sh` (ແລ່ນໃນເຄື່ອງນັກພັດທະນາ)
ຂັ້ນຕອນທີ່ສະຄຣິບເຮັດ:
1. ກວດວ່າ git ສະອາດ ແລະ ຢູ່ branch `main`. ບໍ່ສະອາດ → ຢຸດ.
2. `composer install --no-dev --optimize-autoloader` ໃນ `app/`.
3. ແລ່ນ PHPUnit. ລົ້ມເຫຼວ → ຢຸດ.
4. `rsync -az --delete -e "ssh -p 32"` ຈາກ repo ໄປ `xtklaos@xtklaos.com:~/xtk/` ໂດຍ **ຍົກເວັ້ນ**:
   ```
   .git/  .claude/  docs/  build/  scripts/  app/tests/
   app/config/config.php  app/storage/
   public/uploads/
   public/lo/ public/en/ public/th/ public/zh/ public/index.html public/sitemap.xml public/robots.txt   ← ຕັ້ງແຕ່ເຟດ 2
   ```
   ໃນເຟດ 0–1 ໜ້າເວັບຍັງສ້າງດ້ວຍ Python ໃນເຄື່ອງ, ສະນັ້ນ **ບໍ່** ຍົກເວັ້ນ `public/lo/` … ໃນເຟດນັ້ນ.
5. ຜ່ານ SSH: `$PHP ~/xtk/app/bin/migrate.php` ແລ້ວ `$PHP ~/xtk/app/bin/publish.php` (ຕັ້ງແຕ່ເຟດ 2).
6. ກວດ `curl -fsS https://new.xtklaos.com/lo/index.html` ແລະ `/api/contact.php?token=1` ຕອບ 200.
7. ພິມ commit hash ທີ່ deploy ແລະ ບັນທຶກໃສ່ `~/xtk/app/storage/logs/deploy.log`.

### 4.2 ກົດ
- Deploy ສະເພາະຈາກ `main` ທີ່ຜ່ານການທົດສອບ.
- Migration ຕ້ອງເພີ່ມຢ່າງດຽວ (ເພີ່ມຕາຕະລາງ/ຖັນ). ການລຶບຖັນຕ້ອງເຮັດເປັນ 2 ຮອບ deploy.
- Backup DB ກ່ອນທຸກ migration (`backup.php` ຖືກເອີ້ນອັດຕະໂນມັດໃນ `migrate.php`).

### 4.3 ຖອຍກັບ (Rollback)
| ບັນຫາ | ວິທີ |
|---|---|
| ໂຄດໃໝ່ມີບັກ | `git checkout <commit ກ່ອນ>` ແລ້ວ `deploy.sh` ອີກຄັ້ງ |
| ເຜີຍແຜ່ເນື້ອຫາຜິດ | Admin → ເຜີຍແຜ່ → **Rollback** (ກັບໄປ 1 ໃນ 3 ຊຸດກ່ອນ) |
| Migration ທຳລາຍຂໍ້ມູນ | Restore backup ທີ່ `migrate.php` ສ້າງໄວ້ກ່ອນແລ່ນ |

## 5. `public/.htaccess` (ຂຽນໃນເຟດ 0, ທົດສອບໃນເຟດ S)
```apache
Options -Indexes
ErrorDocument 404 /lo/404.html

RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# admin front controller
RewriteRule ^admin/assets/ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^admin(/.*)?$ admin/index.php [L,QSA]

<IfModule mod_headers.c>
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set Strict-Transport-Security "max-age=31536000"
  <FilesMatch "\.(css|js|woff2|jpg|jpeg|png|webp|svg|mp4|webm|pdf)$">
    Header set Cache-Control "public, max-age=31536000, immutable"
  </FilesMatch>
  <FilesMatch "\.html$">
    Header set Cache-Control "no-cache"
  </FilesMatch>
</IfModule>

<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/css application/javascript image/svg+xml application/json application/xml
</IfModule>
```
`public/uploads/.htaccess`:
```apache
Options -Indexes -ExecCGI
RemoveHandler .php .phtml .phar
<FilesMatch "\.(php|phtml|phar|pl|py|cgi|sh)$">
  Require all denied
</FilesMatch>
```
ກວດທຸກ directive ໃນ server ຈິງ (P0-A6). ບາງ host ປິດ `Header always` ຫຼື `RemoveHandler`.

## 6. Go-live: ສະຫຼັບ `xtklaos.com` ມາເວັບໃໝ່ (ເຟດ 4)
Docroot ຂອງໂດເມນຫຼັກໃນ cPanel ຕາຍຕົວເປັນ `public_html`. ມີ 2 ທາງ:

| ທາງ | ວິທີ | ກັບຄືນ | ໝາຍເຫດ |
|---|---|---|---|
| **A (ແນະນຳ)** | ຂໍໃຫ້ easywinhost ປ່ຽນ docroot ຂອງ `xtklaos.com` ເປັນ `/home/xtklaos/xtk/public` | ຂໍໃຫ້ host ປ່ຽນກັບ | ສະອາດທີ່ສຸດ, ແຕ່ຂຶ້ນກັບ host |
| B | `mv public_html public_html_old && ln -s xtk/public public_html` | `rm public_html && mv public_html_old public_html` (ບໍ່ເຖິງ 1 ນາທີ) | ຕ້ອງທົດສອບວ່າ Apache ຮັບ symlink (SymLinksIfOwnerMatch) ກັບ addon/subdomain ກ່ອນ |

ຂັ້ນຕອນມື້ go-live:
1. ແຈ້ງລູກຄ້າ ແລະ ເລືອກເວລາທີ່ມີຄົນເຂົ້າໜ້ອຍ.
2. Backup ເວັບເກົ່າ (P4-E3) ແລະ backup DB.
3. ອັບເດດ `config.php`: `base_url` = `https://xtklaos.com`. ຕັ້ງ `noindex` = false. ເຜີຍແຜ່ໃໝ່ (canonical, sitemap ຈະໃຊ້ໂດເມນຫຼັກ).
4. ສະຫຼັບຕາມທາງ A ຫຼື B.
5. ກວດ: ທຸກພາສາ, ຟອມຕິດຕໍ່, admin, SSL, `www`, ອີເມວບໍລິສັດ.
6. ຕັ້ງ subdomain ໃຫ້ 301 ໄປໂດເມນຫຼັກ.
7. ສົ່ງ sitemap ໃນ Search Console.

DNS ແລະ MX ບໍ່ປ່ຽນ, ສະນັ້ນອີເມວ `@xtklaos.com` ບໍ່ໄດ້ຮັບຜົນກະທົບ.

## 7. Cron Jobs (cPanel → Cron Jobs)
```
0  3 * * *  /opt/cpanel/ea-php83/root/usr/bin/php /home/xtklaos/xtk/app/bin/cleanup.php  >/dev/null 2>&1
30 3 * * *  /opt/cpanel/ea-php83/root/usr/bin/php /home/xtklaos/xtk/app/bin/backup.php db  >/dev/null 2>&1
0  4 * * 0  /opt/cpanel/ea-php83/root/usr/bin/php /home/xtklaos/xtk/app/bin/backup.php full >/dev/null 2>&1
```
Error ຂອງ script ບັນທຶກລົງ `storage/logs/` ແທນການສົ່ງອີເມວ cron.

## 8. Backup
| ສິ່ງ | ຄວາມຖີ່ | ເກັບໄວ້ | ບ່ອນເກັບ |
|---|---|---|---|
| DB (`mysqldump --single-transaction`, gzip) | ທຸກມື້ | 14 ມື້ | `app/storage/backups/` |
| DB + `public/uploads/` (tar.gz) | ທຸກອາທິດ | 4 ອາທິດ | `app/storage/backups/` |
| ສຳເນົານອກ server | ທຸກອາທິດ | 3 ເດືອນ | ດາວໂຫຼດມາເຄື່ອງ ຫຼື Google Drive ຂອງບໍລິສັດ |
| ກ່ອນ migration | ທຸກຄັ້ງ | 5 ຊຸດລ່າສຸດ | `app/storage/backups/pre-migrate/` |

ໂຄດບໍ່ຕ້ອງ backup ເພາະຢູ່ໃນ git. **ທົດສອບ restore ທຸກ 3 ເດືອນ.**

## 9. ການຕິດຕາມ
- `storage/logs/app-YYYY-MM-DD.log`: error ຂອງ PHP, ອີເມວລົ້ມເຫຼວ, ເຜີຍແຜ່ລົ້ມເຫຼວ. ລຶບອາຍຸ > 30 ວັນ.
- Dashboard admin ສະແດງ: ອີເມວແຈ້ງເຕືອນລົ້ມເຫຼວ 24 ຊົ່ວໂມງຫຼ້າສຸດ ແລະ backup ຫຼ້າສຸດເມື່ອໃດ.
- ທາງເລືອກຟຣີ: UptimeRobot ກວດ `https://xtklaos.com/lo/index.html` ທຸກ 5 ນາທີ.

## 10. ຄວາມປອດໄພຂອງບັນຊີ
- **ປ່ຽນລະຫັດຜ່ານ cPanel** ທີ່ເຄີຍພິມໃນແຊັດ ກ່ອນ go-live (P4-E2).
- ບໍ່ເກັບລະຫັດຜ່ານໃນ repo, ແຊັດ ຫຼື ເອກະສານ. ໃຊ້ password manager.
- SSH ໃຊ້ key ເທົ່ານັ້ນ (ຕາມທີ່ server ຕັ້ງໄວ້ແລ້ວ).
