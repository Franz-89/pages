<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");


function cargar_cups(){

	//Listado
	if (!empty($_FILES['fichero']['tmp_name'][0])){
        $SprdSht = new SprdSht;
        $SprdSht->load($_FILES['fichero']['tmp_name'][0]);
        $cups = $SprdSht->getArray(true);
        unset($SprdSht);
	}
	
	if (isset($cups) && !empty($cups))	{return array_column($cups, 'CUPS');}
	else 				                {return false;}
}

switch ($_POST['action']){
		
	case 'huecos_detallados':
		
		$lista = cargar_cups();
		
		foreach ($lista as $num_cups=>$cups){
			$CdC = new CdC($cups, NULL, NULL, true);
			$detalle = $CdC->getDetailedHuecos();
			if ($detalle['HUECOS']){
				foreach ($detalle['HUECOS'] as $num_row=>$row){$huecos[] = $row;}
			}
			if ($detalle['CEROS']){
				foreach ($detalle['CEROS'] as $num_row=>$row){$ceros[] = $row;}
			}
			unset($detalle, $CdC);
		}
		
		if (isset($huecos) || isset($ceros)){
			$SprdSht = new SprdSht();
			$SprdSht->nuevo();
			switch (true){
				case (isset($huecos) && !isset($ceros)): $SprdSht->putArray($huecos, true); break;
				case (isset($ceros) && !isset($huecos)): $SprdSht->putArray($ceros, true); break;
				case (isset($ceros) && isset($huecos)):
					$SprdSht->putArray($huecos, true);
					$SprdSht->addSheet('CEROS');
					$SprdSht->putArray($ceros, true);
					break;
			}
			
			$SprdSht->directDownload("Huecos detallados");
			unset($SprdSht, $huecos);
		} else {
			header ("Location: CdC.php");
		}
		
		break;
		
	case 'optimizar':
		
        set_time_limit(0);
        
		$lista = cargar_cups();
        $detalle = (isset($_POST['detalle'])) ? true : false;
        $tresunoaseisdos = (isset($_POST['tresunoaseisdos'])) ? true : false;
        $ano_excesos = $_POST['ano_excesos'];
        
        if ($ano_excesos<2021){break;}
        
        if (isset($_POST['desde']) && !empty($_POST['desde'])){
            $desde = new DateClass;
            $desde = $desde->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d 00:00:00');
        }
        
        if (isset($_POST['hasta']) && !empty($_POST['hasta'])){
            $hasta = new DateClass;
            $hasta = $hasta->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d 00:00:00');
        }
		
        if (!isset($desde)){$desde = '';}
        if (!isset($hasta)){$hasta = '';}
        
        $x = 0;
        if ($lista){
            
            foreach($lista as $num_row=>$cups){
                $CdC = new CdC($cups);
                if ($CdC->getFichero($cups)){
                    $opt = $CdC->opt($desde, $hasta, $detalle, $tresunoaseisdos, $ano_excesos);
                    
                    if 		(is_array($opt))   {$BT[] 		= $opt;}
                    elseif 	(is_string($opt))  {$files[] 	= $opt.".xlsx";}
                    else 					   {$errores[] = array($cups, 'NO HAY DATOS');}
                    unset($CdC, $opt);
                    ++$x;
                } else {
                    $errores[] = array($cups, 'NO HAY CURVA');
                }
                unset($CdC);
            }
            
        //Si no se ha cargado ningún fichero .sqlite3
        } else {
            
            $cli = $_POST['cli'];
            $Conn = new Conn('mainsip', 'develop');
            $cups = $Conn->getArray("SELECT DISTINCT CUPS FROM clientes WHERE GRUPO='$cli' AND estado='EN VIGOR'", true);
            unset($Conn);
            
            foreach ($cups as $num_row=>$row){
                $CdC = new CdC($row['CUPS']);
                if ($CdC->getFichero($row['CUPS'])){
                    $opt = $CdC->opt($desde, $hasta, $detalle, $tresunoaseisdos, $ano_excesos);

                    if 		(is_array($opt))    {$BT[] 		= $opt;}
                    elseif 	(is_string($opt))   {$files[] 	= $opt.".xlsx";}
                    else 					    {$errores[] = array($row['CUPS'], 'NO HAY DATOS');}
                    unset($opt);
                } else {
                    $errores[] = array($row['CUPS'], 'NO HAY CURVA');
                }
                unset($CdC);
            }
        }
		
		if (isset($BT)){
			$SprdSht = new SprdSht();
			$SprdSht->nuevo();
			$SprdSht->putArray($BT, true);
			$SprdSht->save("Optimización");
			unset($SprdSht);
			$files[] = "Optimización.xlsx";
		}
		
		if (isset($errores)){
			$SprdSht = new SprdSht();
			$SprdSht->nuevo();
			$SprdSht->putArray($errores);
			$SprdSht->save("Err_opt");
			unset($SprdSht);
			$files[] = "Err_opt.xlsx";
		}
		
        if (!isset($files) || empty($files)){
            header ('Location: CdC.php');
        } else {
            merge_and_dwd_zip('Optimización.zip', $files);
        }
		
		break;
		
	case 'maxima_todos':
		
        set_time_limit(0);
        
        //Saca las fechas (mandatorias)
		if (isset($_POST['desde']) && !empty($_POST['desde'])){
            $desde_date = new DateClass;
            $desde = $desde_date->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d 00:00:00');
        } else {
            header ("Location: CdC.php");
        }
        
        if (isset($_POST['hasta']) && !empty($_POST['hasta'])){
            $hasta_date = new DateClass;
            $hasta = $hasta_date->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d 00:00:00');
        } else {
            header ("Location: CdC.php");
        }
        
        //Recupera los CUPS del cliente seleccionado
        $cli = $_POST['cli'];
        $Conn = new Conn('mainsip', 'develop');
        $cups = $Conn->getArray("SELECT DISTINCT CUPS FROM clientes WHERE GRUPO='$cli' AND estado='EN VIGOR'", true);
        unset($Conn);

        $interval_horario        = date_diff($hasta_date->vardate, $desde_date->vardate)->format('%a')*24;
        $interval_cuartohorario  = $interval_horario*4;
        
        //Scaca la CdC de cada CUPS y los datos
        foreach ($cups as $num_row=>$row){
            
            $CdC = new CdC($row['CUPS']);
            if ($CdC->getFichero($row['CUPS'])){
                
                $curva = $CdC->getCdC($desde, $hasta);
                unset($CdC);

                $headers = array('CUPS', '%', 'TIPO', 'MAX_P1', 'MAX_P2', 'MAX_P3', 'MAX_P4', 'MAX_P5', 'MAX_P6', 'FECHA_MAX_P1', 'FECHA_MAX_P2', 'FECHA_MAX_P3', 'FECHA_MAX_P4', 'FECHA_MAX_P5', 'FECHA_MAX_P6', 'ACT_P1', 'ACT_P2', 'ACT_P3', 'ACT_P4', 'ACT_P5', 'ACT_P6', 'REACT_P1', 'REACT_P2', 'REACT_P3', 'REACT_P4', 'REACT_P5', 'REACT_P6');
                
                
                if (is_array($curva)){
                    
                    $linea = array_fill_keys($headers, 0);
                    $linea['CUPS'] = $row['CUPS'];
                    
                    //Comprueba indicativamente si la curva es horaria o cuartohoraria
                    if (abs(count($curva)-$interval_horario) < abs(count($curva)-$interval_cuartohorario)){
                        $linea['TIPO'] = 'HORARIA';
                        $linea['%'] = (count($curva)/$interval_horario)*100;
                    } else {
                        $linea['TIPO'] = 'CUARTOHORARIA';
                        $linea['%'] = (count($curva)/$interval_cuartohorario)*100;
                    }
                    
                    //Saca los datos
                    foreach ($curva as $num_row_curva=>$row_curva){
                        settype($row_curva['activa'], 'float');
                        settype($row_curva['reactiva'], 'float');
                        $periodo = $row_curva['periodo'];
                        if ($row_curva['activa'] > $linea["MAX_P$periodo"]){
                            $linea["MAX_P$periodo"]         = $row_curva['activa'];
                            $linea["FECHA_MAX_P$periodo"]   = $row_curva['fecha'];
                        }
                        $linea["ACT_P$periodo"]     += $row_curva['activa'];
                        $linea["REACT_P$periodo"]   += $row_curva['reactiva'];
                    }
                } else {
                    $linea          = array_fill_keys($headers, 0);
                    $linea['CUPS']  = $row['CUPS'];
                    $linea['TIPO']  = 'NO HAY DATOS';
                }

                $final[] = $linea;

                unset($linea, $curva);
            }
            unset($CdC);
        }
		
		if (isset($final)){
			$SprdSht = new SprdSht();
			$SprdSht->nuevo();
			$SprdSht->putArray($final, true);
			$SprdSht->directDownload('Elaboración');
			unset($SprdSht, $final);
		}
        
        break;
        
        
    case 'download':
        
        set_time_limit(0);
        
		$lista = cargar_cups();
        
        if (isset($_POST['desde']) && !empty($_POST['desde'])){
            $desde = new DateClass;
            $desde = $desde->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d 00:00:00');
        }
        
        if (isset($_POST['hasta']) && !empty($_POST['hasta'])){
            $hasta = new DateClass;
            $hasta = $hasta->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d 00:00:00');
        }
		
        if (!isset($desde)){$desde = '';}
        if (!isset($hasta)){$hasta = '';}
        
        if ($lista){
            
            foreach ($lista as $num_row=>$cups){
                $CdC = new CdC($cups);
                if ($CdC->getFichero($cups)){
                        
                    $CdC->getCdC($desde = '', $hasta = '');
                    $CdC->save();

                    $files[] = "$cups.xlsx";
                } else {
                    $errores[] = array($cups, 'NO HAY CURVA');
                }
                unset($CdC);
            }
            
            
        //Si no se ha cargado ningún fichero .sqlite3
        } else {
            
            $cli = $_POST['cli'];
            $Conn = new Conn('mainsip', 'develop');
            $cups = $Conn->getArray("SELECT DISTINCT CUPS FROM clientes WHERE GRUPO='$cli' AND estado='EN VIGOR'", true);
            unset($Conn);

            foreach ($cups as $num_row=>$row){
                $CdC = new CdC($row['CUPS']);
                if ($CdC->getFichero($row['CUPS'])){
                    $CdC->getCdC($desde = '', $hasta = '');
                    $CdC->save();

                    $files[] = $row['CUPS'].'.xlsx';
                } else {
                    $errores[] = array($row['CUPS'], 'NO HAY CURVA');
                }
                unset($CdC);
            }
        }
        
        if (isset($errores)){
			$SprdSht = new SprdSht();
			$SprdSht->nuevo();
			$SprdSht->putArray($errores);
			$SprdSht->save("Err");
			unset($SprdSht);
			$files[] = "Err.xlsx";
		}
		
        if (!isset($files) || empty($files)){
            header ('Location: CdC.php');
        } else {
            merge_and_dwd_zip('Curvas.zip', $files);
        }
        
        break;
        
    case "conversion":
		if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: CdC.php");}
		
		set_time_limit(0);
		
        function insertCdcLine($CUPS, $linea){
            $fopen = fopen($CUPS.".csv", "a");
            fputcsv($fopen, $linea, ";");
            fclose($fopen);
            unset($fopen);
        }
		
		$headers_cdc = array('Periodo', 'Fecha', 'kW_Compra', 'KVAr_C1', 'Capacitiva');
		
		$filenum = 0;
		foreach($_FILES['fichero']['tmp_name'] as $file){
			$filename = $_FILES['fichero']['name'][$filenum];
			
			$CdC = array();
			
			$extension = pathinfo($filename, PATHINFO_EXTENSION);
			switch ($extension){
					
				case 'html':
                    
                    //Saca el array asociativo desde la tabla html
					$datos = array();
					$headers = array();
					$fopen = fopen($file, 'r');
					
					$i = 0;
					while (!feof($fopen)) {
						$line 	= fgets($fopen);
						$line 	= trim($line);
						$valor 	= trim(str_replace("<td>", "", str_replace("</td>", "", $line)));
                        
						//Headers
						if (count($headers)<5){
							
							switch ($valor){
                                //HTML_ENDESA
								case 'Dia'		:
								case 'Horas'	:
								case 'Cuarto'	:
								case 'Potencia:':
								case 'Calidad'	:
									$headers[] = $valor;
									break;
							}
                        } else {
                            break;
                        }
                    }
                    fclose($fopen);
					unset ($fopen);
                    
                    switch (true){
                        case (isset($headers[0]) && $headers[0]=='Dia'):
                            $tipo_fichero = 'HTML_ENDESA';
                            break;
                        default:
                            $tipo_fichero = 'HTML_2';
                            break;
                    }
                    unset($headers);
                    
                    break;
                    
                    
                case 'csv':
                    $tipo_fichero = 'CSV';
                    break;
                        
                case 'xlsx':
                    
					//NATURGY, IBERDROLA, ENDESA y TOTAL
					
					$SprdSht = new SprdSht;
					$SprdSht->load($file, true);
					$SprdSht->activeSheet();
					
					
                    switch (true){
                            
                        case (trim($SprdSht->getCellValue('A3')) == 'CUPS:'):
                            $tipo_fichero = 'XLSX_NATURGY';
                            break;
                        case (trim($SprdSht->getCellValue('A3')) == 'Fecha Inicio'):
                            $tipo_fichero = 'XLSX_IB';
                            break;
                        case (trim($SprdSht->getCellValue('A2')) == 'Category'):
                            $tipo_fichero = 'XLSX_TOTAL';
                            break;
                        case (trim($SprdSht->getCellValue('A1')) == 'Periodo'):
                            $tipo_fichero = 'XLSX_GESTINEL';
                            break;
                        case (trim($SprdSht->getCellValue('A1')) == 'ID_CUPS'):
                            $tipo_fichero = 'XLSX_NATURGY_2';
                            break;
                        case (trim($SprdSht->getCellValue('A1')) == 'cups_ree'):
                            $tipo_fichero = 'XLSX_ENDESA';
                            break;
                        case (trim($SprdSht->getCellValue('C1')) == 'Energía AE (kWh)'):
                            $tipo_fichero = 'XLSX_ENDESA_2';
                            break;
                        case (trim($SprdSht->getCellValue('A1')) == 'CUPS'):
                            $tipo_fichero = 'XLSX_ELEIA';
                            break;
                        default:
                            $tipo_fichero = 'XLSX_IBERDROLA';
                            break;
                    }
                    
                    break;
                    
                case 'xls':
                    
                    $SprdSht = new SprdSht;
					$SprdSht->load($file, true, 'Xls');
					$SprdSht->activeSheet();
					
					//NATURGY
                    switch (true){
                        case (trim($SprdSht->getCellValue("A3")) == 'CUPS:'):
                            $tipo_fichero = 'XLS_NATURGY';
                            break;
                        case (trim($SprdSht->getCellValue("A1")) == 'Fecha'):
                            $tipo_fichero = 'XLS_EDP';
                            break;
                    }
                    
                    break;
                
                //Siguiente fichero si no reconoce la extensión
                default:
					++$filenum;
					unset($filename, $extension);
					continue(2);
            }
            
            
            switch ($tipo_fichero){
                case 'HTML_ENDESA':
                    
					//Saca el array asociativo desde la tabla html
					$datos = array();
					$headers = array();
					$fopen = fopen($file, 'r');
					
					$i = 0;
					while (!feof($fopen)) {
						$line 	= fgets($fopen);
						$line 	= trim($line);
						$valor 	= trim(str_replace(array('<td>', '</td>'), array('', ''), $line));
                        
						//Headers
						if (count($headers)<5){
							
							switch ($valor){
                                //HTML_ENDESA
								case 'Dia'		:
								case 'Horas'	:
								case 'Cuarto'	:
								case 'Potencia:':
								case 'Calidad'	:
									$headers[] = $valor;
									break;
							}
                        } else {
                        
                            if (substr($line, 0, 4)=="<td>") {

                                $linea[$headers[$i]] = $valor;

                                ++$i;
                                if ($i==5){

                                    $linea_def = array_fill_keys($headers_cdc, '');

                                    $date = new DateClass;
                                    $date->stringToDate($linea['Dia'], 'd/m/Y');
                                    $date->hourZero();
                                    $hour = $linea['Horas'];
                                    $minutes = 15*$linea['Cuarto'];
                                    $date->add(0,0,0,$hour);
                                    $date->add(0,0,0,0,$minutes);

                                    $linea_def['Fecha'] 	= $date->format('d/m/Y H:i');
                                    settype($linea['Potencia:'], 'float');
                                    $linea_def['kW_Compra'] = $linea['Potencia:'];

                                    $CdC[] = $linea_def;

                                    unset($linea, $linea_def, $hour, $minutes, $date);
                                    $i=0;
                                }
                            }
                            unset($valor, $line);
                        }
					}//Hasta el final del fichero
					fclose($fopen);
					unset ($fopen);
					
					$CUPS = str_replace(".$extension", "", $filename);
					
					break;
                    
                case 'HTML_2':
                    
					//Saca el array asociativo desde la tabla html
					$datos = array();
					$headers = array();
					$fopen = fopen($file, 'r');
					
					$i = 0;
                    $x = 1;
					while (!feof($fopen)) {
						$line 	= fgets($fopen);
						$line 	= trim($line);
						$valor 	= trim(str_replace(array('<td>', '</td>'), array('', ''), $line));
						$valor2 = trim(str_replace(array('<th>', '</th>'), array('', ''), $line));
						
                        //Headers
                        $cnt_headers = 5;
						if (count($headers)<$cnt_headers){
							
							switch ($valor2){
                                //HTML_ENDESA
								case 'CUPS':
								case 'FECHA LECTURA':
								case 'HORA':
								case 'CUARTO':
								case 'ACTIVA (kW)':
								case 'PERIODO':
									$headers[] = $valor2;
									break;
							}
                            $cnt_headers = (in_array('CUARTO', $headers)) ? 6 : 5;
                        } else {
                        
                            if (substr($line, 0, 4)=='<td>') {

                                $linea[$headers[$i]] = $valor;

                                ++$i;
                                
                                //Cuando ha sacado los datos de todos los encabezados para una linea crea la linea_def
                                if ($i==6){
                                    $linea_def = array_fill_keys($headers_cdc, '');

                                    $date = new DateClass;
                                    $date->stringToDate($linea['FECHA LECTURA'], 'd/m/Y');
                                    $date->hourZero();
                                    $hour = $linea['HORA'];
                                    $minutes = 15*$linea['CUARTO'];
                                    $date->add(0,0,0,$hour);
                                    $date->add(0,0,0,0,$minutes);

                                    $linea_def['Fecha'] 	= $date->format('d/m/Y H:i');
                                    settype($linea['ACTIVA (kW)'], 'float');
                                    $linea_def['kW_Compra'] = $linea['ACTIVA (kW)'];

                                    $CdC[] = $linea_def;

                                    unset($linea, $linea_def, $hour, $minutes, $date);
                                    $i=0;
                                }
                            }
                            unset($valor, $line);
                        }
					}//Hasta el final del fichero
                    die;
					fclose($fopen);
					unset ($fopen);
                    
					$CUPS = str_replace(".$extension", "", $filename);
					
					break;
					
                    
				case 'CSV':
					
					$fopen = fopen($file, 'r');
					
					$esActivo = false;
					while (!feof($fopen)) {
						
						$linea 	= array_fill_keys($headers_cdc, '');
						$line = fgetcsv($fopen, 0, ';');
						
						if (empty($line[1])){
							unset($line, $linea);
							continue;
						}
						
						
						//Detecta la comercializadora
						if (!isset($comm)){
                            switch (trim(preg_replace('/\t+/', '', $line[0]))){
                                case 'Type': $comm = 'ENGIE';   break;
                                case 'Cups': $comm = 'NATURGY'; break;
                                default:     $comm = 'EDP';     break;
                            }
                        }
						
						switch ($comm){
							//EDP
							case 'EDP':
								
								if (!$esActivo){
									if ($line[0]=='Fecha'){$esActivo = true; continue(2);} else {$CUPS = substr(trim($line[1]), 0, 20); continue(2);}
								} elseif (!empty(trim($line[0]))) {
									
									$date = date_create_from_format('d/m/Y G:i', $line[0]);
									$date->modify('+15 minutes');
									
									$linea['Fecha'] 	= date_format($date, 'd/m/Y H:i');
									$linea['Periodo'] 	= $line[3];
									$linea['kW_Compra'] = $line[1];
									$linea['KVAr_C1'] 	= $line[2];
								}
								break;
								
							//ENGIE
							case 'ENGIE':
								
								if ($line[0]=='Type' || empty($line[4])){continue(2);}
								
								if (!isset($CUPS)){$CUPS = $line[1];}
								
								$date = date_create_from_format('Y-m-d H:i', str_replace('H', ' ', $line[3]));
								$date->modify('-45 minutes');
								$linea['Fecha'] 	= date_format($date, 'd/m/Y H:i');
								$linea['kW_Compra'] = $line[4];
								break;
                                
                            //NATURGY CSV
                            case 'NATURGY':
                                
                                if (trim(preg_replace('/\t+/', '', $line[0]))=='Cups' || empty($line[0])){continue(2);}
                                $headers_cdc = array('Periodo', 'Fecha', 'kW_Compra', 'KVAr_C1', 'Capacitiva');
                                
                                if (!isset($CUPS)){
                                    $CUPS = substr(trim(preg_replace('/\t+/', '', $line[0])), 0, 20);
                                }
                                

                                //Si cambia el CUPS crea el fichero relativo al CUPS anterior y luego sigue
                                if (substr(trim(preg_replace('/\t+/', '', $line[0])), 0, 20)!= $CUPS){
                                    $new_fopen = fopen($CUPS.".csv", "a");
                                    foreach ($CdC as $num_cdc=>$row_cdc){fputcsv($new_fopen, $row_cdc, ";");}
                                    fclose($new_fopen);
                                    unset($new_fopen);

                                    $files[] = $CUPS.".csv";

                                    $CUPS = substr(trim(preg_replace('/\t+/', '', $line[0])), 0, 20);
                                    unset($CdC);
                                    $CdC = array();
                                }
                                
                                $date = new DateClass;
                                
                                $linea = array_fill_keys($headers_cdc, '');
                                
                                $linea['Fecha']     = $date->fromToFormat($line[1].' '.$line[2], 'd/m/Y G:i', 'd/m/Y H:i');
                                $linea['kW_Compra'] = $line[3];
                                break;
                                
						}
						
						$CdC[] = $linea;
						unset($linea, $line, $date);
						
					}//Hasta el final del fichero
					
					fclose($fopen);
					unset($fopen);
					
					break;
					
                    
				case 'XLSX_NATURGY':
					
                    $CUPS = substr(trim($SprdSht->getCellValue("B3")), 0, 20);

                    $array = $SprdSht->getArray();
                    unset($SprdSht);
                    
                    
                    foreach ($array as $num_row=>$row){$array[$num_row]=array_filter($row);}
                    
                    foreach ($array as $num_row=>$row){

                        if (!isset($row[0]) || $row[0]=='Fecha' || $row[0]=='Data' || $row[0]=='CUPS:' || empty($row[0])){continue;}

                        $linea = array_fill_keys($headers_cdc, '');

                        $date = date_create_from_format('j/m/Y G:i', $row[0]);

                        settype($row[1], 'float');
                        $linea['Fecha'] 	= date_format($date, 'd/m/Y H:i');
                        $linea['kW_Compra'] = $row[1]*4;

                        insertCdcLine($CUPS, $linea);
                        unset($linea, $date);
                    }//Para cada linea
                    unset($array);

                    break;
                    
                case 'XLSX_IB':
					
                    $CUPS = str_replace('.xlsx', '', $filename);

                    $array = $SprdSht->getArray();
                    unset($SprdSht);
                    
                    $Array = new ArrayClass($array);
                    $array = $Array->arrayToAssoc(2);
                    unset($Array);
                    
                    $date = new DateClass;
                    foreach ($array as $num_row=>$row){

                        if (empty($row['Fecha Inicio'])){continue;}
                        $date->stringToDate($row['Fecha Inicio'], 'd/m/Y H:i');
                        
                        for ($x=1;$x<=4;$x++){
                            
                            $date->add(0,0,0,0,15);
                            $linea = array_fill_keys($headers_cdc, '');
                            
                            $linea['Fecha'] 	= $date->format('d/m/Y H:i');
                            $linea['kW_Compra'] = $row['Total (kWh)'];
                            
                            insertCdcLine($CUPS, $linea);
                            unset($linea);
                        }
                        
                        
                        
                    }//Para cada linea
                    unset($array);

                    break;
                    
                    
                case 'XLSX_TOTAL':

                    $CUPS = substr(trim(str_replace('Activa', '', $SprdSht->getCellValue("B2"))), 0, 20);

                    $array = $SprdSht->getArray();
                    unset($SprdSht);

                    foreach($array as $num_row=>$row){

                        if ($num_row<2){continue;}

                        $linea = array_fill_keys($headers_cdc, '');

                        $date = str_replace(array('(', ')'), array('', ''), $row[0]);
                        $strMes = array('ene.', 'feb.', 'mar.', 'abr.', 'may.', 'jun.', 'jul.', 'ago.', 'sep.', 'oct.', 'nov.', 'dic.');
                        $numMes = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
                        $date = str_replace($strMes, $numMes, $date);

                        $date = date_create_from_format('d m Y H:i', $date);
                        $date->modify('-1 hour');

                        for ($x=1; $x<=4; $x++){
                            $date->modify('+15 minutes');
                            $linea['Fecha'] 	= date_format($date, 'd/m/Y H:i');
                            $linea['kW_Compra'] = $row[1];
                            $linea['KVAr_C1'] 	= $row[2];
                            insertCdcLine($CUPS, $linea);
                        }

                        unset($linea, $date, $array[$num_row]);
                    }//Para cada linea
                    unset($array);

                    break;
                    
                    
                case 'XLSX_GESTINEL':
                    $CUPS = trim($SprdSht->getCellValue("R1"));

                    $array = $SprdSht->getArray(true);
                    unset($SprdSht);

                    foreach($array as $num_row=>$row){
                        if (empty($row)){continue;}

                        $date = new DateClass;
                        $linea = array_fill_keys($headers_cdc, '');

                        foreach ($row as $key=>$value){
                            switch ($key){
                                case 'Capacitiva': $linea[$key] = $row['KVAr_C4'];                                              break;
                                case 'Fecha'     : $linea[$key] = $date->fromToFormat($row[$key], 'd/m/Y H:i:s', 'd/m/Y H:i');  break;
                                case 'Periodo'   : 
                                case 'kW_Compra' :
                                case 'KVAr_C1'   :
                                    $linea[$key] = $row[$key];                                                                  break;
                                default          :                                                                              break;
                            }
                        }

                        insertCdcLine($CUPS, $linea);

                        unset($linea, $array[$num_row], $date);
                    }
                    unset($array);

                    break;
                    
                    
                case 'XLSX_NATURGY_2':

                    $headers_cdc = array('Periodo', 'Fecha', 'kW_Compra', 'KVAr_C1', 'Capacitiva');

                    $array = $SprdSht->getArray(true);
                    unset($SprdSht);

                    $CUPS = substr(trim($array[0]['ID_CUPS']), 0, 20);

                    foreach($array as $num_row=>$row){

                        if (empty($row) || ($row['TIPO_MEDIDA']!='AE' && $row['TIPO_MEDIDA']!='R1')){
                            unset($array[$num_row]);
                            continue;
                        }

                        //Si cambia el CUPS crea el fichero relativo al CUPS anterior y luego sigue
                        if (substr(trim($row['ID_CUPS']), 0, 20)!= $CUPS){
                            $fopen = fopen($CUPS.".csv", "a");
                            foreach ($CdC as $num_cdc=>$row_cdc){fputcsv($fopen, $row_cdc, ";");}
                            fclose($fopen);
                            unset($fopen);

                            $files[] = $CUPS.".csv";
                            $CUPS = substr(trim($row['ID_CUPS']), 0, 20);

                            unset($CdC);
                            $CdC = array();
                        }


                        $date = new DateClass;

                        //Para cada periodo
                        for ($x=1; $x<=4; $x++){

                            settype($row["MEDIDA_$x"], 'float');

                            switch ($x){
                                case 1: $cuarto = 15; break;
                                case 2: $cuarto = 30; break;
                                case 3: $cuarto = 45; break;
                                case 4: $cuarto = '00'; break;
                            }
                            $linea = array_fill_keys($headers_cdc, '');
                            $fecha = $date->fromToFormat($row['FECHA'].' '.$row['HORA'], 'Ymd H', 'd/m/Y H:'.$cuarto);

                            //Activa y reactiva se encuentran en lineas distintas, por lo tanto hay que juntarlas en una unica linea creando una referencia (la fecha)
                            if (array_key_exists($fecha, $CdC)){
                                switch ($row['TIPO_MEDIDA']){
                                    case 'AE': $CdC[$fecha]['kW_Compra'] = $row["MEDIDA_$x"]*4; break;
                                    case 'R1': $CdC[$fecha]['KVAr_C1'] = $row["MEDIDA_$x"]*4;   break;
                                }
                            } else {
                                $linea['Fecha'] = $fecha;
                                switch ($row['TIPO_MEDIDA']){
                                    case 'AE': $linea['kW_Compra'] = $row["MEDIDA_$x"]*4; break;
                                    case 'R1': $linea['KVAr_C1']   = $row["MEDIDA_$x"]*4; break;
                                }
                                $CdC[$fecha] = $linea;
                            }
                            unset($linea);
                        }
                        unset($array[$num_row], $date);
                    }
                    unset($array);

                    break;
                            
                    
                case 'XLSX_ENDESA':

                    $date = new DateClass;

                    $headers_cdc = array('Periodo', 'Fecha', 'kW_Compra', 'KVAr_C1', 'Capacitiva');

                    $array = $SprdSht->getArray(true);
                    unset($SprdSht);

                    $CUPS = substr(trim($array[0]['cups_ree_20']), 0, 20);

                    foreach($array as $num_row=>$row){

                        if (empty($row['fecha']) || !isset($row['fecha'])){continue;}

                        $date->stringToDate($row['fecha'], 'Ymd');
                        $date->hourZero();
                        $date->add(0,0,0,0,15);

                        if (substr(trim($row['cups_ree_20']), 0, 20)!= $CUPS){
                            $files[] = $CUPS.".csv";
                            $CUPS = substr(trim($row['cups_ree_20']), 0, 20);
                        }

                        for ($x=1; $x<=24; $x++){

                            $consumo = $row["activa1_h$x"];
                            for ($y=1; $y<=4; $y++){
                                $linea = array_fill_keys($headers_cdc, '');
                                $linea['Fecha'] = $date->format('d/m/Y H:i');
                                settype($row["activa1_h$x"], "float");
                                $linea['kW_Compra'] = $row["activa1_h$x"]/1000;
                                $linea['KVAr_C1'] = $row["reactiva1_h$x"];
                                $linea['Capacitiva'] = $row["reactiva4_h$x"];
                                insertCdcLine($CUPS, $linea);
                                $date->add(0,0,0,0,15);
                            }
                        }
                    }

                    break;
                    
                    
                case 'XLSX_ENDESA_2':

                    $date = new DateClass;

                    $headers_cdc = array('Periodo', 'Fecha', 'kW_Compra', 'KVAr_C1', 'Capacitiva');

                    $array = $SprdSht->getArray(true);
                    unset($SprdSht);
                    

                    foreach($array as $num_row=>$row){

                        if (empty($row['Fecha']) || !isset($row['Fecha'])){continue;}
                        
                        $CUPS = substr(trim($array[0]['CUPS22PM/CUPS20']), 0, 20);
                        
                        $date->fromXl($row['Fecha']);

                        if (substr(trim($row['CUPS22PM/CUPS20']), 0, 20)!= $CUPS){
                            $files[] = $CUPS.".csv";
                            $CUPS = substr(trim($row['CUPS22PM/CUPS20']), 0, 20);
                        }
                        
                        for ($y=1; $y<=4; $y++){
                            $linea = array_fill_keys($headers_cdc, '');
                            $date->add(0,0,0,0,15);
                            $linea['Fecha'] = $date->format('d/m/Y H:i');
                            $linea['kW_Compra'] = $row['Energía AE (kWh)'];
                            $linea['KVAr_C1'] = $row['Energía Reactiva Inductiva (kVarh)'];
                            $linea['Capacitiva'] = $row['Energía Reactiva capacitiva (kVarh)'];
                            insertCdcLine($CUPS, $linea);
                            unset($linea);
                        }
                    }

                    break;
                    
                case 'XLSX_IBERDROLA':

                    if ($SprdSht->sheetExists('Sheet1')){$SprdSht->getSheet('Sheet1');}				//Si cuartohoraria potencia
                    else 								{$SprdSht->getSheet('Datos exportados');}	//Si horario activa

                    $array = $SprdSht->getArray(true);
                    unset($SprdSht);

                    $CUPS 	= substr(trim($array[0]['CUPS']), 0, 20);

                    foreach ($array as $num_row=>$row){

                        if (empty($row['FECHA HORA']) || !isset($row['FECHA HORA'])){continue;}

                        if (substr(trim($row['CUPS']), 0, 20)!= $CUPS){
                            $files[] = $CUPS.".csv";
                            $CUPS = substr(trim($row['CUPS']), 0, 20);
                        }

                        $linea = array_fill_keys($headers_cdc, '');

                        $date = date_create_from_format('d/m/Y G:i', $row['FECHA HORA']);
                        $date = $date->modify('+15 minutes');


                        if (array_key_exists('AI', $row)){			//Si es curva horaria de la activa
                            $max = round($row['AI'], 2);
                            $linea['Periodo'] 	= $row['PERIODO_TA'];
                            $linea['kW_Compra'] = $max;
                            for ($i=1;$i<=4;$i++){
                                $linea['Fecha'] = date_format($date, 'd/m/Y H:i');
                                insertCdcLine($CUPS, $linea);
                                $date = $date->modify('+15 minutes');
                            }

                        } else {									//Si es curva cuartohoraria de potencia
                            $max = $row['Potencia Máxima P1'];
                            if (empty($max)){$max = 0;} else {settype($max, 'float');}
                            for ($i=2; $i<=6; ++$i){
                                $val = $row["Potencia Máxima P$i"];
                                if (empty($val)){$val = 0;} else {settype($val, 'float');}
                                $max = max($max, $val);
                            }

                            $linea['Fecha'] 	= date_format($date, 'd/m/Y H:i');
                            $linea['Periodo'] 	= $row['PERIODO_TA'];
                            $linea['kW_Compra'] = $max;

                            insertCdcLine($CUPS, $linea);
                        }

                        unset($linea, $date, $max, $val, $array[$num_row]);
                    }//Para cada linea
                    unset($array);

                    break;
                    
                    
                case 'XLS_NATURGY':
						
                    $CUPS = substr(trim($SprdSht->getCellValue("B3")), 0, 20);

                    $array = $SprdSht->getArray();
                    unset($SprdSht);


                    foreach ($array as $num_row=>$row){$array[$num_row]=array_filter($row);}

                    foreach ($array as $num_row=>$row){

                        if (!isset($row[0]) || $row[0]=='Fecha' || $row[0]=='Data' || $row[0]=='CUPS:' || empty($row[0])){continue;}

                        $linea = array_fill_keys($headers_cdc, '');

                        $date = date_create_from_format('j/m/Y G:i', $row[0]);

                        settype($row[1], 'float');
                        $linea['Fecha'] 	= date_format($date, 'd/m/Y H:i');
                        $linea['kW_Compra'] = $row[1];

                        insertCdcLine($CUPS, $linea);
                        unset($linea, $date);
                    }//Para cada linea
                    unset($array);
                    
                    break;
                    
                case 'XLS_EDP':
                    
                    $CUPS = substr(trim($SprdSht->getCellValue("B2")), 0, 20);

                    $array = $SprdSht->getArray(true);
                    unset($SprdSht);
                    
                    $date = new DateClass;
                    foreach ($array as $num_row=>$row){

                        if (!isset($row['CUPS']) || empty($row['CUPS'])){continue;}

                        $linea = array_fill_keys($headers_cdc, '');

                        $linea['Fecha'] 	= $date->fromToFormat($row['Fecha'], 'Y-m-d H:i:s', 'd/m/Y H:i');
                        $linea['kW_Compra'] = $row['Energia Activa'];
                        $linea['KVAr_C1'] = $row['Energia Reactiva'];
                        $linea['Capacitiva'] = $row['Energia Capacitiva'];

                        insertCdcLine($CUPS, $linea);
                        unset($linea);
                    }//Para cada linea
                    unset($array, $date);
                    
                    break;
                    
                case 'XLSX_ELEIA':
                    
                    $array = $SprdSht->getArray(true);
                    unset($SprdSht);
                    
                    
                    $date = new DateClass;
                    $date->fromXl($array[0]['FechaMedida']);
                    $uno = $date->format('i');
                    $date->fromXl($array[1]['FechaMedida']);
                    $dos = $date->format('i');
                    $horaria = ($uno==$dos) ? true : false;
                    
                    foreach ($array as $num_row=>$row){
                        
                        if (isset($CUPS) && $CUPS!=$row['CUPS']){$files[] = $CUPS.".csv";}
                        
                        $CUPS = substr(trim($row['CUPS']), 0, 20);
                        if (empty($CUPS)){continue;}
                        
                        $linea = array_fill_keys($headers_cdc, '');
                        
                        $date->fromXl($row['FechaMedida']);
                        
                        //Si es horaria la convierta a cuartohoraria
                        switch ($horaria){
                            case true:
                                
                                for ($x=1;$x<=4;$x++){
                                    $date->add(0,0,0,0,15);
                                    $linea['Fecha'] 	= $date->format('d/m/Y H:i');
                                    $linea['kW_Compra'] = $row['ActivaEntrante'];
                                    insertCdcLine($CUPS, $linea);
                                }
                                
                                break;
                                
                            case false:
                                
                                $linea['Fecha'] 	= $date->format('d/m/Y H:i');
                                $linea['kW_Compra'] = $row['ActivaEntrante'];
                                insertCdcLine($CUPS, $linea);
                                
                                break;
                        }
                        unset($linea);
                    }//Para cada linea
                    unset($array, $date);
                    
                    break;
            }//Switch tipo fichero

            
            unset($file);
			
            if (isset($CdC)){
                foreach ($CdC as $num_row=>$row){insertCdcLine($CUPS, $row);}
            }
			
			//Guarda el fichero
			++$filenum;
			$files[] = $CUPS.".csv";
			
			unset($CUPS);
        }//Para cada fichero
		merge_and_dwd_zip('CdC elaboradas.zip', $files);
        break;
}
header ("Location: CdC.php");

?>