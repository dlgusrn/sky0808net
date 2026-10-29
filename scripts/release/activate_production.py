#!/usr/bin/env python3
"""Review/apply a verified production release with automatic configuration rollback."""
from pathlib import Path
import sys,json,subprocess,shutil,difflib,urllib.request,urllib.error,time,datetime
from production_support import load_plan,verify_release,verify_candidates,use_existing_uploads,file_hash

def main():
    folder,plan,release=load_plan(sys.argv[1])
    verify_release(release,plan);verify_candidates(folder,plan,release)
    for item in plan['configs']:
        before=(folder/item['before']).read_text();after=(folder/item['candidate']).read_text()
        print(''.join(difflib.unified_diff(before.splitlines(True),after.splitlines(True),fromfile=item['target'],tofile=item['candidate'])))
    if '--apply'not in sys.argv[2:]:
        print('REVIEW ONLY: Apache, DB and upload storage are unchanged. Add --apply to switch production.')
        return
    assert not (folder/'activated.json').exists(),'Already activated; use the recorded rollback procedure'
    subprocess.run(['bash',str(release/'scripts/release/check.sh'),'--database'],check=True)
    use_existing_uploads(release)
    class NoRedirect(urllib.request.HTTPRedirectHandler):
        def redirect_request(self,*args,**kwargs):return None
    opener=urllib.request.build_opener(NoRedirect())
    def response(path):
        request=urllib.request.Request('http://127.0.0.1'+path,headers={'Host':plan['ip']})
        try:
            with opener.open(request,timeout=15)as out:return out.status,out.headers,out.read()
        except urllib.error.HTTPError as error:return error.code,error.headers,error.read()
    changed=[]
    try:
        for item in plan['configs']:
            changed.append(item);shutil.copyfile(folder/item['candidate'],item['target'])
        subprocess.run(['apache2ctl','configtest'],check=True)
        subprocess.run(['systemctl','reload','apache2'],check=True)
        for attempt in range(10):
            status,headers,body=response('/user/login')
            if status==200 and b'name="user_name"'in body:break
            time.sleep(0.3)
        assert status==200 and b'name="user_name"'in body,'User login health check failed'
        status,headers,body=response('/admin/login');assert status==200 and b'name="admin_password"'in body,'Admin login health check failed'
        for path,target in [('/live','/user/login'),('/live/admin/','/admin/login')]:
            status,headers,body=response(path);assert status==302 and headers['Location'].endswith(target),'Redirect failed: '+path
        for path in ['/admin/main','/admin/history']:
            status,headers,body=response(path);assert status==401,'Guest admin protection failed'
        status,headers,body=response('/css/admin.css');assert status==200,'Admin stylesheet missing'
        status,headers,body=response('/img/church-logo.png');assert status==200 and body.startswith(b'\x89PNG\r\n\x1a\n'),'Church logo missing'
    except BaseException:
        for item in changed:shutil.copyfile(folder/item['before'],item['target'])
        subprocess.run(['apache2ctl','configtest'],check=True)
        subprocess.run(['systemctl','reload','apache2'],check=True)
        print('ROLLED BACK: previous public Apache configuration restored. DB unchanged; attachment data retained.')
        raise
    (folder/'activated.json').write_text(json.dumps({'revision':plan['revision'],'time':datetime.datetime.now().isoformat(),'active_hashes':{item['target']:file_hash(item['target'])for item in plan['configs']}},indent=2))
    print('PASS: production HTTP/HTTPS switched; configuration and HTTP smoke checks passed.')
    print('Existing proxy/TLS directives and legacy code preserved; no DB records/schema/events changed.')
    print('Uploads use the existing live/uploads folder. Keep the original legacy folder.')
    print('Rollback: python3',release/'scripts/release/rollback_production.py',folder)
if __name__=='__main__':main()
