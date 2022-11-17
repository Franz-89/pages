<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$Conn = new Conn('local', 'enertrade');

switch ($_GET['action']){
		
	case 'datos_contadores':
		$arr_datos = $Conn->getArray("SELECT * FROM datos_contadores ORDER BY CONTADOR");
		$filename = 'Datos contadores';
		break;
		
	case 'envios_gestinel':
		$arr_datos = $Conn->getArray("SELECT * FROM envios_gestinel ORDER BY GRUPO_ENVIO");
		$filename = 'Grupos de envío';
		break;
		
	case 'download_envios_diarios':
		
		$arr_datos = $Conn->getArray('SELECT * FROM envios_gestinel_diarios');
		$filename = 'Grupos de envio diarios';
}

$SprdSht = new SprdSht;
$SprdSht->nuevo();
$SprdSht->putArray($arr_datos, true);
$SprdSht->directDownload($filename);

unset($SprdSht, $Conn);

?>