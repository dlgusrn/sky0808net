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
