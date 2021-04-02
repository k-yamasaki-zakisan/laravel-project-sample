<?php

/**
 * 設定ファイルをExhibitionAdminとSuperadminの２つに分ける。
 */


if (isset($_SERVER['REQUEST_URI']) && preg_match('/^\/superadmin/', $_SERVER['REQUEST_URI'])) {
    return require __DIR__ . '/adminlte_superadmin.php';
}

return require __DIR__ . '/adminlte_exhibition_admin.php';
