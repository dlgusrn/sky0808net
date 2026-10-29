#!/usr/bin/env python3
"""Restore production vhosts without discarding current attachment/database data."""
import sys,json,subprocess,shutil
from production_support import load_plan,file_hash

def main():
    folder,plan,release=load_plan(sys.argv[1]);state=json.loads((folder/'activated.json').read_text())
    for item in plan['configs']:
        assert file_hash(item['target'])==state['active_hashes'][item['target']],'Apache was edited after activation; review manually'
        assert file_hash(folder/item['before'])==item['sha256_before'],'Saved Apache backup changed'
    uploads=release/'storage/notice'
    assert uploads.is_symlink() and uploads.resolve().is_dir(),'Shared attachment storage is missing'
    changed=[]
    try:
        for item in plan['configs']:
            changed.append(item);shutil.copyfile(folder/item['before'],item['target'])
        subprocess.run(['apache2ctl','configtest'],check=True)
        subprocess.run(['systemctl','reload','apache2'],check=True)
    except BaseException:
        for item in changed:shutil.copyfile(folder/item['candidate'],item['target'])
        subprocess.run(['apache2ctl','configtest'],check=True)
        subprocess.run(['systemctl','reload','apache2'],check=True)
        raise
    print('PASS: previous production vhosts restored. Database records and shared uploaded images were retained.')
if __name__=='__main__':main()
