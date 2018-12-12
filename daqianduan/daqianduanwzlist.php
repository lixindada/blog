<?php
date_default_timezone_set('PRC'); // 中国时区
ignore_user_abort();//关闭浏览器后，继续执行php代码
set_time_limit(0);//程序执行时间无限制
$sleep_time = 5;//多长时间执行一次
$switch = include 'switch.php';
$num = 1;

function get($url)
{
    global $curl;
    // 配置curl中的http协议->可配置的荐可以查PHP手册中的curl_
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    // 执行这个请求
    return curl_exec($curl);
}

while($switch){
    $curl = curl_init();
    $num++;
    $switch = include 'switch.php';
    paqu($num);
    if($num > 59){
        file_put_contents("log.log","爬取结束"."\n",FILE_APPEND);//记录日志
        die();
    }
    sleep($sleep_time);//等待时间，进行下一次操作。
}




$a=2;
function paqu($b){
    $url='http://www.daqianduan.com/page/'.$b;
    echo $url;
    paqus($url);
//    $msg=date("Y-m-d H:i:s",time());
    file_put_contents("log.log",$url."\n",FILE_APPEND);//记录日志
}


//$curl = curl_init();
//paqus("http://www.daqianduan.com/page/2");


function paqus($urlss){
//    **************************
    $data=get($urlss);
// 正则区
    $list_preg = '/<article class="(.*)">.+<\/article>/Us';
    $h2_preg = '/<h2>.+<\/h2>/U';
    $a_preg = '/<a href="(.*)" title="(.*)">.+<\/a>/U';
    $title_preg = "%<a>(.*?)</a>%si";
    $p_preg = '/<p class="note">.+<\/p>/U';
    $time_preg = '/<time>.+<\/time>/U';
    $reading_preg = '/<span class="pv">.+<\/p>/U';
// 匹配img标签上的src和alt
//$img_preg = '';
//$img_preg = '/<a href="(.*)" class="a-img" target="_blank">.+<\/a>/Us';
    $img_preg = "/<\s*img\s+[^>]*?src\s*=\s*('|\")(.*?)\\1[^>]*?\/?\s*>/i";
//echo $img_preg;
//匹配电影的url
//$video_preg='/<a href="(.*)" title="(.*)" target="(.*)"><\/a>/U';
//把所有的li存到$list里，$list是个二维数组
    preg_match_all($list_preg,$data,$list);
//var_dump($list);
    foreach ($list[0] as $k => $v) {   //这里$v就是每一个li标签
//    echo $v;
        // 生成随机数
        $num = time().rand(1000000,9999999).rand(1000000,9999999);
        // 图片
        preg_match($img_preg,$v,$img);
        preg_match($h2_preg,$v,$h2);
        preg_match($a_preg,$h2[0],$a);
        preg_match($p_preg,$v,$content);
        preg_match($time_preg,$v,$time);
        preg_match($reading_preg,$v,$reading);
        $readstr = strip_tags($reading[0]);
        $readnum = preg_replace('/([\x80-\xff]*)/i','',$readstr);
        $readArr = explode(")(",$readnum);
        $reg='/\d+/';//匹配数字的正则表达式
        $bb = preg_match_all($reg,$readArr[0],$readings);
        $cc = preg_match_all($reg,$readArr[1],$fabulous);
        $dd = preg_match_all($reg,$a[1],$article_id);
        echo $article_id;
//    echo $readings[1];
//    print_r($readings);
        $data_db = [
            'userid'=> 1,
            'article_id' => $article_id[0][0],
            'title' => strip_tags($a[0]),
            'content' => strip_tags($content[0]),
            'fabulous' => $fabulous[0][0],
            'reading' => $readings[0][0],
            'time' => strip_tags($time[0]),
            'detailurl' => $a[1],
            'imgurl' => 'http://qxu1142100139.my3w.com/api/public/uploads/'.$num.'jpg'
        ];
//    echo $a[0];
        print_r("<pre>");
        print_r($data_db);
        print_r("</pre>");
//    die;
//    echo $img[2];
        // 下载
        $imgData = get($img[2]);
        // 把图片文件写到硬盘上【下载】
        // 因为操作系统是GBK的，所以要把UTF8转成GBK
        is_dir('./img/') ? '': mkdir('./img/');
        file_put_contents('./img/'.mb_convert_encoding($num, 'gbk', 'utf-8').'.jpg', $imgData);

        //// 数据库
#1、获取连接
        $conn=mysql_connect("qdm116283588.my3w.com","qdm116283588","lx123456");
        if(!$conn)
        {
            die("数据库连接失败！".mysql_error());
        }
#2、选择数据库
        mysql_select_db("qdm116283588_db");
#3、设置操作编码
        mysql_query("set names utf8");
#4、发送指令（ddl数据定义/dml数据操作/dql数据查询/dtl数据事务控制）
        $article_id_db = addslashes($article_id[0][0]);
        $title_db = addslashes(strip_tags($a[0]));
        $content_db = addslashes(strip_tags($content[0]));
        $fabulous_db = addslashes($fabulous[0][0]);
        $reading_db = addslashes($readings[0][0]);
        $time_db = addslashes(strip_tags($time[0]));
        $detailurl_db = addslashes($a[1]);
        $imgurl = 'http://qxu1142100139.my3w.com/api/public/uploads/'.$num.'jpg';
//    echo gettype($aa);
//    die();
        if($time_db!=''){
            $sql="insert into boke_article values ('','".$article_id_db."',1,'".$title_db."','".$content_db."','".$time_db."','".$fabulous_db."','".$reading_db."','".$detailurl_db."','".$imgurl."')";
            echo $sql;
            $res=mysql_query($sql,$conn);

#5、接收返回的结果，并处理
            if(!$res)
            {
                die("插入操作失败！".mysql_error());
            }
            if(mysql_affected_rows($conn)>0)
            {
                echo "操作成功";//mysq_affected_rows成功受影响行数
                file_put_contents("log.log","添加库成功"."\n",FILE_APPEND);//记录日志
            }else
            {
                echo "0行受影响！";
            }
        }
#6、释放资源，关闭连接
        mysql_close();
    }

//    **************************
}
exit();

?>