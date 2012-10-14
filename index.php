<?php

require_once __DIR__ . '/silex/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;

// doctrine Provider
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'db.options' => array(
    'dbname'   => 'magcup',
    'user'     => '***',
    'password' => '***',
    'host'     => '***',
    'driver'   => 'pdo_mysql',
  )
));

$mustBeLogined = function () use ($app) {

};

$app->get('/', function() use ($app)  {
    $stmt = $app['db']->fetchColumn('SELECT contents FROM page where id = ?', array(1) ,1);
    return $stmt;
});

$app->match('/save', function() use ($app)  {
    // jsonを受け取る
    $request_param = $app['request']->get('json_data');
    //var_dump($request_param);
    
    /* いらない
    $json_data = urldecode($request_param);
    var_dump($json_data);
    */
    
    //echo $request_param[0]["content"];
 
    // timestamp生成
    date_default_timezone_set('Asia/Tokyo');
    $datetime = new DateTime("now");
    $timestapm = date_format($datetime, 'Y/m/d H:i:s');
    
    try{
        $sql = 'insert into page (timestamp,contents) values (?,?)';
        $stmt = $app['db']->prepare($sql);
        $stmt->bindValue(1, $timestapm);
        $stmt->bindValue(2, $request_param[0]["content"]);
        $stmt->execute();
    } catch (Exception $e) {
        echo "例外キャッチ：", $e->getMessage(), "\n";
    }
    return "ok";
});

$app->match('/load/{id}', function($id) use ($app)  {
    $params = array($id);
    $contents = $app['db']->fetchColumn('SELECT contents FROM page where id = ?', $params,0);
    $json = array("content" => $contents);
    return $app->json($json);
});

$app->run();

?>