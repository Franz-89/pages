<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$Conn = new Conn('local', 'enertrade');

switch ($_GET['action']){
	
	case "getContadores":
		
		$strSQL = "SELECT ID, CONTADOR, TARIFA, CALENDARIO, CUPS, GRUPO, CIF, RAZON_SOCIAL, TIPO_PRECIO, COMERCIALIZADORA, GRUPO_ENVIO, GRUPO_ENVIO_DIARIO FROM datos_contadores";
		$datos = $Conn->getArray($strSQL);
		
		foreach ($datos as $num_row=>$row){
			$row[] = '<a class="btn btn-default btn-sm btn-warning fa fa-edit" href="moddatos_contadores.php?id='.$row['ID'].'"></a><button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delContador($(this).val())"></button>';
			$final[] = array_values($row);
		}
		unset($datos);
		
		echo json_encode($final);
		
		break;
		
	case "getGruposEnvio":
		
		$datos = $Conn->getArray("SELECT * FROM envios_gestinel");
		
		foreach ($datos as $num_row=>$row){
			$row[] = '<a class="btn btn-default btn-sm btn-warning fa fa-edit" href="modenvios_gestinel.php?id='.$row['ID'].'"></a><button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delGrupoEnvio($(this).val())"></button>';
			$final[] = array_values($row);
		}	
		unset($datos);
		
		echo json_encode($final);
		
		break;
		
	case "getGruposEnvioDiario":
		
		$datos = $Conn->getArray("SELECT * FROM envios_gestinel_diarios");
		
		foreach ($datos as $num_row=>$row){
			$row[] = '<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delGrupoEnvioDiario($(this).val())"></button>';
			$final[] = array_values($row);
		}
		unset($datos);
		
		echo json_encode($final);
		
		break;
		
	case "delContador":
		$ID = $_POST['id'];
		$Conn->Query("DELETE FROM datos_contadores WHERE ID=$ID");
		break;
		
	case "delGrupoEnvio":
		$ID = $_POST['id'];
		$Conn->Query("DELETE FROM envios_gestinel WHERE ID=$ID");
		break;
		
	case "delGrupoEnvioDiario":
		$ID = $_POST['id'];
		$Conn->Query("DELETE FROM envios_gestinel_diarios WHERE ID=$ID");
		break;
		
	case "getAlarmas":
		
		session_start();
		if (isset($_SESSION['contadores']))	{$contadores = $_SESSION['contadores'];}
		if (isset($_SESSION['desde']))		{$desde = $_SESSION['desde'];}
		if (isset($_SESSION['hasta']))		{$hasta = $_SESSION['hasta'];}
		session_write_close();
		
		if (isset($contadores) && !empty($desde) && !empty($hasta)){

			$fecha_desde = date_create_from_format('d/m/Y', $desde);
			$fecha_hasta = date_create_from_format('d/m/Y', $hasta);
			$interval = (date_diff($fecha_desde, $fecha_hasta)->format('%a'))*96;

			
			$Conn = new Conn('local', 'cdc');

			foreach ($contadores as $contador){

				$contador = str_replace(" ", "_", $contador);

				// Huecos/Máxima
				$strSQL = "SELECT ($interval-COUNT(Fecha)) huecos, MAX(Fecha) maxima FROM $contador WHERE Fecha>'".date_format($fecha_desde, 'Y-m-d')."' AND Fecha<='".date_format($fecha_hasta, 'Y-m-d')."'";
				$datos = $Conn->getArray($strSQL);
				
				$maxima = (!empty($datos[0]['maxima'])) ? date_format(date_create_from_format('Y-m-d H:i:s', $datos[0]['maxima']), 'd/m/Y H:i') : "";
				$huecos = $datos[0]['huecos'];

				// Ceros
				$strSQL = "SELECT COUNT(kW_Compra) ceros FROM $contador WHERE Fecha>'".date_format($fecha_desde, 'Y-m-d')."' AND Fecha<='".date_format($fecha_hasta, 'Y-m-d')."' AND kW_Compra='0'";
				$ceros = $Conn->getArray($strSQL);
				$ceros = $ceros[0]['ceros'];
				
				$contador = str_replace("_", " ", $contador);
				
				$linea = [$contador, $huecos, $ceros, $maxima];
				$final[] = $linea;
				unset($linea);
				
			}
		} else {
			$final[0] = ['', '', '', ''];
		}
		
		echo json_encode($final);
		
	case 'saveDate':
		session_start();
		if (isset($_POST['desde'])){$_SESSION['desde'] = $_POST['desde'];}
		if (isset($_POST['hasta'])){$_SESSION['hasta'] = $_POST['hasta'];}
		session_write_close();
		break;
		
	case 'saveContadores':
		
		$prioridad 	= $_POST['prioridad'];
		
		switch ($prioridad){
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			case 8:
			case 9:
			case 10:
				
				$Conn 		= new Conn('local', 'enertrade');

				$arr_grupos = $Conn->getArray("SELECT GRUPO_ENVIO FROM envios_gestinel WHERE PRIORIDAD='$prioridad'");
				$str_grupos = "'".implode("', '", array_column($arr_grupos, 'GRUPO_ENVIO'))."'";
				unset($arr_grupos);

				$contadores = $Conn->getArray("SELECT CONTADOR FROM datos_contadores WHERE GRUPO_ENVIO IN ($str_grupos)");
				$contadores = array_column($contadores, 'CONTADOR');

				unset($str_grupos);
				break;
				
			case 'Diario':
				
				$Conn 		= new Conn('local', 'enertrade');

				$contadores = $Conn->getArray("SELECT CONTADOR FROM datos_contadores WHERE GRUPO_ENVIO_DIARIO!=''");
				$contadores = array_column($contadores, 'CONTADOR');
				
				break;
				
			case 'Ninguna':
				if (isset($_POST['contadores'])){$contadores = $_POST['contadores'];}
				break;
		}
		
		session_start();
		$_SESSION['contadores'] = $contadores;
		session_write_close();
		
		echo json_encode(array_values($contadores));
		
		break;
		
	case 'updateContadores':
		
		session_start();
		if (isset($_SESSION['desde'])){$desde = date_create_from_format('d/m/Y', $_SESSION['desde']);}
		if (isset($_SESSION['hasta'])){$hasta = date_create_from_format('d/m/Y', $_SESSION['hasta']);}
		if (isset($_SESSION['contadores'])){$contadores = $_SESSION['contadores'];}
		session_write_close();
		
		if (!isset($desde) && !isset($hasta) && !isset($contadores)){die;}
		
		set_time_limit(0);
		$hasta->modify('+1 day');
		
		$array_header = array('Fecha', 'kW_Compra', 'kW_venta', 'KVAr_C1', 'KVAr_CAP_V', 'KVAr_IND_V', 'KVAr_C4', 'Flags', 'HORARIOS', 'kW_Compra_H', 'kW_venta_H', 'kVAr_C1_H', 'KVAr_CAP_V_H', 'KVAr_IND_V_H', 'KVAr_C4_H');
		
		$Conn 	= new Conn('local', 'cdc');
		
		foreach ($contadores as $contador){
			
			$contador = str_replace(" ", "_", $contador);
			
			//Crea tabla si no existe
			$strSQL = "CREATE TABLE IF NOT EXISTS `$contador` (
						  `Fecha` datetime NOT NULL,
						  `kW_Compra` int(11) NOT NULL,
						  `kW_Venta` int(11) NOT NULL,
						  `KVAr_C1` int(11) NOT NULL,
						  `KVAr_CAP_V` int(11) NOT NULL,
						  `KVAr_IND_V` int(11) NOT NULL,
						  `KVAr_C4` int(11) NOT NULL,
						  `Flags` int(11) NOT NULL,
						  `HORARIOS` int(11) DEFAULT NULL,
						  `kW_Compra_H` int(11) DEFAULT NULL,
						  `kW_Venta_H` int(11) DEFAULT NULL,
						  `KVAr_C1_H` int(11) DEFAULT NULL,
						  `KVAr_CAP_V_H` int(11) DEFAULT NULL,
						  `KVAr_IND_V_H` int(11) DEFAULT NULL,
						  `KVAr_C4_H` int(11) DEFAULT NULL
						) ENGINE=InnoDB DEFAULT CHARSET=utf16;";
			$Conn->Query($strSQL);
			
			$Conn->Query("ALTER TABLE `$contador` ADD PRIMARY KEY (`Fecha`)");
			
			$values = array();
			// Para cada año
			for ($ano=date_format($desde, 'Y'); $ano<=date_format($hasta, 'Y'); $ano++){
				$dir = "C:/Gestinel2/Gestinel/".str_replace("_", " ", $contador)."/$ano/";
				$files = @scandir($dir);
				
				if (empty($files)){continue;} //Si no existe la carpeta
				
                //Cambio de la hora en octubre
                if (in_array('cambio.dat', $files)){$fcambiopen = fopen($dir.'cambio.dat', 'r');}
                
				foreach($files as $file){
					
					//Si es un fichero .dat
					if (substr($file,-3) == 'dat'){
						//Si el nombre del fichero está entre desde y hasta
						if (date_create_from_format('dmY', substr($file, 0, 4).$ano)>=$desde && date_create_from_format('dmY', substr($file, 0, 4).$ano)<=$hasta){
							$fopen = fopen($dir.$file, 'r');
							while (!feof($fopen)) {
								
								$line=trim(fgets($fopen));
								if (!empty($line)){
									$arr_line = explode("\t", $line);
									unset ($line);
									
									$linea = array_fill_keys(array('Fecha', 'kW_Compra', 'kW_venta', 'KVAr_C1', 'KVAr_CAP_V', 'KVAr_IND_V', 'KVAr_C4', 'Flags'), '');
									$linea['Fecha'] 		= date_format(date_create_from_format('d/m/Y H:i', $arr_line[1]), 'Y-m-d H:i:s');
									$linea['kW_Compra'] 	= $arr_line[2];
									$linea['kW_venta'] 		= $arr_line[3];
									$linea['KVAr_C1'] 		= $arr_line[4];
									$linea['KVAr_CAP_V'] 	= $arr_line[5];
									$linea['KVAr_IND_V'] 	= $arr_line[6];
									$linea['KVAr_C4'] 		= $arr_line[7];
									$linea['Flags'] 		= $arr_line[8];
									
                                    //Cambio de la hora en octubre
                                    /*
                                    if (in_array('cambio.dat', $files)){
                                        rewind($fcambiopen); //Mete el puntero al principio del fichero
                                        while (!feof($fcambiopen)) {

                                            $cambio_line=trim(fgets($fcambiopen));
                                            if (!empty($cambio_line)){
                                                $arr_cambio_line = explode("\t", $cambio_line);
                                                unset ($cambio_line);
                                                
                                                $cambio_linea          = array_fill_keys(array('Fecha', 'kW_Compra', 'kW_venta', 'KVAr_C1', 'KVAr_CAP_V', 'KVAr_IND_V', 'KVAr_C4', 'Flags'), '');
								                $cambio_linea['Fecha'] = date_format(date_create_from_format('d/m/Y H:i', $arr_cambio_line[1]), 'Y-m-d H:i:s');
                                                
                                                if ($cambio_linea['Fecha']==$linea['Fecha']){
                                                    $linea['kW_Compra'] 	+= $arr_cambio_line[2];
                                                    $linea['kW_venta'] 		+= $arr_cambio_line[3];
                                                    $linea['KVAr_C1'] 		+= $arr_cambio_line[4];
                                                    $linea['KVAr_CAP_V'] 	+= $arr_cambio_line[5];
                                                    $linea['KVAr_IND_V'] 	+= $arr_cambio_line[6];
                                                    $linea['KVAr_C4'] 		+= $arr_cambio_line[7];
                                                }
                                                
                                                unset($arr_cambio_line, $cambio_linea);
                                            }
                                        } // hasta EOF
                                    }
                                    */
                                    
									$values[] = "'".implode("','", $linea)."'";
									unset($linea);
									
									if((count($values)%1000) == 0){
										$str_values = "(".implode("),(", $values).")";
										$values = array();
										$strSQL = "INSERT INTO $contador (	Fecha, 
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
										$Conn->Query($strSQL);;
									}
								}// Si linea vacía
							}// Hasta EOF
							fclose($fopen);
							unset ($fopen);
						} // Si el nombre del fichero está entre desde y hasta
					} // Si es .dat
				} // Por cada fichero
				
                //Cambio de la hora en octubre
                if (in_array('cambio.dat', $files)){
                    fclose($fcambiopen);
				    unset ($fcambiopen);
                }
                
				if (!empty($values)){
					$str_values = "(".implode("),(", $values).")";
					unset($values);
					$strSQL = "INSERT INTO $contador (	Fecha, 
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
				}
			} // Por cada año
			
			$fecha_maxima = $Conn->getArray("SELECT MAX(Fecha) maxima FROM $contador");
			
			if (isset($fecha_maxima[0]['maxima']) && !empty($fecha_maxima[0]['maxima'])){
				$fecha_maxima = date_create_from_format('Y-m-d H:i:s', $fecha_maxima[0]['maxima']);
				$fecha_maxima->modify('-2 years');
				$fecha_maxima->modify('-6 month');
				//$Conn->Query("DELETE FROM $contador WHERE Fecha<'".date_format($fecha_maxima, 'Y-m-d')."'");
				unset($fecha_maxima);
			}
			
		} // Por cada contador
		
		break;
        
    case 'updateContadoresPalomares':
		
        $desde = new DateClass;
        $hasta = new DateClass;
        
        session_start();
		if (isset($_SESSION['desde'])){$desde->stringToDate($_SESSION['desde'], 'd/m/Y');}
		if (isset($_SESSION['hasta'])){$hasta->stringToDate($_SESSION['hasta'], 'd/m/Y');}
		if (isset($_SESSION['contadores'])){$contadores = $_SESSION['contadores'];}
		session_write_close();
		
		if (!isset($desde) && !isset($hasta) && !isset($contadores)){die;}
		
		set_time_limit(1000);
		$hasta->add(0,0,1);
        
        $Conn = new Conn('local', 'enertrade');
        $Conn2 = new Conn('local', 'cdc');
		foreach ($contadores as $contador){
            
			$contador = str_replace(" ", "_", $contador);
			
			//Crea tabla si no existe
			$strSQL = "CREATE TABLE IF NOT EXISTS `$contador` (
						  `Fecha` datetime NOT NULL,
						  `kW_Compra` int(11) NOT NULL,
						  `kW_Venta` int(11) NOT NULL,
						  `KVAr_C1` int(11) NOT NULL,
						  `KVAr_CAP_V` int(11) NOT NULL,
						  `KVAr_IND_V` int(11) NOT NULL,
						  `KVAr_C4` int(11) NOT NULL,
						  `Flags` int(11) NOT NULL,
						  `HORARIOS` int(11) DEFAULT NULL,
						  `kW_Compra_H` int(11) DEFAULT NULL,
						  `kW_Venta_H` int(11) DEFAULT NULL,
						  `KVAr_C1_H` int(11) DEFAULT NULL,
						  `KVAr_CAP_V_H` int(11) DEFAULT NULL,
						  `KVAr_IND_V_H` int(11) DEFAULT NULL,
						  `KVAr_C4_H` int(11) DEFAULT NULL
						) ENGINE=InnoDB DEFAULT CHARSET=utf16;";
			$Conn2->Query($strSQL);
			
			$Conn2->Query("ALTER TABLE `$contador` ADD PRIMARY KEY (`Fecha`)");
            
            $contador = str_replace("_", " ", $contador);
            $cups = $Conn->oneData("SELECT CUPS FROM datos_contadores WHERE CONTADOR = '$contador'");
            $contador = str_replace(" ", "_", $contador);
            
            if (!isset($cups) || empty($cups)){continue;}
            
            $CdC = new CdC($cups);
            $curva = $CdC->getCdC($desde->format('Y-m-d 00:00:00'), $hasta->format('Y-m-d 00:00:00'));
			foreach ($curva as $num_row=>$row){
                $linea = array();
                $linea['Fecha']     = date_format($row['fecha'], 'Y-m-d H:i:s');
                $linea['kW_Compra'] = $row['activa'];
                $linea['KVAr_C1']   = $row['reactiva'];
                $linea['Flags']     = '99';
                $final[] = $linea;
                unset($linea);
            }
            
            if (isset($final) && !empty($final)){
                $values = implode_values($final);
                $Conn2->Query("INSERT INTO $contador (Fecha, kW_Compra, KVAr_C1, Flags) VALUES $values ON DUPLICATE KEY UPDATE Fecha=VALUES(Fecha), kW_Compra=VALUES(kW_Compra), KVAr_C1=VALUES(KVAr_C1), Flags=VALUES(Flags)");
                unset($final);
            }
            
            unset($curva, $CdC, $cups);
        }
        unset($Conn, $Conn2);
        
        break;
        
	case "createCalendar":
		
		set_time_limit(0);
		
		$ano = $_POST['ano'];
		
		$fecha = new DateTime("$ano-01-01 00:15:00");
		
		//Comprueba que el calendario no exista
		$result = $Conn->getArray("SELECT * FROM nuevo_cal_periodos WHERE FECHA='".date_format($fecha, 'Y-m-d H:i:s')."'");
		
		if ($result){die;}
		
		//Recupera los datos para la creación del calendario
		$array = $Conn->getArray("SELECT * FROM temporadas_cal_periodo ORDER BY MES");
		foreach ($array as $num_row=>$row){$temporadas[$row['MES']] = $row;}
		unset($array);
		
		$array = $Conn->getArray("SELECT * FROM nuevos_periodos_por_hora");
		foreach ($array as $num_row=>$row){$periodos_hora[$row['HORA']] = $row;}
		unset($array);
		
		//Crea el calendario
		while ($fecha<=new DateTime(($ano+1)."-01-01 00:00:00")){
			
			switch (true){
				case (date_format($fecha, 'D') 		== 'Sun'):											//Si es domingo
				case (date_format($fecha, 'D') 		== 'Sat' 		&& date_format($fecha, 'G') > 0): 	//Si es sabado
				case (date_format($fecha, 'jM') 	== '1Jan' 		&& date_format($fecha, 'G') > 0):	//Si es 1/1/Y
				case (date_format($fecha, 'jM') 	== '6Jan' 		&& date_format($fecha, 'G') > 0):	//Si es 6/1/Y
				case (date_format($fecha, 'jM') 	== '1May' 		&& date_format($fecha, 'G') > 0):	//Si es 1/5/Y
				case (date_format($fecha, 'jM') 	== '15Aug' 		&& date_format($fecha, 'G') > 0):	//Si es 15/8/Y
				case (date_format($fecha, 'jM') 	== '12Oct' 		&& date_format($fecha, 'G') > 0):	//Si es 12/10/Y
				case (date_format($fecha, 'jM') 	== '1Nov' 		&& date_format($fecha, 'G') > 0):	//Si es 1/11/Y
				case (date_format($fecha, 'jM') 	== '6Dec' 		&& date_format($fecha, 'G') > 0):	//Si es 6/12/Y
				case (date_format($fecha, 'jM') 	== '8Dec' 		&& date_format($fecha, 'G') > 0):	//Si es 8/12/Y
				case (date_format($fecha, 'jM') 	== '25Dec' 		&& date_format($fecha, 'G') > 0):	//Si es 25/12/Y
				//La hora 00 del dia siguiente al festivo
				case (date_format($fecha, 'D') 		== 'Mon' 		&& date_format($fecha, 'G') == 0): 	//Si es sabado
				case (date_format($fecha, 'jM') 	== '2Jan' 		&& date_format($fecha, 'G') == 0):	//Si es 1/1/Y
				case (date_format($fecha, 'jM') 	== '7Jan' 		&& date_format($fecha, 'G') == 0):	//Si es 6/1/Y
				case (date_format($fecha, 'jM') 	== '2May' 		&& date_format($fecha, 'G') == 0):	//Si es 1/5/Y
				case (date_format($fecha, 'jM') 	== '16Aug' 		&& date_format($fecha, 'G') == 0):	//Si es 15/8/Y
				case (date_format($fecha, 'jM') 	== '13Oct' 		&& date_format($fecha, 'G') == 0):	//Si es 12/10/Y
				case (date_format($fecha, 'jM') 	== '2Nov' 		&& date_format($fecha, 'G') == 0):	//Si es 1/11/Y
				case (date_format($fecha, 'jM') 	== '7Dec' 		&& date_format($fecha, 'G') == 0):	//Si es 6/12/Y
				case (date_format($fecha, 'jM') 	== '9Dec' 		&& date_format($fecha, 'G') == 0):	//Si es 8/12/Y
				case (date_format($fecha, 'jM') 	== '26Dec' 		&& date_format($fecha, 'G') == 0):	//Si es 25/12/Y
					$PEN	 	= 6;
					$BAL 		= 6;
					$CAN 		= 6;
					$CEUTA 		= 6;
					$MELILLA 	= 6;
					$BT_PEN 	= 3;
					$BT_CM  	= 3;
					break;
					
				default:
					$hora = date_format($fecha, 'G');
					$mes = date_format($fecha, 'n');
					
					if 		(date_format($fecha, 'i') == 0 && $hora >= 1){--$hora;}
					elseif 	(date_format($fecha, 'i') == 0 && $hora == 0){
						$hora = 23;
						if 		(date_format($fecha, 'j') == 1 && $mes  > 1){--$mes;}
						elseif 	(date_format($fecha, 'j') == 1 && $mes == 1){$mes = 12;}
					}
					
					$PEN	 	= $periodos_hora[$hora]['PEN_'		.$temporadas[$mes]['PEN']];
					$BAL 		= $periodos_hora[$hora]['BAL_'		.$temporadas[$mes]['BAL']];
					
					$CAN 		= $periodos_hora[$hora]['CAN_'		.$temporadas[$mes]['CAN']];
					$CEUTA 		= $periodos_hora[$hora]['CEUTA_'	.$temporadas[$mes]['CEUTA']];
					$MELILLA 	= $periodos_hora[$hora]['MELILLA_'	.$temporadas[$mes]['MELILLA']];
                    $BT_PEN     = $periodos_hora[$hora]['BT_PEN'];
                    $BT_CM      = $periodos_hora[$hora]['BT_CM'];
					break;
			} //Selecciona casos festivos o horas 00:00
			
			$valores[] = "'".implode("', '", array(date_format($fecha, 'Y-m-d H:i:s'), $PEN, $BAL, $CAN, $CEUTA, $MELILLA, $BT_PEN, $BT_CM))."'";
			
			if((count($valores)%1000) == 0){
				$str_valores = "(".implode("),(", $valores).")";
				
				$strSQL = "INSERT INTO nuevo_cal_periodos (FECHA, PEN, BAL, CAN, CEUTA, MELILLA, BT_PEN, BT_CM) VALUES $str_valores ON DUPLICATE KEY UPDATE FECHA=VALUES(FECHA)";
				
				$Conn->Query($strSQL);
				unset($str_valores, $valores);
			}
			
			$fecha->modify('+15 minutes');
			
		} //Para cada fecha
		
		if (isset($valores)){
			$str_valores = "(".implode("),(", $valores).")";

			$strSQL = "INSERT INTO nuevo_cal_periodos (FECHA, PEN, BAL, CAN, CEUTA, MELILLA, BT_PEN, BT_CM) VALUES $str_valores ON DUPLICATE KEY UPDATE FECHA=VALUES(FECHA)";
			
			$Conn->Query($strSQL);
			unset($str_valores, $valores);
		}
		
		break;
        
    case 'update_potencias':
        
        $Conn = new Conn('local', 'enertrade');
        $contadores = $Conn->getArray('SELECT CONTADOR, CUPS FROM datos_contadores WHERE CUPS IS NOT NULL', true);
        unset($Conn);
        
        $cups = array_column($contadores, 'CUPS');
        $cups = "('".implode("', '", $cups)."')";
        
        //Recupera las potencias de la intranet de palomares
        $Conn = new Conn('mainsip', 'develop');
        $potencias = $Conn->getArray("SELECT a.CUPS, a.P1, a.P2, a.P3, a.P4, a.P5, a.P6 FROM clientes a INNER JOIN(SELECT CUPS, MAX(fecha_alta) maxalta FROM clientes GROUP BY CUPS) b ON a.CUPS=b.CUPS WHERE a.CUPS IN $cups AND a.fecha_alta=b.maxalta");
        unset($Conn);
        
        foreach ($contadores as $num_row=>$row){
            $contadores[$row['CUPS']] = $row['CONTADOR'];
            unset($contadores[$num_row]);
        }
        
        foreach ($potencias as $num_row=>$row){$potencias[$num_row]['CONTADOR'] = $contadores[$row['CUPS']];}
        unset($contadores);
        
        $values_potencias = implode_values($potencias);
        
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("INSERT INTO datos_contadores (CUPS, P1, P2, P3, P4, P5, P6, CONTADOR) VALUES $values_potencias ON DUPLICATE KEY UPDATE P1=VALUES(P1), P2=VALUES(P2), P3=VALUES(P3), P4=VALUES(P4), P5=VALUES(P5), P6=VALUES(P6)");
        unset($Conn, $potencias, $values_potencias);
        
        header('Location: datos_contadores.php');
        
        break;
		
	case 'addEnvioDiario':
		
		if (empty($_POST['grupo']) || empty($_POST['a'])){echo 'Los campos "Grupo" y "A" no pueden estar vacios'; die;}
		
		$grupo 			= $_POST['grupo'];
		$a 				= $_POST['a'];
		$copia 			= $_POST['copia'];
		$observaciones 	= $_POST['observaciones'];
		
		$dato = $Conn->oneData("SELECT GRUPO FROM envios_gestinel_diarios WHERE GRUPO='$grupo'");
		if ($dato){echo "El grupo especificado ya existe!"; die;}
		
		$Conn->Query("INSERT INTO envios_gestinel_diarios (GRUPO, A , COPIA, OBSERVACIONES) VALUES ('$grupo', '$a', '$copia', '$observaciones')");
		
		break;
    
}

unset($Conn);