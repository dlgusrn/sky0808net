"""Production cutover guards; no actions occur when imported."""
from pathlib import Path
import socket,json,hashlib,re,shutil,os,pwd,grp,subprocess
EXPECTED_HOST='sky0808net-235826'
LEGACY=Path('/var/www/html/sky0808net')

def load_plan(folder):
    assert os.geteuid()==0,'Run as root on the production server'
    assert socket.gethostname().split('.')[0]==EXPECTED_HOST,'Wrong production hostname'
    folder=Path(folder).resolve();plan=json.loads((folder/'plan.json').read_text())
    release=Path(plan['release']).resolve()
    assert plan.get('environment')=='production' and plan.get('hostname')==EXPECTED_HOST,'Not a production plan'
    assert release.parent==Path('/var/www/html/sky0808net-prod-releases') and folder==release/'deployment','Unexpected production release path'
    assert Path(plan['backup']).is_dir(),'Recorded backup folder is missing'
    assert len(plan['configs'])==2,'Expected HTTP and HTTPS configuration records'
    expected={str((Path('/etc/apache2/sites-enabled')/name).resolve()) for name in ['000-default.conf','000-default-ssl.conf']}
    assert {item['target']for item in plan['configs']}==expected,'Unexpected Apache configuration targets'
    return folder,plan,release

def file_hash(path):return hashlib.sha256(Path(path).read_bytes()).hexdigest()

def verify_release(release,plan):
    metadata=json.loads((release/'release.json').read_text())
    assert metadata['revision']==plan['revision'],'Release/plan revision mismatch'
    for name,digest in metadata['files'].items():
        path=Path(name)
        assert not path.is_absolute() and '..'not in path.parts,'Unsafe manifest path'
        assert file_hash(release/path)==digest,'Release changed after packaging: '+name
    assert 'public/.htaccess'in metadata['files'],'Rewrite file missing'

def preserve_directives(before,after):
    # A production switch must not remove/change the existing proxy or TLS directives.
    for line in before.splitlines():
        stripped=line.strip()
        if stripped and not stripped.startswith('#') and re.match(r'(Proxy\w*|SSLEngine|SSLCertificate\w*|ServerName|ServerAlias)\s',stripped,re.I):
            assert line in after,'Existing proxy/TLS/host directive changed'

def verify_candidates(folder,plan,release):
    for item in plan['configs']:
        assert file_hash(item['target'])==item['sha256_before'],'Active Apache config changed; prepare/review again'
        before=(folder/item['before']).read_text();after=(folder/item['candidate']).read_text()
        assert hashlib.sha256(before.encode()).hexdigest()==item['sha256_before'],'Saved Apache backup changed'
        roots=re.findall(r'^\s*DocumentRoot\s+"?([^"\s]+)"?\s*$',after,re.M)
        assert roots and all(root==str(release/'public')for root in roots),'Candidate has an unexpected DocumentRoot'
        preserve_directives(before,after)

def use_existing_uploads(release):
    # Share the existing mutable data path: uploads finishing during reload and rollback stay valid.
    original=LEGACY/'live/uploads'
    assert original.is_dir(),'Existing upload folder missing'
    subprocess.run(['runuser','-u','www-data','--','test','-r',str(original)],check=True)
    subprocess.run(['runuser','-u','www-data','--','test','-w',str(original)],check=True)
    runtime=release/'storage/notice'
    if runtime.is_symlink():
        assert runtime.resolve()==original.resolve(),'Unexpected upload symlink'
        return
    assert runtime.is_dir(),'Prepared attachment folder missing'
    for source in runtime.iterdir():
        if not source.is_file():continue
        assert re.fullmatch(r'[A-Za-z0-9_-]+\.(jpg|jpeg|png|gif)',source.name,re.I),'Unexpected attachment filename'
        dest=original/source.name
        if dest.exists():assert file_hash(source)==file_hash(dest),'Attachment name collision'
        else:
            shutil.copy2(source,dest)
            os.chown(dest,pwd.getpwnam('www-data').pw_uid,grp.getgrnam('www-data').gr_gid);dest.chmod(0o640)
    saved=release/'storage/notice.before-cutover'
    assert not saved.exists(),'Saved preview upload copy already exists'
    runtime.rename(saved)
    try:runtime.symlink_to(original.resolve(),target_is_directory=True)
    except BaseException:
        saved.rename(runtime)
        raise
