<?php
require 'cek_login.php';

if ($_SESSION['level'] != 1){
	echo "<script>
			alert('Akses Ditolak! Halaman ini hanya untuk owner.');
			window.Location.href = 'index.php';
		  </script>";
	exite;
}
?>