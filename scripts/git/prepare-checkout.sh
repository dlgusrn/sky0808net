#!/usr/bin/env bash
# Prepare only. Apache configuration and the running service are untouched.
set -euo pipefail
if [[ $# != 3 ]]; then
  echo "Usage: bash $0 <active-mvc-root> <new-checkout> <develop|master>" >&2
  exit 1
fi
active_root="$(realpath "$1")"
checkout="$2"
branch="$3"
[[ "$branch" == develop || "$branch" == master ]] || { echo 'Unsupported branch' >&2; exit 1; }
[[ -f "$active_root/public/index.php" && -f "$active_root/config/database.local.php" && -d "$active_root/storage/notice" ]] || { echo 'Active MVC configuration/uploads missing' >&2; exit 1; }
[[ ! -e "$checkout" ]] || { echo 'Checkout already exists; inspect it instead of overwriting' >&2; exit 1; }
upload_path="$(realpath "$active_root/storage/notice")"
# A copy inside a release must remain there until storage is migrated separately.
git clone --single-branch --branch "$branch" https://github.com/dlgusrn/sky0808net.git "$checkout"
install -m 640 -o root -g www-data "$active_root/config/database.local.php" "$checkout/config/database.local.php"
mkdir -p "$checkout/storage"
ln -s "$upload_path" "$checkout/storage/notice"
runuser -u www-data -- test -r "$checkout/config/database.local.php"
runuser -u www-data -- test -w "$checkout/storage/notice"
bash "$checkout/scripts/release/check.sh" --database
echo "PASS: Git checkout prepared at $checkout ($branch). Apache is unchanged."
echo "Upload storage: $upload_path (keep this directory)."
echo "Next: review and change both DocumentRoot and Directory paths to $checkout/public."
