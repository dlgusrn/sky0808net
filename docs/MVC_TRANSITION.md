# 운영본 → MVC 전환 점검

> 개발 중 이관 기록입니다. Git 기준 신규 배포는 [DEV_RELEASE.md](DEV_RELEASE.md)를 따릅니다. `tools/*.py`는 일회성 로컬 패치 기록이며 릴리스에 포함하지 않습니다.


## 기준

- 운영 소스: 로컬 `sky0808_live/`. 운영 서버 경로는 `/var/www/html/sky0808net/`.
- MVC 소스: 로컬 `sky0808net/`, master `307261f`.
- 운영 저장소의 HEAD는 `883822f`로 11개 커밋 뒤에 있지만, 별도 미커밋 수정과 신규 공지사항·업로드가 있으므로 단순 pull/덮어쓰기로 대체하면 안 됩니다.
- 운영본에 추가했던 UI 수정은 작업 전 스냅샷으로 되돌렸습니다. MVC 전환과 UI 수정은 순서대로 진행합니다.

## 확인된 차이와 이번 수정

1. 현재 HTTP/HTTPS DocumentRoot는 저장소 루트입니다. MVC는 `public/`을 루트로 사용해야 합니다.
2. 기존 HTTP `.conf.next`는 `/worship` HTTP/WebSocket 프록시를 누락하고 HTTPS→HTTP 전환을 추가한 별도 설정이었습니다. 현재 운영 설정을 바탕으로 전환 후보를 다시 작성했습니다.
3. HTTP/HTTPS 각각 `000-default.conf.next`, `000-default-ssl.conf.next`를 마련했습니다. 활성 `.conf` 및 sites-enabled는 수정하지 않았습니다. 인증서와 기존 각 가상 호스트의 프록시 동작을 보존합니다.
4. MVC bootstrap·controller·model/view 로딩 경로를 `__DIR__` 기준으로 바꾸어 PHP 작업 디렉터리 의존성을 제거했습니다.
5. 제공된 Apache 설정에는 rewrite 모듈과 `/var/www`의 AllowOverride All이 있습니다. 따라서 이 자료만으로 mod_rewrite 미활성화를 원인으로 단정할 수 없습니다.

## 아직 통합이 필요한 기능 차이

- 운영의 공지사항 등록·수정·노출·활성화·이미지 첨부는 MVC controller/model에 구현돼 있지 않습니다.
- 운영본의 CSRF, POST 검증, 세션 재발급, 권한 검사를 MVC 각 동작과 대조해야 합니다. auth.inc 파일이 존재하는 것만으로 실제 적용됐다고 볼 수 없습니다.
- 운영 `/live/admin/`, `/live/index.html`, `/live/member/*`, `/live/functions/*`와 MVC `/admin/*`, `/user/*`, `/live/*`는 URL과 세션 키가 다릅니다. 기존 링크·폼·AJAX 호출을 함께 이관해야 합니다.
- MVC에서 추가된 다시보기·설교자 테이블이 실제 DB에 있는지 확인해야 합니다. 기존 SQL 파일에는 DROP TABLE 문이 있으므로 운영 DB에 그대로 실행하지 않습니다.
- 운영 `live/uploads/` 데이터와 DB 접속 설정을 별도 보존해야 합니다.

## 실서버 진단에 필요한 자료

실패한 URL·HTTP 상태와 해당 요청 시각의 Apache/PHP 오류 로그를 우선 확인합니다. 같은 시점의 자료가 없으면 현재 설정만으로 당시 실패 원인을 확정할 수 없습니다.

서버에서 읽기 전용으로 확인할 명령:

```sh
sudo apache2ctl -S
sudo apache2ctl -M
php -v
php -m
sudo tail -n 100 /var/log/apache2/error.log
```

CLI PHP와 Apache PHP 설정은 다를 수 있습니다. `short_open_tag` 사용 여부 및 Apache에서 로드하는 PHP 설정도 확인 대상입니다.

## 전환 순서

기능 차이 이관 → 별도 환경에서 PHP 7.4/Apache·DB 복제본 검증 → HTTP/HTTPS 전환 후보 설정의 `apache2ctl configtest` → 적용 및 로그인·CRUD·송출·접속자·공지·업로드·기존 링크 확인 → UI 수정.

현재는 로컬 소스와 전환 후보만 수정했습니다. 서버 접속·DB 변경·Apache 재시작·운영 배포는 하지 않았습니다. PHP 실행기가 없어 로컬 PHP 문법 검사와 Apache 실행 검증도 아직 하지 못했습니다.
