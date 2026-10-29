# 운영 사전 점검 (2026-10-29)

대상 hostname: sky0808net-235826. 운영 IP·서비스 Host는 별도 확인 후 전환합니다. dev 전용 prepare_dev/activate_dev 스크립트를 운영에서 실행하지 않습니다.

확인된 환경: Apache 2.4 + mod_php7 + rewrite/proxy/SSL, PHP 7.4.3, MySQL 8.0.42. HTTP/HTTPS 모두 기존 `/var/www/html/sky0808net`을 제공하고 HTTP `/worship` 프록시가 있습니다. 기존 인증서는 변경하지 않습니다. PHP 업로드 한도 2M은 새 사이트 범위에서 5M/POST 8M으로 설정해야 합니다.

- 기존 Git 작업 트리의 27개 수정 및 신규 공지/첨부 파일을 포함해 폴더 전체를 보존합니다. git pull/reset 하지 않습니다.
- 공지 첨부 9개를 원본 그대로 백업·복사합니다.
- `preacher_list.update_date`가 없습니다. 설교자 수정을 위해 nullable DATETIME 컬럼 추가가 필요합니다. `deploy/production-compatibility.sql`은 해당 컬럼만 조건부 추가합니다. 적용 전 백업·ALTER 락 영향을 검토합니다.
- `notice.title`은 varchar(100)이므로 앱 검증과 입력칸을 100자로 맞춥니다. 제목 컬럼 변경은 필요하지 않습니다.
- 관리자 비밀번호 varchar(64)는 현재 PHP 7.4의 bcrypt 해시(60자) 및 기존 SHA-256(64자)을 저장할 수 있습니다. 이번 전환에서 기존 비밀번호를 일괄 변경하지 않습니다.
- `member_admin`의 관련 없는 `열 14` 컬럼은 그대로 둡니다.
- `EV_del_live_entry`는 3초마다 실행되는 기존 이벤트입니다. 정의와 event_scheduler 상태를 읽기 전용으로 확인하고 변경하지 않습니다.

순서: 운영 코드·Apache·DB 백업 → 읽기 전용 이벤트/컬럼 확인 → 호환 컬럼 적용 검토 → 별도 릴리스/운영 비공개 DB 설정/첨부 복사 → 운영 localhost 전용 사전 검사 → HTTP/HTTPS 전환 후보 검토 → 실제 전환. 아직 운영 설정이나 DB를 변경하지 않았습니다.

## 별도 폴더 준비

`prepare_production.py`는 hostname `sky0808net-235826`만 허용하며 기본 코드/DB/Apache를 변경하지 않습니다. 기존 백업의 gzip 무결성과 SQL 완료 표식, 배포 체크섬·manifest를 검사하고 새 폴더를 만듭니다. 운영 legacy DB 설정을 서버 내부에서 비공개 파일로 가져오고 동일 DB/서버인지 검증합니다. 공지 이미지도 원본 그대로 복사합니다. 기존 이벤트는 조회만 합니다.

```sh
python3 /root/prepare_production.py /root/sky0808net-COMMIT.tar.gz /root/sky0808-before-mvc-20261029-160025
```

PHP/권한/검색/공지 테스트와 읽기 전용 DB 검사를 통과하면 `sky0808net-prod-releases/시간-커밋/deployment`에 운영 Apache 원본·전환 후보를 준비합니다. enabled 설정 변경이나 reload는 하지 않습니다. 다음 단계는 localhost 전용 미리보기와 전환 후보 검토입니다. dev용 activate_dev.py를 운영에 사용하면 안 됩니다.

## 최종 전환

방송 선택은 `use=Y AND view=Y`를 관리자·일반·상태 조회에 동일하게 적용합니다. 운영 미리보기에서 확인된 과거 영상 표시 문제를 고쳤고, 상태 조합 테스트 및 운영 DB 읽기 전용 비교를 회귀 검사에 포함합니다. 임시 패치된 미리보기 대신 새 커밋의 배포본을 다시 준비합니다.

새 배포본 준비 후 `activate_production.py RELEASE/deployment`로 후보 차이를 확인하고, 승인된 전환은 같은 명령에 `--apply`를 붙여 실행합니다. 스크립트는 hostname·계획·파일 manifest·기존 설정 해시·프록시/인증서 보존을 확인하고 회귀 검사 후 전환합니다. 설정 또는 HTTP 검사 실패 시 기존 vhost를 자동 복원합니다.

전환 중 기존 공지 업로드가 끝나는 경우와 롤백을 대비해 최종 배포본의 `storage/notice`는 **기존 `/var/www/html/sky0808net/live/uploads`를 공유하는 링크**로 연결합니다. 기존 첨부는 덮어쓰지 않고 충돌을 검사하며 준비한 이미지 복사본도 보존합니다. 기존 legacy 폴더를 삭제하지 않습니다. DB 데이터·이벤트는 변경하지 않습니다.

수동 롤백은 `rollback_production.py RELEASE/deployment`입니다. 이후 별도 수정된 Apache 설정은 덮어쓰지 않고 중단합니다. DB 기록은 자동 원복하지 않으며, 업로드 데이터는 공유 폴더에 유지됩니다. HTTP가 실제 사용 경로이며 HTTPS는 기존 인증서를 보존합니다.
