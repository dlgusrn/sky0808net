#!/usr/bin/env python3
"""Review/apply a prepared dev-only Apache switch, with automatic rollback on failure."""
from pathlib import Path
import sys,json,hashlib,subprocess,shutil,difflib,urllib.request,urllib.error,time
plan_dir=Path(sys.argv[1]).resolve();plan=json.loads((plan_dir/'plan.json').read_text())
assert str(plan_dir).startswith('/var/www/html/sky0808net-releases/'),'Unexpected dev release folder'
assert Path(plan['release']).is_dir(),'Prepared release missing'
for item in plan['configs']:
    before=(plan_dir/item['before']).read_text();after=(plan_dir/item['candidate']).read_text()
    print(''.join(difflib.unified_diff(before.splitlines(True),after.splitlines(True),fromfile=item['target'],tofile=item['candidate'])))
if '--apply' not in sys.argv[2:]:
    print('Review only. Add --apply to switch the normal dev HTTP/HTTPS vhosts.')
    sys.exit(0)
for item in plan['configs']:
    assert hashlib.sha256(Path(item['target']).read_bytes()).hexdigest()==item['sha256_before'],'Active configuration changed since preparation; prepare/review again'
subprocess.run(['bash',str(Path(plan['release'])/'scripts/release/check.sh'),'--database'],check=True)
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self,*args,**kwargs):return None
opener=urllib.request.build_opener(NoRedirect())
def response(path):
    try:
        with opener.open('http://127.0.0.1'+path,timeout=15)as out:return out.status,out.headers,out.read()
    except urllib.error.HTTPError as error:return error.code,error.headers,error.read()
changed=[]
try:
    for item in plan['configs']:
        changed.append(item);shutil.copyfile(plan_dir/item['candidate'],item['target'])
    subprocess.run(['apache2ctl','configtest'],check=True)
    subprocess.run(['systemctl','reload','apache2'],check=True)
    for attempt in range(10):
        status,headers,body=response('/user/login')
        if status==200 and b'name="user_name"'in body:break
        time.sleep(0.3)
    assert status==200 and b'name="user_name"'in body,'User login health check failed'
    status,headers,body=response('/admin/login');assert status==200 and b'name="admin_password"'in body,'Admin login health check failed'
    status,headers,body=response('/live');assert status==302 and headers['Location'].endswith('/user/login'),'MVC live redirect failed'
    status,headers,body=response('/live/admin/');assert status==302 and headers['Location'].endswith('/admin/login'),'Legacy admin bookmark failed'
    status,headers,body=response('/admin/main');assert status==401,'Guest admin access is not blocked'
    status,headers,body=response('/css/admin.css');assert status==200,'Admin stylesheet missing'
except BaseException:
    for item in changed:shutil.copyfile(plan_dir/item['before'],item['target'])
    subprocess.run(['apache2ctl','configtest'],check=True)
    subprocess.run(['systemctl','reload','apache2'],check=True)
    print('Restored previous dev vhosts. Existing app/data folders are unchanged.')
    raise
(plan_dir/'activated.json').write_text(json.dumps({'revision':plan['revision'],'active_hashes':{item['target']:hashlib.sha256(Path(item['target']).read_bytes()).hexdigest()for item in plan['configs']}},indent=2))
print('PASS: normal dev HTTP/HTTPS vhosts now use the prepared MVC release; HTTP smoke checks passed.')
print('HTTPS uses the existing certificate/configuration; verify it in the browser as well.')
print('Rollback: python3',Path(plan['release'])/'scripts/release/rollback_dev.py',plan_dir)
