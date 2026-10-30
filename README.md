# 하늘문 예배 생방송 MVC

PHP MVC 기반 예배 생방송·다시보기 및 관리자 서비스입니다. 웹 루트는 저장소의 `public/`입니다.

## 주요 기능

- 일반 로그인·교회별 생방송·상태 자동 반영
- 실시간 접속자와 입장/퇴장 이력
- 관리자 서버 권한 검사, 계정·블랙리스트·설교자 관리
- 방송 링크·다시보기 관리
- 공지사항·이미지 첨부·기간 노출·하루 숨기기
- 목록 10/20/50/100개 보기, 기간 프리셋/직접 날짜/검색어, 조건 유지 및 5페이지 묶음 이동
- 공식 로고와 PC/모바일 관리자 UI

## 실행 환경과 설정

dev에서 Apache 2.4, mod_php 7.4, MySQL로 검증했습니다. rewrite·PDO MySQL·DOM·fileinfo 확장이 필요합니다. Apache의 DocumentRoot를 `public/`으로 지정하고 `.htaccess`를 허용합니다. 기존 일부 뷰는 short_open_tag를 사용하므로 사이트 범위에서 활성화합니다.

DB 설정은 `config/database.local.php.example`을 참고해 서버의 `config/database.local.php`에 작성하거나 `SKY_DB_HOST`, `SKY_DB_NAME`, `SKY_DB_USER`, `SKY_DB_PASSWORD` 환경 변수로 공급합니다. 비공개 설정·업로드는 Git에 넣지 않습니다.

공지 이미지는 `storage/notice`에 저장하며 PHP 프로세스의 쓰기 권한이 필요합니다. 첨부 최대 5MB를 위해 사이트의 PHP 업로드 한도 5M, POST 한도 8M을 사용합니다.

## 검사 및 dev 릴리스

```sh
bash scripts/release/check.sh
# dev DB에 읽기 전용 검색 검사도 수행:
bash scripts/release/check.sh --database
python3 scripts/release/build.py
```

실제 dev 기본 주소 전환·검증·롤백은 [DEV_RELEASE.md](docs/DEV_RELEASE.md)를 따릅니다. 개발 중 localhost:18080에서 수행한 검증과 기본 80/443 전환 검증은 구분합니다. 운영 배포는 dev 기본 주소 검증 후 진행합니다.

## Git 배포

앞으로의 코드 배포는 [Git 배포 절차](docs/GIT_DEPLOYMENT.md)를 기준으로 합니다. `master` 하나를 사용하며, dev에서 확인한 커밋을 운영에 반영합니다. 기존 압축 배포에서 Git 폴더로 전환하는 최초 작업이 필요하며, 서버 설정·DB 비공개 설정·업로드는 별도로 유지합니다.
