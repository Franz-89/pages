<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
$Audax = new Audax;


if (!isset($_POST['action'])){goto JAVASCRIPT;}
switch ($_POST['action']){
	case 'download':
		
		if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: audax.php");}
		
		set_time_limit(0);
		
		$timestamp = getMicroTimeString();
        
		//Obtiene el listado de CUPS
		$filenum = 0;
		foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){
				
				$SprdSht = new SprdSht;
				$SprdSht->load($file);
				$CUPS = $SprdSht->getArray(true);
				unset($SprdSht);
			}
		}
		
		$hayGas 	= false;
		$PS 		= array();
		$PS_GAS 	= array();
		$BBDD 		= array();
		$BBDD_GAS 	= array();
		$total_cups = count($CUPS);
		foreach ($CUPS as $num_cups=>$CUP){
			
			//$Session->write('porcentaje', round($num_cups/$total_cups*100));
			
			$datos = array();
			for ($x=1; $x<=5; $x++){
				unset($datos);
				$datos = $Audax->getData($CUP['CUPS']);
				if ($datos){break;}
			}
			if (!$datos){
				unset($datos);
				$errores[] = $CUP['CUPS'];
				continue;
			}
			if ($datos['esGas']){
				$PS_GAS[] = $datos['PS'];
				$BBDD_GAS = array_merge($BBDD_GAS, $datos['BBDD']);
				$hayGas = true;
			} else {
				$PS[] = $datos['PS'];
				$BBDD = array_merge($BBDD, $datos['BBDD']);
			}
			unset($datos);
			
			if ($num_cups && ($num_cups % 200)==0){
				$filename = getMicrotimeString();
				
				$SprdSht = new SprdSht;
				$SprdSht->nuevo();
				$SprdSht->putArray($PS, true);
				$SprdSht->setColumnsAutoWidth();
				$SprdSht->addSheet('BBDD');
				$SprdSht->putArray($BBDD, true);
				$SprdSht->setColumnsAutoWidth();
				if (!empty($PS_GAS)){
					$SprdSht->addSheet('PS_GAS');
					$SprdSht->putArray($PS_GAS, true);
					$SprdSht->setColumnsAutoWidth();
					$SprdSht->addSheet('BBDD_GAS');
					$SprdSht->putArray($BBDD_GAS, true);
					$SprdSht->setColumnsAutoWidth();
				}
				
				if (isset($errores)){
					$SprdSht->addSheet('NO AUDAX');
					$SprdSht->putArray($errores);
					$SprdSht->setColumnsAutoWidth();
				}
				$SprdSht->save($filename);
				unset($SprdSht);
				
				$PS 		= array();
				$BBDD 		= array();
				$PS_GAS 	= array();
				$BBDD_GAS 	= array();
				
				$files[] = $filename.".xlsx";
				unset($filename);
			}
		}
		
        
		if (!empty($PS) || !empty($PS_GAS)){
			$filename = getMicrotimeString();
			
			$SprdSht = new SprdSht;
			$SprdSht->nuevo();
            if (!empty($PS)){
                $SprdSht->putArray($PS, true);
                $SprdSht->addSheet('BBDD');
                $SprdSht->putArray($BBDD, true);
                $SprdSht->setColumnsAutoWidth();
            }
			if (!empty($PS_GAS)){
				$SprdSht->addSheet('PS_GAS');
				$SprdSht->putArray($PS_GAS, true);
				$SprdSht->setColumnsAutoWidth();
				$SprdSht->addSheet('BBDD_GAS');
				$SprdSht->putArray($BBDD_GAS, true);
				$SprdSht->setColumnsAutoWidth();
			}
			if (isset($errores)){
				$SprdSht->addSheet('NO AUDAX');
				$SprdSht->putArray($errores);
				$SprdSht->setColumnsAutoWidth();
			}
			$SprdSht->save($filename);
			unset($SprdSht);

			$PS 		= array();
			$BBDD 		= array();
			$PS_GAS 	= array();
			$BBDD_GAS 	= array();

			$files[] = $filename.".xlsx";
			unset($filename);
		}
        
		if (!empty($files) && isset($files)){merge_and_dwd_zip('Datos Audax.zip', $files);;} else {header ("Location: audax.php"); die;}
		
		break;
		
	case 'download_unico':
		
		if (isset($_POST['cups'])){$CUPS = $_POST['cups'];} else {die;}
		
		$datos = $Audax->getData($CUPS);
		if (!$datos){header('Location: audax.php');}
		
		$PS[] = $datos['PS'];
		unset($datos['PS']);
		
		$SprdSht = new SprdSht;
		$SprdSht->nuevo();
		$SprdSht->addSheet('PS');
		$SprdSht->putArray($PS, true);
		$SprdSht->addSheet('BBDD');
		$SprdSht->putArray($datos['BBDD'], true);
		$SprdSht->delSheet(0);
		$SprdSht->directDownload($CUPS);
		unset($SprdSht);
		
		break;
}


JAVASCRIPT:
switch ($_GET['action']){
	case 'datos_busqueda':
		
		if (isset($_POST['cups'])){$CUPS = $_POST['cups'];} else {die;}
		
		$datos = $Audax->getData($CUPS);
		if (!$datos || $datos['esGas']){break;}
		
		foreach ($datos['BBDD'] as $num_row=>$row){
			
			//Datos iniciales
			//Activa
			$activa[$num_row][] 		= $row['Fecha desde'];
			$activa[$num_row][] 		= $row['Fecha hasta'];
			$activa[$num_row][] 		= $row['Tipo lecura'];
			
			//Reactiva
			$reactiva[$num_row][] 		= $row['Fecha desde'];
			$reactiva[$num_row][] 		= $row['Fecha hasta'];
			$reactiva[$num_row][] 		= $row['Tipo lecura'];
			
			//maxima
			$maxima[$num_row][] 		= $row['Fecha desde'];
			$maxima[$num_row][] 		= $row['Fecha hasta'];
			$maxima[$num_row][] 		= $row['Tipo lecura'];
			
			//CONSUMOS
			for ($x=1; $x<=6; $x++){
				$activa[$num_row][] 	= $row["Activa P$x"];
				$reactiva[$num_row][] 	= $row["Reactiva P$x"];
				$maxima[$num_row][] 	= $row["Máxima P$x"];
			}
			
			$activa[$num_row][] 		= $row["Activa Tot"];
			$reactiva[$num_row][] 		= $row["Reactiva Tot"];
		}
		
		$activa 	= array_reverse($activa);
		$reactiva 	= array_reverse($reactiva);
		$maxima 	= array_reverse($maxima);
		
		foreach ($datos['PS'] as $key=>$value){$detalle[] = array($key, $value);}
		
		unset($datos);
		echo json_encode(array_values($detalle))."|".json_encode(array_values($activa))."|".json_encode(array_values($reactiva))."|".json_encode(array_values($maxima));
		
		break;
		
	case 'audax_timestamp':
		
		session_start();
		$timestamp = $_SESSION['AUDAX'];
		session_write_close();
		echo $timestamp;
		break;
	
	case 'get_porcentaje':
		if (isset($_POST['timestamp'])){
			$Session = new Session;
			$Session->open($_POST['timestamp']);
			$porcentaje = $Session->read('porcentaje');
			if (!isset($porcentaje)){$Session->close($_POST['timestamp']);}
			echo $porcentaje;
		} else {
			echo false;
		}
		
		break;
		
}
unset($Audax);

?>