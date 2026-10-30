# Git으로 dev 검증 후 운영 반영

## 기준

- 로컬에서 코드 수정 → `develop` push → dev에서 pull 및 기능 확인.
- 검증한 develop 커밋을 `master`에 fast-forward merge → push → 운영에서 pull.
- 서버에서 코드를 직접 수정하지 않는다. DB 비공개 설정과 업로드는 Git 대상이 아니다.
- Apache/PHP 설정, DB 스키마 변경은 필요한 작업에서 따로 검토·적용한다. git pull은 DB를 변경하지 않는다.
- 서버의 기존 `/var/www/html/sky0808net`는 레거시 변경분과 업로드가 있으므로 그대로 보존한다.
- 두 서버의 새 Git 폴더는 각각 `/var/www/sky0808net-app`을 사용한다.
- 기존 tar/일회용 Python 패치는 이 전환 이후의 코드 배포에 사용하지 않는다.

## 최초 전환: dev부터

먼저 로컬의 현재 수정까지 커밋하고 `develop`을 GitHub에 push한다. 이 문서와 준비 스크립트도 그 커밋에 포함한다.

각 서버에서 현재 Apache의 DocumentRoot와 Directory 경로를 확인한다.

```bash
rg -n 'DocumentRoot|<Directory' /etc/apache2/sites-enabled/*.conf
```

개발 서버는 `develop`, 운영 서버는 `master`를 clone한다. 저장소 읽기 인증은 서버별 read-only GitHub deploy key를 권장한다. HTTPS clone을 쓰면 사용자 이름과 PAT를 대화식으로 입력한다. 토큰을 URL·명령어에 넣지 않는다.

아래 `ACTIVE_MVC_ROOT`는 **해당 서버가 현재 서비스하는 MVC 경로**로 바꾼다. 개발 서버와 운영 서버의 경로는 서로 다르다.

```bash
git clone --single-branch --branch develop https://github.com/dlgusrn/sky0808net.git /var/www/sky0808net-app
# 운영은 위 명령의 develop 대신 master 사용
```

clone한 준비 스크립트는 새 경로를 요구하므로, 이미 clone한 경우 아래 초기화 명령으로 이어간다.

```bash
ACTIVE_MVC_ROOT=/현재/Apache가/사용하는/MVC폴더
install -m 640 -o root -g www-data "$ACTIVE_MVC_ROOT/config/database.local.php" /var/www/sky0808net-app/config/database.local.php
mkdir -p /var/www/sky0808net-app/storage
ln -s "$(realpath "$ACTIVE_MVC_ROOT/storage/notice")" /var/www/sky0808net-app/storage/notice
runuser -u www-data -- test -r /var/www/sky0808net-app/config/database.local.php
runuser -u www-data -- test -w /var/www/sky0808net-app/storage/notice
bash /var/www/sky0808net-app/scripts/release/check.sh --database
```

업로드 심볼릭 링크가 가리키는 원본 폴더는 보존한다. 운영은 기존 `/var/www/html/sky0808net/live/uploads`를 계속 사용한다. dev는 현재 사용 중인 저장소를 연결한다.

다른 방법으로, 준비 스크립트를 `/root`로 복사해 두고 **아직 없는 새 경로**를 인자로 실행해도 된다. clone·설정 복사·업로드 연결·검사만 수행하며 Apache는 바꾸지 않는다.

```bash
bash /root/prepare-checkout.sh "$ACTIVE_MVC_ROOT" /var/www/sky0808net-app develop
```

검사가 통과하면 현재 HTTP/HTTPS 가상호스트 파일을 백업하고, 각 파일에서 아래 두 경로만 새 Git 폴더로 바꾼다.

```apache
DocumentRoot "/var/www/sky0808net-app/public"
<Directory "/var/www/sky0808net-app/public">
    # 현재 Directory 블록의 설정은 유지
</Directory>
```

기존 ProxyPass, TLS, 업로드 크기, rewrite/AllowOverride, PHP 설정은 유지한다. `apache2ctl configtest` 성공 후 `systemctl reload apache2`를 실행하고 브라우저로 로그인·생방송·관리 목록·첨부를 확인한다. 먼저 dev에서 완료한 뒤 운영을 전환한다. 최초 전환 실패 시 백업한 Apache 설정을 복원하고 configtest 후 reload한다. 기존 release 전용 활성화/롤백 스크립트는 이 전환 이후의 Apache 상태를 관리하지 않는다.

## 평소 개발 → dev 반영

로컬:

```bash
git switch develop
# 코드 수정 후 변경 파일만 선택해서 커밋
git add <변경한-파일>
git commit -m "변경 내용"
git push origin develop
```

dev 서버:

```bash
cd /var/www/sky0808net-app
git status --short                 # 코드 변경이 있으면 원인을 먼저 확인
git branch --show-current          # develop 확인
git pull --ff-only origin develop
bash scripts/release/check.sh --database
# PASS 후
systemctl reload apache2
git rev-parse HEAD                 # 브라우저 검증한 커밋을 기록
```

실제 로그인·방송·변경 기능까지 확인한다. PHP OPcache 반영을 위해 reload한다. 직접 사용하는 Git 폴더에서 pull하면 코드가 즉시 바뀌므로 이용이 적은 시간에 실행하고, 검사 실패 시 아래 절차로 직전 코드로 되돌린다.

## dev에서 확인한 커밋만 운영 반영

로컬에서 dev 검증 완료 커밋을 사용한다. 그 사이 develop에 추가된 미검증 커밋은 포함하지 않는다.

```bash
git fetch origin
git switch master
git pull --ff-only origin master
git merge --ff-only <dev에서-확인한-커밋-SHA>
git push origin master
git rev-parse HEAD
```

운영 서버:

```bash
cd /var/www/sky0808net-app
git status --short                 # 코드 변경이 없어야 함
git branch --show-current          # master 확인
git rev-parse HEAD > /root/sky0808net-before-pull.sha
git pull --ff-only origin master
git rev-parse HEAD                 # dev에서 확인한 SHA와 일치 확인
bash scripts/release/check.sh --database
# PASS 후
systemctl reload apache2
```

`--ff-only`가 실패하면 서버에서 merge하지 말고 로컬에서 브랜치 상태를 확인한다. 검사 실패 시 reload를 진행하지 말고 즉시 아래 코드 롤백을 수행한다. 공지 이미지 등은 Git 밖에 있어 pull로 교체되지 않는다.

## 코드 롤백

운영에서 코드 변경분이 없는 상태에 한해:

```bash
cd /var/www/sky0808net-app
git switch --detach "$(cat /root/sky0808net-before-pull.sha)"
systemctl reload apache2
```

DB·업로드·Apache 설정은 되돌리지 않는다. 스키마 변경을 포함한 배포는 그 변경에 맞는 별도 복구 절차를 준비한다. 수정본을 master에 반영한 다음 운영에서 `git switch master`, `git pull --ff-only origin master`, 검사, reload 순으로 복귀한다.
