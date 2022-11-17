<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$sql_table 	= $_GET['table'];
$action 	= $_GET['action'];

$id 				= (isset($_POST['id'])) 															? $_POST['id'] 					: '';
$contador 			= (isset($_POST['contador'])) 														? $_POST['contador'] 			: '';
$tarifa 			= (isset($_POST['tarifa'])) 														? $_POST['tarifa'] 				: '';
$tarifa_nueva 		= (isset($_POST['tarifa_nueva'])) 													? $_POST['tarifa_nueva'] 		: '';
$cal 				= (isset($_POST['cal'])) 															? $_POST['cal'] 				: '';
$cal_nuevo			= (isset($_POST['cal_nuevo'])) 														? $_POST['cal_nuevo'] 			: '';
$cups 				= (isset($_POST['cups'])) 															? $_POST['cups'] 				: '';
$cliente 			= (isset($_POST['cliente'])) 														? $_POST['cliente'] 			: '';
$cif 				= (isset($_POST['cif'])) 															? $_POST['cif'] 				: '';
$empresa 			= (isset($_POST['empresa'])) 														? $_POST['empresa'] 			: '';
$precio 			= (isset($_POST['precio'])) 														? $_POST['precio'] 				: '';
$comm 				= (isset($_POST['comm'])) 															? $_POST['comm'] 				: '';
$enum 				= (isset($_POST['enum'])) 															? $_POST['enum'] 				: '';
$distr 				= (isset($_POST['distr'])) 															? $_POST['distr'] 				: '';
$tension 			= (isset($_POST['tension'])) 														? $_POST['tension'] 			: '';
$fin_contrato 		= (isset($_POST['fin_contrato'])) 													? $_POST['fin_contrato'] 		: '';
$equipo_medida 		= (isset($_POST['equipo_medida'])) 													? $_POST['equipo_medida'] 		: '';
$grupo_envio 		= (isset($_POST['grupo_envio']) 		&& $_POST['grupo_envio']!='Ninguno') 		? $_POST['grupo_envio'] 		: '';
$grupo_envio_diario = (isset($_POST['grupo_envio_diario']) 	&& $_POST['grupo_envio_diario']!='Ninguno') ? $_POST['grupo_envio_diario'] 	: '';


for ($i=1; $i<=6; $i++){
	$P 		= "P$i";
	$PC 	= "PC$i";
	$ATR 	= "ATR$i";
	$TP 	= "TP$i";

	$$P		= $_POST[$P];
	$$PC	= $_POST[$PC];
	$$ATR	= $_POST[$ATR];
	$$TP	= $_POST[$TP];
}

if (empty($contador) || empty($tarifa) || empty($cal) || empty($cliente)) goto Campos_vacios;

switch ($action){
	case "add":
		$strSQL = "INSERT INTO $sql_table (CONTADOR, TARIFA, TARIFA_NUEVA, CALENDARIO, CALENDARIO_NUEVO, CUPS, GRUPO, CIF, RAZON_SOCIAL, TIPO_PRECIO, COMERCIALIZADORA, DISTRIBUIDORA, TENSION, FIN_CONTRATO, EQUIPO_MEDIDA, GRUPO_ENVIO, GRUPO_ENVIO_DIARIO, ENUMERACION";
		
		for ($i=1; $i<=6; $i++){
			$P 		= "P$i";
			$PC 	= "PC$i";
			$ATR 	= "ATR$i";
			$TP 	= "TP$i";

			$strSQL .= ", $P, $PC, $ATR, $TP";
		}

		$strSQL .= ") VALUES('$contador', '$tarifa', '$tarifa_nueva', '$cal', '$cal_nuevo', '$cups', '$cliente', '$cif', '$empresa', '$precio', '$comm', '$distr', '$tension', '$fin_contrato', '$equipo_medida', '$grupo_envio', '$grupo_envio_diario', '$enum'";
		
		for ($i=1; $i<=6; $i++){
			$P 		= "P$i";
			$PC 	= "PC$i";
			$ATR 	= "ATR$i";
			$TP 	= "TP$i";

			$strSQL .= ", '".$$P."', '".$$PC."', '".$$ATR."', '".$$TP."'";
		}
		$strSQL .= ")";
		
		break;

	case "mod":
		$strSQL = "UPDATE $sql_table SET CONTADOR='$contador', TARIFA='$tarifa', CALENDARIO='$cal', CUPS='$cups', GRUPO='$cliente', CIF='$cif', RAZON_SOCIAL='$empresa', TIPO_PRECIO='$precio', COMERCIALIZADORA='$comm', DISTRIBUIDORA='$distr', TENSION='$tension', FIN_CONTRATO='$fin_contrato', EQUIPO_MEDIDA='$equipo_medida', GRUPO_ENVIO='$grupo_envio', GRUPO_ENVIO_DIARIO='$grupo_envio_diario', CALENDARIO_NUEVO='$cal_nuevo', TARIFA_NUEVA='$tarifa_nueva', ENUMERACION='$enum'";
		
		for ($i=1; $i<=6; $i++){
			$P 		= "P$i";
			$PC 	= "PC$i";
			$ATR 	= "ATR$i";
			$TP 	= "TP$i";

			$strSQL .= ", $P='".$$P."', $PC='".$$PC."', $ATR='".$$ATR."', $TP='".$$TP."'";
		}
		
		$strSQL .= " WHERE id=$id";
		break;
}

//Redirección final
$Conn 	= new Conn('local', 'enertrade');

if (!$Conn->Query($strSQL)){
	print_r ($Conn->error());
	//echo "Imposible realizar la acción!";
	exit();
} else {
	header("Location: /Enertrade/pages/Telemedida/$sql_table.php?$action=1");
	exit();
}

Campos_vacios:
header ("Location: /Enertrade/pages/Telemedida/mod$sql_table.php?error=camposvacios&id=$id");
exit();

?>