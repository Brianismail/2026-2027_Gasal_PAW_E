<?php

$fruits = array("Avocado", "Blueberry", "Cherry");

array_push($fruits, "Durian", "Elderberry", "Fig", "Grape", "Honeydew");

// unset($fruits[1]);

$key = array_search("Blueberry", $fruits);
if ($key !== false) {
	unset($fruits[$key]);
	echo "Data Blueberry dihapus.<br>";
}

$fruits = array_values($fruits);


echo 'fruits = ("'. implode('","', $fruits). '")<br>';
echo "Nilai dengan indeks tertinggi: ". end($fruits);


?>