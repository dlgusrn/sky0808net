<?php
/** Permission policy shared by every public Admin action. Unknown actions fail closed. */
class Admin_access {
    public static function allows($action, array $admin) {
        if (in_array($action, ['login', 'admin_login_proc'], true)) return true;
        if (!$admin) return false;
        if ($action === 'admin_logout_proc') return true;
        $permissions = [
            'index' => ['live_manage'],
            'main' => ['live_manage'],
            'get_sky_live_user' => ['live_manage'],
            'get_beer_live_user' => ['live_manage'],
            'live_list' => ['live_manage', 'live_list'],
            'live_read' => ['live_manage', 'live_list'],
            'set_live_list' => ['live_manage', 'live_list'],
            'del_live_list' => ['live_manage', 'live_list'],
            'update_use' => ['live_manage', 'live_list'],
            'update_view' => ['live_manage', 'live_list'],
            'history' => ['live_manage', 'history'],
            'black_list' => ['live_manage', 'black_list'],
            'set_black_list' => ['live_manage', 'black_list'],
            'del_black_list' => ['live_manage', 'black_list'],
            'video_list' => ['video_list'],
            'video_read' => ['video_list'],
            'set_video_list' => ['video_list'],
            'del_video_list' => ['video_list'],
        ];
        $level_one = ['admin_list', 'admin_read', 'set_admin', 'del_admin',
            'preacher_list', 'get_preacher', 'set_preacher', 'del_preacher'];
        if (in_array($action, $level_one, true)) {
            return isset($admin['level']) && (string) $admin['level'] === '1';
        }
        if (in_array($action, ['notice', 'get_notice', 'set_notice', 'del_notice', 'update_enable'], true)) {
            $permission = array_key_exists('notice', $admin) ? 'notice' : 'live_list';
            return ($admin['live_manage'] ?? 'N') === 'Y' && ($admin[$permission] ?? 'N') === 'Y';
        }
        if (!isset($permissions[$action])) return false;
        foreach ($permissions[$action] as $permission) {
            if (($admin[$permission] ?? 'N') !== 'Y') return false;
        }
        return true;
    }
}
