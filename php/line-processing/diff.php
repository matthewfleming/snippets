<?php

/*
	In linux:
		Sort files:
			sort -u fileA.txt > sortedA.txt
			sort -u fileB.txt > sortedB.txt
		Exclusive A:
			diff sortedA.txt sortedB.txt | grep '<' | cut -c3-
		Exclusive B:
			diff sortedA.txt sortedB.txt | grep '>' | cut -c3-
 
 */

function writeArrayByLine($array, $filename) {
	$fp = fopen($filename, "w");
	
	if(!$fp) {
		trigger_error("Unable to open output file '" . $filename . "'", E_ERROR);
		return;
	}
	foreach($array as $line) {
		fputs($fp, $line);
		fputs($fp, "\n");
	}
	fclose ($fp);
}

$handleA = fopen("old-sorted.txt", "r");
$handleB = fopen("new-sorted.txt", "r");

$diff = array();

if ($handleA && $handleB) {
	$exclusiveA = array();
	$exclusiveB = array();
	
	$valueA = fgets($handleA, 4096);
	$valueB = fgets($handleB, 4096);
	
	while($valueA !== false && $valueB !== false) {
		$valueA = trim($valueA);
		$valueB = trim($valueB);
		
		// handleA blank lines
		if(empty($valueA)) {
			$valueA = fgets($handleA, 4096);
			continue;
		}
		if(empty($valueB)) {
			$valueB = fgets($handleB, 4096);
			continue;
		}
		
		if($valueA == $valueB) {
			$valueA = fgets($handleA, 4096);
			$valueB = fgets($handleB, 4096);
		} else if ($valueA > $valueB) {
			$exclusiveB[] = $valueB;
			$valueB = fgets($handleB, 4096);
		} else { // $valueB > $valueA
			$exclusiveA[] = $valueA;
			$valueA = fgets($handleA, 4096);
		}
	}
	
	// finish processing to end of file with larger values
	if($valueA) {
		$exclusiveA[] = $valueA;
		while (($valueA = fgets($handleA, 4096)) !== false) {
			$exclusiveA[] = trim($valueA);
		}
    }
	if($valueB) {
		$exclusiveB[] = $valueB;
		while (($valueB = fgets($handleA, 4096)) !== false) {
			$exclusiveB[] = trim($valueB);
		}
    }
	
    fclose($handleA);
	fclose($handleB);
	
	writeArrayByLine($exclusiveA, "exclusiveA.txt");
	writeArrayByLine($exclusiveB, "exclusiveB.txt");
}



