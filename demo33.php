<?php
date_default_timezone_set('PRC'); // 中国时区
ignore_user_abort();//关闭浏览器后，继续执行php代码
set_time_limit(0);//程序执行时间无限制
$sleep_time = 5;//多长时间执行一次
$switch = include 'switch.php';
$num = -1;
// 封装日志记录函数 $data sql返回参 $text 记录文本
function white_log($data,$text){
    if($data[code]==200){
        file_put_contents("log.log",$text."\n",FILE_APPEND);//记录日志
    }
}
// 封装sql函数 $sql sql语句 $kg 需不需要在log中写入
function sql_fun($sql){
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
    $res=mysql_query($sql,$conn);
    #5、接收返回的结果，并处理
    if(!$res)
    {
        die("数据库操作失败！".mysql_error());
        return array("code"=>201);
    }
    if(mysql_affected_rows($conn)>0)
    {
        //echo "数据库操作成功";//mysq_affected_rows成功受影响行数
//        file_put_contents("log.log","添加库成功"."\n",FILE_APPEND);//记录日志
    }else
    {
        //echo "0行受影响！";
    };
    #6、释放资源，关闭连接
    mysql_close();
    if($res!=1){
        while($row=mysql_fetch_assoc($res)){
            $arr[] = $row;
        };
        return array("data"=>$arr,"code"=>200);
    }else{
        return array("code"=>200);
    }
}
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

// 查询要爬取的文章id
$sql="select article_id from boke_article order by id asc";
$arr = sql_fun($sql);
$arr_len = count($arr[data]);
while($switch){
    $curl = curl_init();
    $num++;
    $switch = include 'switch.php';
    file_put_contents("log.log",$num."\n",FILE_APPEND);//记录日志
    if($num >= $arr_len){
//    if($num > 0){
        file_put_contents("log.log","爬取结束"."\n",FILE_APPEND);//记录日志
        die();
    }else{
//        paqu(6385);
        paqu($arr['data'][$num]['article_id']);
    }
//    echo '<pre>';
//    print_r($arr);
//    echo  '</pre>';
    sleep($sleep_time);//等待时间，进行下一次操作。
}
//$title_db = addslashes(strip_tags($a[0]));
//$content_db = addslashes(strip_tags($content[0]));
//$fabulous_db = addslashes($fabulous[0][0]);
//$reading_db = addslashes($readings[0][0]);
//$time_db = addslashes(strip_tags($time[0]));
//$detailurl_db = addslashes($a[1]);
//$imgurl = 'http://qxu1142100139.my3w.com/api/public/uploads/'.$num.'jpg';
//    echo gettype($aa);
//    die();
function paqu($b){
    $url='http://www.daqianduan.com/'.$b.'.html';
    file_put_contents("log.log",$url."\n",FILE_APPEND);//记录日志
    paqus($url,$b);
//    $msg=date("Y-m-d H:i:s",time());
}

function paqus($urlss,$articleId){
//    **************************
    $data = get($urlss);
// 正则区
    $list_preg = '/<article class="article-content">.+<\/article>/Us';
    $title_preg = '/<h1 class="article-title">.+<\/h1>/U';
    $div_preg = '/<span class="item">.+<\/span>/U';
    $read_preg = '/<span class="item post-views">.+<\/span>/U';
//    $title_preg = "%<a>(.*?)</a>%si";
    $p_preg = '/<ol class="commentlist">(.*?)<\/ol>/ies';
    $comments_preg = '/<p>.+<\/p>/U';
    $img_preg = "/<\s*img\s+[^>]*?src\s*=\s*('|\")(.*?)\\1[^>]*?\/?\s*>/i";
//    $reading_preg = '/<span class="pv">.+<\/p>/U';
// 匹配img标签上的src和alt
//$img_preg = '';
//$img_preg = '/<a href="(.*)" class="a-img" target="_blank">.+<\/a>/Us';
//echo $img_preg;
//匹配电影的url
//$video_preg='/<a href="(.*)" title="(.*)" target="(.*)"><\/a>/U';
//把所有的li存到$list里，$list是个二维数组
    preg_match_all($list_preg, $data, $list);
    // 图片
    preg_match_all($img_preg, $list[0][0], $imgsrc);
    preg_match_all($title_preg, $data, $title);
    preg_match_all($div_preg, $data, $divs);
    preg_match_all($read_preg, $data, $reads);
    preg_match_all($p_preg, $data, $p);
    // 评论
    foreach ($p[0] as $k => $v) {   //这里$v就是每一个li标签
//        echo $v;
        preg_match_all($comments_preg, $v, $comments);
        // 图片
//        echo date('Y-m-d',$time);
//        die();
//       $comments = strip_tags($comments);
        foreach ($comments[0] as $ks => $vs) {
            $time = rand(1524630741, 1532493217); //随机生成一个时间
            $user_id = rand(15, 25);// 随机产生用户id 随机数
            $comments[0][$ks] = array('content' => strip_tags($vs), 'time' => date('Y-m-d', $time));
//            echo $articleId.'111';
//            die;
            $sql = "insert into boke_article_comments values ('','" . $articleId . "','" . strip_tags($vs) . "','" . $time . "','" . $user_id . "')";
            $msg = sql_fun($sql);
            if($msg[code]==200){
                file_put_contents("log.log","添加库成功"."\n",FILE_APPEND);//记录日志
            }
        }
        echo "<pre>";
//        print_r($comments);
        echo "</pre>";
    }

    $reg = '/\d+/';//匹配数字的正则表达式
    $bb = preg_match_all($reg, $reads[0][0], $readings);
    $cc = preg_match_all($reg, $divs[0][2], $comment);
    $times = strip_tags($divs[0][0]);
    $data_db = [
        'article_id' => $articleId,
        'title' => strip_tags($title[0][0]),
        'content' => $list[0][0],
        'time' => $times,
        'reading' => $readings[0][0],
        'comment' => $comment[0][0],
//            'comment_list' => strip_tags($time[0]),
    ];

    // 下载
    $list_str = "";
    foreach ($imgsrc[2] as $k => $v){
        $imgData = get($imgsrc[2][$k]);
        // 生成随机数
        $img_arr = explode("/",$imgsrc[2][$k]);
        $img_arr_len = count($img_arr);
//        echo $img_arr[$img_arr_len-1];
        $img_str = '';
        for($i=0;$i<$img_arr_len-1;$i++){
            if($i==$img_arr_len-1){
                $img_str .= $img_arr[$i];
            }else{
                $img_str .= $img_arr[$i].'/';
            }
        }
        $list_str = str_replace($img_str,'http://www.520yueyue.top/public/img/' ,$list[0][0]);
//        echo $list_str;
        echo "<pre>";
//        print_r($img_arr);
        echo "</pre />";
        echo "<br />";
//        echo $img_str;
        echo "<br />";
        // 把图片文件写到硬盘上【下载】
        // 因为操作系统是GBK的，所以要把UTF8转成GBK
        is_dir('./youkuimg/') ? '': mkdir('./youkuimg/');
        file_put_contents('./youkuimg/'.mb_convert_encoding($img_arr[$img_arr_len-1], 'gbk', 'utf-8'), $imgData);
//        die;
    }
//var_dump($list);
    echo "<pre>";
//    print_r($img_arr);
//    print_r($imgsrc[2]);
    echo "</pre>";

    $article_id_db = addslashes(strip_tags($articleId));
    $title_db = addslashes(strip_tags(strip_tags($title[0][0])));
    if($list_str==""){
        $content_db = addslashes($list[0][0]);
    }else{
        $content_db = addslashes($list_str);
    }
    $time_db = addslashes($times);
    $reading_db = addslashes($readings[0][0]);
    $comment_db = addslashes($comment[0][0]);
    $sql = "insert into boke_article_details_s values ('','" . $article_id_db . "','" . $title_db . "','" . $time_db . "','" . $content_db . "','" . $reading_db . "','" . $comment_db . "')";
//    echo $sql;
    $result = sql_fun($sql);
    white_log($result,"详情写入成功");
    print_r("<pre>");
//    print_r($divs);
//    print_r(sql_fun($sql));
//    print_r($data_db);
    print_r("</pre>");
}
?>