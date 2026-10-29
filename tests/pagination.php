<?php
require_once __DIR__ . '/../app/helpers/pagination_helper.php';
function check_links($query, $total, $last, $expected_count) {
    $_GET = $query;
    $html = render_pagination(1, $last, 1, min(3, max(1, $last)), $total);
    preg_match_all('/href="([^"]*)"/', $html, $matches);
    if (count($matches[1]) !== $expected_count) throw new RuntimeException('Unexpected link count');
    foreach ($matches[1] as $href) {
        $href = html_entity_decode($href, ENT_QUOTES, 'UTF-8');
        parse_str(parse_url($href, PHP_URL_QUERY), $params);
        $expected = $query;
        unset($expected['url'], $expected['page']);
        $actual = $params;
        unset($actual['page']);
        if ($expected !== $actual) throw new RuntimeException('Search conditions lost');
        if ((int) $params['page'] < 1 || (int) $params['page'] > max(1, $last)) throw new RuntimeException('Invalid page');
    }
}
check_links(['url' => 'admin/video_list', 'church' => 'sky', 'preacher' => '시험설교자 & "이름"', 'page' => '1'], 30, 3, 7);
check_links(['url' => 'admin/live_list', 'church' => 'beer'], 20, 2, 6);
check_links([], 0, 0, 5);
check_links(['church' => '', 'preacher' => '시험가'], 10, 1, 5);
echo "PASS: pagination preserves filters, escapes links, omits internal route and bounds page numbers.\n";
function pagination_target($page,$last,$start,$end,$label){
 $_GET=['church'=>'sky','q'=>'시험','per_page'=>50];
 $html=render_pagination($page,$last,$start,$end,1000);
 if(!preg_match('/href="([^"]*)" aria-label="'.$label.'"/',$html,$match))throw new RuntimeException('Missing pagination control');
 parse_str(parse_url(html_entity_decode($match[1],ENT_QUOTES,'UTF-8'),PHP_URL_QUERY),$params);
 return (int)$params['page'];
}
foreach([[1,15,1,5,1,6],[5,15,1,5,1,6],[6,15,6,10,5,11],[9,15,6,10,5,11],[11,13,11,13,10,13]]as $case){
 list($page,$last,$start,$end,$previous,$next)=$case;
 if(pagination_target($page,$last,$start,$end,'Previous')!==$previous||pagination_target($page,$last,$start,$end,'Next')!==$next)throw new RuntimeException('Block navigation regression');
}
echo 'PASS: Previous/Next move across five-page blocks.',PHP_EOL;
