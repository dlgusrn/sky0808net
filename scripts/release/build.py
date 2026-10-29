#!/usr/bin/env python3
"""Build a release from a committed tree, never from untracked dev files."""
from pathlib import Path
import subprocess, sys, io, tarfile, json, hashlib
root=Path(__file__).resolve().parents[2]
revision=subprocess.check_output(['git','rev-parse',sys.argv[1] if len(sys.argv)>1 else 'HEAD'],cwd=root,text=True).strip()
paths=['README.md','app','core','public','config','tests','scripts','docs']
raw=subprocess.check_output(['git','archive','--format=tar',revision,'--']+paths,cwd=root)
manifest={}
with tarfile.open(fileobj=io.BytesIO(raw),mode='r:')as source:
    for member in source.getmembers():
        if member.isfile():manifest[member.name]=hashlib.sha256(source.extractfile(member).read()).hexdigest()
        elif not member.isdir():raise RuntimeError('Release must not contain links or special files')
assert 'public/.htaccess' in manifest,'Front-controller rewrite file is missing'
assert 'config/database.local.php' not in manifest,'Private configuration must not be packaged'
out=root/'dist';out.mkdir(exist_ok=True)
archive=out/('sky0808net-'+revision[:12]+'.tar.gz')
with tarfile.open(archive,'w:gz')as target,tarfile.open(fileobj=io.BytesIO(raw),mode='r:')as source:
    for member in source.getmembers():target.addfile(member,source.extractfile(member)if member.isfile()else None)
    metadata=json.dumps({'revision':revision,'files':manifest},indent=2,sort_keys=True).encode()
    info=tarfile.TarInfo('release.json');info.size=len(metadata);info.mode=0o644;target.addfile(info,io.BytesIO(metadata))
digest=hashlib.sha256(archive.read_bytes()).hexdigest()
archive.with_suffix(archive.suffix+'.sha256').write_text(digest+'  '+archive.name+'\n')
print('Release:',archive)
print('SHA256:',digest)
print('Files:',len(manifest))
