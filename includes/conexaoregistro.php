<?php
if(session_id() == '' || !isset($_SESSION)) {
    session_start();
}
if(!isset($_SESSION['cnpj'])){
	echo "<script>location.href='login.php'</script>";
}
$conexaoregistro=mysqli_connect('localhost', 'root','MDd7xdtbcbfJ') or die();	//Conecta com o MySQL- MDd7xdtbcbfJ
mysqli_select_db($conexaoregistro,'ultras27_registros') or die('unknownDatabase');						//Seleciona banco de dados
mysqli_query($conexaoregistro,"SET NAMES 'utf8'");
?>
