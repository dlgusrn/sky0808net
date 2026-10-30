# master 하나로 dev 검증 후 운영 반영

## 현재 서버 구조

| 구분 | dev | 운영 |
|---|---|---|
| 서버 | 49.247.175.56 | 49.247.173.113 |
| Git 코드 | /var/www/sky0808net-app | /var/www/sky0808net-app |
| Apache DocumentRoot | /var/www/sky0808net-app/public | /var/www/sky0808net-app/public |
| 업로드 | /var/lib/sky0808net/notice | /var/lib/sky0808net/notice |
| 브랜치 | master | master |

각 서버의 `storage/notice`는 그 서버의 독립 업로드 디렉터리를 가리킵니다. DB 비공개 설정은 `config/database.local.php`에 유지합니다. Git pull은 업로드·비공개 설정을 교체하지 않으며 DB 스키마를 변경하지 않습니다.

로컬에서만 코드를 수정합니다. 흐름은 **커밋·push → dev pull·검사·브라우저 확인 → 같은 커밋을 운영에 반영**입니다. 이전 release 폴더·미리보기 사이트·레거시 코드는 두 서버에서 정리됐습니다. 예전 Python 배포/롤백 스크립트는 사용하지 않습니다.

## 로컬

```bash
git switch master
git pull --ff-only origin master
# 코드 수정 후 변경 파일만 선택
git add <변경한-파일>
git commit -m "변경 내용"
git push origin master
```

## dev

작업 폴더에 코드 변경이 없는지 확인합니다. 각 명령이 실패하면 다음 단계로 진행하지 않습니다.

```bash
cd /var/www/sky0808net-app
git status --short
git branch --show-current          # master
git rev-parse HEAD > /root/sky0808net-before-pull.sha
git pull --ff-only origin master
bash scripts/release/check.sh --database
systemctl reload apache2
git rev-parse HEAD
```

실제 로그인·방송·변경 기능·공지 이미지를 확인하고 검증한 SHA를 기록합니다. PHP OPcache 반영을 위해 reload합니다. Git 폴더를 직접 서비스하므로 pull 시 코드가 바로 변경됩니다.

## 운영

먼저 dev 검증 후 추가 push를 멈춥니다. 운영에서 fetch한 SHA가 **dev에서 검증한 SHA**와 같은지 확인한 후 pull합니다. 다르면 dev에서 새 master를 검증합니다.

```bash
cd /var/www/sky0808net-app
git status --short
git branch --show-current          # master
git fetch origin master
git rev-parse origin/master        # dev에서 검증한 SHA와 같아야 진행
git rev-parse HEAD > /root/sky0808net-before-pull.sha
git pull --ff-only origin master
bash scripts/release/check.sh --database
systemctl reload apache2
git rev-parse HEAD                 # dev SHA와 다시 비교
```

`--ff-only`가 실패하면 서버에서 merge하거나 reset하지 말고 로컬에서 원인을 확인합니다. 검사 실패 시 즉시 아래 코드 롤백을 수행합니다. DB 스키마·Apache/PHP 설정 변경은 별도 절차로 처리합니다.

## 코드 롤백

서버 코드 변경분이 없는 상태에서:

```bash
cd /var/www/sky0808net-app
git switch --detach "$(cat /root/sky0808net-before-pull.sha)"
systemctl reload apache2
```

DB·업로드·Apache 설정은 되돌리지 않습니다. 수정 커밋을 push하고 dev에서 확인한 다음 `git switch master`, `git pull --ff-only origin master`, 검사, reload 순으로 복귀합니다. 스키마 변경이 있는 배포는 별도 복구 계획이 필요합니다.

## 백업

운영 정리 백업: `/root/sky0808net-cleanup-kNgKWg/old-code-and-apache.tar.gz`. dev 정리 백업은 해당 서버에서 출력된 `/root/sky0808net-cleanup-*/old-code-and-apache.tar.gz`를 확인합니다. 오래된 Apache 설정을 복원하면 이미 정리한 코드 경로를 참조하므로, 전체 복구가 필요한 경우에만 코드와 함께 검토해 복원합니다.
