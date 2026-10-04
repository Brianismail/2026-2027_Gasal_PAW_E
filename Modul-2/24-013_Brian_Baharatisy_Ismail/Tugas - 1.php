<?php 
$matkul = ["PTI", "ALPRO", "DPW", "STRUKDAT", "JARKOM", "PAW", "PSBF", "RPL"];
$praktikum = ["JARKOM", "PAW"];

for ($i=0; $i <= count($matkul)-1 ; $i++) { 
	if ($matkul[$i] == $praktikum[0] || $matkul[$i] == $praktikum[1]){
		echo "Saya sedang mengambil matkul{$matkul[$i]}termasuk praktikumnya <br>";
		} elseif ($i == 6 OR $i == 7) {
			echo "Saya belum mengambil matkul {$matkul[$i]} <br>";
		} else {
			echo "Saya sudah mengambil matkul	{$matkul[$i]} semester lalu <br>";
		}
}
?>