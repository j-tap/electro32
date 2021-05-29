<?php
namespace classes;

use DomDocument;

class Parse
{

	public static function getDom($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$res = curl_exec($ch);

		curl_close($ch);

		$dom = new DomDocument();
		@ $dom->loadHTML(mb_convert_encoding($res, 'HTML-ENTITIES', 'UTF-8'));

		return $dom;
	}
}
?>