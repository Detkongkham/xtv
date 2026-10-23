<?php
/**
 * <link> to the admin colors set in /admin/theme; nothing while they are the defaults. In the head of every admin page
 * (layout, login, error pages). An error page can be shown while the database is down: then the default colors stay.
 */
use App\Http\Admin\ThemeController;

try {
    $ver = ThemeController::adminVersion(ThemeController::current());
} catch (\Throwable) {
    $ver = null;
}
if ($ver !== null): ?><link rel="stylesheet" href="/admin/theme.css?v=<?= e($ver) ?>">
<?php endif;
