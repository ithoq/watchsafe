<?php
class Model_Curl {

    public static function Delquery($url,$header='') {
        $ch = curl_init();
        $header_arr = array("Content-Type: application/json; charset=utf-8","Accept:application/json, text/javascript, */*; q=0.01",$header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_ENCODING, 'utf-8');
        curl_setopt($ch, CURLOPT_AUTOREFERER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT,999);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'DELETE');        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER,  $header_arr);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function GETquery($url, $h=null) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_HEADER, false);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
          
          $data = curl_exec($ch);
          $header  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
          if ($h)
          {
            return $header;
          }
          else
          {
          return $data;
          }
    }

    public static function POSTquery($url, $postdata, $header='') {
          $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";
          $header_arr = array("Content-Type: application/json; charset=utf-8","Accept:application/json, text/javascript, */*; q=0.01",$header);
          //var_dump($header_arr);
          //exit();
          $ch = curl_init( $url );
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
          curl_setopt($ch, CURLOPT_VERBOSE, 0);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($ch, CURLOPT_ENCODING, "");
          curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
          curl_setopt($ch, CURLOPT_TIMEOUT, 120);
          curl_setopt($ch, CURLOPT_FAILONERROR, 1);
          curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_HTTPHEADER,  $header_arr);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
          
          $content = curl_exec( $ch );
          $err     = curl_errno( $ch );
          $errmsg  = curl_error( $ch );
          $header  = curl_getinfo( $ch );     
    
          $header['errno']   = $err;
          $header['errmsg']  = $errmsg;
          $header['content'] = $content;
          return $header;
    }

} // End Curl Model
?>