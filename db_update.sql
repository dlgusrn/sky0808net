-- v2.0 변경사항
--  1. 접속자 테이블 실시간 동기화 가능하도록 수정
--  2. 관리자 계정 테이블 분리
--  3. 시스템 로그 테이블 추가하여 동작 로그 기록
--  4. 생방송 접속자 상태 체크하는 이벤트 추가

-- 기존 생방송 접속자 테이블 삭제 후 변경된 테이블로 다시 생성
DROP TABLE IF EXISTS `live_entry`;

CREATE TABLE IF NOT EXISTS `live_entry` (
	`idx` INT NOT NULL AUTO_INCREMENT,
	`session` CHAR(26) NOT NULL COMMENT '접속자 세션' COLLATE 'utf8mb4_unicode_ci',
	`branch` ENUM('ADMIN','MEMBER') NOT NULL COMMENT '접속자 타입' COLLATE 'utf8mb4_unicode_ci',
	`church` VARCHAR(10) NULL DEFAULT NULL COMMENT '접속 시 선택한 교회' COLLATE 'utf8mb4_unicode_ci',
	`name` VARCHAR(10) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`date_update` DATETIME NULL DEFAULT NULL COMMENT '실시간 접속 시간',
	`date_insert` DATETIME NOT NULL COMMENT '최초 접속 시간',
	PRIMARY KEY (`idx`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=103
;

CREATE TABLE IF NOT EXISTS `member_admin` (
	`idx` INT NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(10) NOT NULL COMMENT '관리자 이름' COLLATE 'utf8mb4_unicode_ci',
	`password` VARCHAR(64) NOT NULL COMMENT '관리자 패스워드' COLLATE 'utf8mb4_unicode_ci',
	`level` INT NOT NULL COMMENT '관리자 등급',
	`church` ENUM('sky','beer') NOT NULL DEFAULT 'sky' COMMENT '관리자 소속 교회' COLLATE 'utf8mb4_unicode_ci',
	`live_manage` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT '생방송 보기 페이지 접속 권한' COLLATE 'utf8mb4_unicode_ci',
	`live_list` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT '생방송 관리 리스트 페이지 접속 권한' COLLATE 'utf8mb4_unicode_ci',
	`history` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '접속 이력 페이지 접속 권한' COLLATE 'utf8mb4_unicode_ci',
	`black_list` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '블랙리스트 관리 페이지 접속 권한' COLLATE 'utf8mb4_unicode_ci',
	`video_list` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '다시보기 관리 페이지 접속 권한' COLLATE 'utf8mb4_unicode_ci',
	`event_video` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '행사 영상 관리 페이지 접속 권한' COLLATE 'utf8mb4_unicode_ci',
	`member_admin` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '관리자 관리 페이지 접속 권한' COLLATE 'utf8mb4_unicode_ci',
	`member_saint` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '성도 관리 페이지 접속 권한' COLLATE 'utf8mb4_unicode_ci',
	`insert_date` DATETIME NOT NULL COMMENT '관리자 생성일시',
	`update_date` DATETIME NULL DEFAULT NULL COMMENT '관리자 정보 업데이트 일시',
	PRIMARY KEY (`idx`) USING BTREE
)
COMMENT='관리자 계정 정보'
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=10
;

CREATE TABLE IF NOT EXISTS `member_saint` (
	`idx` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(10) NOT NULL COMMENT '성도 이름' COLLATE 'utf8mb4_unicode_ci',
	`password` VARCHAR(64) NOT NULL COMMENT '로그인 패스워드' COLLATE 'utf8mb4_unicode_ci',
	`insert_date` DATETIME NOT NULL COMMENT '등록 일시',
	`update_date` DATETIME NULL DEFAULT NULL COMMENT '정보 수정 일시',
	PRIMARY KEY (`idx`) USING BTREE
)
COMMENT='성도 관리'
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;

CREATE TABLE IF NOT EXISTS `system_log` (
	`idx` INT NOT NULL AUTO_INCREMENT,
	`log_lev` VARCHAR(10) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	`message` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`insert_date` DATETIME NOT NULL,
	PRIMARY KEY (`idx`) USING BTREE
)
COMMENT='사이트에 발생하는 특정 로그들을 저장하는 테이블'
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=151
;

DELIMITER //
CREATE DEFINER=`sky0808net`@`%` EVENT `EV_del_live_entry`
	ON SCHEDULE
		EVERY 3 SECOND STARTS '2024-09-18 21:50:12'
	ON COMPLETION PRESERVE
	ENABLE
	COMMENT '실시간으로 생방송 접속자의 상태를 확인하여 미접속 시 데이터 삭제'
	DO BEGIN
	DELETE FROM live_entry
	WHERE idx IN (
		SELECT idx FROM (SELECT idx FROM live_entry WHERE date_update < DATE_SUB(NOW(), INTERVAL 3 SECOND) AND `branch` <> 'ADMIN') t
	);
	
	# 해당 사용자의 로그아웃 시간 기록
#	UPDATE live_history SET logout_date = NOW() WHERE `name` IN (
#		SELECT `name` FROM (SELECT `name` FROM live_entry WHERE date_update < DATE_SUB(NOW(), INTERVAL 3 SECOND) AND `branch` <> 'ADMIN') t
#	);
END//

DELIMITER ;