<?php
$key = "000000";
$API = "http://zongqing.liu.in/Alert/index.php/API/ImportAlert";
 
$data['key'] = $key;
$data['checktime'] = time();
$data['category'] = 'nagios';
$data['service'] = 'check_host_alive2';
$data['level'] = 3;
$data['message'] = 'timeout';
$data['info']['project'] = 'farm';
$data['info']['release'] = 'tw';
$data['info']['type'] = 'mysql';
$data['address'] = '54.12.45.78';
 
$result = PostData($API,$data);
echo $result;
 
function PostData($url,$data){
    $content = http_build_query($data);
    $opts = array( 
        'http'  => array(
            'method'    =>   'POST',
            'header'    =>   'Content-type: application/x-www-form-urlencoded',
            'content'   =>   $content,
                 )
    );
    // Set the header for post data
    $contents = stream_context_create($opts);
    // Post the data to the API
    $result= file_get_contents($url,false,$contents);
    return $result;
}
?>