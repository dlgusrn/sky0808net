CREATE TABLE IF NOT EXISTS `member_admin` (
	`idx` INT(10) NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(10) NOT NULL COMMENT '관리자 이름' COLLATE 'utf8mb4_unicode_ci',
	`password` VARCHAR(64) NOT NULL COMMENT '관리자 패스워드' COLLATE 'utf8mb4_unicode_ci',
	`level` INT(10) NOT NULL COMMENT '관리자 등급',
	`church` VARCHAR(50) NOT NULL COMMENT '관리자 소속 교회' COLLATE 'utf8mb4_unicode_ci',
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
AUTO_INCREMENT=9
;


CREATE TABLE IF NOT EXISTS `member_saint` (
	`idx` INT(10) NOT NULL AUTO_INCREMENT,
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
	`idx` INT(10) NOT NULL AUTO_INCREMENT,
	`log_lev` VARCHAR(10) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	`message` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`insert_date` DATETIME NOT NULL,
	PRIMARY KEY (`idx`) USING BTREE
)
COMMENT='사이트에 발생하는 특정 로그들을 저장하는 테이블'
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=15
;
