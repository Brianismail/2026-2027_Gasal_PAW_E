<?php
// 2.2) Buat array baru bernama $vegies
$vegies = array("Carrot", "Broccoli", "Spinach");

// Hitung panjang array
$arrlength = count($vegies);
echo "Panjang array vegies: " . $arrlength . "<br>";

// Tampilkan isi array dengan perulangan for
for ($x = 0; $x < $arrlength; $x++) {
    echo $vegies[$x] . "<br>";
}
?>
