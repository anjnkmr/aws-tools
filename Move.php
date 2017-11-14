<?php
require 'vendor/autoload.php';
use Aws\DynamoDb\AwsDynamoDbMarshaler;

if(!isset($_GET['table'])) {
    die("_GET Param `table` not specified");
}

$tableName = $_GET['table'];

$sourceConfig = getConfig('source');
$destConfig = getConfig('destination');

$sdk = new Aws\Sdk($sourceConfig);
$dynamodb = $sdk->createDynamoDb();

$destSdk = new Aws\Sdk($destConfig);
$destDynamodb = $destSdk->createDynamoDb();

$marshaler = new Aws\DynamoDb\Marshaler();
$params = ['TableName' => $tableName];
try {
    $result = $dynamodb->scan($params);
    foreach ($result['Items'] as $i) {
        // $item = $marshaler->unmarshalItem($i);
        // $items[] = $item;
        $insertResult = $destDynamodb->putItem(array("TableName"=>$tableName, 'Item'=>$i));
        echo "<pre>";
        print_r($insertResult);
        echo "</pre>";
    }
}catch (DynamoDbException $e){
    print_r($e);
}

function getConfig($key) {
    $json = file_get_contents('config/aws.json');
    $config = (array)json_decode($json);
    $config = (array)$config[$key];
    $config["credentials"] = (array) $config['credentials'];
    return $config;
}
?>