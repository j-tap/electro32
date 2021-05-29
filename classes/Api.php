<?php
namespace classes;

require_once 'Parse.php';
require_once 'Pdf.php';
require_once 'Tsv.php';

use DateTime;

use classes\Parse;
use classes\Pdf;
use classes\Tsv;

class Api
{

	protected string $urlBase;
	protected string $urlParse;
	protected string $saveTsvFolder;
	protected string $savePdfFolder;
	protected $search;
	protected $now;
	protected $db;

	public function __construct(
		string $urlBase = 'https://www.elektro-32.ru',
		string $urlParse = null,
		string $saveTsvFolder = null,
		string $savePdfFolder = null
	) {
		$this->urlBase = $urlBase;
		$this->urlParse = $urlParse ? $urlParse : "{$this->urlBase}/grafikiogr";
		$this->saveTsvFolder = $saveTsvFolder ? $saveTsvFolder : dirname(__DIR__) . '/files/tables/';
		$this->savePdfFolder = $savePdfFolder ? $savePdfFolder : dirname(__DIR__) . '/files/pdfTmp/';

		$this->search = null;
		$this->now = new DateTime();
	}

	public function get($search = null)
	{
		$search = $this->search;

		$data = $this->getAllLocaleTsv();
		if (count($data) === 0) $data = $this->getAllParsePdf();

		return $data;
	}

	private function getAllParsePdf()
	{
		// Получение DOM
		$dom = Parse::getDom($this->urlParse);

		$result = array();
		$patternDate = "/\d{2}\.\d{2}\.\d{4}/";

		$newslist = $dom->getElementById('newslist');
		$linkList = $newslist->getElementsByTagName('a');

		// Перебор всех ссылок в блоке
		foreach($linkList as $link)
		{
			$urlPdf = $this->urlBase . $link->getAttribute('href');
			$nameStr = $link->nodeValue;

			// Только с датами в названиях
			if (preg_match_all($patternDate, $nameStr, $matches))
			{
				$item = $this->getItem($matches[0], $urlPdf, null);

				if (!$item) continue;

				array_push($result, $item);
			}
		}
		return $result;
	}

	private function getItem($dates, $urlPdf, $content)
	{
		$dateFrom = $dates[0];
		$dateTo = $dates[1];

		// Только те, у которых дата ДО ещё не прошла
		if (
			// for test: strtotime('-30 day', $this->now->getTimestamp())
			$this->now->getTimestamp() > strtotime('+1 day', strtotime($dateTo))
		) return null;
		// if ($_GET['s'])
		// {
		// 	$data = $_GET['s'];
		// }
		$result = array();
		$filename = $dateFrom .'-'. $dateTo;

		if (!$content)
		{
			$content = $this->getDataTsv($urlPdf, $filename);
		}

		$result['from'] = $dateFrom;
		$result['to'] = $dateTo;
		$result['pdf_url'] = $urlPdf;
		$result['tsv_url'] = $this->saveTsvFolder . $filename . '.tsv';
		$result['content'] = Tsv::toArray($content);

		return $result;
	}

	private function getDataTsv($urlPdf, $filename)
	{
		$savePathTsv = $this->saveTsvFolder . $filename . '.tsv';

		$tsv = $this->getTsv($savePathTsv);

		if ($tsv) return $tsv;
		return $this->downloadPdfAndSaveTsv($urlPdf, $filename, $savePathTsv);
	}

	private function getAllLocaleTsv()
	{
		$result = array();
		$urlPdf = 'locale';
		$files = array_diff(scandir($this->saveTsvFolder), array('..', '.'));

		foreach($files as $filename)
		{
			$filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
			$dates = explode('-', $filenameWithoutExt);
			$pathTsv = $this->saveTsvFolder . $filename;
			$content = $this->getTsv($pathTsv);

			if (!$content) continue;

			$item = $this->getItem($dates, $urlPdf, $content);

			if (!$item) continue;

			array_push($result, $item);
		}
		return $result;
	}

	private function getTsv($pathTsv)
	{
		if (file_exists($pathTsv))
		{
			$fileCreateDate = strtotime('+1 hour', filemtime($pathTsv));

			if ($this->now->getTimestamp() < $fileCreateDate)
			{
				return file_get_contents($pathTsv);
			}
		}
		return null;
	}

	private function downloadPdfAndSaveTsv($urlPdf, $filename, $savePathTsv)
	{
		$urlPdfLocal = Pdf::downloadAndSave($urlPdf, $filename, $this->savePdfFolder);
		$pdfContent = Pdf::getContent($urlPdfLocal);
		$tableContent = Tsv::createFile($savePathTsv, $pdfContent);
		Pdf::remove($urlPdfLocal);
		return $tableContent;
	}

}
?>