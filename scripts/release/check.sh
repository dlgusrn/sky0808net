#!/usr/bin/env bash
set -euo pipefail
release_root="$(cd "$(dirname "$0")/../.." && pwd)"
if ! command -v php >/dev/null; then
  echo "PHP is required. Run this check on the PHP dev server." >&2
  exit 1
fi
while IFS= read -r -d '' release_file; do
  php -d short_open_tag=1 -l "$release_file" >/dev/null
done < <(find "$release_root/app" "$release_root/core" "$release_root/config" "$release_root/public" "$release_root/tests" -type f -name '*.php' -print0)
for release_test in database_settings admin_permissions admin_filters date_presets pagination notices; do
  php "$release_root/tests/$release_test.php"
done
if [[ "${1:-}" == "--database" ]]; then
  php "$release_root/tests/admin_listing_db.php"
fi
python3 "$release_root/tests/release_tools.py"
printf 'PASS: PHP syntax and release regression checks.\n'
