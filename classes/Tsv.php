<?php
namespace classes;

class Tsv
{
	public static function createFile($savePath,  $pdfContent)
	{
		$fp = fopen($savePath, 'w+');
		fwrite($fp, $pdfContent);
		fclose($fp);

		return $pdfContent;
	}

	public static function toArray($content)
	{
		$rows = explode("\n", $content);
		$result = array();
		// $result = array_map('str_getcsv', $rows);
		foreach ($rows as $i => $row) {
			$ind = $i + 1;
			$arr = str_getcsv($row, "   ");
			array_push($result, $arr);
			// var_dump($i, $arr);
			// echo '<br>';
		}
		return $result;
	}

	// NEED TEST!
	public static function toJson($content)
	{
		$key = fgetcsv($content, '1024', ',');
		
		$json = array();
			while ($row = fgetcsv($content, '1024', ',')) {
			$json[] = array_combine($key, $row);
		}
		
		return json_encode($json);
	}

}
?>