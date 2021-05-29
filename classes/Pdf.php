<?php
namespace classes;

include dirname(__DIR__) . '/vendor/autoload.php';

use DateTime;
/* PdfParser */
use Smalot\PdfParser\Parser;

class Pdf
{
	// private $localePdfFile = null;

	// public function __construct($file)
	// {
	// 	if (file_exists($file)) $this->localePdfFile = $file;
	// }

	public static function downloadAndSave($path, $name, $saveFolder)
	{
		$now = new DateTime();
		$filename = $name ? $name : "file-{$now->getTimestamp()}";

		$savePath = $saveFolder . $filename . '.pdf';

		// Crete new file
		$fp = fopen($savePath, 'w+');
		// Initialize the cURL session
		$ch = curl_init();
		// Set the URL of the page or file to download
		curl_setopt($ch, CURLOPT_URL, $path);
		// To write the contents to a file
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// Execute the cURL session
		$is = curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		if ($is)
		{
			if (file_exists($savePath)) return $savePath;
		}
		return null;
	}

	public static function remove($pathLocal)
	{
		if (file_exists($pathLocal)) unlink($pathLocal);
	}

	public static function getContent($path)
	{
		/* PdfParser */
		$parser = new Parser;
		$document = $parser->parseFile($path);
		// $pages = $document->getPages();
		$result = $document->getText();

		return $result;
	}

}
?>