<?php
class Youtube_link {
    /** Return only a validated video ID. Never persist an entire share URL. */
    public static function video_id($input) {
        if (!is_string($input)) return null;
        $input=trim($input);
        if (strlen($input)>2048) return null;
        if (preg_match('/^[A-Za-z0-9_-]{11}$/D',$input)) return $input;
        // Old edit screens prepend youtu.be even when a full URL was stored.
        for($i=0;$i<3;$i++) {
            if(!preg_match('~^https?://(?:www\.)?youtu\.be/(https?://.+)$~iD',$input,$nested)) break;
            $input=$nested[1];
        }
        if(preg_match('~^(?:www\.|m\.|music\.)?(?:youtube\.com|youtu\.be)/~i',$input)) $input='https://'.$input;
        $url=parse_url($input);
        if(!$url||!isset($url['scheme'],$url['host'])||!in_array(strtolower($url['scheme']),['http','https'],true)||isset($url['user'])||isset($url['pass']))return null;
        $host=strtolower($url['host']);$path=rawurldecode($url['path']??'');$id=null;
        if(in_array($host,['youtu.be','www.youtu.be'],true)) {
            if(preg_match('~^/([A-Za-z0-9_-]{11})/?$~D',$path,$match))$id=$match[1];
        }elseif(in_array($host,['youtube.com','www.youtube.com','m.youtube.com','music.youtube.com'],true)) {
            if($path==='/watch') {
                parse_str($url['query']??'',$query);$id=$query['v']??null;
            }elseif(preg_match('~^/(?:live|embed|shorts)/([A-Za-z0-9_-]{11})/?$~D',$path,$match))$id=$match[1];
        }elseif(in_array($host,['youtube-nocookie.com','www.youtube-nocookie.com'],true)&&preg_match('~^/embed/([A-Za-z0-9_-]{11})/?$~D',$path,$match))$id=$match[1];
        return is_string($id)&&preg_match('/^[A-Za-z0-9_-]{11}$/D',$id)?$id:null;
    }
}
