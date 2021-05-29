<?php
require_once 'classes/Api.php';

use classes\Api;

$api = new Api;

$result = $api->get();

echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>