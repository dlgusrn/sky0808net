#!/usr/bin/env bash
set -euo pipefail
request_root="$(cd "$(dirname "$0")/.." && pwd)"
request_count=0
for action in admin_login_proc admin_logout_proc set_live_list del_live_list update_use update_view set_black_list del_black_list set_video_list del_video_list set_admin del_admin set_preacher del_preacher set_notice del_notice update_enable; do
    for token in missing bad array valid; do
        php -d short_open_tag=1 "$request_root/tests/admin_request_case.php" "$action" POST "$token" >/dev/null
        request_count=$((request_count+1))
    done
    php -d short_open_tag=1 "$request_root/tests/admin_request_case.php" "$action" GET valid >/dev/null
    request_count=$((request_count+1))
done
printf 'PASS: %s actual controller request cases (POST/GET/CSRF), with mock database.\n' "$request_count"
