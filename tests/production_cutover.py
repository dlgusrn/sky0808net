#!/usr/bin/env python3
from pathlib import Path
import unittest,tempfile,hashlib,json,sys
from unittest.mock import patch
from types import SimpleNamespace
ROOT=Path(__file__).resolve().parents[1]
sys.path.insert(0,str(ROOT/'scripts/release'))
import production_support as support
class ProductionCutoverTests(unittest.TestCase):
    def setUp(self):self.temp=tempfile.TemporaryDirectory();self.root=Path(self.temp.name)
    def tearDown(self):self.temp.cleanup()
    def release(self):
        release=self.root/'release';(release/'public').mkdir(parents=True)
        f=release/'public/.htaccess';f.write_text('RewriteEngine On\n')
        (release/'release.json').write_text(json.dumps({'revision':'test','files':{'public/.htaccess':support.file_hash(f)}}))
        return release
    def test_manifest_accepts_exact_files(self):support.verify_release(self.release(),{'revision':'test'})
    def test_manifest_rejects_modified_preview(self):
        release=self.release();(release/'public/.htaccess').write_text('changed')
        with self.assertRaises(AssertionError):support.verify_release(release,{'revision':'test'})
    def test_manifest_rejects_wrong_revision(self):
        with self.assertRaises(AssertionError):support.verify_release(self.release(),{'revision':'other'})
    def test_proxy_tls_guard(self):
        before='ProxyPass /worship/ http://127.0.0.1:8080/\nSSLCertificateFile /etc/ssl/certs/existing.pem\nServerName localhost\n'
        support.preserve_directives(before,before+'# extra\n')
        with self.assertRaises(AssertionError):support.preserve_directives(before,before.replace('8080','9000'))
        with self.assertRaises(AssertionError):support.preserve_directives(before,before.replace('existing.pem','other.pem'))
    def storage(self):
        legacy=self.root/'legacy';original=legacy/'live/uploads';original.mkdir(parents=True)
        (original/'old.jpg').write_bytes(b'old-image')
        release=self.root/'release';runtime=release/'storage/notice';runtime.mkdir(parents=True)
        (runtime/'old.jpg').write_bytes(b'old-image');(runtime/'new.png').write_bytes(b'new-image')
        return legacy,original,release,runtime
    def mocked_sync(self,legacy,release):
        with patch.object(support,'LEGACY',legacy),patch.object(support.subprocess,'run'),patch.object(support.os,'chown'),patch.object(support.pwd,'getpwnam',return_value=SimpleNamespace(pw_uid=1)),patch.object(support.grp,'getgrnam',return_value=SimpleNamespace(gr_gid=1)):
            support.use_existing_uploads(release)
    def test_shared_uploads_keep_old_and_new_images(self):
        legacy,original,release,runtime=self.storage();self.mocked_sync(legacy,release)
        self.assertTrue(runtime.is_symlink());self.assertEqual(runtime.resolve(),original.resolve())
        self.assertEqual((original/'old.jpg').read_bytes(),b'old-image');self.assertEqual((original/'new.png').read_bytes(),b'new-image')
        self.assertTrue((release/'storage/notice.before-cutover').is_dir())
    def test_upload_conflicts_do_not_overwrite_original(self):
        legacy,original,release,runtime=self.storage();(runtime/'old.jpg').write_bytes(b'conflict')
        with self.assertRaises(AssertionError):self.mocked_sync(legacy,release)
        self.assertEqual((original/'old.jpg').read_bytes(),b'old-image');self.assertFalse(runtime.is_symlink())
    def test_symlink_failure_restores_prepared_folder(self):
        legacy,original,release,runtime=self.storage()
        with patch.object(Path,'symlink_to',side_effect=OSError('test failure')):
            with self.assertRaises(OSError):self.mocked_sync(legacy,release)
        self.assertTrue(runtime.is_dir());self.assertFalse(runtime.is_symlink());self.assertEqual((runtime/'old.jpg').read_bytes(),b'old-image')
if __name__=='__main__':unittest.main()
