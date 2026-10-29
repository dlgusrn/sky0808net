#!/usr/bin/env python3
"""Prepare a verified MVC release and Apache candidates; never activate them."""
from pathlib import Path, PurePosixPath
import sys,tarfile,json,hashlib,subprocess,shutil,os,pwd,grp,datetime,re
def candidate_config(text,legacy,destination):
    document_roots=re.findall(r'^\s*DocumentRoot\s+"?([^"\s]+)"?\s*$',text,re.M)
    assert document_roots and all(p in [str(legacy),str(legacy/'public')] for p in document_roots),'Unexpected active DocumentRoot; review manually'
    changed=re.sub(r'(^\s*DocumentRoot\s+)"?[^"\s]+"?\s*$',lambda m:m[1]+'"'+str(destination/'public')+'"',text,flags=re.M)
    directory="""
    <Directory "%s">
        Options +FollowSymLinks -Indexes -MultiViews
        AllowOverride All
        DirectoryIndex index.php
        Require all granted
        php_flag short_open_tag On
        php_value upload_max_filesize 5M
        php_value post_max_size 8M
        LimitRequestBody 8388608
    </Directory>
"""%(destination/'public')
    marker='</VirtualHost>';assert changed.count(marker)==1,'Review multiple VirtualHost blocks manually'
    return changed.replace(marker,directory+marker,1)

def main():
    archive=Path(sys.argv[1]).resolve()
    source=Path('/var/www/html/sky0808net-mvc-dev')
    legacy=Path('/var/www/html/sky0808net')
    assert source.is_dir() and legacy.is_dir(),'Expected dev folders not found'
    checksum=archive.with_suffix(archive.suffix+'.sha256')
    assert checksum.is_file(),'Copy the .sha256 file alongside the archive'
    assert hashlib.sha256(archive.read_bytes()).hexdigest()==checksum.read_text().split()[0],'Archive checksum mismatch'
    with tarfile.open(archive,'r:gz')as package:
        members=package.getmembers();names=[m.name for m in members]
        assert len(names)==len(set(names)),'Duplicate archive entries'
        metadata=json.load(package.extractfile('release.json'))
        revision=metadata['revision'];assert re.fullmatch('[0-9a-f]{40}',revision),'Invalid revision'
        destination=Path('/var/www/html/sky0808net-releases')/(datetime.datetime.now().strftime('%Y%m%d-%H%M%S')+'-'+revision[:12])
        assert not destination.exists(),'Release folder already exists'
        for member in members:
            path=PurePosixPath(member.name)
            assert not path.is_absolute() and '..' not in path.parts and '\\' not in member.name,'Unsafe archive path'
            assert member.isdir()or member.isfile(),'Links/special files are not allowed'
            if member.isfile()and member.name!='release.json':
                assert member.name in metadata['files'],'Unlisted release file'
                assert hashlib.sha256(package.extractfile(member).read()).hexdigest()==metadata['files'][member.name],'Release file hash mismatch'
        assert set(metadata['files'])=={m.name for m in members if m.isfile()and m.name!='release.json'},'Incomplete manifest'
        destination.mkdir(parents=True,mode=0o755)
        for member in members:
            path=destination/member.name
            if member.isdir():path.mkdir(parents=True,exist_ok=True)
            else:
                path.parent.mkdir(parents=True,exist_ok=True)
                with package.extractfile(member)as incoming,path.open('wb')as outgoing:shutil.copyfileobj(incoming,outgoing)
                path.chmod(0o755 if member.mode&0o111 else 0o644)
    uid,gid=pwd.getpwnam('www-data').pw_uid,grp.getgrnam('www-data').gr_gid
    # Export the existing dev connection settings privately; no credentials are printed.
    exporter=r'''
    $_SERVER['HTTP_HOST']='localhost';
    require $argv[1].'/config/config.php';
    file_put_contents($argv[2].'/config/database.local.php',"<?php\nreturn ".var_export(['host'=>DB_HOST,'database'=>DB_NAME,'username'=>DB_USER,'password'=>DB_PASS],true).";\n");
    '''
    subprocess.run(['php','-r',exporter,str(source),str(destination)],check=True)
    private=destination/'config/database.local.php';private.chmod(0o640);os.chown(private,0,gid)
    storage=destination/'storage/notice';storage.mkdir(parents=True,exist_ok=True)
    # Copy tested attachments first and the legacy originals as a fallback; never overwrite conflicts.
    for folder in [source/'storage/notice',legacy/'live/uploads']:
        if not folder.is_dir():continue
        for item in folder.iterdir():
            if not item.is_file()or not re.fullmatch(r'[A-Za-z0-9_-]+\.(jpg|jpeg|png|gif)',item.name,re.I):continue
            out=storage/item.name
            if out.exists():assert hashlib.sha256(out.read_bytes()).digest()==hashlib.sha256(item.read_bytes()).digest(),'Attachment conflict'
            else:shutil.copy2(item,out)
    for item in [storage]+list(storage.iterdir()):os.chown(item,uid,gid);item.chmod(0o750 if item.is_dir()else 0o640)
    subprocess.run(['bash',str(destination/'scripts/release/check.sh'),'--database'],check=True)
    # Both HTTP and HTTPS candidates come from the actual currently enabled dev configurations.
    plan=destination/'deployment';plan.mkdir()
    records=[]
    for filename in ['000-default.conf','000-default-ssl.conf']:
        enabled=Path('/etc/apache2/sites-enabled')/filename
        assert enabled.exists(),'Expected enabled dev vhost not found: '+filename
        target=enabled.resolve()
        text=target.read_text()
        shutil.copy2(target,plan/(filename+'.before'))
        changed=candidate_config(text,legacy,destination)
        candidate=plan/(filename+'.candidate');candidate.write_text(changed)
        records.append({'target':str(target),'before':filename+'.before','candidate':candidate.name,'sha256_before':hashlib.sha256(text.encode()).hexdigest()})
    (plan/'plan.json').write_text(json.dumps({'revision':revision,'release':str(destination),'configs':records},indent=2))
    print('PASS: release prepared and regression/database checks passed. Apache is unchanged.')
    print('Release:',destination)
    print('Review:',plan)
    print('Next: inspect configuration diffs, then run activate_dev.py with the deployment folder.')

if __name__=='__main__':
    main()
