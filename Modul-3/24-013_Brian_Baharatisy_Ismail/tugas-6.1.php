<?php
$arr1 = array("A");
array_push($arr1, "B");
echo "Array awal: (\"A\")<br>";
echo "Hasil array_push: " . implode(" ", $arr1) . "<br><br>";

$arr2 = array("A", "B");
$arr3 = array("C");
$merged = array_merge($arr2, $arr3);
echo "Array awal: (\"A\", \"B\") digabung dengan (\"C\")<br>";
echo "Hasil array_merge: " . implode(" ", $merged) . "<br><br>";

$arr4 = array("x" => 1, "y" => 2);
$values = array_values($arr4);
echo "Array awal: (\"x\"=>1, \"y\"=>2)<br>";
echo "Hasil array_values: " . implode(" ", $values) . "<br><br>";

$arr5 = array("A", "B", "C");
$searchIndex = array_search("B", $arr5);
echo "Mencari \"B\" pada array: (\"A\", \"B\", \"C\")<br>";
echo "Hasil array_search: " . $searchIndex . "<br><br>";

$arr6 = array(0, 1, false, 2, "", 3, "array");
$filtered = array_filter($arr6);
echo "Array awal: (0, 1, false, 2, \"\", 3, \"array\")<br>";
echo "Hasil array_filter: " . implode(" ", $filtered) . "<br><br>";

$arr7 = array(3, 1, 2);
$asc = $arr7;
sort($asc);
$desc = $arr7;
rsort($desc);
echo "Array awal: (3, 1, 2)<br>";
echo "Hasil sort: " . implode(" ", $asc) . "<br>";
echo "Hasil rsort: " . implode(" ", $desc) . "<br><br>";

$arr8 = array("Peter"=>35, "Ben"=>37, "Joe"=>43);

$asortArr = $arr8;
asort($asortArr);
echo "Array awal: (\"Peter\"=>35, \"Ben\"=>37, \"Joe\"=>43)<br>";
echo "Hasil asort: ";
foreach ($asortArr as $k=>$v) echo "$k=>$v, ";
echo "<br>";

$ksortArr = $arr8;
ksort($ksortArr);
echo "Hasil ksort: ";
foreach ($ksortArr as $k=>$v) echo "$k=>$v, ";
echo "<br>";

$arsortArr = $arr8;
arsort($arsortArr);
echo "Hasil arsort: ";
foreach ($arsortArr as $k=>$v) echo "$k=>$v, ";
echo "<br>";

$krsortArr = $arr8;
krsort($krsortArr);
echo "Hasil krsort: ";
foreach ($krsortArr as $k=>$v) echo "$k=>$v, ";
echo "<br>";
?>
