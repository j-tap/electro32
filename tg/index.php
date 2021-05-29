<?php
/*
* Для привязке бота к домену
* https://api.telegram.org/botAPI_TOKEN_TG/setWebhook?url=https://YOUR_DOMAIN/webhook.php
*
* https://api.telegram.org/bot1801219129:AAF1cfaBeEbzRpcpoq4WASUO34Aj39lpdho/setWebhook?url=https://snow-mesa.com/api/electro32/tg/index.php
*/

require dirname(__DIR__) . '/vendor/autoload.php';

// require_once 'tg.class.php';
require_once 'TgBot.php';

// use telegram\TG;
use snowMesa\electro32\tg\TgBot;

$bot = new TgBot('1801219129:AAF1cfaBeEbzRpcpoq4WASUO34Aj39lpdho');
$bot->run();

?>