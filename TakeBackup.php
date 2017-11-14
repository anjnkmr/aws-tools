<?php
require 'vendor/autoload.php';
use Aws\DynamoDb\AwsDynamoDbMarshaler;

if(!isset($_GET['table'])) {
    die("_GET Param `table` not specified");
}

$tableName = $_GET['table'];

$sourceConfig = getConfig('source');

$sdk = new Aws\Sdk($sourceConfig);
$dynamodb = $sdk->createDynamoDb();

$marshaler = new Aws\DynamoDb\Marshaler();
$params = ['TableName' => $tableName];
$items = array();
try {
    $result = $dynamodb->scan($params);
    foreach ($result['Items'] as $i) {
        $item = $marshaler->unmarshalItem($i);
        $items[] = $item;
    }
}catch (DynamoDbException $e){
    print_r($e);
}
echo "<pre>";
$data = json_encode($items);
file_put_contents("path\\to\\save\\files\\".$tableName.".json", $data);

function getConfig($key) {
    $json = file_get_contents('config/aws.json');
    $config = (array)json_decode($json);
    $config = (array)$config[$key];
    $config["credentials"] = (array) $config['credentials'];
    return $config;
}
?>