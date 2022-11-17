<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");



switch ($_POST['action']){
		
	case "cdc":
		if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: op_conversiones.php");}
		
		set_time_limit(0);
		
		
		$headers_cdc = array('Periodo', 'Fecha', 'kW_Compra', 'KVAr_C1', 'Capacitiva');
		
		$filenum = 0;
		foreach($_FILES['fichero']['tmp_name'] as $file){
			$filename = $_FILES['fichero']['name'][$filenum];
			
			$CdC = array();
			
			$extension = pathinfo($filename, PATHINFO_EXTENSION);
			switch ($extension){
					
				case "html":
					
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
								case 'Dia'		:
								case 'Horas'	:
								case 'Cuarto'	:
								case 'Potencia:':
								case 'Calidad'	:
									$headers[] = $valor;
									break;
							}
						//Valores
						} elseif (substr($line, 0, 4)=="<td>") {
							
							$linea[$headers[$i]] = $valor;
							
							++$i;
							if ($i==5){
								
								$linea_def = array_fill_keys($headers_cdc, '');
								
								$date = date_create_from_format('d/m/Y', $linea['Dia']);
								$date = date_time_set($date, 0, 0, 0);
								$hour = $linea['Horas'];
								$minutes = 15*$linea['Cuarto'];
								$date->modify("+$hour hours");
								$date->modify("+$minutes minutes");
								
								$linea_def['Fecha'] 	= date_format($date, 'd/m/Y H:i');
								settype($linea['Potencia:'], 'float');
								$linea_def['kW_Compra'] = $linea['Potencia:'];
								
								$CdC[] = $linea_def;
								
								unset($linea, $linea_def, $hour, $minutes, $date);
								$i=0;
							}
						}
						unset($valor, $line);
					}//Hasta el final del fichero
					fclose($fopen);
					unset ($fopen);
					
					$CUPS = str_replace(".$extension", "", $filename);
					
					break;
					
				case "csv":
					
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

                                    ++$filenum;
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
                                
                                /*
                                //Para cada periodo
                                for ($x=1; $x<=4; $x++){

                                    settype($row['Consumo (kwh)'], 'float');

                                    switch ($x){
                                        case 1: $cuarto = 15; break;
                                        case 2: $cuarto = 30; break;
                                        case 3: $cuarto = 45; break;
                                        case 4: $cuarto = '00'; break;
                                    }
                                    $linea = array_fill_keys($headers_cdc, '');
                                    $fecha = $date->fromToFormat($row['FECHA'].' '.$row['HORA'], 'Ymd H', 'd/m/Y H:'.$cuarto);
                                    $date->fromXl($row['Fecha de la lectura']+$row['Hora de la lectura']);

                                    $linea['Fecha']     = $date->format('d/m/Y H:'.$cuarto);
                                    $linea['kW_Compra'] = $row['Consumo (kwh)'];
                                    $CdC[] = $linea;

                                    unset($linea);
                                }
                                unset($array[$num_row], $date);
                                
                                unset($array);
                                */
						}
						
						$CdC[] = $linea;
						unset($linea, $line, $date);
						
					}//Hasta el final del fichero
					
					fclose($fopen);
					unset($fopen);
					
					break;
					
				case "xlsx":
					
					
					//NATURGY, IBERDROLA y TOTAL
					
					$SprdSht = new SprdSht;
					$SprdSht->load($file, true);
					$SprdSht->activeSheet();
					
					//NATURGY
					if (trim($SprdSht->getCellValue("A3")) == 'CUPS:'){
						
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
							
							$CdC[] = $linea;
							unset($linea, $date);
						}//Para cada linea
						unset($array);
						
					//TOTAL
					} elseif (trim($SprdSht->getCellValue("A2")) == 'Category'){
						
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
								$CdC[] = $linea;
							}
							
							unset($linea, $date, $array[$num_row]);
						}//Para cada linea
						
						unset($array);
						break;
				
                    //GESTINEL
                    } elseif (trim($SprdSht->getCellValue("A1")) == 'Periodo') {
                        
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
                            
                            $CdC[] = $linea;
                            
                            unset($linea, $array[$num_row], $date);
                        }
						
                        unset($array);
                        
                        
                    //NATURGY otro formato
                    } elseif (trim($SprdSht->getCellValue("A1")) == 'ID_CUPS') {
                        
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
                                
                                ++$filenum;
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
                        
                        
					//IBERDROLA
					} else {
						
						if ($SprdSht->sheetExists('Sheet1')){$SprdSht->getSheet('Sheet1');}				//Si cuartohoraria potencia
						else 								{$SprdSht->getSheet('Datos exportados');}	//Si horario activa
						
						$array = $SprdSht->getArray(true);
						unset($SprdSht);
						
						$CUPS 	= substr(trim($array[0]['CUPS']), 0, 20);
						
						foreach ($array as $num_row=>$row){
							
							if (empty($row['FECHA HORA']) || !isset($row['FECHA HORA'])){continue;}
							
                            if (substr(trim($row['CUPS']), 0, 20)!= $CUPS){
                                $fopen = fopen($CUPS.".csv", "a");
                                foreach ($CdC as $num_cdc=>$row_cdc){fputcsv($fopen, $row_cdc, ";");}
                                fclose($fopen);
                                unset($fopen);
                                
                                ++$filenum;
			                    $files[] = $CUPS.".csv";
                                
                                $CUPS = substr(trim($row['CUPS']), 0, 20);
                                unset($CdC);
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
									$CdC[] = $linea;
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
								
								$CdC[] = $linea;
							}
							
							unset($linea, $date, $max, $val, $array[$num_row]);
						}//Para cada linea
						unset($array);
					}//if NATURGY, IBERDROLA o TOTAL
					
					break;
					
				default:
					++$filenum;
					unset($filename, $extension);
					continue(2);
			}//switch extensión del fichero
			
			unset($file);
			
			$fopen = fopen($CUPS.".csv", "a");
			foreach ($CdC as $num_row=>$row){fputcsv($fopen, $row, ";");}
			fclose($fopen);
			unset($fopen);
			
			//Guarda el fichero
			++$filenum;
			$files[] = $CUPS.".csv";
			
			unset($CUPS);
		}//Para cada fichero
		
		merge_and_dwd_zip('CdC elaboradas.zip', $files);
}//switch actions


?>

