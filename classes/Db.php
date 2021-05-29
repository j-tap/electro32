<?php
namespace classes;

use PDO;

class Db
{
	private $pdo;

	public function __construct()
	{
		$this->pdo = new PDO('mysql:host=localhost;dbname=ck51028_electr32', 'ck51028_electr32', '6bJw3JjY');
	}

	public function insertUser($telegram_id, $telegram_name)
	{
		$sql = $this->pdo->prepare("INSERT INTO `users` (`telegram_id`, `telegram_name`) VALUES (:telegram_id, :telegram_name)");
		$sql->execute([
			':telegram_id' => $telegram_id,
			':telegram_name' => $telegram_name,
		]);
		return true;
	}

	public function getUserByTgId($telegram_id)
	{
		$sql = $this->pdo->prepare("SELECT * FROM `users` WHERE `telegram_id` = ?");
		$sql->execute([$telegram_id]);
		return $sql->fetch(PDO::FETCH_LAZY);
	}

	public function insertSubscribe($user_id, $address)
	{
		$sql = $this->pdo->prepare("INSERT INTO `user_subscribes` (`user_id`, `address`) VALUES (:user_id, :address)");
		$sql->execute([
			':user_id' => $user_id,
			':address' => $address,
		]);
		return true;
	}
}

?>