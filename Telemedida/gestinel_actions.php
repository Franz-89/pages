<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");



$action = $_POST['action'];

switch($action){
	case "precios_gestinel":
	case "crear_calendario":
	case "download_calendario":
	case "nuevos_contadores":
		goto SIN_DATOS; break;
}

session_start();
if (!isset($_SESSION['contadores']) || !isset($_SESSION['desde']) || !isset($_SESSION['hasta'])){header ("Location: datos_contadores.php");}

$horaria = (isset($_POST['horaria']) && !empty($_POST['horaria'])) ? "checked" : "";

//Si se ha seleccionado un grupo con prioridad
if (is_numeric($_POST['prioridad'])){
	$prioridad 	= $_POST['prioridad'];
	$Conn 		= new Conn('local', 'enertrade');
	
	$arr_grupos = $Conn->getArray("SELECT GRUPO_ENVIO FROM envios_gestinel WHERE PRIORIDAD='$prioridad'");
	$str_grupos = "'".implode("', '", array_column($arr_grupos, 'GRUPO_ENVIO'))."'";
	unset($arr_grupos);
	
	$contadores = $Conn->getArray("SELECT CONTADOR FROM datos_contadores WHERE GRUPO_ENVIO IN ($str_grupos)");
	$contadores = array_column($contadores, 'CONTADOR');
	
	unset($str_grupos, $Conn);

} else {
	$contadores = $_SESSION['contadores'];
}


$desde 		= date_create_from_format('d/m/Y', $_SESSION['desde']);
$hasta 		= date_create_from_format('d/m/Y', $_SESSION['hasta']);
session_write_close();


SIN_DATOS:
switch ($action){
	
	case "download":
		
		set_time_limit(0);
		$timestamp 	= getMicrotimeString();
		
		$desde 		= $_SESSION['desde'];
		$hasta 		= $_SESSION['hasta'];
		
		$esHoraria = ($horaria!='') ? true : false;
		
		foreach ($contadores as $num_contador=>$contador){
			
			if (!$num_contador)	{$Telemedida = new Telemedida($contador, $desde, $hasta);}
			else				{$Telemedida->changeContador($contador);}
			
            $Telemedida->getCurva($esHoraria);
			
			$files[] = $Telemedida->saveCurva($timestamp);
			
		} // Por cada contador
		unset($Telemedida);
		
		$files = array_filter($files);
		merge_and_dwd_zip("CdC $timestamp.zip", $files, $timestamp);
		
		break;
		
    case "download_csv":
        
        set_time_limit(0);
		$timestamp 	= getMicrotimeString();
		
		$desde 		= $_SESSION['desde'];
		$hasta 		= $_SESSION['hasta'];
		
		$esHoraria = ($horaria!='') ? true : false;
		$date = new DateClass;
        
		foreach ($contadores as $num_contador=>$contador){
			
			if (!$num_contador)	{$Telemedida = new Telemedida($contador, $desde, $hasta);}
			else				{$Telemedida->changeContador($contador);}
			
			$curva = $Telemedida->getCurva($esHoraria);
            $cups  = $Telemedida->datos_contador['CUPS'];
            
            
            
            foreach ($curva as $num_row=>$row){
                
                if (!$num_row){
                    unset($curva[$num_row]);
                    continue;
                } elseif ($num_row == 1){
                    unset($curva[$num_row][0]);
                    unset($row[0]);
                }
                
                foreach ($row as $key=>$value){
                    
                    switch ($key){
                        case 'Fecha':
                            $curva[$num_row][$key] = $date->fromToFormat($value, 'd/m/Y H:i:s', 'd/m/Y H:i');
                            break;
                        case 'Periodo':
                        case 'kW_Compra':
                        case 'KVAr_C1':
                        case 'KVAr_C4':
                            break;
                        default:
                            unset($curva[$num_row][$key]);
                            break;
                    }
                    
                }
            }
            
            
            //Crea y guarda el fichero
            $fopen = fopen($cups.".csv", "a");
			foreach ($curva as $num_row=>$row){fputcsv($fopen, $row, ";");}
			fclose($fopen);
			unset($fopen);
            
			$files[] = $cups.".csv";
			
		} // Por cada contador
		unset($Telemedida);
		
		$files = array_filter($files);
		merge_and_dwd_zip("CdC $timestamp.zip", $files, $timestamp);
        
        break;
        
	case "informes":
		$timestamp = getMicrotimeString();
		
		set_time_limit(0);
		
		$desde = $_SESSION['desde'];
		$hasta = $_SESSION['hasta'];
		$counter = 0;
		
		foreach ($contadores as $contador){
			if (!$counter)	{$Telemedida = new Telemedida($contador, $desde, $hasta);}
			else 			{$Telemedida->changeContador($contador);}
			
			$filename = $contador.$timestamp.'.xlsx';
            if ($Telemedida->informeMensual($filename)){
                $files[] = $filename;
                $BBDD[] = $Telemedida->getFraLine();
            }
		}
		unset($Telemedida);
        
        if (isset($BBDD) && !empty($BBDD)){
            
            //Guarda las facturas ficticias en la carpeta de cada cliente dividiendo los ficheros
            $cups = array_column($BBDD, 'CUPS');
            $cups = "('".implode("', '", $cups)."')";
            
            $Conn = new Conn('mainsip', 'develop');
            $grupos = $Conn->getArray("SELECT GRUPO, CUPS FROM clientes WHERE CUPS IN $cups GROUP BY CUPS");
            unset($Conn, $cups);
            
            $Array = new ArrayClass($grupos);
            $grupos = $Array->assocFromColumn('CUPS', true);
            unset($Array);
            
            $division_por_grupo = array();
            foreach ($BBDD as $num_row=>$row){
                $division_por_grupo[$grupos[$row['CUPS']]['GRUPO']][] = $row;
            }
            unset($grupos);
            
            $Carpetas = new Carpetas;
            
            foreach ($divison_por_grupo as $grupo=>$facturas){
                $Carpetas->checkInformesMensuales($grupo, 'CARGA BBDD');
                
                $SprdSht = new SprdSht;
                $SprdSht->nuevo();
                $SprdSht->putArray($facturas);
                $SprdSht->save(date('Ymd')."_$grupo".'_FACTURACIÓN FICTICIA');
                unset($SprdSht);
            }
            unset($division_por_grupo, $facturas, $grupo);
            
            //Saca el resumen de todas las facturas ficticias
            $SprdSht = new SprdSht;
            $SprdSht->nuevo();
            $SprdSht->putArray($BBDD, true);
            $SprdSht->save('BBDD.xlsx');
            unset($SprdSht);
            $files[] = 'BBDD.xlsx';
        }
        
		merge_and_dwd_zip('Datos Informes '.$timestamp.'.zip', $files, $timestamp);
		
		break;
		
	case "informes_diarios":
		$timestamp = getMicrotimeString();

		set_time_limit(0);

		$desde = $_SESSION['desde'];
		$hasta = $_SESSION['hasta'];

		$counter = 0;

		$Conn = new Conn('local', 'enertrade');
		foreach ($contadores as $contador){
			if (!$counter)	{$Telemedida = new Telemedida($contador, $desde, $hasta);}
			else 			{$Telemedida->changeContador($contador);}

			$razon_social = $Conn->oneData("SELECT RAZON_SOCIAL FROM datos_contadores WHERE contador='$contador'");
			
			$filename = $razon_social.$timestamp.'.xlsx';
			$info_contador[$contador] = $Telemedida->informeDiario($filename);

			$files[$contador] = $filename;

		}
		unset($Telemedida);

		//Agrupa los contadores en los varios grupos de envío
		foreach ($info_contador as $contador=>$datos){
			if (empty($datos['GRUPO_ENVIO_DIARIO'])){continue;}
			$grupos_envio[$datos['GRUPO_ENVIO_DIARIO']]['CONTADOR'][] = $contador;
			$grupos_envio[$datos['GRUPO_ENVIO_DIARIO']]['FICHEROS'][] = $files[$contador];
		}
		if (!isset($grupos_envio)){foreach ($files as $file){unlink($file); die;}}	//Si no hay ningún grupo de envío
		unset($files);
		
		//Saca a los destinatarios del correo
		$groups[0] = array_keys($grupos_envio);
		$groups = implode_Values($groups);

		$Conn 			= new Conn('local', 'enertrade');
		$info_grupos 	= $Conn->getArray("SELECT * FROM envios_gestinel_diarios WHERE GRUPO IN $groups");
		unset($groups);

		foreach ($info_grupos as $num_row=>$row){
			
			$A = array_filter(explode(';', $row['A']));
			foreach ($A as $num_val=>$value){$A[$num_val] = trim($value);}
			
			$copia = array_filter(explode(';', $row['COPIA']));
			if (!empty($copia)){foreach ($copia as $num_val=>$value){$copia[$num_val] = trim($value);}}
			
			$grupos_envio[$row['GRUPO']]['A'] 		= $A;
			$grupos_envio[$row['GRUPO']]['COPIA'] 	= (!empty($copia)) ? $copia : NULL;

			unset($A, $copia);
		}
		unset($info_grupos);
		
		
		//Envia los correos
		foreach ($grupos_envio as $grupo=>$datos){
			
			$A 		= $datos['A'];
			$CC 	= $datos['COPIA'];
			$SUJETO = 'INFORME CONSUMO DIARIO '.date('m/Y');
			
			$CUERPO = 	"Buenos días,<br><br>
						Adjunto los informes diarios de consumo. Indicarte que:<br><br>";

			foreach ($datos['CONTADOR'] as $num_cont=>$contador){
				$CUERPO .= "Para ".$info_contador[$contador]['RAZON_SOCIAL'].":<br><br>";

				//Excesos de potencia
				$CUERPO .= ($info_contador[$contador]['POT']) ? 'Hubo excesos de potencia acumulados del mes por valor de '.$info_contador[$contador]['POT'].'€' : 'No hubo excesos de potencia acumulados del mes';
				$CUERPO .= '<br>';

				//Reactiva inductiva
				$CUERPO .= ($info_contador[$contador]['TR']) ? 'Hubo penalización por reactiva inductiva por valor de '.$info_contador[$contador]['TR'].'€' : 'No hubo penalización por reactiva inductiva';
				$CUERPO .= '<br><br>';
                
				//Reactiva capacitiva
                $CUERPO .= 'Por el momento el precio de la penalización por reactiva capacitiva es de 0 €/kVAr. Para llevar el seguimiento de la penalización por reactiva capacitiva utilizamos el precio 0,05 €/kVAr.<br>';
				$CUERPO .= ($info_contador[$contador]['CAPACITIVA']) ? 'Hubo penalización por reactiva capacitiva por valor de '.$info_contador[$contador]['CAPACITIVA'].'€' : 'No hubo penalización por reactiva capacitiva';
				$CUERPO .= '<br><br>';

				$CUERPO .= 'El último dato disponible es del '.$info_contador[$contador]['MAXIMA'].'<br><br>';
			}
			$CUERPO .= "Si la fecha del último dato disponible es demasiado antigua podría haber un problema en la telemedida.<br>
						Contacte con nosotros o revise el estado del contador.<br><br>
						Quedo a su disposición para cualquier aclaración que precise.<br><br>Un saludo";
			
			$zip_name = 'Informes Diarios.zip';
			merge_zip($zip_name, $datos['FICHEROS'], $timestamp);
			$ATTACH = array($zip_name);
			
			mailDeA($SUJETO, $CUERPO, $A, $CC, NULL, false, $ATTACH);
			unlink($zip_name);
		}
		
		header ('Location: datos_contadores.php');

		break;
		
	case "correos":
		
		$arr_datos = array();
		foreach ($contadores as $contador){
			
			$conn 	= connect_server("local", 'enertrade');
			$CUPS 	= get_registry($conn, 'datos_contadores', 'CONTADOR', $contador);
			
			
			$contador = str_replace(" ", "_", $contador);
			
			// cuartohoraria
			$strSQL = "SELECT 	
								Fecha,
								YEAR(Fecha) ANY,
								MONTH(Fecha) MES,
								DAY(Fecha) DIA,
								HOUR(Fecha) HORA,
								kW_Compra, 
								kW_Compra_H ENERGIAH,
								CONCAT('".$CUPS['CUPS']."') CUPS,
								CONCAT('Real') TipusLectura
						FROM cdc.$contador
						
						WHERE Fecha>'".date_format($desde, 'Y-m-d')." 00:00:00'
						AND Fecha<='".date_format($hasta, 'Y-m-d')." 00:00:00'
						ORDER BY Fecha";
			
			$conn 	= connect_server("local", '');
			$query = mysqli_query($conn, $strSQL);
			
			$suma_activa 	= 0;
			$suma_reactiva 	= 0;
			$counter 		= 0;
			while ($row = mysqli_fetch_assoc($query)){
				
				$fecha = date_create_from_format('Y-m-d H:i:s', $row['Fecha']);
				
				$suma_activa 	+= $row['kW_Compra'];
				++$counter;
				
				// Si hora = 0
				if (date_format($fecha, 'i')==0){
					
					$row['ENERGIAH'] 	= round($suma_activa/$counter);

					$suma_activa 	= 0;
					$counter		= 0;
					
					//Cambio de horas, dias, meses y años
					switch (date_format($fecha, 'H')){
						case 0:
							if (date_format($fecha, 'd')==1){
								if (date_format($fecha, 'm')==1){
									--$row['ANY'];
									$row['MES'] = 12;
								} else {
									--$row['MES'];
								}
								$row['DIA'] = date_format(date_create_from_format('Y-m-d', $row['ANY'].'-'.$row['MES'].'-1'), 't');
							} else {
								--$row['DIA'];
							}
							$row['HORA'] = 23;
							
							break;
							
						default:
							--$row['HORA'];
					}
					
					unset($row['Fecha'], $row['kW_Compra']);
					
					$arr_datos[] = $row;
					
				} //Si hora = 0
			} //Para cada linea
			unset($query);
		} //Para cada contador
		
		$SprdSht = new SprdSht;
		$SprdSht->nuevo();
		$SprdSht->putArray($arr_datos, true);
		$SprdSht->directDownload("Curvas correos juntadas");
		unset($SprdSht);
		
		break;
        
    case 'le2_curva':
        
        $desde 		= $_SESSION['desde'];
		$hasta 		= $_SESSION['hasta'];
		
		$date = new DateClass;
        
		$Telemedida = new Telemedida('LE2', $desde, $hasta);
        $curva = $Telemedida->getCurva();
        unset($Telemedida);
        
        foreach ($curva as $num_row=>$row){
            if (!$num_row){ //Si es la linea 0 - encabezado
                unset($curva[$num_row]);
                continue;
            }
            foreach ($row as $key=>$value){
                switch ($key){
                    case 'Periodo':
                    case 'Mes':
                    case 'Fecha':
                    case 'kW_Compra':
                    case 'KVAr_C1':
                    case 'kW_Compra_H':
                    case 'KVAr_C1_H':
                        break;
                    default:
                        unset($curva[$num_row][$key]);
                        break;
                }
            }
        }
        
        $SprdSht = new SprdSht;
        $SprdSht->load('plantillas/LE2 curva.xlsx', false);
        $SprdSht->putArray($curva, false, 'A3');
        $filename = 'LE2 curva '.date('d_m_Y').'.xlsx';
        $SprdSht->save($filename);
        unset($SprdSht);
        
        $A = array('ignacio.sanzo@lingotes.com');
        $SUJETO = 'CURVA '.date('d-m');
        $ATTACH = array($filename);
        
        mailDeA($SUJETO, '', $A, NULL, NULL, false, $ATTACH);
        unlink($filename);
        
        header ('Location: datos_contadores.php');
        
        break;
		
	case "download_calendario":
		
		$ano = $_POST['ano'];
		
		$desde = date_format(new DateTime("$ano-01-01 00:15:00"), 'Y-m-d H:i:s');
		$hasta = date_format(new DateTime(($ano+1)."-01-01 00:00:00"), 'Y-m-d H:i:s');
		
		$strSQL = "SELECT * FROM nuevo_cal_periodos WHERE FECHA BETWEEN '$desde' AND '$hasta' ORDER BY FECHA";
		$Conn 	= new Conn('local', 'enertrade');
		$arr_datos = $Conn->getArray($strSQL);
		
		$SprdSht = new SprdSht;
		$SprdSht->nuevo();
		$SprdSht->putArray($arr_datos, true);
		$SprdSht->directDownload("Calendario $ano");
		unset($SprdSht);
		
		break;
		
	case "nuevos_contadores":
		
		if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header('Location: datos_contadores.php'); die;}
		
		$Conn             = new Conn('mainsip', 'develop');
		$listado_clientes = array_column($Conn->getArray("SELECT name FROM gestion_clientes"), 'name');
		unset($Conn);
		
		$Conn                 = new Conn('local', 'enertrade');
		$listado_contadores   = array_column($Conn->getArray("SELECT CONTADOR FROM datos_contadores"), 'CONTADOR');
		
		$datos                = $Conn->getArray("DESCRIBE cal_periodos");
		$listado_calendarios  = array_column($datos , 'Field');
		unset($datos);
		
		$errores = array();
		
		foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){
				
				$SprdSht = new SprdSht;
				$SprdSht->load($file);
				$datos_assoc = $SprdSht->getArray(true);
				unset($SprdSht);
				
				
				foreach ($datos_assoc as $row){
					
                    //Comprueba que haya contador en el fichero
					switch (true){
						case (!isset($row['CONTADOR'])):
						case (empty($row['CONTADOR'])):
							$linea_err['CONTADOR'] 		= $row['CONTADOR'];
							$linea_err['OBSERVACIONES'] = "Este CONTADOR ya existe";
							$errores[] = $linea_err;
                            unset($linea_err);
                            continue(2);
                            break;
					}
					
                    $update = array();
                    foreach (array_keys($row) as $key){
                        $update[] = "$key=VALUES($key)";
                    }
                    
                    $str_update = implode(', ', $update);
					$str_keys 	= implode(',', array_keys($row));
					$str_values[] = "('".implode("', '", array_values($row))."')";
                    
				} //Para cada linea
                $str_values = implode(", ", $str_values);
                $Conn->Query("INSERT INTO datos_contadores ($str_keys) VALUES ($str_values) ON DUPLICATE KEY UPDATE $str_update");
			} //Si se ha subido
		} //Para cada fichero
		
		unset($Conn);
		
        die;
        
		if (!empty($errores)){
			$SprdSht = new SprdSht;
			$SprdSht->nuevo();
			$SprdSht->putArray($errores, true);
			$SprdSht->directDownload('Errores');
			unset($SprdSht);
		}
		
		header ("Location: datos_contadores.php?horaria=$horaria&prioridad=$prioridad&nuevos=1");
		break;
		
	case 'nuevos_grupos_envios':
		
		if (!isset($_FILES['fichero']['tmp_name'][1]) || empty($_FILES['fichero']['tmp_name'][1])){header('Location: datos_contadores.php'); die;}
		
		$Conn = new Conn('local', 'enertrade');
		
		foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){
				
				$SprdSht = new SprdSht;
				$SprdSht->load($file);
				$datos_assoc = $SprdSht->getArray(true);
				unset($SprdSht);
				
				$grupo_intranet = $Conn->oneData("SELECT GRUPO_ENVIO FROM envios_gestinel WHERE GRUPO_ENVIO='".$row['GRUPO_ENVIO']."'");
				
				foreach ($datos_assoc as $num_row=>$row){
					
					switch (true){
						case (!is_numeric($row['PRIORIDAD'])):							//Si la prioridad no es un numero
							$linea_err['GRUPO_ENVIO'] 	= $row['GRUPO_ENVIO'];
							$linea_err['OBSERVACIONES'] = "La PRIORIDAD tiene que ser un valor que va de 1 a 10";
							break;
						
						case (!$grupo_intranet):										//Si el grupo de envío ya existe
							$linea_err['GRUPO_ENVIO'] 	= $row['GRUPO_ENVIO'];
							$linea_err['OBSERVACIONES'] = "Este GRUPO_ENVIO ya existe";
							break;
							
						case (empty($row['A'])):										//Si el campo A está vacío
							$linea_err['GRUPO_ENVIO'] 	= $row['GRUPO_ENVIO'];
							$linea_err['OBSERVACIONES'] = "El campo A no puede estar vacío";
					}
					
					if (isset($linea_err)){
						$errores[] = $linea_err;
						unset($linea_err);
						continue;
					}
					
					$str_keys 	= implode(',', array_keys($row));
					$str_values = "'".implode(array_values("', '", $row))."'";
					
					$Conn->Query("INSERT INTO envios_gestinel ($str_keys) VALUES ($str_values)");
					
				} //Para cada linea
			} //Si se ha subido
		} //Para cada fichero
		
		unset($Conn);
		
		if (!empty($errores)){
			$SprdSht = new SprdSht;
			$SprdSht->nuevo();
			$SprdSht->putArray($errores, true);
			$SprdSht->directDownload('Errores');
			unset($SprdSht);
		}
		
		header ("Location: datos_contadores.php?horaria=$horaria&prioridad=$prioridad&nuevos=1");
		
		break;
        
    case 'update_precio':
        
        $desde = date_format($desde, 'd/m/Y');
        $hasta = date_format($hasta, 'd/m/Y');
        
        $Indexado = new Indexado;
        $Indexado->actualizarPrecioNuevo($desde, $hasta, $contadores);
        
        break;
        
    case 'seatFicticio':
        
		$desde = $_SESSION['desde'];
		$hasta = $_SESSION['hasta'];
        
        //Descarga las curvas de SEAT3 a SEAT6
        for ($x=0; $x<=3; $x++){
            $contador = 'SEAT'.(3 + $x);
            if (!$x){$Telemedida = new Telemedida($contador, $desde, $hasta);}
            else {$Telemedida->changeContador($contador, $desde, $hasta);}
            $$contador = $Telemedida->getCurva();
            
            foreach ($$contador as $num_row=>$row){
                if (!$num_row){unset($$contador[$num_row]); continue;}
                
                $$contador[$row['Fecha']] = $row;
                unset($$contador[$num_row]);
            }
        }
        
        unset($Telemedida);
        
        $SEAT16 = array();
        $SEAT17 = array();
        $SEAT18 = array();
        $values = array();
        
        //FUNCIONES
        //limpia fila
        function clean_row_CdC($row){
            foreach ($row as $key=>$value){
                switch ($key){
                    case 'Fecha':
                    case 'kW_Compra':
                    case 'kW_venta':
                    case 'KVAr_C1':
                    case 'KVAr_CAP_V':
                    case 'KVAr_IND_V':
                    case 'KVAr_C4':
                    case 'Flags':
                        break;
                    
                    default:
                        unset($row[$key]);
                        break;
                }
                switch ($value){
                    case 'SEAT3':
                    case 'SEAT4':
                    case 'SEAT5':
                        unset($row[$key]);
                        break;
                }
            }
            
            return $row;
        }
        
        //Carga datos
        function load_data_CdC($values, $contador){
            $Conn = new Conn('local', 'cdc');
            $str_values = implode_values($values);
            $values = array();
            $strSQL = "INSERT INTO $contador (	    Fecha, 
                                                kW_Compra, 
                                                kW_venta, 
                                                KVAr_C1, 
                                                KVAr_CAP_V, 
                                                KVAr_IND_V, 
                                                KVAr_C4, 
                                                Flags)

                                    VALUES $str_values

                        ON DUPLICATE KEY UPDATE Fecha		=VALUES(Fecha),
                                                kW_Compra	=VALUES(kW_Compra),
                                                kW_venta	=VALUES(kW_venta),
                                                KVAr_C1		=VALUES(KVAr_C1),
                                                KVAr_CAP_V	=VALUES(KVAr_CAP_V),
                                                KVAr_IND_V	=VALUES(KVAr_IND_V),
                                                KVAr_C4		=VALUES(KVAr_C4),
                                                Flags		=VALUES(Flags)";
            $Conn->Query($strSQL);
            unset($Conn);
        }
        
        $Conn = new Conn('local', 'cdc');
        $date = new DateClass;
        
        //Calcula activa y reactiva y carga los datos
        //SEAT16
        foreach ($SEAT3 as $fecha=>$row){
            
            $EAcS3 = $SEAT3[$fecha]['kW_Compra'];
            $EAcS4 = $SEAT4[$fecha]['kW_Compra'];
            $EAcS5 = $SEAT5[$fecha]['kW_Compra'];
            $EAcS6 = $SEAT6[$fecha]['kW_Compra'];
            
            $EAvS3 = $SEAT3[$fecha]['kW_venta'];
            $EAvS4 = $SEAT4[$fecha]['kW_venta'];
            $EAvS5 = $SEAT5[$fecha]['kW_venta'];
            $EAvS6 = $SEAT6[$fecha]['kW_venta'];
            
            $ERcS3 = $SEAT3[$fecha]['KVAr_C1'];
            $ERcS4 = $SEAT4[$fecha]['KVAr_C1'];
            $ERcS5 = $SEAT5[$fecha]['KVAr_C1'];
            $ERcS6 = $SEAT6[$fecha]['KVAr_C1'];
            
            $ERvS3 = $SEAT3[$fecha]['KVAr_CAP_V'];
            $ERvS4 = $SEAT4[$fecha]['KVAr_CAP_V'];
            $ERvS5 = $SEAT5[$fecha]['KVAr_CAP_V'];
            $ERvS6 = $SEAT6[$fecha]['KVAr_CAP_V'];
            
            $row = clean_row_CdC($row);
            
            $row['Fecha'] = $date->fromToFormat($fecha, 'd/m/Y H:i:s', 'Y-m-d H:i:s');
            
            $SEAT16[$fecha] = $row;
            
            switch (true){
                case ($row['kW_Compra']==0 && $EAcS4==0 && $EAcS5==0):
                    $SEAT16[$fecha]['kW_Compra'] = $EAcS3 - $EAvS3 + (0.99529*$EAvS6 - $EAcS6)/3;
                    $SEAT16[$fecha]['KVAr_C1'] = $ERcS3 - (0.99529*$ERvS6)/3;
                    break;

                case (($row['kW_Compra']==0 && $EAcS4==0) || ($row['kW_Compra']==0 && $EAcS5==0)):
                    $SEAT16[$fecha]['kW_Compra'] = $EAcS3 - $EAvS3 + (0.99529*$EAvS6 - $EAcS6)/2;
                    $SEAT16[$fecha]['KVAr_C1'] = $ERcS3 - (0.99529*$ERvS6)/2;
                    break;
                    
                case ($row['kW_Compra']==0):
                    $SEAT16[$fecha]['kW_Compra'] = $EAcS3 - $EAvS3 + (0.99529*$EAvS6 - $EAcS6);
                    $SEAT16[$fecha]['KVAr_C1'] = $ERcS3 - (0.99529*$ERvS6);
                    break;
                    
                case ($EAcS4==0 || $EAcS5==0):
                    $SEAT16[$fecha]['kW_Compra'] = (($EAcS3 - $EAvS3)<0) ? $EAcS3 : ($EAcS3 - $EAvS3);;
                    $SEAT16[$fecha]['KVAr_C1'] = $ERcS3;
                    break;
                    
                default:
                    $SEAT16[$fecha]['kW_Compra'] = $EAcS3 - $EAvS3 + (0.99529*$EAvS6 - $EAcS6)*($EAcS3/($EAcS3+$EAcS4+$EAcS5));
                    $SEAT16[$fecha]['KVAr_C1'] = $ERcS3 - (0.99529*$ERvS6)*($EAcS3/($EAcS3+$EAcS4+$EAcS5));
                    break;
            }
            
            $values[] = $SEAT16[$fecha];
            
            if((count($values)%1000) == 0){load_data_CdC($values, 'SEAT16');}
        }
        
        if ($SEAT16[$fecha]['KVAr_C1']<0){$SEAT16[$fecha]['KVAr_C1'] = 0;}
        
        unset($SEAT16);
        
        if(!empty($values)){load_data_CdC($values, 'SEAT16');}
        
        
        
        //SEAT17
        foreach ($SEAT4 as $fecha=>$row){
            
            $EAcS3 = $SEAT3[$fecha]['kW_Compra'];
            $EAcS4 = $SEAT4[$fecha]['kW_Compra'];
            $EAcS5 = $SEAT5[$fecha]['kW_Compra'];
            $EAcS6 = $SEAT6[$fecha]['kW_Compra'];
            
            $EAvS3 = $SEAT3[$fecha]['kW_venta'];
            $EAvS4 = $SEAT4[$fecha]['kW_venta'];
            $EAvS5 = $SEAT5[$fecha]['kW_venta'];
            $EAvS6 = $SEAT6[$fecha]['kW_venta'];
            
            $ERcS3 = $SEAT3[$fecha]['KVAr_C1'];
            $ERcS4 = $SEAT4[$fecha]['KVAr_C1'];
            $ERcS5 = $SEAT5[$fecha]['KVAr_C1'];
            $ERcS6 = $SEAT6[$fecha]['KVAr_C1'];
            
            $ERvS3 = $SEAT3[$fecha]['KVAr_CAP_V'];
            $ERvS4 = $SEAT4[$fecha]['KVAr_CAP_V'];
            $ERvS5 = $SEAT5[$fecha]['KVAr_CAP_V'];
            $ERvS6 = $SEAT6[$fecha]['KVAr_CAP_V'];
            
            $row = clean_row_CdC($row);
            
            $row['Fecha'] = $date->fromToFormat($fecha, 'd/m/Y H:i:s', 'Y-m-d H:i:s');
            
            $SEAT17[$fecha] = $row;
            
            switch (true){
                case ($row['kW_Compra']==0 && $EAcS3==0 && $EAcS5==0):
                    $SEAT17[$fecha]['kW_Compra'] = $EAcS4 - $EAvS4 + (0.99529*$EAvS6 - $EAcS6)/3;
                    $SEAT17[$fecha]['KVAr_C1'] = $ERcS4 - (0.99529*$ERvS6)/3;
                    break;

                case (($row['kW_Compra']==0 && $EAcS3==0) || ($row['kW_Compra']==0 && $EAcS5==0)):
                    $SEAT17[$fecha]['kW_Compra'] = $EAcS4 - $EAvS4 + (0.99529*$EAvS6 - $EAcS6)/2;
                    $SEAT17[$fecha]['KVAr_C1'] = $ERcS4 - (0.99529*$ERvS6)/2;
                    break;
                    
                case ($row['kW_Compra']==0):
                    $SEAT17[$fecha]['kW_Compra'] = $EAcS4 - $EAvS4 + (0.99529*$EAvS6 - $EAcS6);
                    $SEAT17[$fecha]['KVAr_C1'] = $ERcS4 - (0.99529*$ERvS6);
                    break;
                    
                case ($EAcS3==0 || $EAcS5==0):
                    $SEAT17[$fecha]['kW_Compra'] = (($EAcS4 - $EAvS4)<0) ? $EAcS4 : ($EAcS4 - $EAvS4);;
                    $SEAT17[$fecha]['KVAr_C1'] = $ERcS4;
                    break;
                    
                default:
                    $SEAT17[$fecha]['kW_Compra'] = $EAcS4 - $EAvS4 + (0.99529*$EAvS6 - $EAcS6)*($EAcS4/($EAcS3+$EAcS4+$EAcS5));
                    $SEAT17[$fecha]['KVAr_C1'] = $ERcS4 - (0.99529*$ERvS6)*($EAcS4/($EAcS3+$EAcS4+$EAcS5));
                    break;
            }
            
            if ($SEAT17[$fecha]['KVAr_C1']<0){$SEAT17[$fecha]['KVAr_C1'] = 0;}
            
            $values[] = $SEAT17[$fecha];
            
            if((count($values)%1000) == 0){load_data_CdC($values, 'SEAT17');}
        }
        
        unset($SEAT17);
        
        if(!empty($values)){load_data_CdC($values, 'SEAT17');}
        
        //SEAT18
        foreach ($SEAT5 as $fecha=>$row){
            
            $EAcS3 = $SEAT3[$fecha]['kW_Compra'];
            $EAcS4 = $SEAT4[$fecha]['kW_Compra'];
            $EAcS5 = $SEAT5[$fecha]['kW_Compra'];
            $EAcS6 = $SEAT6[$fecha]['kW_Compra'];
            
            $EAvS3 = $SEAT3[$fecha]['kW_venta'];
            $EAvS4 = $SEAT4[$fecha]['kW_venta'];
            $EAvS5 = $SEAT5[$fecha]['kW_venta'];
            $EAvS6 = $SEAT6[$fecha]['kW_venta'];
            
            $ERcS3 = $SEAT3[$fecha]['KVAr_C1'];
            $ERcS4 = $SEAT4[$fecha]['KVAr_C1'];
            $ERcS5 = $SEAT5[$fecha]['KVAr_C1'];
            $ERcS6 = $SEAT6[$fecha]['KVAr_C1'];
            
            $ERvS3 = $SEAT3[$fecha]['KVAr_CAP_V'];
            $ERvS4 = $SEAT4[$fecha]['KVAr_CAP_V'];
            $ERvS5 = $SEAT5[$fecha]['KVAr_CAP_V'];
            $ERvS6 = $SEAT6[$fecha]['KVAr_CAP_V'];
            
            $row = clean_row_CdC($row);
            
            $row['Fecha'] = $date->fromToFormat($fecha, 'd/m/Y H:i:s', 'Y-m-d H:i:s');
            
            $SEAT18[$fecha] = $row;
            
            switch (true){
                case ($row['kW_Compra']==0 && $EAcS3==0 && $EAcS4==0):
                    $SEAT18[$fecha]['kW_Compra'] = $EAcS5 - $EAvS5 + (0.99529*$EAvS6 - $EAcS6)/3;
                    $SEAT18[$fecha]['KVAr_C1'] = $ERcS5 - (0.99529*$ERvS6)/3;
                    break;

                case (($row['kW_Compra']==0 && $EAcS4==0) || ($row['kW_Compra']==0 && $EAcS3==0)):
                    $SEAT18[$fecha]['kW_Compra'] = $EAcS5 - $EAvS5 + (0.99529*$EAvS6 - $EAcS6)/2;
                    $SEAT18[$fecha]['KVAr_C1'] = $ERcS5 - (0.99529*$ERvS6)/2;
                    break;
                    
                case ($row['kW_Compra']==0):
                    $SEAT18[$fecha]['kW_Compra'] = $EAcS5 - $EAvS5 + (0.99529*$EAvS6 - $EAcS6);
                    $SEAT18[$fecha]['KVAr_C1'] = $ERcS5 - (0.99529*$ERvS6);
                    break;
                    
                case ($EAcS4==0 || $EAcS3==0):
                    $SEAT18[$fecha]['kW_Compra'] = (($EAcS5 - $EAvS5)<0) ? $EAcS5 : ($EAcS5 - $EAvS5);
                    $SEAT18[$fecha]['KVAr_C1'] = $ERcS5;
                    break;
                    
                default:
                    $SEAT18[$fecha]['kW_Compra'] = $EAcS5 - $EAvS5 + (0.99529*$EAvS6 - $EAcS6)*($EAcS5/($EAcS3+$EAcS4+$EAcS5));
                    $SEAT18[$fecha]['KVAr_C1'] = $ERcS5 - (0.99529*$ERvS6)*($EAcS5/($EAcS3+$EAcS4+$EAcS5));
                    break;
            }
            
            if ($SEAT18[$fecha]['KVAr_C1']<0){$SEAT18[$fecha]['KVAr_C1'] = 0;}
            
            $values[] = $SEAT18[$fecha];
            
            if((count($values)%1000) == 0){load_data_CdC($values, 'SEAT18');}
        }
        
        unset($SEAT18);
        
        if(!empty($values)){load_data_CdC($values, 'SEAT18');}
        
        
        unset($SEAT3, $SEAT4, $SEAT5, $SEAT6);
        
        header ("Location: datos_contadores.php?horaria=$horaria&prioridad=$prioridad&nuevos=1");
        
        break;
		
} // Switch action

?>