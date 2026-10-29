# Git 기준 MVC를 dev 기본 주소로 전환

현재 대상은 **dev 49.247.175.56**입니다. 운영 서버 전환은 이 절차의 대상이 아닙니다.

## 보존하는 환경

- 기존 dev 코드·미커밋 운영본: `/var/www/html/sky0808net` (덮어쓰거나 git pull/reset 하지 않음)
- 수동 검증한 MVC: `/var/www/html/sky0808net-mvc-dev`, localhost:18080 (유지)
- Git 기준 새 릴리스: `/var/www/html/sky0808net-releases/시간-커밋/`
- 기본 80/443 가상 호스트만 새 릴리스의 `public/`으로 전환
- `/worship` HTTP/WebSocket 프록시·현재 인증서는 기존 활성 설정 그대로 유지

## 릴리스 구성

Git에 커밋된 `app`, `core`, `public`, `config`, `tests`, `scripts`, `docs`, README만 묶습니다. 임시 미리보기, `.idea`, 이전 `live/`·`www/`, 백업, 업로드, DB 비밀번호가 담긴 비공개 파일은 포함하지 않습니다. `.htaccess`와 파일별 SHA-256 manifest를 포함합니다.

DB는 분리하지 않고 기존 dev DB를 그대로 사용합니다. 접속 정보는 기존 MVC dev `config/config.php`에서 서버 내부에서만 가져와 새 릴리스의 `config/database.local.php`에 저장합니다. 파일 권한은 root:www-data 640이며 Git·릴리스 패키지에 넣지 않습니다. 환경 변수 `SKY_DB_HOST/NAME/USER/PASSWORD`가 있다면 우선 적용됩니다. 새 릴리스는 비공개 설정 없이 DB 연결을 시도하지 않습니다.

## 1. Mac에서 빌드

```sh
cd /Users/lhg/workspace/1_private/sky0808net
python3 scripts/release/build.py
```

출력된 `.tar.gz`, `.tar.gz.sha256`, `scripts/release/prepare_dev.py`를 root의 dev 서버에 복사합니다. 패키지는 현재 작업 파일이 아니라 명시된 Git 커밋에서 생성됩니다.

## 2. 서버에서 준비 (Apache 변경 없음)

```sh
python3 /root/prepare_dev.py /root/sky0808net-COMMIT.tar.gz
```

체크섬·manifest 검증 후 별도 릴리스 폴더에 풀고, 비공개 DB 설정과 공지 이미지 복사본을 준비합니다. PHP 문법, 권한/검색/기간/페이지네이션/공지 단위 테스트와 DB 읽기 전용 검사를 실행합니다. 파일 충돌은 덮어쓰지 않고 중단합니다. DB 스키마·레코드는 변경하지 않습니다.

기존 활성 HTTP/HTTPS 설정에서 DocumentRoot와 MVC Directory 블록만 바꾼 후보 파일 및 원본 백업을 `릴리스/deployment/`에 만듭니다. 이 단계에서는 Apache를 reload하지 않습니다.

## 3. 후보 확인 후 dev에 적용

스크립트가 출력한 실제 RELEASE 경로를 사용합니다.

```sh
python3 RELEASE/scripts/release/activate_dev.py RELEASE/deployment
# 차이를 확인한 다음 dev 기본 주소로 전환:
python3 RELEASE/scripts/release/activate_dev.py RELEASE/deployment --apply
```

준비 이후 원본 Apache 설정이 바뀌었으면 중단합니다. 회귀 테스트를 재실행하고 HTTP/HTTPS 후보를 반영한 뒤 `apache2ctl configtest` → graceful reload → HTTP smoke 검사를 실행합니다. 실패하면 이전 Apache 설정으로 복원합니다.

같은 인증서·프록시를 유지하지만 HTTPS는 브라우저에서도 별도 확인합니다. `18080`의 기존 테스트 사이트는 변경하지 않습니다.

## 4. 실제 dev 기본 주소 확인

터널의 localhost:18080이 아니라 실제 dev 주소에서 확인합니다.

- `/live` → `/user/login`, 일반 로그인·교회 전환·방송 상태 전환
- `/admin/login`, `/admin/` 메인 표시, 관리자 로그아웃
- `/live/admin/` 등 이전 읽기 전용 북마크 이동
- 관리자 권한별 메뉴와 URL 차단
- 목록 표시 개수·기간 프리셋·직접 날짜·검색어·교회/설교자 조건·5페이지 묶음 이동
- 링크/블랙리스트/관리자/설교자/다시보기 CRUD (테스트 항목만)
- 접속자 갱신·송출 중단/재개·입장/퇴장 이력
- 공지 등록/수정/이미지 유지·교체·5MB 제한·팝업/숨김
- PC/모바일 메뉴·영상/접속자 높이·표 스크롤
- 기존 `/worship` 서비스와 WebSocket

## 롤백

```sh
python3 RELEASE/scripts/release/rollback_dev.py RELEASE/deployment
```

활성화 이후 Apache에 다른 수정이 있으면 덮어쓰지 않고 중단합니다. 테스트 중 생성된 공지 첨부를 이전 MVC와 legacy 업로드 폴더로 먼저 복사한 뒤 기존 HTTP/HTTPS 설정을 복원합니다. DB는 공유하므로 테스트 중 추가·수정한 레코드를 자동 원복하지 않습니다. DB 백업이 필요하면 테스트 전 별도로 확보합니다. 기존 SQL의 DROP/ALTER 스크립트는 이 배포에서 실행하지 않습니다.

## Git 반영 범위

기능 이관·권한·접속 이력·공지/첨부·검색/페이지 이동·공식 로고·UI를 최종 소스와 테스트로 커밋합니다. 개발 중 전달한 `tools/*.py` 패치 스크립트는 로컬에 보관하고 신규 배포에는 사용하지 않습니다. 운영 서버 정보 확인 및 실제 운영 배포는 dev 기본 주소 확인 이후 별도로 진행합니다.
