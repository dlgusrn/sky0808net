<?php
require_once __DIR__ . '/../app/helpers/Admin_access.php';
$all = ['level' => 1, 'live_manage' => 'Y', 'live_list' => 'Y', 'history' => 'Y', 'black_list' => 'Y', 'video_list' => 'Y'];
$viewer = ['level' => 4, 'live_manage' => 'Y'];
$grouped = [
    'public' => ['login', 'admin_login_proc'],
    'viewer' => ['index', 'main', 'get_sky_live_user', 'get_beer_live_user'],
    'links' => ['notice', 'get_notice', 'set_notice', 'del_notice', 'update_enable', 'live_list', 'live_read', 'set_live_list', 'del_live_list', 'update_use', 'update_view'],
    'history' => ['history'],
    'blacklist' => ['black_list', 'set_black_list', 'del_black_list'],
    'videos' => ['video_list', 'video_read', 'set_video_list', 'del_video_list'],
    'level1' => ['admin_list', 'admin_read', 'set_admin', 'del_admin', 'preacher_list', 'get_preacher', 'set_preacher', 'del_preacher'],
    'logout' => ['admin_logout_proc'],
];
$count = 0;
function check_access($action, $admin, $expected) {
    global $count;
    if (Admin_access::allows($action, $admin) !== $expected) {
        throw new RuntimeException('Policy regression: ' . $action);
    }
    $count++;
}
foreach ($grouped as $group => $actions) {
    foreach ($actions as $action) {
        check_access($action, [], $group === 'public');
        check_access($action, $viewer, in_array($group, ['public', 'viewer', 'logout'], true));
        check_access($action, $all, true);
        check_access($action, ['level' => 4, 'live_manage' => 'Y', 'live_list' => 'Y'], in_array($group, ['public', 'viewer', 'links', 'logout'], true));
    }
}
foreach (['links' => 'live_list', 'history' => 'history', 'blacklist' => 'black_list', 'videos' => 'video_list'] as $group => $flag) {
    foreach ($grouped[$group] as $action) {
        $revoked = $all;
        $revoked[$flag] = 'N';
        check_access($action, $revoked, false);
    }
}
foreach ($grouped['links'] as $action) check_access($action, ['live_manage' => 'N', 'live_list' => 'Y'], false);
foreach (['notice', 'get_notice', 'set_notice', 'del_notice', 'update_enable'] as $action) {
    check_access($action, $all + ['notice' => 'N'], false);
    check_access($action, ['level' => 3, 'live_manage' => 'Y', 'live_list' => 'N', 'notice' => 'Y'], true);
}
check_access('unknown_action', $all, false);
check_access('__construct', $all, false);
// Every public action must invoke the guard as its first statement.
$controller = file_get_contents(__DIR__ . '/../app/controllers/Admin.php');
preg_match_all('/^    public function (\w+)\([^)]*\)\s*\{([^\n]*\n[^\n]*)/m', $controller, $matches, PREG_SET_ORDER);
$known = array_merge(...array_values($grouped));
foreach ($matches as $match) {
    if ($match[1] === '__construct') continue;
    if (!in_array($match[1], $known, true) || strpos($match[2], '$this->authorizeAdmin(__FUNCTION__);') === false) {
        throw new RuntimeException('Unguarded action: ' . $match[1]);
    }
}
echo 'PASS: ', $count, ' permission checks; all public Admin actions guarded.', PHP_EOL;
