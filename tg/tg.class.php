<?php
namespace telegram;

class TG {

	public $token = '';

	public function __construct($token)
	{
		$this->token = $token; 
	}

	/*
	* Отправка сообщения
	* Получаем в параметрах ID диалога, сообщение и инлайн клавиатуру, если она нужна
	*/
	public function send($id, $message, $kb)
	{
		$data = array(
			'chat_id' => $id,
			'text' => $message,
			'parse_mode' => 'HTML',
			'disable_web_page_preview' => true,
			'reply_markup' => json_encode(array('inline_keyboard' => $kb))
		);
		$this->request('sendMessage', $data);
	}  

	/*
	* Редактирование текста сообщения
	* Редактируем с помощью нее сообщение бота в телеграме всемсте с инлайн клавиатурой, если нужно
	* Получаем в качестве параметров ID чата, ID сообщения, новый текст сообщения, инлайн клавиатуру
	*/
	public function editMessageText($id, $m_id, $m_text, $kb = '')
	{
		$data = array(
			'chat_id' => $id,
			'message_id' => $m_id,
			'parse_mode' => 'HTML',
			'text' => $m_text
		);
		if ($kb) $data['reply_markup']=json_encode(array('inline_keyboard' => $kb));

		$this->request('editMessageText', $data); 
	}

	/*
	* Редактирования разметки/кнопок
	* Получаем как параметр ID чата, ID сообщения, новую разметку/клавиатуру
	* Используем в паре с answerCallbackQuery, для ответа на запрос с заменой разметки
	*/
	public function editMessageReplyMarkup($id, $m_id, $kb)
	{
		$data = array(
			'chat_id' => $id,
			'message_id' => $m_id,
			'reply_markup' => json_encode(array('inline_keyboard' => $kb))
		);
		$this->request('editMessageReplyMarkup', $data); 
	}

	/*
	* Ответ на событие нажатия кнопки (обратного запроса)
	* Получаем в параметрах ID обратного запроса и текст ответа
	*/
	public function answerCallbackQuery($cb_id, $message)
	{
		$data = array(
			'callback_query_id' => $cb_id,
			'text' => $message
		);
		$this->request('answerCallbackQuery', $data);
	} 

	public function sendChatAction($id, $action='typing')
	{
		$data = array(
			'chat_id' => $id,
			'action' => $action
		);
		$this->request('sendChatAction', $data);
	}

	/*
	* Отправка запроса в telegram
	* Отправляем запрос вида https://api.telegram.org/botAPI_KEY/ИМЯ_МЕТОДА по протоколу post через curl
	*/
	public  function request($method, $data = array())
	{
		$urlTg = 'https://api.telegram.org/bot';
		$curl = curl_init();
		  
		curl_setopt($curl, CURLOPT_URL, $urlTg . $this->token .  '/' . $method);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST'); 
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		  
		$out = json_decode(curl_exec($curl), true);
		  
		curl_close($curl);
		return $out;
	}
}

?>