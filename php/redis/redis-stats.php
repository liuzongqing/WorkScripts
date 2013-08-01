<?php
$fp = fsockopen('127.0.0.1', 6379, $errno, $errstr, 30);
 
$data = array();
 
if (!$fp) {
    die($errstr);
} else {
    fwrite($fp, "INFO\r\nQUIT\r\n");
    while (!feof($fp)) {
        $info = split(':', trim(fgets($fp)));
        if (isset($info[1])) $data[$info[0]] = $info[1];
    }
    fclose($fp);
}
?>
 
<html>
<head>
 <title>Redis statistics</title>
 <style type="text/css">
  body {
    font-family: arial,sans-serif;
    color: #111;
  }
 
  div.box {
    background-color: #DFA462;
    width: 200px;
    height: 200px;
    text-align: center;
    margin: 6px;
    float: left;
  }
 
  div.key {
    font-weight: bold;
    font-size: 42px;
  }
 
  div.detail {
    text-align: left;
  }
 
  div.detail span {
    width: 90px;
    padding: 2px;
    display: inline-block;
  }
 
  div.detail span.title {
    text-align: right;
  }
 </style>
</head>
<body>
 
<div class='box'>
    <div>Percentage hits</div>
    <div class='key'><?php echo round($data['keyspace_hits'] / ($data['keyspace_hits'] + $data['keyspace_misses']) * 100)."%";?></div>
    <div class='detail'>
        <span class='title'>Hits</span>
        <span><?php echo $data['keyspace_hits']; ?></span>
    </div>
    <div class='detail'>
        <span class='title'>Misses</span>
        <span><?php echo $data['keyspace_misses']; ?></span>
    </div>
</div>
 
<div class='box'>
    <div>Memory usage</div>
    <div class='key'><?php echo $data['used_memory_human'];?></div>
</div>
 
<div class='box'>
    <div>Peak memory usage</div>
    <div class='key'><?php echo $data['used_memory_peak_human'];?></div>
</div>
 
<div class='box'>
    <div>Keys in store</div>
    <div class='key'>
        <?php
            $values = split(',', $data['db0']);
            foreach($values as $value) {
                $kv = split('=', $value);
                $keyData[$kv[0]] = $kv[1];
            }
            echo $keyData['keys'];
        ?>
    </div>
</div>
</body>
</html>
