# 하늘문 예배 생방송 MVC

PHP MVC 기반 예배 생방송·다시보기 및 관리자 서비스입니다. Apache 웹 루트는 `public/`입니다.

## 구조

- `app/`: 컨트롤러·모델·화면·공통 헬퍼
- `core/`: MVC 라우팅과 기반 클래스
- `public/`: 웹 진입점·CSS·JavaScript·이미지
- `config/`: 설정 로더와 비공개 DB 설정 예제
- `tests/`: 권한·로그인·목록·날짜·공지·방송·YouTube 링크 회귀 검사
- `scripts/release/check.sh`: 코드 검사 명령. 기존 서버 사용 경로를 유지합니다.
- `docs/`: 기능 설명·Git 배포·운영 기준점

## 환경 및 데이터

검증 환경은 Apache 2.4, mod_php 7.4, MySQL 8입니다. rewrite·PDO MySQL·DOM·fileinfo가 필요하며 `public/.htaccess`를 허용하고 사이트 범위에서 short_open_tag를 활성화합니다. 첨부 한도는 5MB이며 PHP upload_max_filesize 5M, post_max_size 8M을 사용합니다.

DB 설정은 `config/database.local.php.example`을 참고해 서버별 `config/database.local.php`에 작성하거나 `SKY_DB_HOST`, `SKY_DB_NAME`, `SKY_DB_USER`, `SKY_DB_PASSWORD`로 공급합니다. Git에는 비공개 설정과 업로드를 넣지 않습니다.

현재 dev·운영 모두 서비스 코드는 `/var/www/sky0808net-app`, 업로드는 서버별 `/var/lib/sky0808net/notice`입니다. 저장소의 `storage/notice`가 해당 업로드 디렉터리를 연결합니다.

## 검사와 배포

```bash
bash scripts/release/check.sh
# 서버에서 읽기 전용 DB 검사까지 실행
bash scripts/release/check.sh --database
```

master 하나를 사용하며 로컬 커밋·push → dev pull·검증 → 동일 커밋을 운영에 반영합니다. 자세한 명령과 복구 절차는 [Git 배포 문서](docs/GIT_DEPLOYMENT.md)에 있습니다. 서버 설정과 DB 스키마는 별도로 관리합니다.

예전 코드, 압축 배포 도구, 일회성 Python 패치는 정리했습니다. 기록은 Git 이력과 [정리 기록](docs/LOCAL_CLEANUP.md)을 참고하세요.
