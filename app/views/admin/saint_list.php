<?
// 기본 설정 파일을 불러온다
include_once $_SERVER['DOCUMENT_ROOT'].'/live/inc/config.inc';
include_once $_SERVER['DOCUMENT_ROOT'].'/live/inc/top.inc';

// 게시물 번호를 위한 DB 조회
$num = $DB->query( "SELECT * FROM `member_saint`" )->rowCount() ;
$subt = 0 ; // 게시물 번호 계산을 위한 변수 초기화

// 페이지네이션
$page_set = 6 ; // 한 페이지에 출력할 게시물 수
$block_set = 5 ; // 블럭당 페이지 수

$page = isset ( $_GET['page'] ) ? $_GET['page'] : 1 ; // 현재 페이지
$block = ceil ( $page / $block_set ) ; // 현재 블럭(올림 함수)

$block_start = ( ( $block - 1 ) * $block_set ) + 1 ; // 블록의 시작번호
$block_end = $block_start + $block_set - 1 ; // 블록 마지막 번호

$total = $num ; // 총 게시글 수
$total_page = ceil ( $total / $page_set ) ; // 총 페이지 수(올림 함수)
$total_block = ceil ( $total_page / $block_set ) ; // 총 블럭 수(올림 함수)

if ( $block_end > $total_page ) $block_end = $total_page ; // 블록의 마지막 번호가 총 페이지 수보다 크면 총 페이지 수를 마지막 번호로 처리
 
$start_num = ( $page - 1 ) * $page_set ; // limit 시작 위치

$listSql = $DB->query( "SELECT * FROM `member_saint` ORDER BY idx ASC LIMIT " . $start_num . ', ' . $page_set ) ;
?>
<main class="live-main">
    <div class="main-row">
        <div class="nav-left">
            <? include_once $_SERVER['DOCUMENT_ROOT'].'/live/inc/admin_nav.inc' ?>
        </div>
        <div class="nav-right">
            <h4 class="main-title">성도 관리</h4>
            <div class="admin-row">
                <table class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th width="8%">번호</th>
                            <th width="8%">이름</th>
                            <th width="8%">레벨</th>
                            <th width="9%">교회</th>
                            <th width="16%">수정일자</th>
                            <th width="16%">생성일자</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                    <? if ( $listSql->rowCount() <= 0 ) : ?>
                        <tr>
                        <td class="text-center" colspan="7">데이터가 없습니다.</td>
                        </tr>
                    <? else : ?>
                        <? foreach ( $listSql as $row ) : ?>
                        <?
                            // 교회 이름 변환
                            switch($row['church']){
                                case 'sky':
                                    $church = '하늘문';
                                break;
                                
                                case 'beer':
                                    $church = '브엘성회';
                                break;
                            }
                        ?>
                        <tr>
                            <td><?=$num - ( $page_set * ( $page - 1 ) ) - $subt?></td>
                            <td><a href="<?=SITE_LIVE?>/admin_read.html?idx=<?=$row['idx']?>" style="text-decoration: none; color: black;"><?=$row['username']?></a></td>
                            <td><?=$row['level']?></td>
                            <td><?=$church?></td>
                            <td><?=! empty ( $row['update_date'] ) ? $row['update_date'] : '-'?></td>
                            <td><?=$row['insert_date']?></td>
                        </tr>
                        <? if ( $subt < $page_set ) $subt++ ; ?>
                        <? endforeach ; ?>
                    <? endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-grid d-flex justify-content-end">
                <a type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#liveaddModal">등록</a>
            </div>
            <!-- <div class="d-flex justify-content-center mb-5">
                <div class="me-3"><input type="search" class="form-control" name="link" id="link" placeholder="검색"></div>
                <div><button type="button" class="btn btn-success" id="search_btn">검색</button></div>
            </div> -->

            <?php
            include_once __DIR__ . '/../../helpers/pagination_helper.php';
            echo render_pagination($page, $total_page, $block_start, $block_end, $total);
            ?>
        </div>
    </div>
</main>

<!-- 링크 등록 모달 -->
<div class="modal fade" id="liveaddModal" tabindex="-1" aria-labelledby="liveaddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="liveaddModalLabel">생방송 링크 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="./save_live_list.php">
                    <div class="container">
                        <div class="mb-3 row">
                            <label for="title" class="col-sm-2 col-form-label">교회</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="church" id="church">
                                    <option value="">-</option>
                                    <option value="sky">하늘문</option>
                                    <option value="beer">브엘성회</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="title" class="col-sm-2 col-form-label">제목</label>
                            <div class="col-sm-3">
                                <select class="form-select" name="worship" id="worship">
                                    <option value="">예배시간</option>
                                    <option value="낮예배">낮예배</option>
                                    <option value="밤예배">밤예배</option>
                                    <option value="11시기도">11시기도</option>
                                    <option value="특별예배">특별예배</option>
                                    <option value="etc">직접입력</option>
                                </select>
                            </div>
                            <div class="col-sm-7">
                                <input type="date" class="form-control" id="title" name="title">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="link" class="col-sm-2 col-form-label">링크</label>
                            <div class="col-sm-10">
                            <input type="text" class="form-control" name="link" id="link" placeholder="유튜브 링크">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="password" class="col-sm-2 col-form-label">비밀번호</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="password" id="password" value="<?=! empty ( $passRow ) ? $passRow['passwd'] : '019108'?>">
                            </div>
                        </div>
                    </div>
                    <div class="gap-2 d-flex justify-content-end">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">취소</button>
                        <button class="btn btn-primary" type="submit">저장</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // 오늘 날짜 자동 입력
    $('#title').val(new Date().toISOString().substring(0, 10));

    // 교회 변경
    $('#church_select').change(function(){
        location.href = 'http://live.sky0808.net/dev/admin/live_list.html?' + 'church=' + $(this).val();
    });

    // use
    $('.use').click(function(){
        var use = $(this).text();
        var useIdx = $(this).attr('id');

        $.ajax({
            type: 'GET',
            url: './update_use.php',
            data: {idx: useIdx},
            success: function(data) {
                location.reload();
            }
        });

        // 버튼 변경
        if ( use == 'Y' ) {
            $(this).removeClass('btn-success').addClass('btn-danger').text('N');
        } else {
            $(this).removeClass('btn-danger').addClass('btn-success').text('Y');
        }
    });

    // view
    $('.view').click(function(){
        var view = $(this).text();
        var viewIdx = $(this).attr('id');

        $.ajax({
            type: 'GET',
            url: './update_view.php',
            data: {idx: viewIdx, view: view},
            success: function(data) {
                location.reload();
            }
        });

        // 버튼 변경
        if ( view == 'Y' ) {
            $(this).removeClass('btn-success').addClass('btn-danger').text('N');
        } else {
            $(this).removeClass('btn-danger').addClass('btn-success').text('Y');
        }
    });
</script>