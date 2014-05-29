<?php
$handle = fopen("new.txt", "r");
$everything = array();
if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
		$trimmed = trim($buffer);
		if(is_numeric($trimmed)) {
			$everything[] = intval($trimmed);
		}
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}
sort($everything);
$fp = fopen("new-sorted.txt", "w");
foreach($everything as $line) {
	fputs($fp, $line);
	fputs($fp, "\n");
}
fclose ($fp);
