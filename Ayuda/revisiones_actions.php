<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$action = $_POST['action'];


switch ($action){
	case 'PS':
	case 'BBDD':
    case 'desvio_consumo':
    case 'descarga_ps':
		$cli 	= $_POST['cli'];
		$CalculosSimples = new CalculosSimples;
		$ID 	= $CalculosSimples->getIdCliente($cli);
		unset($CalculosSimples);
		break;
}

$Conn 	= new Conn('mainsip', 'develop');

switch ($action){
	
	case "PS":
		
		$strSQL = 	"SELECT
						CUPS,
						FECHA_INICIO,
						FECHA_FIN,
						fecha_alta,
						estado
					FROM clientes
					WHERE Grupo='".$cli."'
					ORDER BY CUPS, fecha_inicio";
		
		
		$query = $Conn->Query($strSQL);
		
		$datos = array();
		
		//Divide los datos por CUPS
		while ($row = mysqli_fetch_assoc($query)){$datos[$row['CUPS']][] = $row;}
		unset($query);
		
		
		$val_fechas = array();
		$val_estado = array();
		
		foreach($datos as $CUPS=>$array_rows){	//Para cada CUPS
			
			$cnt_vigor 	= 0;
			$cnt_baja 	= 0;
			
			foreach($array_rows as $num_row=>$row){	//Para cada linea del CUPS
				for ($x=$num_row+1; $x<=count($array_rows)-1; $x++){	//Para cada linea a partir de la actual
					
					//Si la fecha alta está duplicada
					if ($row['fecha_alta'] == $array_rows[$x]['fecha_alta']){
						$linea = array_fill_keys(array('CUPS', 'fecha_alta', 'FECHA_INICIO', 'FECHA_FIN', 'OBSERVACIONES'), '');
						$linea['OBSERVACIONES'] = "Fecha de alta duplicada";
						foreach($row as $key=>$value){if (array_key_exists($key, $linea)){$linea[$key] = $row[$key];}}
						$val_fechas[] = $linea;
						unset($linea);
					}
					//Si hay solape de fechas
					if (($row['FECHA_INICIO'] >= $array_rows[$x]['FECHA_INICIO'] && $row['FECHA_INICIO'] <= $array_rows[$x]['FECHA_FIN'])
					   || ($row['FECHA_FIN'] >= $array_rows[$x]['FECHA_INICIO'] && $row['FECHA_FIN'] <= $array_rows[$x]['FECHA_FIN'])){
						$linea = array_fill_keys(array('CUPS', 'fecha_alta', 'FECHA_INICIO', 'FECHA_FIN', 'OBSERVACIONES'), '');
						$linea['OBSERVACIONES'] = "Solape de fechas";
						foreach($row as $key=>$value){if (array_key_exists($key, $linea)){$linea[$key] = $row[$key];}}
						$val_fechas[] = $linea;
						unset($linea);
					}
					
				} //Para cada linea a partir de la actual
				
				
				//Cuenta los estados
				switch ($row['estado']){
					case "EN VIGOR": ++$cnt_vigor; break;
					case "BAJA": ++$cnt_baja; break;
				}
			} // Para cada linea del CUPS
			
			//Si fecha fin < hoy
			if ($array_rows[count($array_rows)-1]['FECHA_FIN']<date('Y-m-d') && $array_rows[count($array_rows)-1]['estado']=="EN VIGOR"){
				$linea = array_fill_keys(array('CUPS', 'fecha_alta', 'FECHA_INICIO', 'FECHA_FIN', 'OBSERVACIONES'), '');
				$linea['OBSERVACIONES'] = "Fecha fin < hoy";
				foreach($row as $key=>$value){if (array_key_exists($key, $linea)){$linea[$key] = $array_rows[count($array_rows)-1][$key];}}
				$val_fechas[] = $linea;
				unset($linea);
			}
			
			//Si no tiene el mismo estado en todas las lineas
			if ($cnt_vigor>0 && $cnt_baja>0){
				$linea = array_fill_keys(array('CUPS', 'OBSERVACIONES'), '');
				$linea['OBSERVACIONES'] = "No tiene el mismo estado en todas las lineas";
				foreach($row as $key=>$value){if (array_key_exists($key, $linea)){$linea[$key] = $row[$key];}}
				$val_estado[] = $linea;
				unset($linea);
			}
			
			
		} //Para cada CUPS
		unset($array_rows, $datos);
		
		foreach ($val_fechas as $num_row=>$linea){
			$val_fechas[$num_row]['fecha_alta'] 	= date_sql_to_php($linea['fecha_alta']);
			$val_fechas[$num_row]['FECHA_INICIO'] 	= date_sql_to_php($linea['FECHA_INICIO']);
			$val_fechas[$num_row]['FECHA_FIN'] 		= date_sql_to_php($linea['FECHA_FIN']);
		}
		
		
		if (!isset($val_fechas[0]) && !isset($val_estado[0])){header ("Location: revisiones.php?cli=$cli");}
		
		$SprdSht = new SprdSht;
		$SprdSht->nuevo();
		
		// FECHAS
		if (isset($val_fechas[0])){
			$SprdSht->addSheet('FECHAS');
			$SprdSht->putArray($val_fechas, true);
		}
		
		// ESTADO
		if (isset($val_estado[0])){
			$SprdSht->addSheet('ESTADO');
			$SprdSht->putArray($val_estado, true);
		}
		
		$SprdSht->directDownload("Revisión PS $cli");
		unset($SprdSht);
		
		break;
		
	//REVISIÓN DE LAS ULTIMAS FECHAS DE LAS FRAS
	case 'ultima_fecha_fra':

		//Si no se han seleccionado las fecha interrumpe
		if (!isset($_POST['desde']) || !isset($_POST['hasta'])){break;}

		//Ultima fecha de facturación por cups
		$strSQL = "SELECT CONCAT('$cli', '') cliente, a.cups, MAX(a.Fecha_hasta) max_fecha_hasta, MAX(a.Fecha_factura) max_fecha_factura, b.estado FROM facturas a
                    RIGHT JOIN (
						SELECT
							cups,
                            estado
						FROM clientes
						WHERE Grupo='$cli') b
					ON a.cups=b.cups
                    WHERE a.id_cliente='$ID' GROUP BY a.cups";
		$val_fechas = $Conn->getArray($strSQL, true);
		
		//Si no hay facturas desde hace más de 3 meses o no hay facturas
        $date = new DateClass;
        $date->subtract(0,3);
        $max_hasta = new DateClass;
        $max_factura = new DateClass;
        
        foreach ($val_fechas as $num_row=>$row){
            $val_fechas[$num_row]['observaciones'] = '';
            
            if (empty($row['max_fecha_hasta'])){
                $val_fechas[$num_row]['observaciones'] = ' No hay facturas emitidas para este cups.';
                continue;
            }
            
            $max_hasta->stringToDate($row['max_fecha_hasta']);
            $max_factura->stringToDate($row['max_fecha_factura']);
            
            if ($max_hasta->vardate<=$date->vardate){
				$val_fechas[$num_row]['observaciones'] .= ' La ultima factura fecha hasta de hace más de 3 meses.';
			}
            if ($max_factura->vardate<=$date->vardate){
				$val_fechas[$num_row]['observaciones'] .= ' Este CUPS no emite desde hace más de 3 meses.';
			}

			$val_fechas[$num_row]['fecha_consulta'] = date('Y-m-d');
        }

		unset($Conn);
		$Conn = new Conn('local', 'enertrade');
		$values = implode_values($val_fechas);
		$Conn->Query("DELETE FROM revisiones_fechas_fra WHERE CLIENTE='$cli'");
		$Conn->Query("INSERT INTO revisiones_fechas_fra (
						CLIENTE,
						CUPS,
						MAX_FECHA_HASTA,
						MAX_FECHA_FACTURA,
						ESTADO,
						OBSERVACIONES,
						FECHA_CONSULTA)
					VALUES $values
		");


		break;
	
	//REVISIÓN DE CONSUMOS ACUMULADOS O 0
	case 'revision_consumos':

		//Si no se han seleccionado las fecha interrumpe
		if (!isset($_POST['desde']) || !isset($_POST['hasta'])){break;}

		//Consumos NT
		$strSQL = "SELECT
						cups,
						mes,
						total,
						termino_energia
					FROM datos_notelemedidas
					WHERE grupo='$cli'
					AND mes>='".(date('Y')-1)."-01-01'
					ORDER BY cups, mes DESC";

        $consumos_nt = $Conn->getArray($strSQL, true);
		$Array 		 = new ArrayClass($consumos_nt);
		$consumos_nt = $array->assocFromColumn('cups');
		unset($Array);
        
        
        foreach ($consumos_nt as $cups=>$consumos){
            $cnt_0 = 0;
            $is_0  = false;
            $msg   = '';
            
            //Comprueba si hay 6 meses sin consumos
            foreach ($consumos as $num_row=>$row){
                if ($row['total']==0){
                    ++$cnt_0;
                } else {
                    $cnt_0 = 0;
                }
                
                if ($cnt_0 >=6){
                    $msg .= '6 o más meses sin consumo.';
                    $break;
                }
            }
            if (!empty($msg)){
                $linea = array();
                $linea['CLIENTE']       = $cli;
                $linea['CUPS']          = $cups;
                $linea['MES']           = '';
                $linea['CONSUMO']       = '';
                $linea['PROMEDIO']      = '';
                $linea['TE']            = '';
                $linea['PRECIO €/kW']   = '';
                $linea['OBSERVACIONES'] = $msg;
                $val_consumos_mes[]     = $linea;
                unset($linea);
            }
            
            //Comprueba si hay consumos acumulados
            foreach ($consumos as $num_row=>$row){
                if ($num_row==17){break;}   //Si ha comprobado 18 meses corta
                if ($row['total']==0){
                    if ($is_0){continue;}
                    $is_0 = true;
                    
                } else {
                    $is_0 = false;
                    $avg = 0;
                    $cnt = 0;
                    //Promedio de los 12 meses anteriores sin los meses con consumo a 0
                    for ($x=1; $x<=12; $x++){
                        if (isset($consumos[($num_row+$x)])){
                            if ($x==1 && $consumos[($num_row+$x)]['total']!=0){break;} //Si la siguiente factura no tiene un 0 corta
                            $avg += $consumos[($num_row+$x)]['total'];
                            if ($consumos[($num_row+$x)]['total']!=0){++$cnt;}
                        } else {
                            break;
                        }
                    }
                    
                    if ($cnt!=0){
                        $avg = ($avg/$cnt)*2;
                        if ($row['total']>=$avg){
                            $linea = array();
                            $linea['CLIENTE']       = $cli;
                            $linea['CUPS']          = $cups;
                            $linea['MES']           = $row['mes'];
                            $linea['CONSUMO']       = $row['total'];
                            $linea['PROMEDIO']      = round(($avg/2), 0);
                            $linea['TE']            = $row['termino_energia'];
                            $linea['PRECIO €/kW']   = $row['termino_energia']/$row['total'];
                            $linea['OBSERVACIONES'] = "Consumo más del doble que el promedio de los anteriores 12 meses sin consumo a 0.";
                            $val_consumos_mes[]     = $linea;
                            unset($linea);
                        }
                    }
                }
            }
        }

		$values = implode_values($val_consumos_mes);

		unset($Conn);
		$Conn = new Conn('local', 'enertrade');
		$Conn->Query("DELETE FROM revisiones_consumos WHERE CLIENTE='$cli'");
		$Conn->Query("INSERT INTO revisiones_consumos (
						CLIENTE,
						CUPS,
						MES,
						CONSUMO,
						PROMEDIO_ANUAL_SIN_CEROS,
						TE,
						PRECIO_EUR_KW,
						OBSERVACIONES)
					VALUES $values
		");
		unset($Conn, $values, $val_consumos_mes);

		break;

		
	case 'revisiones_duplicadas':

		//Si no se han seleccionado las fecha interrumpe
		if (!isset($_POST['desde']) || !isset($_POST['hasta'])){break;}

		$val_duplicadas		= array();
		$header_duplicadas 	= array('CUPS', 'NUM_FRA', 'EMISION', 'DESDE', 'HASTA', 'TOT_FRA', 'OBSERVACIONES');

		/*
		El numero de fras duplicadas se puede sacar con una query mysql sencilla
		Falta todo este case por redactar
		*/


		//DUPLICADAS	#####################################
		$msg = '';
				
		if (!array_key_exists($row['numero_factura'], $val_duplicadas) && $cnt_num_fra[$row['numero_factura']] > 1){$msg .= "Num fra duplicado";}
		if (!array_key_exists($row['numero_factura'], $val_duplicadas) && $cnt_id_duplicadas[$row['id_duplicada']] > 1){$msg .= "Mismas fechas e importes (num registros = ".$cnt_id_duplicadas[$row['id_duplicada']].")";}
		
		if (!empty($msg)){
			$linea 									= array_fill_keys($header_duplicadas, '');
			$linea['CUPS'] 							= $CUPS;
			$linea['NUM_FRA'] 						= $row['numero_factura'];
			$linea['EMISION'] 						= date_format($emision, 'd/m/Y');
			$linea['DESDE'] 						= date_format($desde, 'd/m/Y');
			$linea['HASTA'] 						= date_format($hasta, 'd/m/Y');
			$linea['TOT_FRA'] 						= $row['Total_factura'];
			$linea['OBSERVACIONES'] 				= $msg;
			$val_duplicadas[$row['numero_factura']] = $linea;
			unset($linea);
		}
		
		//NEGATIVAS DUPLICADAS
		$msg = '';
		if (!array_key_exists($CUPS, $val_duplicadas) && isset($dos_negativas[$CUPS]) && $dos_negativas[$CUPS]['cuenta']>1){
			$num_negativas = $dos_negativas[$CUPS]['cuenta'];
			$msg .= "Más de una negativa en el mismo periodo ($num_negativas)";
			unset($num_negativas);
		}
		
		$date_neg_dup = new dateClass;
		if (!empty($msg)){
			$linea 									= array_fill_keys($header_duplicadas, '');
			$linea['CUPS'] 							= $CUPS;
			$linea['NUM_FRA'] 						= '';
			$linea['EMISION'] 						= '';
			$linea['DESDE'] 						= $date_neg_dup->fromToFormat($dos_negativas[$CUPS]['Fecha_desde']);
			$linea['HASTA'] 						= $date_neg_dup->fromToFormat($dos_negativas[$CUPS]['Fecha_hasta']);
			$linea['TOT_FRA'] 						= '';
			$linea['OBSERVACIONES'] 				= $msg;
			$val_duplicadas[$CUPS]                  = $linea;
			unset($linea);
		}

		break;


	//BBDD
	case 'BBDD':
		
		if (!isset($_POST['desde']) || !isset($_POST['hasta'])){
			header ("Location: revisiones.php?cli=$cli");
			die;
		}
		
        set_time_limit(7200);
        
        $date = new DateClass;
		$desde_usu = $date->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d');
		$hasta_usu = $date->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d');
		unset($date);
		
		//Comprueba si son demasiadas facturas
		$strSQL = 	"SELECT
						COUNT(numero_factura) cuenta
					FROM facturas
					WHERE (id_cliente='".$ID."')
					AND (Fecha_factura>='".$desde_usu."' And Fecha_factura<='".$hasta_usu."')";
		
		$result = $Conn->getArray($strSQL);
		
		$num_fras = $result[0]['cuenta'];
		unset($result);
		
		if ($num_fras > 50000){
			header ("Location: revisiones.php?cli=$cli&desde=$desde_usu&hasta=$hasta_usu&num_fras=$num_fras");
			die;
		}
		
		
		
		//Saca las fras
		$strSQL = "SELECT
						CONCAT(a.cups, a.Fecha_desde, a.Fecha_hasta, a.Total_factura) id_duplicada,
						CONCAT(a.cups, a.Fecha_desde, a.Fecha_hasta) id_duplicada_negativas,
						a.cups,
						b.Tarifa,
						b.numero_contrato,
                        b.estado,
						a.numero_factura,
						a.factura_rectificada,
						a.Fecha_factura,
						a.Fecha_desde,
						a.Fecha_hasta,
						a.consumo_energia_p1,
						a.consumo_energia_p2,
						a.consumo_energia_p3,
						a.consumo_energia_p4,
						a.consumo_energia_p5,
						a.consumo_energia_p6,
						a.consumo_total,
						a.consumo_reactiva_p1,
						a.consumo_reactiva_p2,
						a.consumo_reactiva_p3,
						a.consumo_reactiva_p4,
						a.consumo_reactiva_p5,
						a.consumo_reactiva_p6,
						a.consumo_total_reactiva,
						a.potencia_registrada_p1,
						a.potencia_registrada_p2,
						a.potencia_registrada_p3,
						a.potencia_registrada_p4,
						a.potencia_registrada_p5,
						a.potencia_registrada_p6,
						a.Ter_potencia,
						a.Ter_Energia,
						a.Excesos_reactiva,
						a.alq_equipo,
						a.Excesos_potencia,
						a.concepto_regulado_termino_variable,
						a.otros_conceptos_con_iva,
						a.otros_conceptos_con_iva_iee,
						a.otros_conceptos_sin_iva,
						a.Der_acceso,
						a.Der_enganche,
                        a.tope_gas,
						a.impuesto_electricidad,
						a.base_imponible_total,
						(a.valor_iva_general + a.valor_iva_reducido + a.valor_iva_superreducido) Valor_IVA,
						a.Total_factura,
						a.revisada
					FROM facturas a
					INNER JOIN (
						SELECT
							cups,
							numero_contrato,
							fecha_inicio,
							fecha_fin,
							Tarifa,
                            estado
						FROM clientes
						WHERE (Grupo='$cli')) b
					ON a.cups=b.CUPS
					AND (a.Fecha_desde BETWEEN b.fecha_inicio AND b.fecha_fin)
					WHERE (a.id_cliente='$ID')
					AND (a.Fecha_hasta>='$desde_usu' And a.Fecha_desde<='$hasta_usu')
					ORDER BY CUPS, Fecha_desde, Fecha_factura";
		
		$query = $Conn->Query($strSQL);
		
		while ($row = mysqli_fetch_assoc($query)){$validaciones[$row['cups']][] = $row;}
		unset($query);
		
		//Ultima fecha de facturación por cups
		$strSQL = "SELECT a.cups, MAX(a.Fecha_hasta) max_fecha_hasta, MAX(a.Fecha_factura) max_fecha_factura, b.estado FROM facturas a
                    RIGHT JOIN (
						SELECT
							cups,
                            estado
						FROM clientes
						WHERE Grupo='$cli') b
					ON a.cups=b.cups
                    WHERE a.id_cliente='$ID' GROUP BY a.cups";
		$val_fechas = $Conn->getArray($strSQL, true);
		
        //Consumos NT
		$strSQL = "SELECT cups, mes, total, termino_energia FROM datos_notelemedidas WHERE grupo='$cli' AND mes>='".(date('Y')-1)."-01-01' ORDER BY cups, mes DESC";
        $query = $Conn->Query($strSQL);
        while ($row = mysqli_fetch_assoc($query)){$consumos_nt[$row['cups']][] = $row;}
		unset($query);
        
        
        foreach ($consumos_nt as $cups=>$consumos){
            $cnt_0 = 0;
            $is_0  = false;
            $msg   = '';
            
            //Comprueba si hay 6 meses sin consumos
            foreach ($consumos as $num_row=>$row){
                if ($row['total']==0){
                    ++$cnt_0;
                } else {
                    $cnt_0 = 0;
                }
                
                if ($cnt_0 >=6){
                    $msg .= '6 o más meses sin consumo.';
                    $break;
                }
            }
            if (!empty($msg)){
                $linea = array();
                $linea['CUPS']          = $cups;
                $linea['MES']           = '';
                $linea['CONSUMO']       = '';
                $linea['PROMEDIO']      = '';
                $linea['TE']            = '';
                $linea['PRECIO €/kW']   = '';
                $linea['OBSERVACIONES'] = $msg;
                $val_consumos_mes[]     = $linea;
                unset($linea);
            }
            
            //Comprueba si hay consumos acumulados
            foreach ($consumos as $num_row=>$row){
                if ($num_row==17){break;}   //Si ha comprobado 18 meses corta
                if ($row['total']==0){
                    if ($is_0){continue;}
                    $is_0 = true;
                    
                } else {
                    $is_0 = false;
                    $avg = 0;
                    $cnt = 0;
                    //Promedio de los 12 meses anteriores sin los meses con consumo a 0
                    for ($x=1; $x<=12; $x++){
                        if (isset($consumos[($num_row+$x)])){
                            if ($x==1 && $consumos[($num_row+$x)]['total']!=0){break;} //Si la siguiente factura no tiene un 0 corta
                            $avg += $consumos[($num_row+$x)]['total'];
                            if ($consumos[($num_row+$x)]['total']!=0){++$cnt;}
                        } else {
                            break;
                        }
                    }
                    
                    if ($cnt!=0){
                        $avg = ($avg/$cnt)*2;
                        if ($row['total']>=$avg){
                            $linea = array();
                            $linea['CUPS']          = $cups;
                            $linea['MES']           = $row['mes'];
                            $linea['CONSUMO']       = $row['total'];
                            $linea['PROMEDIO']      = round(($avg/2), 0);
                            $linea['TE']            = $row['termino_energia'];
                            $linea['PRECIO €/kW']   = $row['termino_energia']/$row['total'];
                            $linea['OBSERVACIONES'] = "Consumo más del doble que el promedio de los anteriores 12 meses sin consumo a 0.";
                            $val_consumos_mes[]     = $linea;
                            unset($linea);
                        }
                    }
                }
            }
        }
        
        
        //Si no hay facturas desde hace más de 3 meses o no hay facturas
        $date = new DateClass;
        $date->subtract(0,3);
        $max_hasta = new DateClass;
        $max_factura = new DateClass;
        
        foreach ($val_fechas as $num_row=>$row){
            $val_fechas[$num_row]['observaciones'] = '';
            
            if (empty($row['max_fecha_hasta'])){
                $val_fechas[$num_row]['observaciones'] = ' No hay facturas emitidas para este cups.';
                continue;
            }
            
            $max_hasta->stringToDate($row['max_fecha_hasta']);
            $max_factura->stringToDate($row['max_fecha_factura']);
            
            if ($max_hasta->vardate<=$date->vardate){
				$val_fechas[$num_row]['observaciones'] .= ' La ultima factura fecha hasta de hace más de 3 meses.';
			}
            if ($max_factura->vardate<=$date->vardate){
				$val_fechas[$num_row]['observaciones'] .= ' Este CUPS no emite desde hace más de 3 meses.';
			}
        }
		
        //Cups con 2 fras negativas en el mismo periodos
        $strSQL = "SELECT cups, Fecha_desde, Fecha_hasta, COUNT(cups) cuenta FROM facturas WHERE id_cliente='$ID' AND Total_factura<0 AND (Fecha_hasta>='$desde_usu' And Fecha_desde<='$hasta_usu') GROUP BY cups, Fecha_Desde, Fecha_hasta";
		$dos_negativas_temp = $Conn->getArray($strSQL, true);
		
        foreach($dos_negativas_temp as $num_row=>$row){
            if ($row['cuenta']>1){$dos_negativas[$row['cups']] = $row;}
        }
        unset($dos_negativas_temp);
		
		$val_consumos 		= array();
		$header_consumos 	= array('CUPS', 'TARIFA', 'NUM_FRA', 'EMISION', 'DESDE', 'HASTA', 'ACT1', 'ACT2', 'ACT3', 'ACT4', 'ACT5', 'ACT6', 'TOT_ACT', 							 'REA1', 'REA2', 'REA3', 'REA4','REA5', 'REA6', 'TOT_REA', 'MAX1', 'MAX2', 'MAX3', 'MAX4', 'MAX5', 'MAX6', 											'OBSERVACIONES');
		
		$val_importes		= array();
		$header_importes 	= array('CUPS', 'TARIFA', 'NUM_FRA', 'EMISION', 'DESDE', 'HASTA', 'TP', 'TE', 'EXC_REA', 'EXC_POT', 'ALQUILER', 'OTROS', 								'OTROS_IVA_IE', 'IE', 'DER_ACCESO', 'BI', 'IVA', 'TOT_FRA', 'TOT_ACT', 'TOT_REA', 'OBSERVACIONES');
		
		$val_duplicadas		= array();
		$header_duplicadas 	= array('CUPS', 'TARIFA', 'NUM_FRA', 'EMISION', 'DESDE', 'HASTA', 'TOT_FRA', 'OBSERVACIONES');
		
		$val_huecos			= array();
		$header_huecos 		= array('CUPS', 'NUM_FRA', 'EMISION', 'DESDE', 'HASTA', 'HUECO_DESDE', 'HUECO_HASTA', 'OBSERVACIONES');
		
		$date = new DateClass;
		foreach ($validaciones as $CUPS=>$array_rows){
			
			$cnt_num_fra 		= array_count_values(array_column($array_rows, 'numero_factura'));
			$cnt_id_duplicadas 	= array_count_values(array_column($array_rows, 'id_duplicada'));
            $rectificadas       = array();
            $rectificadas       = array_unique(array_filter(array_column($array_rows, 'factura_rectificada')));
            
            //Comprueba que los valores de anuladoras y anuladas se anulen reciprocamente
            if (!empty($rectificadas)){
                
                foreach ($rectificadas as $key=>$rectificada){
                    $msg 		    = '';
                    $total_activa   = 0;
                    foreach ($array_rows as $num_row=>$row){
                        if (($row['factura_rectificada']==$rectificada && $row['consumo_total']<0) || $row['numero_factura']==$rectificada){
                            for ($x=1; $x<=6; $x++){$total_activa += $row["consumo_energia_p$x"];}
                        }
                    }
                    
                    if ($total_activa!=0)  {$msg .= 'Consumo rectificada <> rectificadora.';}
                    
                    if (!empty($msg)){
                        foreach ($array_rows as $num_row=>$row){
                            if ($row['numero_factura']==$rectificada){
                                $linea 						= array_fill_keys($header_importes, '');
                                $linea['CUPS'] 				= $CUPS;
                                $linea['TARIFA'] 			= $row['Tarifa'];
                                $linea['NUM_FRA'] 			= $row['numero_factura'];
                                $linea['EMISION'] 			= $date->fromToFormat($row['Fecha_factura']);
                                $linea['DESDE'] 			= $date->fromToFormat($row['Fecha_desde']);
                                $linea['HASTA'] 			= $date->fromToFormat($row['Fecha_hasta']);
                                $linea['TP'] 				= $row['Ter_potencia'];
                                $linea['TE'] 				= $row['Ter_Energia'];
                                $linea['EXC_REA'] 			= $row['Excesos_reactiva'];
                                $linea['EXC_POT'] 			= $row['Excesos_potencia'];
                                $linea['ALQUILER'] 			= $row['alq_equipo'];
                                $linea['OTROS'] 			= $row['concepto_regulado_termino_variable'] + $row['otros_conceptos_con_iva'];
                                $linea['OTROS_IVA_IE'] 		= $row['otros_conceptos_con_iva_iee'] + $row['tope_gas'];
                                $linea['IE'] 				= $row['impuesto_electricidad'];
                                $linea['DERECHOS'] 		    = $row['Der_acceso'] + $row['Der_enganche'];
                                $linea['BI'] 				= $row['base_imponible_total'];
                                $linea['IVA'] 				= $row['Valor_IVA'];
                                $linea['TOT_FRA'] 			= $row['Total_factura'];
                                $linea['TOT_ACT'] 			= $row['consumo_total'];
                                $linea['TOT_REA'] 			= $row['consumo_total_reactiva'];
                                $linea['OBSERVACIONES'] 	= $msg;
                                $val_importes[] 			= $linea;
                                unset($linea, $e);
                                break;
                            } //Llena los datos de la validación de importes
                        }
                    }
                }
            }
            
            
			foreach ($array_rows as $num_row=>$row){
				
				//FECHAS	##########################################
				$msg 		= '';
				$emision 	= date_create_from_format('Y-m-d', $row['Fecha_factura']);
				$desde 		= date_create_from_format('Y-m-d', $row['Fecha_desde']);
				$hasta 		= date_create_from_format('Y-m-d', $row['Fecha_hasta']);
				
				//Comprobaciones
				if ($desde > $hasta)	{$msg .= "Desde > Hasta. ";}
				if ($emision < $hasta)	{$msg .= "Emision < Hasta. ";}
				
				if (!empty($msg)){
					$linea 					= array_fill_keys($header_fechas, '');
					$linea['CUPS'] 			= $CUPS;
					$linea['NUM_FRA'] 		= $row['numero_factura'];
					$linea['EMISION'] 		= date_format($emision, 'd/m/Y');
					$linea['DESDE'] 		= date_format($desde, 'd/m/Y');
					$linea['HASTA'] 		= date_format($hasta, 'd/m/Y');
					$linea['OBSERVACIONES'] = $msg;
					$val_fechas2[] 			= $linea;
					
					unset($linea);
				} //Llena los datos de la validación de fechas
				
				
				//CONSUMOS	#####################################
				$msg 			= '';
				$suma_activa 	= 0;
				$suma_reactiva 	= 0;
				$suma_max		= 0;
				for ($x=1;$x<=6;$x++){
					$suma_activa 	+= $row["consumo_energia_p$x"];
					$suma_reactiva 	+= $row["consumo_reactiva_p$x"];
					$suma_max		+= $row["potencia_registrada_p$x"];
				}
				
				//Comprobaciones
				if ($row['revisada']==0){
					if (abs($suma_activa - $row['consumo_total']) > 1)										{$msg .= "Suma activa no coincide. ";}
					if (abs($suma_reactiva - $row['consumo_total_reactiva']) > 1)							{$msg .= "Suma reactiva no coincide. ";}
					if ($row['consumo_total'] == 0 && $row['consumo_total_reactiva'] != 0)					{$msg .= "Reactiva sin activa. ";}
					if ($row['consumo_total_reactiva'] > $row['consumo_total'])								{$msg .= "Reactiva > activa. ";}
					if ($row['consumo_total'] != 0 && $suma_max == 0 && substr($row['Tarifa'], 0, 1) != '2'){$msg .= "Consumo sin máxima. ";}
                    
					$suma_activa = 0;
					switch ($row['Tarifa']){
						case '2.0TD':
							for ($x=4; $x<=6; $x++){$suma_activa += $row["consumo_energia_p$x"];}
							break;
					}
					if ($suma_activa != 0){$msg .= "Periodos no coinciden con tarifa. ";}
				}
				
				if (!empty($msg)){
					$linea 					= array_fill_keys($header_consumos, '');
					$linea['CUPS'] 			= $CUPS;
					$linea['TARIFA'] 		= $row['Tarifa'];
					$linea['NUM_FRA'] 		= $row['numero_factura'];
					$linea['EMISION'] 		= date_format($emision, 'd/m/Y');
					$linea['DESDE'] 		= date_format($desde, 'd/m/Y');
					$linea['HASTA'] 		= date_format($hasta, 'd/m/Y');
					for ($x=1; $x<=6; $x++){
						$linea["ACT$x"] =  $row["consumo_energia_p$x"];
						$linea["REA$x"] =  $row["consumo_reactiva_p$x"];
						$linea["MAX$x"] =  $row["potencia_registrada_p$x"];
					}
					$linea['TOT_ACT'] 		= $row['consumo_total'];
					$linea['TOT_REA'] 		= $row['consumo_total_reactiva'];
					$linea['OBSERVACIONES'] = $msg;
					$val_consumos[] 		= $linea;
					unset($linea);
				} //Llena los datos de la validación de consumos
				
				
				//IMPORTES	#####################################
				$msg 		= '';
				$suma_BI 	= $row['Ter_potencia'] + $row['Ter_Energia'] + $row['Excesos_potencia'] + $row['Excesos_reactiva'] + $row['alq_equipo'] + $row['concepto_regulado_termino_variable'] + $row['impuesto_electricidad'] + $row['otros_conceptos_con_iva'] + $row['otros_conceptos_con_iva_iee'] + $row['Der_acceso'] + $row['tope_gas'];
				
				//Comprobaciones
				if ($row['revisada']==0){
					if ($row['consumo_total'] == 0 && $row['impuesto_electricidad'] != 0)				{$msg .= "IE sin consumo. ";}
					if (($suma_BI-$row['base_imponible_total'])>0.5 || ($suma_BI-$row['base_imponible_total'])<-0.5){$msg .= "Suma conceptos <> BI. ";}
					if (abs($row['base_imponible_total'] + $row['Valor_IVA'] + $row['otros_conceptos_sin_iva'] - $row['Total_factura']) > 1)	{$msg .= "BI+IVA <> TOT. ";}
					if ($row['consumo_total'] == 0 && $row['Ter_Energia'] != 0)							{$msg .= "TE sin consumo. ";}
					if ($row['consumo_total_reactiva'] == 0 && $row['Excesos_reactiva'] != 0)			{$msg .= "Excesos REA sin reactiva. ";}
					if ($row['Ter_Energia'] < 0 && $row['consumo_total'] > 0)							{$msg .= "TE negativo y consumo positivo. ";}
					if (empty($row['numero_contrato']) || $row['numero_contrato'] == '-')				{$msg .= "No contrato. ";}
					if ((substr($row['Tarifa'], 0, 1) == '2') && $row['Excesos_potencia'] != 0)			{$msg .= "BT con excesos. ";}
				}
				if (!empty($msg)){
					$linea 						= array_fill_keys($header_importes, '');
					$linea['CUPS'] 				= $CUPS;
					$linea['TARIFA'] 			= $row['Tarifa'];
					$linea['NUM_FRA'] 			= $row['numero_factura'];
					$linea['EMISION'] 			= date_format($emision, 'd/m/Y');
					$linea['DESDE'] 			= date_format($desde, 'd/m/Y');
					$linea['HASTA'] 			= date_format($hasta, 'd/m/Y');
					$linea['TP'] 				= $row['Ter_potencia'];
					$linea['TE'] 				= $row['Ter_Energia'];
					$linea['EXC_REA'] 			= $row['Excesos_reactiva'];
					$linea['EXC_POT'] 			= $row['Excesos_potencia'];
					$linea['ALQUILER'] 			= $row['alq_equipo'];
					$linea['OTROS'] 			= $row['concepto_regulado_termino_variable'];
					$linea['OTROS_IVA_IE'] 		= $row['otros_conceptos_con_iva_iee'] + $row['otros_conceptos_con_iva'];
					$linea['IE'] 				= $row['impuesto_electricidad'];
					$linea['DER_ACCESO'] 		= $row['Der_acceso'];
					$linea['BI'] 				= $row['base_imponible_total'];
					$linea['IVA'] 				= $row['Valor_IVA'];
					$linea['TOT_FRA'] 			= $row['Total_factura'];
					$linea['TOT_ACT'] 			= $row['consumo_total'];
					$linea['TOT_REA'] 			= $row['consumo_total_reactiva'];
					$linea['OBSERVACIONES'] 	= $msg;
					$val_importes[] 			= $linea;
					unset($linea);
				} //Llena los datos de la validación de importes
				
				//DUPLICADAS	#####################################
				$msg = '';
				
				if (!array_key_exists($row['numero_factura'], $val_duplicadas) && $cnt_num_fra[$row['numero_factura']] > 1){$msg .= "Num fra duplicado";}
				if (!array_key_exists($row['numero_factura'], $val_duplicadas) && $cnt_id_duplicadas[$row['id_duplicada']] > 1){$msg .= "Mismas fechas e importes (num registros = ".$cnt_id_duplicadas[$row['id_duplicada']].")";}
				
				if (!empty($msg)){
					$linea 									= array_fill_keys($header_duplicadas, '');
					$linea['CUPS'] 							= $CUPS;
					$linea['TARIFA'] 						= $row['Tarifa'];
					$linea['NUM_FRA'] 						= $row['numero_factura'];
					$linea['EMISION'] 						= date_format($emision, 'd/m/Y');
					$linea['DESDE'] 						= date_format($desde, 'd/m/Y');
					$linea['HASTA'] 						= date_format($hasta, 'd/m/Y');
					$linea['TOT_FRA'] 						= $row['Total_factura'];
					$linea['OBSERVACIONES'] 				= $msg;
					$val_duplicadas[$row['numero_factura']] = $linea;
					unset($linea);
				}
				
                //NEGATIVAS DUPLICADAS
                $msg = '';
                if (!array_key_exists($CUPS, $val_duplicadas) && isset($dos_negativas[$CUPS]) && $dos_negativas[$CUPS]['cuenta']>1){
                    $num_negativas = $dos_negativas[$CUPS]['cuenta'];
                    $msg .= "Más de una negativa en el mismo periodo ($num_negativas)";
                    unset($num_negativas);
                }
                
                $date_neg_dup = new dateClass;
                if (!empty($msg)){
					$linea 									= array_fill_keys($header_duplicadas, '');
					$linea['CUPS'] 							= $CUPS;
					$linea['TARIFA'] 						= $row['Tarifa'];
					$linea['NUM_FRA'] 						= '';
					$linea['EMISION'] 						= '';
					$linea['DESDE'] 						= $date_neg_dup->fromToFormat($dos_negativas[$CUPS]['Fecha_desde']);
					$linea['HASTA'] 						= $date_neg_dup->fromToFormat($dos_negativas[$CUPS]['Fecha_hasta']);
					$linea['TOT_FRA'] 						= '';
					$linea['OBSERVACIONES'] 				= $msg;
					$val_duplicadas[$CUPS]                  = $linea;
					unset($linea);
				}
                
				//HUECOS	#####################################
				$msg = '';
				
				if ($num_row < count($array_rows)-1){
					$next_emision 	= date_create_from_format('Y-m-d', $array_rows[$num_row+1]['Fecha_factura']);
					$next_desde 	= date_create_from_format('Y-m-d', $array_rows[$num_row+1]['Fecha_desde']);
					$next_hasta 	= date_create_from_format('Y-m-d', $array_rows[$num_row+1]['Fecha_hasta']);
				}
				if ($num_row < count($array_rows)-2){
					$dos_next_emision 	= date_create_from_format('Y-m-d', $array_rows[$num_row+2]['Fecha_factura']);
					$dos_next_desde 	= date_create_from_format('Y-m-d', $array_rows[$num_row+2]['Fecha_desde']);
					$dos_next_hasta 	= date_create_from_format('Y-m-d', $array_rows[$num_row+2]['Fecha_hasta']);
				}
				
				if ($desde != $hasta){
					switch (true){
						
						case ($num_row < count($array_rows)-1 && $next_desde != $next_hasta):		//Antes de la penultima linea
							
							$diff = (date_diff($hasta, $next_desde)->format('%a'));					//next_desde <> next_hasta
							switch (true){
								
								case (($desde!=$next_desde && $hasta==$next_hasta) || ($desde==$next_desde && $hasta!=$next_hasta)):																						//una de las dos fechas = a la siguiente
									$msg = "Revisar"; break;
								
								case ($desde!=$next_desde && $hasta!=$next_hasta):					//Si fechas <> de las siguientes

									switch (true){
										case ($diff > 1): $msg = "Hueco"; break;
										case ($diff < 0): $msg = "Solape"; break;
									}
									break;
							}
							break;
						
						case ($num_row < count($array_rows)-2 && $next_desde == $next_hasta): 		//Antes de las dos ultimas lineas
							
							$dos_diff = (date_diff($hasta, $dos_next_desde)->format('%a'));
							switch (true){
								
								case (($desde!=$dos_next_desde && $hasta==$dos_next_hasta) || ($desde==$dos_next_desde && $hasta!=$dos_next_hasta)):																		//una de las dos fechas = a las dos siguientes
									$msg = "Revisar"; break;
								
								case ($dos_next_desde != $dos_next_hasta):							//dos_desde <> dos_hasta
									
									switch (true){
										case ($dos_diff > 1): $msg = "Hueco"; break;
										case ($dos_diff < 0): $msg = "Solape"; break;
									}
									break;
							}
							break;
					} //Switch
				} //Si desde <> hasta
				
				if (!empty($msg)){
					
					if (!array_key_exists($row['numero_factura'], $val_huecos)){
						$val_huecos[$row['numero_factura']] 					= array_fill_keys($header_huecos, '');
						$val_huecos[$row['numero_factura']]['CUPS'] 			= $CUPS;
						$val_huecos[$row['numero_factura']]['NUM_FRA'] 			= $row['numero_factura'];
						$val_huecos[$row['numero_factura']]['EMISION'] 			= date_format($emision, 'd/m/Y');
						$val_huecos[$row['numero_factura']]['DESDE'] 			= date_format($desde, 'd/m/Y');
						$val_huecos[$row['numero_factura']]['HASTA'] 			= date_format($hasta, 'd/m/Y');
						$val_huecos[$row['numero_factura']]['OBSERVACIONES'] 	= $msg;
						
						unset($linea);
					}
					
					if ($msg == "Hueco"){
						
						$val_huecos[$row['numero_factura']]['HUECO_DESDE'] = date_format($hasta, 'd/m/Y');
						switch (true){
							case (isset($diff)):
								$val_huecos[$row['numero_factura']]['HUECO_HASTA'] = date_format($next_desde, 'd/m/Y');
								break;
							case (isset($dos_diff)):
								$val_huecos[$row['numero_factura']]['HUECO_HASTA'] = date_format($dos_next_desde, 'd/m/Y');
								break;
						}
						
					}
					
					//Datos siguiente fra
					switch (true){
						case (isset($diff)):
							$val_huecos[$array_rows[$num_row+1]['numero_factura']] 					= array_fill_keys($header_huecos, '');
							$val_huecos[$array_rows[$num_row+1]['numero_factura']]['CUPS'] 			= $CUPS;
							$val_huecos[$array_rows[$num_row+1]['numero_factura']]['NUM_FRA'] 		= $array_rows[$num_row+1]['numero_factura'];
							$val_huecos[$array_rows[$num_row+1]['numero_factura']]['EMISION'] 		= date_format($next_emision, 'd/m/Y');
							$val_huecos[$array_rows[$num_row+1]['numero_factura']]['DESDE'] 		= date_format($next_desde, 'd/m/Y');
							$val_huecos[$array_rows[$num_row+1]['numero_factura']]['HASTA'] 		= date_format($next_hasta, 'd/m/Y');
							$val_huecos[$array_rows[$num_row+1]['numero_factura']]['OBSERVACIONES'] = $msg;
							break;
						case (isset($dos_diff)):
							$val_huecos[$array_rows[$num_row+2]['numero_factura']] 					= array_fill_keys($header_huecos, '');
							$val_huecos[$array_rows[$num_row+2]['numero_factura']]['CUPS'] 			= $CUPS;
							$val_huecos[$array_rows[$num_row+2]['numero_factura']]['NUM_FRA'] 		= $array_rows[$num_row+2]['numero_factura'];
							$val_huecos[$array_rows[$num_row+2]['numero_factura']]['EMISION'] 		= date_format($dos_next_emision, 'd/m/Y');
							$val_huecos[$array_rows[$num_row+2]['numero_factura']]['DESDE'] 		= date_format($dos_next_desde, 'd/m/Y');
							$val_huecos[$array_rows[$num_row+2]['numero_factura']]['HASTA'] 		= date_format($dos_next_hasta, 'd/m/Y');
							$val_huecos[$array_rows[$num_row+2]['numero_factura']]['OBSERVACIONES'] = $msg;
							break;
					}
					
				}
				
				unset($diff, $dos_diff, $next_desde, $next_hasta, $dos_next_desde, $dos_next_hasta);
				
			} //Para cada linea
		} //Para cada CUPS
		
		unset($header_fechas, $header_consumos, $header_importes, $header_duplicadas, $header_huecos);
		
		$SprdSht = new SprdSht;
		$SprdSht->nuevo();
		$x = 0;
        
        // FECHAS
		if (isset($val_fechas[0])){
			$SprdSht->addSheet('FECHAS');
			$SprdSht->putArray($val_fechas, true);
			unset($val_fechas);
		} else {
            ++$x;
        }
        
        // FECHAS2
		if (isset($val_fechas2[0])){
			$SprdSht->addSheet('FECHAS2');
			$SprdSht->putArray($val_fechas2, true);
			$i=2;
			foreach ($val_fechas2 as $row) {
				$SprdSht->setValueAsText("B$i", $row['NUM_FRA']);
				++$i;
			}
			unset($val_fechas2);
		} else {
            ++$x;
        }
        
		// CONSUMOS
		if (isset($val_consumos[0])){
			$SprdSht->addSheet('CONSUMOS');
			$SprdSht->putArray($val_consumos, true);
			$i=2;
			foreach ($val_consumos as $row) {
				$SprdSht->setValueAsText("C$i", $row['NUM_FRA']);
				++$i;
			}
			unset($val_consumos);
		} else {
            ++$x;
        }
        
        // CONSUMOS MES
		if (isset($val_consumos_mes[0])){
			$SprdSht->addSheet('CONSUMOS MES');
			$SprdSht->putArray($val_consumos_mes, true);
			unset($val_consumos_mes);
		} else {
            ++$x;
        }
		
		// IMPORTES
		if (isset($val_importes[0])){
			$SprdSht->addSheet('IMPORTES');
			$SprdSht->putArray($val_importes, true);
			$i=2;
			foreach ($val_importes as $row) {
				$SprdSht->setValueAsText("C$i", $row['NUM_FRA']);
				++$i;
			}
			unset($val_importes);
		} else {
            ++$x;
        }
		
		// DUPLICADAS
		if (!empty($val_duplicadas)){
			$SprdSht->addSheet('DUPLICADAS');
			$SprdSht->putArray($val_duplicadas, true);
			$i=2;
			foreach ($val_duplicadas as $row) {
				$SprdSht->setValueAsText("C$i", $row['NUM_FRA']);
				++$i;
			}
			unset($val_duplicadas);
		} else {
            ++$x;
        }
		
		// HUECOS
		if (isset($val_huecos) && !empty($val_huecos)){
			$SprdSht->addSheet('HUECOS');
			$SprdSht->putArray($val_huecos, true);
			$i=2;
			foreach ($val_huecos as $row) {
				$SprdSht->setValueAsText("B$i", $row['NUM_FRA']);
				++$i;
			}
			unset($val_huecos);
		} else {
            ++$x;
        }
		
		if ($x!=7) {
            $SprdSht->directDownload("Revisión BBDD $cli");
        } else {
            header ('Location: revisiones.php');
        }
		
		break;
		
    case 'desvio_consumo':
        
        set_time_limit(3600);
        
        $date = new DateClass;
        $hasta = new DateClass;
        
        $desde = (!isset($_POST['desde']) || empty($_POST['desde'])) ? date('Y-m-01') : $date->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-01');
        $consumo = (!isset($_POST['desde_consumo']) || empty($_POST['desde_consumo'])) ? 0 : $_POST['desde_consumo'];
        
        $Conn = new Conn('mainsip', 'develop');
        
        $hasta->stringtoDate($desde);
        $hasta->subtract(1);
        $hasta = $hasta->format();
        $date->stringtoDate($desde);
        
        $promedio_ano_movil = $Conn->getArray("SELECT cups, AVG(total) promedio FROM datos_notelemedidas WHERE grupo='$cli' AND estado='EN VIGOR' AND mes<'$desde' AND mes>='$hasta' GROUP BY cups", true);
        
        foreach ($promedio_ano_movil as $num_row=>$row){
            $promedio_ano_movil[$row['cups']] = $row;
            unset($promedio_ano_movil[$num_row]);
        }
        
        for ($x=0; $x<=10; $x++){
            $date->stringtoDate($desde);
            $date->subtract(0, $x);
            $mes = $date->format();
            $dias_en_mes = $date->format('t');
            
            $consumo_mes = $Conn->getArray("SELECT cups, dias, mes, total FROM datos_notelemedidas WHERE grupo='$cli' AND estado='EN VIGOR' AND total>=$consumo AND mes='$mes'", true);
            
            $date->subtract(1);
            $mes = $date->format();
            
            $consumo_mes_ano_anterior = $Conn->getArray("SELECT cups, dias, total FROM datos_notelemedidas WHERE grupo='$cli' AND estado='EN VIGOR' AND mes='$mes'", true);
            
            //Comprueba que el mes sea completo
            foreach ($consumo_mes as $num_row=>$row){
                if ($row['dias']>=$dias_en_mes){$consumo_mes[$row['cups']] = $row;}
                unset($consumo_mes[$num_row]);
            }
            
            foreach ($consumo_mes_ano_anterior as $num_row=>$row){
                if ($row['dias']>=$dias_en_mes){$consumo_mes_ano_anterior[$row['cups']] = $row;}
                unset($consumo_mes_ano_anterior[$num_row]);
            }
            
            //Inserta los datos completos relativos al mes
            foreach ($consumo_mes as $cups=>$row){
                $row['mes_año_anterior']      = '';
                $row['promedio_año_anterior'] = '';
                $row['mes_año_anterior']      = (!isset($consumo_mes_ano_anterior[$cups])) ? '' : $consumo_mes_ano_anterior[$cups]['total'];
                $row['promedio_año_anterior'] = (!isset($promedio_ano_movil[$cups])) ? '' : $promedio_ano_movil[$cups]['promedio'];
                
                $final[] = $row;
                unset($consumo_mes[$cups], $consumo_mes_ano_anterior[$cups]);
            }
            unset($consumo_mes, $consumo_mes_ano_anterior);
        }
        unset($promedio_ano_movil, $date, $desde, $hasta, $Conn);
        
        //Realiza los calculos
        foreach ($final as $num_row=>$row){
            $final[$num_row]['relación_mes_mes'] = '';
            $final[$num_row]['relación_mes_año'] = '';
            if (!empty($row['total']) && !empty($row['mes_año_anterior']) && $row['mes_año_anterior']!=0 && $row['total']!=0){
                $final[$num_row]['relación_mes_mes'] = round(($row['total']/$row['mes_año_anterior'])-1, 2);
            }
            if (!empty($row['total']) && !empty($row['promedio_año_anterior']) && $row['promedio_año_anterior']!=0 && $row['total']!=0){
                $final[$num_row]['relación_mes_año'] = round(($row['total']/$row['promedio_año_anterior'])-1, 2);
            }
        }
        
        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->putArray($final, true);
        unset($final);
        $SprdSht->directDownload("DESVÍO CONSUMOS $cli");
        
        break;
        
    case 'descarga_ps':
        
        if (!isset($_POST['desde_ps']) || empty($_POST['desde_ps'])){
            header ("Location: revisiones.php?cli=$cli");
			die;
        }
        
        set_time_limit(3600);
        
        $fecha_inicio = new DateClass;
        $fecha_inicio = $fecha_inicio->fromToFormat($_POST['desde_ps'], 'd/m/Y', 'Y-m-d');
        
        $strSQL = "SELECT
                        a.Grupo,
                        a.cif,
                        a.Empresa,
                        a.Comercializadora,
                        a.tipo_edificio,
                        a.mercado,
                        a.estado,
                        a.Distribuidora,
                        a.tension_texto,
                        a.CUPS,
                        a.codigo_oficina,
                        a.dar,
                        a.numero_contrato,
                        a.Direccion,
                        a.Poblacion,
                        a.Cod_postal,
                        a.provincia,
                        a.tension,
                        a.Tarifa,
                        a.ciclo, 
                        a.region,
                        a.zona,
                        a.P1,
                        a.P2,
                        a.P3,
                        a.P4,
                        a.P5,
                        a.P6,
                        a.cuenta,
                        a.observaciones,
                        DATE_FORMAT(a.fecha_inicio, '%d/%m/%Y'),
                        DATE_FORMAT(a.fecha_fin, '%d/%m/%Y'),
                        DATE_FORMAT(a.fecha_alta, '%d/%m/%Y'),
                        a.precio_energia_p1,
                        a.precio_energia_p2,
                        a.precio_energia_p3,
                        a.precio_energia_p4,
                        a.precio_energia_p5,
                        a.precio_energia_p6,
                        a.precio_potencia_p1,
                        a.precio_potencia_p2,
                        a.precio_potencia_p3,
                        a.precio_potencia_p4,
                        a.precio_potencia_p5,
                        a.precio_potencia_p6,
                        a.maximetro,
                        a.dia_extra,
                        a.curva_carga,
                        a.punto_socorro,
                        a.con_facturas,
                        b.nombre,
                        a.libre_1,
                        a.libre_2,
                        a.libre_3,
                        a.libre_4,
                        a.libre_5,
                        a.libre_6,
                        a.libre_7,
                        a.libre_8,
                        a.libre_9,
                        a.libre_10,
                        a.libre_11,
                        a.libre_12,
                        a.libre_13,
                        a.libre_14,
                        a.libre_15,
                        a.libre_16,
                        a.libre_17,
                        a.libre_18,
                        a.libre_19,
                        a.libre_20
                FROM clientes a
                INNER JOIN (SELECT
                                id,
                                nombre
                            FROM plantillas_validacion
                ) b
                ON a.plantilla_validacion_id=b.id
                WHERE a.Grupo='$cli'
                
        ";
        
        $tot_lineas = $Conn->oneData("SELECT COUNT(CUPS) FROM clientes WHERE Grupo='$cli' AND fecha_inicio>='$fecha_inicio'");
        
        //Si hay menos de 15.000 lineas descarga un fichero unico
        if ($tot_lineas<15000){
            $strSQL .= " AND a.fecha_inicio>='$fecha_inicio' ORDER BY a.CUPS, a.fecha_inicio DESC";
            
            $PS = $Conn->getArray($strSQL, true);
        
            if (isset($PS) && !empty($PS)){
                $SprdSht = new SprdSht;
                $SprdSht->load('plantillas/PS ayuda.xlsx', false);
                $SprdSht->putArray($PS, false, 'A2');
                foreach ($PS as $num_row=>$row){$SprdSht->setValueAsText('M'.($num_row+2), $row['numero_contrato']);}
                $SprdSht->setColumnsAutoWidth();
                $SprdSht->directDownload("PS $cli.xlsx");
                unset($PS, $SprdSht);
            }
        //Sino divide por mes las lineas
        } else {
            
            $timestamp = getMicrotimeString();
            
            $fecha_inicio = new DateClass;
            $fecha_inicio->stringToDate($_POST['desde_ps'], 'd/m/Y');
            
            $fecha_fin = new DateClass;
            $fecha_fin->stringToDate($_POST['desde_ps'], 'd/m/Y');
            $fecha_fin->add(0,1);
            
            $date = new DateClass;
            
            $x = 1;
            while ($fecha_inicio->vardate<$date->vardate){
                $desde = $fecha_inicio->format();
                $hasta = $fecha_fin->format();
                
                $strSQLporPeriodo = $strSQL . " AND a.fecha_inicio>='$desde' AND a.fecha_inicio<'$hasta' ORDER BY a.CUPS, a.fecha_inicio DESC";
                
                $PS = $Conn->getArray($strSQLporPeriodo, true);
                
                if (isset($PS) && !empty($PS)){
                    $SprdSht = new SprdSht;
                    $SprdSht->load('plantillas/PS ayuda.xlsx', false);
                    $SprdSht->putArray($PS, false, 'A2');
                    foreach ($PS as $num_row=>$row){$SprdSht->setValueAsText('M'.($num_row+2), $row['numero_contrato']);}
                    $SprdSht->setColumnsAutoWidth();
                    
                    $filename = "PS $cli $x $timestamp.xlsx";
                    $SprdSht->save($filename);
                    $files[] = $filename;
                    
                    unset($PS, $SprdSht, $filename);
                    
                    ++$x;
                }
                
                $fecha_inicio->add(0,1);
                $fecha_fin->add(0,1);
            }
            
            merge_and_dwd_zip("PS $cli.zip", $files, $timestamp);
            
        }
        
        
        
        
        break;
        
	case 'showSlaveStatus':
		
		$datos 		= $Conn->oneRow('SHOW SLAVE STATUS');
		$seconds 	= $datos['Seconds_Behind_Master'];
		unset($datos);
		
		switch (true){
			case ($seconds>=60 && $seconds<3600):
				$seconds = round($seconds/60);
				echo "Retraso de $seconds minuto/s";
				break;
				
			case ($seconds>=3600 && $seconds<3600*24):
				$seconds = round($seconds/60/60, 1);
				echo "Retraso de $seconds hora/s";
				break;
				
			case ($seconds>=(3600*24)):
				$seconds = round($seconds/60/60/24, 1);
				echo "Retraso de $seconds dia/s";
				break;
				
			case (!isset($seconds)):
				echo 'Error en el servidor. Contactar con Palomares';
				break;
				
			case (!$seconds):
				echo 'Ningún retraso';
				break;
				
			default:
				echo "Retraso de $seconds segundo/s";
				break;
		}
		unset($seconds);
		
		break;
		
} // Switch action

unset($Conn);

?>