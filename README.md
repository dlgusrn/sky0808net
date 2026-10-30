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

## 코드 수정 및 배포 순서

**master 하나로 로컬 수정·push → dev pull·검증 → 운영에 같은 커밋 반영** 순서로 진행합니다. 서버에서 직접 코드를 수정하지 않습니다. 아래 서버 명령은 root SSH 접속 후 실행하며, zsh에서도 동일하게 동작하도록 bash를 명시했습니다.

### 1. 로컬 Mac — 수정 전 최신 코드 받기

```bash
cd /Users/lhg/workspace/1_private/sky0808net
git switch master
git pull --ff-only origin master
```

실패하거나 충돌이 발생하면 먼저 해결한 뒤 코드를 수정합니다.

### 2. 로컬 Mac — 수정 후 커밋·push

```bash
git status --short
git diff
```

변경 내용을 확인하고 아래 `<수정한-파일>`과 커밋 메시지를 바꿔 실행합니다. 파일이 여러 개면 공백으로 구분합니다.

```bash
git add <수정한-파일>
git commit -m "수정 내용"
git push origin master
```

push가 성공한 다음 dev로 진행합니다.

### 3. dev 서버 — pull·검사

Mac에서 접속합니다.

```bash
ssh root@49.247.175.56
```

접속한 dev 서버에서 아래 블록 전체를 실행합니다. 브랜치가 다르거나 변경분이 있거나 검사에 실패하면 중단합니다. 이 블록은 자동 롤백하지 않으므로 pull 이후 실패하면 아래 복구 절차를 실행합니다.

```bash
bash <<'BASH'
set -euo pipefail
test "$(hostname -s)" = sky0808net-259401
cd /var/www/sky0808net-app

test "$(git branch --show-current)" = master
if [ -n "$(git status --porcelain)" ]; then
    git status --short
    echo "서버 변경분이 있어 중단합니다."
    exit 1
fi

git rev-parse HEAD > /root/sky0808net-before-pull.sha
git pull --ff-only origin master
bash scripts/release/check.sh --database
apache2ctl configtest
systemctl reload apache2
curl -fsS -o /dev/null http://127.0.0.1/admin/login

echo "PASS: dev 반영·검사 완료. 브라우저 확인 후 아래 SHA를 기록하세요."
git rev-parse HEAD
BASH
```

브라우저에서 **변경한 기능, 로그인, 생방송, 관리 목록, 공지 이미지·업로드**를 확인합니다. 정상일 때 출력된 **전체 커밋 SHA**를 기록합니다. dev 확인 이후에는 운영 반영이 끝날 때까지 추가 push를 멈춥니다.

### 4. 운영 서버 — 검증한 커밋 확인·반영

Mac의 다른 터미널에서 접속하거나 dev에서 `exit` 후 접속합니다.

```bash
ssh root@49.247.173.113
```

아래 `DEV_VERIFIED_SHA` 값을 **dev에서 확인한 전체 SHA**로 바꾼 다음 블록 전체를 실행합니다. 원격 master가 그 SHA와 다르면 pull 전에 중단합니다. pull 이후 검사 실패 시 아래 복구 절차를 실행합니다.

```bash
bash <<'BASH'
set -euo pipefail
DEV_VERIFIED_SHA='여기에_dev에서_확인한_전체_SHA_입력'

test "$(hostname -s)" = sky0808net-235826
cd /var/www/sky0808net-app

test "$(git branch --show-current)" = master
if [ -n "$(git status --porcelain)" ]; then
    git status --short
    echo "서버 변경분이 있어 중단합니다."
    exit 1
fi

git fetch origin master
if [ "$(git rev-parse origin/master)" != "$DEV_VERIFIED_SHA" ]; then
    echo "dev 검증 커밋과 원격 master가 다릅니다. 반영을 중단합니다."
    git rev-parse origin/master
    exit 1
fi

git rev-parse HEAD > /root/sky0808net-before-pull.sha
git pull --ff-only origin master
test "$(git rev-parse HEAD)" = "$DEV_VERIFIED_SHA"
bash scripts/release/check.sh --database
apache2ctl configtest
systemctl reload apache2
curl -fsS -o /dev/null http://127.0.0.1/admin/login

echo "PASS: 운영 반영·검사 완료"
git rev-parse HEAD
BASH
```

운영 브라우저에서도 변경한 기능과 이미지·업로드를 확인하면 완료입니다. SHA가 다르다는 메시지가 나오면 새 master를 dev에서 먼저 검증합니다. `--ff-only` 실패 시 서버에서 강제 reset이나 merge를 하지 말고 원인을 확인합니다.

### 5. 문제 발생 시 — 해당 서버에서 이전 코드 복구

pull 이후 검사나 기능 확인에 실패했을 때 실행합니다. `/root/sky0808net-before-pull.sha`는 위 명령이 저장한 **마지막 pull 직전 커밋**입니다. 실패한 배포 블록을 반복 실행하면 이 값이 바뀌므로 먼저 복구합니다. 서버 코드에 별도 수정이 있으면 중단하고 확인합니다.

```bash
bash <<'BASH'
set -euo pipefail
cd /var/www/sky0808net-app

test -s /root/sky0808net-before-pull.sha
test -z "$(git status --porcelain)"
git switch --detach "$(cat /root/sky0808net-before-pull.sha)"
apache2ctl configtest
systemctl reload apache2
echo "이전 코드로 복구 완료"
git log -1 --oneline
BASH
```

복구 후에는 detached HEAD 상태입니다. 로컬에서 수정본을 커밋·push하고 dev에서 먼저 검증합니다. 해당 서버를 다시 반영할 때는 `git switch master`로 복귀한 다음 위의 dev 또는 운영 배포 블록을 실행합니다. 복귀하는 순간 master 코드로 바뀌므로 수정본 반영을 진행할 때 실행합니다.

## 배포 시 유지되는 데이터

- DB 비밀번호 등 비공개 설정: 서버별 `config/database.local.php`
- 업로드 원본: 서버별 `/var/lib/sky0808net/notice`
- 업로드 연결: `/var/www/sky0808net-app/storage/notice`

코드 pull과 위 롤백은 **DB 데이터·스키마, 첨부파일, Apache/PHP 설정을 변경하지 않습니다.** 스키마나 서버 설정 변경은 별도로 적용·복구합니다.

추가 운영 정보는 [Git 배포 문서](docs/GIT_DEPLOYMENT.md), 예전 코드·배포 도구 정리 기록은 [정리 기록](docs/LOCAL_CLEANUP.md)을 참고하세요.
