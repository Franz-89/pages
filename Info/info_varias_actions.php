<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

if (!isset($_POST['action'])){header ('Location: info_varias.php'); die;}

switch ($_POST['action']){
		
	case 'download_gdos':
		
		if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: info_varias.php"); die;}
		
		$timestamp = getMicrotimeString();
		
		//set_time_limit(0);
		
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
		
		$CUPS 		= array_column($CUPS, 'CUPS');
		$tipo 		= ($_POST['tipo']=='XLS') ? 1 : 0;
		$extension 	= ($_POST['tipo']=='XLS') ? '.xls' : '.pdf';
		$ano 		= $_POST['ano'];
		
		$curl = new curlClass;
		foreach ($CUPS as $num_cup=>$cup){
			usleep(200000);
			$curl->url("https://gdo.cnmc.es/CNE/informePdfPorCUPS.do?anio=$ano&tipoFiltro=1&cups=$cup&tipoArchivo=$tipo");
			$datos = $curl->execute();
			
			$filename = $cup.$timestamp.$extension;
			$zip_name = $cup.$timestamp.'.zip';
			file_put_contents($zip_name, $datos);
			
			$zip = new ZipArchive;

			if ($zip->open($zip_name) === TRUE){
				
				for ($x=0; $x<$zip->numFiles; $x++){
					$zip->extractTo('EXTRACCIONES', array($zip->statIndex($x)['name']));
					rename('EXTRACCIONES/'.$zip->statIndex($x)['name'], 'EXTRACCIONES/'.$filename);
				}
				$zip->close();
				unlink($zip_name);
			}
			unset($zip);
			
			$files[] = 'EXTRACCIONES/'.$filename;
		}
		
		merge_and_dwd_zip('GDOs.zip', $files, $timestamp);
		
		break;
}




?>