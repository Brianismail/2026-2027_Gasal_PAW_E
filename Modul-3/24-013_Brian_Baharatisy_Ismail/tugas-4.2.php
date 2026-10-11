<?php
$weight = array(
    "Andy" => 70,
    "Barry" => 65,
    "Charlie" => 75
);

$keys = array_keys($weight);
$values = array_values($weight);

$arrlength = count($weight);

echo 'weight = ("Andy"=>"70", "Barry"=>"65", "Charlie"=>"75")<br><br>';

for ($i = 0; $i < $arrlength; $i++) {
    echo $keys[$i] . " is " . $values[$i] . " kg.<br>";
}
?>
