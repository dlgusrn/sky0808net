#!/usr/bin/env python3
"""Prepare production files/candidates only: no DB writes, enabled-site changes or reload."""
from pathlib import Path,PurePosixPath
import sys,os,socket,tarfile,json,hashlib,shutil,pwd,grp,datetime,re,subprocess
EXPECTED_HOST='sky0808net-235826'
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
        php_flag display_errors Off
        php_flag log_errors On
        php_value upload_max_filesize 5M
        php_value post_max_size 8M
        LimitRequestBody 8388608
    </Directory>
"""%(destination/'public')
    marker='</VirtualHost>';assert changed.count(marker)==1,'Review multiple VirtualHost blocks manually'
    return changed.replace(marker,directory+marker,1)

def main():
    assert os.geteuid()==0,'Run on the production server as root'
    assert socket.gethostname().split('.')[0]==EXPECTED_HOST,'Wrong server; production hostname guard failed'
    archive=Path(sys.argv[1]).resolve()
    backup=Path(sys.argv[2]).resolve()
    legacy=Path('/var/www/html/sky0808net')
    assert (legacy/'live/inc/db_conf.php').is_file(),'Legacy DB configuration not found'
    assert backup.parent==Path('/root') and backup.name.startswith('sky0808-before-mvc-'),'Unexpected backup folder'
    for name in ['apache2.tar.gz','project.tar.gz','sky0808.sql']:
        assert (backup/name).is_file() and (backup/name).stat().st_size>0,'Backup missing: '+name
    subprocess.run(['gzip','-t',str(backup/'apache2.tar.gz'),str(backup/'project.tar.gz')],check=True)
    with (backup/'sky0808.sql').open('rb')as dump:
        dump.seek(max(0,(backup/'sky0808.sql').stat().st_size-8192))
        assert b'-- Dump completed on ' in dump.read(),'SQL backup completion marker missing'
    checksum=archive.with_suffix(archive.suffix+'.sha256')
    assert checksum.is_file(),'Copy the checksum file alongside the archive'
    assert hashlib.sha256(archive.read_bytes()).hexdigest()==checksum.read_text().split()[0],'Archive checksum mismatch'
    with tarfile.open(archive,'r:gz')as package:
        members=package.getmembers();names=[m.name for m in members]
        assert len(names)==len(set(names)),'Duplicate archive paths'
        metadata=json.load(package.extractfile('release.json'));revision=metadata['revision']
        assert re.fullmatch('[0-9a-f]{40}',revision),'Invalid revision'
        for member in members:
            path=PurePosixPath(member.name)
            assert not path.is_absolute() and '..' not in path.parts and '\\'not in member.name,'Unsafe archive path'
            assert member.isdir()or member.isfile(),'Archive links/special files are not allowed'
            if member.isfile()and member.name!='release.json':
                assert member.name in metadata['files'],'Unexpected archive file'
                assert hashlib.sha256(package.extractfile(member).read()).hexdigest()==metadata['files'][member.name],'File checksum mismatch'
        assert set(metadata['files'])=={m.name for m in members if m.isfile()and m.name!='release.json'},'Incomplete archive manifest'
        assert 'config/database.local.php'not in metadata['files'],'Private settings must not be packaged'
        destination=Path('/var/www/html/sky0808net-prod-releases')/(datetime.datetime.now().strftime('%Y%m%d-%H%M%S')+'-'+revision[:12])
        assert not destination.exists(),'Release folder already exists'
        destination.mkdir(parents=True,mode=0o755)
        for member in members:
            path=destination/member.name
            if member.isdir():path.mkdir(parents=True,exist_ok=True)
            else:
                path.parent.mkdir(parents=True,exist_ok=True)
                with package.extractfile(member)as incoming,path.open('wb')as outgoing:shutil.copyfileobj(incoming,outgoing)
                path.chmod(0o755 if member.mode&0o111 else 0o644)
    uid,gid=pwd.getpwnam('www-data').pw_uid,grp.getgrnam('www-data').gr_gid
    exporter=r'''
    ob_start();
    require $argv[1].'/live/inc/db_conf.php';
    ob_end_clean();
    if (!isset($DB) || !($DB instanceof PDO) || !isset($host,$database,$user,$password)) throw new RuntimeException('Legacy DB configuration does not match the expected format.');
    if ($DB->query('SELECT DATABASE()')->fetchColumn() !== 'sky0808' || $database !== 'sky0808') throw new RuntimeException('Unexpected production database.');
    file_put_contents($argv[2].'/config/database.local.php',"<?php\nreturn ".var_export(['host'=>$host,'database'=>$database,'username'=>$user,'password'=>$password,'server_identity'=>$DB->query('SELECT @@hostname')->fetchColumn()],true).";\n");
    '''
    subprocess.run(['php','-d','short_open_tag=1','-d','zend.exception_ignore_args=1','-r',exporter,str(legacy),str(destination)],check=True)
    private=destination/'config/database.local.php';private.chmod(0o640);os.chown(private,0,gid)
    storage=destination/'storage/notice';storage.mkdir(parents=True,exist_ok=True)
    old_uploads=legacy/'live/uploads';count=0
    assert old_uploads.is_dir(),'Existing upload directory not found'
    for item in old_uploads.iterdir():
        if not item.is_file()or not re.fullmatch(r'[A-Za-z0-9_-]+\.(jpg|jpeg|png|gif)',item.name,re.I):continue
        out=storage/item.name;shutil.copy2(item,out)
        assert hashlib.sha256(out.read_bytes()).digest()==hashlib.sha256(item.read_bytes()).digest(),'Attachment checksum mismatch'
        os.chown(out,uid,gid);out.chmod(0o640);count+=1
    os.chown(storage,uid,gid);storage.chmod(0o750)
    checker=r'''
    require $argv[1].'/core/Model.php';
    require $argv[1].'/app/helpers/Notice_support.php';
    class ProductionSchemaCheck extends Model {
        public function check($directory) {
            $private=require dirname($directory,2).'/config/database.local.php';
            if ($this->db->query('SELECT DATABASE()')->fetchColumn() !== 'sky0808' || $this->db->query('SELECT @@hostname')->fetchColumn() !== $private['server_identity']) throw new RuntimeException('Effective DB connection differs from the production source');
            $column=$this->db->query("SHOW COLUMNS FROM preacher_list LIKE 'update_date'")->fetch();
            if (!$column) throw new RuntimeException('preacher_list.update_date is missing');
            foreach ($this->db->query("SELECT DISTINCT attached FROM notice WHERE attached IS NOT NULL AND attached <> ''")->fetchAll(PDO::FETCH_COLUMN) as $name) {
                if (!Notice_support::filename($name) || !is_file($directory.'/'.$name)) throw new RuntimeException('A referenced attachment is missing or uses an unsupported filename');
            }
            echo 'Event scheduler: '.$this->db->query('SELECT @@event_scheduler')->fetchColumn().PHP_EOL;
        }
    }
    (new ProductionSchemaCheck())->check($argv[1].'/storage/notice');
    '''
    subprocess.run(['php','-d','zend.exception_ignore_args=1','-r',checker,str(destination)],check=True)
    subprocess.run(['bash',str(destination/'scripts/release/check.sh'),'--database'],check=True)
    # Candidate generation does not modify enabled configs or restart/reload Apache.
    plan=destination/'deployment';plan.mkdir()
    records=[]
    for filename in ['000-default.conf','000-default-ssl.conf']:
        enabled=Path('/etc/apache2/sites-enabled')/filename
        assert enabled.exists(),'Expected production vhost is missing'
        target=enabled.resolve();text=target.read_text()
        shutil.copy2(target,plan/(filename+'.before'))
        candidate=plan/(filename+'.candidate');candidate.write_text(candidate_config(text,legacy,destination))
        records.append({'target':str(target),'before':filename+'.before','candidate':candidate.name,'sha256_before':hashlib.sha256(text.encode()).hexdigest()})
    (plan/'plan.json').write_text(json.dumps({'environment':'production','hostname':EXPECTED_HOST,'ip':'49.247.173.113','revision':revision,'release':str(destination),'backup':str(backup),'configs':records},indent=2))
    print('PASS: production release prepared; backup/archive/attachment/regression checks passed.')
    print('Release:',destination)
    print('Review:',plan)
    print('Copied image files:',count)
    print('Production Apache, DB records/schema, event and original code/uploads were NOT changed.')
    print('Review command: python3',destination/'scripts/release/activate_production.py',plan)
    print('Apply command: python3',destination/'scripts/release/activate_production.py',plan,'--apply')
    print('Rollback command: python3',destination/'scripts/release/rollback_production.py',plan)
    print('Preparation did not switch the public service. Review the candidate before running Apply.')
if __name__=='__main__':main()
