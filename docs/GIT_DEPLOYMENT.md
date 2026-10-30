# 운영 기준 코드와 master 단일 브랜치 배포

## 기준점

2026-10-30 운영 스냅샷을 로컬 코드와 비교했다. `app/`, `core/`, `public/`의 실제 서비스 코드와 기존 `tests/`, `scripts/release/`, `config/config.php`는 로컬 최신 코드와 바이트 단위로 일치한다. 운영 문서 두 개는 구버전이며 Python 캐시는 제외한다. 비교 기록은 [PRODUCTION_BASELINE.md](PRODUCTION_BASELINE.md)에 남겼다.

master 하나만 사용한다. 흐름은 **로컬 수정·커밋·push → dev pull·검증 → 운영에 같은 커밋 반영**이다. 서버에서 서비스 코드를 직접 고치지 않는다. DB 비공개 설정·업로드·Apache 설정은 각 서버에서 유지한다. git pull은 DB 스키마를 변경하지 않는다.

## 최초 전환

현재 Apache는 Git 저장소가 아닌 release 폴더를 서비스한다. 예전 `/var/www/html/sky0808net`에는 레거시 변경분과 운영 업로드가 있으므로 그대로 보존한다. 두 서버에 각각 깨끗한 `/var/www/sky0808net-app` Git 폴더를 준비하고 Apache 경로만 한 번 전환한다. **dev부터 완료한 다음 운영을 전환한다.**

각 서버의 활성 경로 확인:

```bash
grep -nE 'DocumentRoot|<Directory' /etc/apache2/sites-enabled/000-default*.conf
```

새 경로가 없을 때만 clone한다. 이미 있으면 `git status --short`, `git branch -vv`부터 확인하고 덮어쓰지 않는다.

```bash
git clone --single-branch --branch master https://github.com/dlgusrn/sky0808net.git /var/www/sky0808net-app
```

HTTPS 인증에는 GitHub 사용자 이름과 PAT를 대화식으로 입력한다. 토큰은 URL에 넣지 않는다. 아래 `ACTIVE_MVC_ROOT`는 해당 서버의 현재 DocumentRoot에서 `/public`을 뺀 경로다. 두 서버에서 서로 다르다.

```bash
(
set -e
ACTIVE_MVC_ROOT=/현재/서비스중인/MVC폴더
install -m 640 -o root -g www-data "$ACTIVE_MVC_ROOT/config/database.local.php" /var/www/sky0808net-app/config/database.local.php
mkdir -p /var/www/sky0808net-app/storage
test ! -e /var/www/sky0808net-app/storage/notice
test ! -L /var/www/sky0808net-app/storage/notice
ln -s "$(realpath "$ACTIVE_MVC_ROOT/storage/notice")" /var/www/sky0808net-app/storage/notice
runuser -u www-data -- test -r /var/www/sky0808net-app/config/database.local.php
runuser -u www-data -- test -w /var/www/sky0808net-app/storage/notice
bash /var/www/sky0808net-app/scripts/release/check.sh --database
)
```

`storage/notice`가 가리키는 원본 폴더를 보존한다. 운영은 기존 `/var/www/html/sky0808net/live/uploads`를 그대로 공유한다. dev는 자신의 업로드 저장소를 사용한다. 준비 스크립트를 따로 이용하는 경우 `scripts/git/prepare-checkout.sh`는 아직 없는 새 폴더만 생성하며 Apache를 변경하지 않는다.

검사가 통과한 다음 활성 HTTP/HTTPS 설정을 백업하고 두 파일의 DocumentRoot와 해당 Directory 경로를 `/var/www/sky0808net-app/public`으로 바꾼다. ProxyPass·TLS·AllowOverride·short_open_tag·업로드 크기 등 기존 지시문은 유지한다. configtest 성공 후에만 reload하고 로그인·생방송·관리 목록·첨부를 브라우저에서 확인한다.

```bash
apache2ctl configtest && systemctl reload apache2
```

최초 전환 실패 시 백업한 Apache 설정을 복원하고 configtest 후 reload한다. 이전 release 전용 활성화/롤백 스크립트는 이 전환 이후의 Apache 상태를 관리하지 않는다.

## 평소 개발 및 dev 반영

로컬에서 master에 변경 파일만 커밋한다.

```bash
git switch master
git pull --ff-only origin master
git add <변경한-파일>
git commit -m "변경 내용"
git push origin master
```

dev 서버에서 아래 순서로 실행한다. 명령 중 실패하면 다음 단계로 진행하지 않는다.

```bash
cd /var/www/sky0808net-app
git status --short                 # 코드 변경이 없어야 함
git branch --show-current          # master
git rev-parse HEAD > /root/sky0808net-before-pull.sha
git pull --ff-only origin master
bash scripts/release/check.sh --database
systemctl reload apache2
git rev-parse HEAD                 # 실제 브라우저 검증을 마친 SHA를 기록
```

## 운영 반영

먼저 로컬의 추가 push를 멈추고 dev에서 실제로 확인한 SHA를 기록한다. 운영은 최신 master가 그 SHA인지 **pull 전에** 확인한다. 다르면 미검증 변경이 있으므로 dev에서 새 master를 다시 검증한다.

```bash
cd /var/www/sky0808net-app
git status --short                 # 코드 변경이 없어야 함
git branch --show-current          # master
git fetch origin master
git rev-parse origin/master        # dev에서 검증한 SHA와 일치해야 진행
git rev-parse HEAD > /root/sky0808net-before-pull.sha
git pull --ff-only origin master
bash scripts/release/check.sh --database
systemctl reload apache2
git rev-parse HEAD                 # dev SHA와 다시 비교
```

PHP OPcache 반영을 위해 reload한다. Git 폴더를 직접 서비스하므로 pull 시 코드가 바로 바뀐다. 검사 실패 시 바로 아래 코드 롤백을 실행한다. DB 스키마 변경은 별도 절차로 처리한다.

## 코드 롤백

서버 코드 변경분이 없는 상태에서:

```bash
cd /var/www/sky0808net-app
git switch --detach "$(cat /root/sky0808net-before-pull.sha)"
systemctl reload apache2
```

DB·업로드·Apache는 되돌리지 않는다. 수정 커밋을 push하고 dev에서 확인한 뒤 `git switch master`, `git pull --ff-only origin master`, 검사, reload 순으로 복귀한다. 이전 브랜치는 기록으로 남겨도 되며 배포에는 사용하지 않는다.
