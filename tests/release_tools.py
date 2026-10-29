#!/usr/bin/env python3
from pathlib import Path
import unittest,importlib.util
ROOT=Path(__file__).resolve().parents[1]
spec=importlib.util.spec_from_file_location('prepare_dev',ROOT/'scripts/release/prepare_dev.py')
module=importlib.util.module_from_spec(spec);spec.loader.exec_module(module)
class DevConfigurationTests(unittest.TestCase):
    def candidate(self,text):return module.candidate_config(text,Path('/var/www/html/sky0808net'),Path('/var/www/html/sky0808net-releases/test'))
    def test_http_proxy_is_preserved(self):
        text='<VirtualHost *:80>\nDocumentRoot /var/www/html/sky0808net\nProxyPass /worship/ http://127.0.0.1:8080/\nProxyPass /worship/ws ws://127.0.0.1:8080/ws\nLimitRequestBody 52428800\n</VirtualHost>\n'
        out=self.candidate(text)
        for line in text.splitlines():
            if not line.startswith('DocumentRoot')and line!='</VirtualHost>':self.assertIn(line,out)
        self.assertIn('/sky0808net-releases/test/public',out)
        self.assertIn('AllowOverride All',out)
        self.assertIn('upload_max_filesize 5M',out)
    def test_https_certificate_is_preserved(self):
        text='<IfModule mod_ssl.c>\n<VirtualHost _default_:443>\nDocumentRoot /var/www/html/sky0808net\nSSLEngine on\nSSLCertificateFile /etc/ssl/certs/existing.pem\nSSLCertificateKeyFile /etc/ssl/private/existing.key\n</VirtualHost>\n</IfModule>'
        out=self.candidate(text)
        self.assertIn('SSLCertificateFile /etc/ssl/certs/existing.pem',out)
        self.assertIn('SSLCertificateKeyFile /etc/ssl/private/existing.key',out)
        self.assertNotIn('R=301',out)
    def test_unexpected_application_is_rejected(self):
        with self.assertRaises(AssertionError):self.candidate('<VirtualHost *:80>\nDocumentRoot /var/www/html/other\n</VirtualHost>')
    def test_multiple_vhosts_are_rejected(self):
        with self.assertRaises(AssertionError):self.candidate('<VirtualHost *:80>\nDocumentRoot /var/www/html/sky0808net\n</VirtualHost>\n<VirtualHost *:81>\nDocumentRoot /var/www/html/sky0808net\n</VirtualHost>')
if __name__=='__main__':unittest.main()
