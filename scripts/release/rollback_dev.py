#!/usr/bin/env python3
"""Restore dev vhosts and retain attachments created while testing the release."""
from pathlib import Path
import sys,json,hashlib,shutil,subprocess,re,os,pwd,grp
plan_dir=Path(sys.argv[1]).resolve();plan=json.loads((plan_dir/'plan.json').read_text())
assert str(plan_dir).startswith('/var/www/html/sky0808net-releases/'),'Unexpected dev release folder'
state=json.loads((plan_dir/'activated.json').read_text())
for item in plan['configs']:
    assert hashlib.sha256(Path(item['target']).read_bytes()).hexdigest()==state['active_hashes'][item['target']],'Active Apache config was edited after activation; review manually'
# Database is shared: preserve new attachment files for both older dev views before restoring Apache.
uid,gid=pwd.getpwnam('www-data').pw_uid,grp.getgrnam('www-data').gr_gid
for dest in [Path('/var/www/html/sky0808net-mvc-dev/storage/notice'),Path('/var/www/html/sky0808net/live/uploads')]:
    dest.mkdir(parents=True,exist_ok=True)
    for source in (Path(plan['release'])/'storage/notice').iterdir():
        if not source.is_file()or not re.fullmatch(r'[A-Za-z0-9_-]+\.(jpg|jpeg|png|gif)',source.name,re.I):continue
        target=dest/source.name
        if target.exists():assert hashlib.sha256(target.read_bytes()).digest()==hashlib.sha256(source.read_bytes()).digest(),'Attachment conflict'
        else:shutil.copy2(source,target)
        os.chown(target,uid,gid);target.chmod(0o640)
for item in plan['configs']:shutil.copyfile(plan_dir/item['before'],item['target'])
subprocess.run(['apache2ctl','configtest'],check=True)
subprocess.run(['systemctl','reload','apache2'],check=True)
print('PASS: previous dev Apache configuration restored. No DB records were reverted/deleted; attachment copies retained.')
