<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

session_start();
if (isset($_SESSION['email'])){
	$usuario = $_SESSION['email'];
} else {
	header ("Location: /Enertrade/index.php");
}
session_write_close();

$Conn = new Conn('local', 'enertrade');

switch ($_GET['action']){
	case "mod":
		
		$id 	= $_POST['id'];
		$email 	= $_POST['email'];
		$in_out = $_POST['in_out'];
		$hora 	= $_POST['hora'];

		if (!validate_date($hora)){
			header ("Location: /Enertrade/pages/Fichar/modfichadas.php?error=formatofecha&id=$id");
			die;
		}
		
		if (!isset($hora) || empty($hora) || !isset($email) || empty($email)){
			header ("Location: /Enertrade/pages/Fichar/modfichadas.php?error=camposvacios&id=$id");
			die;
		}

		$strSQL = "UPDATE fichadas SET USUARIO='$email', IN_OUT='$in_out', HORA='$hora' WHERE id=$id";

		if (!$Conn->Query($strSQL)){
			echo "Imposible realizar la acción!";
		} else {
			header("Location: /Enertrade/pages/Fichar/fichadas.php?mod=1");
		}
		
		break;
		
	case "download":

		switch ($usuario){
			
			case "mmontero@enertrade.es":
			case "slizarrlade@enertrade.es":
			case "juansalsas@enertrade.es":
				$var_user = '%%';
				break;

			default:
				$var_user = $usuario;
				break;
		}
		
		$arr_datos = $Conn->getArray("CALL FICHADAS ('$var_user')");
		
		$SprdSht = new SprdSht;
		$SprdSht->nuevo();
		$SprdSht->putArray($arr_datos, true);
		$SprdSht->directDownload("resumen_entradas_salidas");
		unset($SprdSht, $arr_datos);
		
		break;
}

unset($Conn);
?>