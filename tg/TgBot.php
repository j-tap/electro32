<?php
namespace snowMesa\electro32\tg;

require dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/classes/Api.php';
require_once dirname(__DIR__) . '/classes/Db.php';

/* https://telegram-bot-sdk.readme.io/reference#getme */
use Telegram\Bot\Api as TgApi;
use classes\Api;
use classes\Db;

class TgBot
{
	private $token = null;
	private $tg = null;
	private $db = null;

	private $text = null;
	private $chat_id = null;
	private $name = null;
	private $keyboard = Array();

	public $user;

	private $translitActions = Array(
		'подписаться' => 'subscribe',
		'поиск' => 'search',
		'отписаться' => 'unsubscribe',
	);
	private $actions = Array(
		0 => null,
		1 => 'unsubscribe',
		2 => 'subscribe',
		3 => 'search',
	);

	public function __construct(string $token)
	{
		$this->token = $token;
		$this->tg = new TgApi($token);
		$this->api = new Api();
		$this->db = new Db();

		// полная информация о сообщении пользователя
		$request = $this->tg->getWebhookUpdates();

		// Текст сообщения
		$this->text = $request['message']['text'];
		// Уникальный идентификатор пользователя
		$this->chat_id = $request['message']['chat']['id'];
		// Юзернейм пользователя
		$this->name = $request['message']['from']['username'];
	}

	public function run()
	{
		$model = Array(
			'chat_id' => $this->chat_id,
			'text' => null, // str
			'reply_markup' => Array(),
			'parse_mode' => 'HTML',
			'disable_web_page_preview' => true,
		);

		$action = $this->text;
		$reply = $this->actionHelp();

		if ($action)
		{
			$action = ltrim($action, '/');
			$transAction = mb_strtolower($action);
			if ($this->translitActions[$transAction])
			{
				$action = $this->translitActions[$transAction];
			}

			$nameAction = 'action' . ucfirst($action);

			if (method_exists(get_called_class(), $nameAction))
			{
				$reply = $this->$nameAction();
			}

			$resultModel = array_merge($model, $reply);

			// $resultModel['text'] = json_encode($resultModel, JSON_UNESCAPED_UNICODE);			

			$this->send($resultModel);
		}
		exit('error');
	}

	public function send($reply)
	{
		$model = Array(
			'text' => null, // str
			'reply_markup' => Array(),
			'parse_mode' => 'HTML',
			'disable_web_page_preview' => true,
		);

		// $this->tg->sendPhoto([ 'chat_id' => $chat_id, 'photo' => $url, 'caption' => "Описание." ]);
		// $this->tg->sendDocument([ 'chat_id' => $chat_id, 'document' => $url, 'caption' => "Описание." ]);

		$this->tg->sendMessage(array_merge(
			$model,
			$reply,
			Array('chat_id' => $this->chat_id)
		));

		exit('ok');
	}

	public function db_createNewOrGetUser()
	{
		$telegram_id = $this->chat_id;
		$telegram_name = $this->name;

		$result = $this->db->getUserByTgId($telegram_id);
		if (!$result) $this->db->insertUser($telegram_id, $telegram_name);
		$result = $this->db->getUserByTgId($telegram_id);

		return $result;
	}

	public function db_insertSubscribe()
	{
		$user_id = $this->user['id'];
		$address = null;

		$this->db->insertSubscribe($user_id, $address);
	}

	private function actionStart()
	{
		$this->user = $this->db_createNewOrGetUser();

		$keyboard = Array(
			Array('Подписаться'),
			Array('Поиск'),
			Array('Отписаться'),
		);

		$result = Array(
			'text' => "Здравствуйте!
Я могу подписать вас на рассылку о плановом отключении электроэнергии в городе Брянск

",
			'reply_markup' => $this->tg->replyKeyboardMarkup(Array(
				'keyboard' => $keyboard,
				'resize_keyboard' => true,
				'one_time_keyboard' => false,
			)),
		);
		return $result;
	}

	private function actionHelp()
	{
		$result = Array(
			'text' => "
Вы можете управлять мной, отправляя эти команды:

/help - информация и список доступных команд
/start - начать
/subscribe - подписаться на информацию по вашей улице и дому
/unsubscribe - отписаться от сообщений
/search - поиск информации по адресу

",
		);
		return $result;
	}

	private function actionSubscribe()
	{

		$result = Array(
			'text' => "
Напишите улицу и номер дома по которому хотите получать информацию о запланированных отключениях электроэнергии,
например: Ленина 25

",
		);
		return $result;
	}

	private function actionUnsubscribe()
	{
		$result = Array(
			'text' => "
Вы больше не будете получать сообщения от меня о запланированных отключениях электроэнергии

",
		);
		return $result;
	}

	private function actionSearch()
	{
		$result = Array(
			'text' => "
Напишите улицу и номер дома по которому хотите найти информацию о запланированных отключениях электроэнергии,
например: Ленина 25

",
		);
		return $result;
	}
}

// $api = new Api;

// $result = $api->get();

// echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>