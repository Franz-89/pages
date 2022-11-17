<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

switch ($_POST['action']){
		
	case 'update_todo':
		
		set_time_limit(1000);
		
		if (!isset($_POST['desde']) || !isset($_POST['hasta']) || empty($_POST['desde']) || empty($_POST['hasta'])){die;}
		
        $date = new DateClass;
		$Conn = new Conn('local', 'enertrade');
		
//COMPONENTES -------------------------------------------------------------
		$start_date = (empty($_POST['desde'])) 	? $Conn->oneData("SELECT MAX(fecha) FROM componentes_precio WHERE mercado_diario!=0")
												: $date->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d');
		
		$end_date = (empty($_POST['hasta'])) ? date('Y-m-d') : $date->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d');
		
		$start_date	= urlencode($start_date.'T00:00:00');
		$end_date	= urlencode($end_date.'T00:00:00');
		
		$componentes = array(805,806,807,808,809,810,811,812,813,814,815,816,1277,1286,1368);
		/*
		*805: Precio medio horario componente mercado diario
		*806: Precio medio horario componente restricciones PBF
		*807: Precio medio horario componente restricciones tiempo real
		*808: Precio medio horario componente mercado intradiario
		*809: Precio medio horario componente restricciones intradiario
		*810: Precio medio horario componente reserva de potencia adicional a subir
		*811: Precio medio horario componente banda secundaria
		*812: Precio medio horario componente desvíos medidos
		*813: Precio medio horario componente saldo de desvíos
		*814: Precio medio horario componente pago de capacidad
		*815: Precio medio horario componente saldo PO146
		*816: Precio medio horario componente fallo nominación UPG
		*1277: Precio medio horario componente servicio interrumpibilidad
		*1286: Precio medio horario componente control factor potencia
		*1368: Precio medio horario componente incumplimiento energía de balance
		*/
		
		$encabezados = array(
			'fecha',
			'mercado_diario',
			'restricciones_PBF',
			'restricciones_tiempo_real',
			'mercado_intradiario',
			'restricciones_intradiario',
			'reserva_de_potencia_adicional_a_subir',
			'banda_secundaria',
			'desvíos_medidos',
			'saldo_de_desvíos',
			'pago_de_capacidad',
			'saldo_PO146',
			'fallo_nominación_UPG',
			'servicio_interrumpibilidad',
			'control_factor_potencia',
			'incumplimiento_energía_de_balance'
		);
		
		$headers = array(
			'Accept: application/json; application/vnd.esios-api-v1+json',
			'Content-Type: application/json',
			'Host: api.esios.ree.es',
			'Authorization: Token token="18908d58a46d2ae5606f7b7efc9971a54ff8c6ff9dce14663735b2943387ba59"',
			'Cookie: '
		);
		
        
        $curl = new curlClass;
		$final = array();
        
		foreach ($componentes as $componente){
			
			//Solicita los datos
            $curl->url("https://api.esios.ree.es/indicators/$componente?start_date=$start_date&end_date=$end_date");
            $curl->httpHeaders($headers);
			$datos = $curl->execute();
			
			$datos = json_decode($datos);
			
			//Ordena los datos
			if (isset($datos) && !empty($datos)){
				foreach ($datos->indicator->values as $obj){
					$tipo = str_replace(' ', '_', str_replace('.', '', trim(str_replace('Precio medio horario componente', '', $datos->indicator->name))));
					$fecha = date('Y-m-d H:i', strtotime($obj->datetime.'+1 hour'));

					if (!array_key_exists($fecha, $final)){
						$final[$fecha] = array_fill_keys($encabezados, '');
						$final[$fecha]['fecha'] = $fecha;
					}
                    if ($tipo == 'servicio_de_interrumpibilidad'){$tipo = 'servicio_interrumpibilidad';}
					$final[$fecha][$tipo] = $obj->value;
				}
			}
		}
		unset($curl);
        
		//Carga los datos
		$str_headers = implode(", ", $encabezados);
		
		if (isset($final) && !empty($final)){
			
			foreach ($final as $fecha=>$valores){

			$values[] = "'".implode("','", $valores)."'";

				if((count($values)%1000) == 0){

					$str_values = "(".implode("),(", $values).")";
					$values = array();

					$StrSQL = "INSERT INTO componentes_precio ($str_headers) VALUES $str_values
								ON DUPLICATE KEY UPDATE
									mercado_diario 							= VALUES(mercado_diario),
									restricciones_PBF 						= VALUES(restricciones_PBF),
									restricciones_tiempo_real 				= VALUES(restricciones_tiempo_real),
									mercado_intradiario 					= VALUES(mercado_intradiario),
									restricciones_intradiario 				= VALUES(restricciones_intradiario),
									reserva_de_potencia_adicional_a_subir 	= VALUES(reserva_de_potencia_adicional_a_subir),
									banda_secundaria 						= VALUES(banda_secundaria),
									desvíos_medidos 						= VALUES(desvíos_medidos),
									saldo_de_desvíos 						= VALUES(saldo_de_desvíos),
									pago_de_capacidad 						= VALUES(pago_de_capacidad),
									saldo_PO146 							= VALUES(saldo_PO146),
									fallo_nominación_UPG 					= VALUES(fallo_nominación_UPG),
									servicio_interrumpibilidad 				= VALUES(servicio_interrumpibilidad),
									control_factor_potencia 				= VALUES(control_factor_potencia),
									incumplimiento_energía_de_balance 		= VALUES(incumplimiento_energía_de_balance)
								";
					$Conn->Query($StrSQL);
				}
			}

			if(!empty($values)){

				$str_values = "(".implode("),(", $values).")";
				$values = array();

				$StrSQL = "INSERT INTO componentes_precio ($str_headers) VALUES $str_values
							ON DUPLICATE KEY UPDATE
								mercado_diario 							= VALUES(mercado_diario),
								restricciones_PBF 						= VALUES(restricciones_PBF),
								restricciones_tiempo_real 				= VALUES(restricciones_tiempo_real),
								mercado_intradiario 					= VALUES(mercado_intradiario),
								restricciones_intradiario 				= VALUES(restricciones_intradiario),
								reserva_de_potencia_adicional_a_subir 	= VALUES(reserva_de_potencia_adicional_a_subir),
								banda_secundaria 						= VALUES(banda_secundaria),
								desvíos_medidos 						= VALUES(desvíos_medidos),
								saldo_de_desvíos 						= VALUES(saldo_de_desvíos),
								pago_de_capacidad 						= VALUES(pago_de_capacidad),
								saldo_PO146 							= VALUES(saldo_PO146),
								fallo_nominación_UPG 					= VALUES(fallo_nominación_UPG),
								servicio_interrumpibilidad 				= VALUES(servicio_interrumpibilidad),
								control_factor_potencia 				= VALUES(control_factor_potencia),
								incumplimiento_energía_de_balance 		= VALUES(incumplimiento_energía_de_balance)
							";
				$Conn->Query($StrSQL);
			}
			unset($final);
		}
		unset($StrSQL);
		
		$fecha_componentes = $date->fromToFormat($Conn->oneData("SELECT MAX(fecha) FROM componentes_precio WHERE mercado_diario!=0"), 'Y-m-d H:i:s', 'd/m/Y');
		
		
//AJUSTE OMIE -----------------------------------------------------------------------
        $Mercado = new Mercado;
        $Mercado->uploadOMIEAjuste();
        unset($Mercado);
        
//KEST -----------------------------------------------------------------------
		$desde = $date->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d');
		$hasta = $date->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d');
		
        //Suma OMIE ajustes al OMIE
        $Conn->Query("UPDATE componentes_precio SET mercado_diario_suma=(mercado_diario+mercado_diario_ajustes) WHERE fecha BETWEEN '$desde 00:00:00' AND '$hasta 00:00:00'");
        
		$timestamp = getMicrotimeString();
		
        //Repite el procedimiento para A2 primero y luego C2 para que sobrescriba
        for ($x=1; $x<=2;$x++){
            
            switch ($x){
                case 1:
                    $url = "https://api.esios.ree.es/archives/3/download?date_type=publicacion&end_date=$hasta&locale=es&start_date=$desde";
                    $kest = 'A2_Kestimado';
                    $mecanismo_ajuste = 'A2_grcosdecom';
                    break;  //A2
                case 2:
                    $url = "https://api.esios.ree.es/archives/8/download?date_type=publicacion&end_date=$hasta&locale=es&start_date=$desde";
                    $kest = 'C2_Kestimado';
                    $mecanismo_ajuste = 'C2_grcosdecom';
                    break;  //C2
                //case 3: $url = $url = "https://api.esios.ree.es/archives/2/download?date_type=publicacion&end_date=$hasta&locale=es&start_date=$desde"; break;  //A1
            }
            
            //Recupera los ficheros
            $curl = new curlClass;
            $curl->url($url);
            $datos = $curl->execute();

            $zip_name = "DATOS $timestamp.zip";
            file_put_contents($zip_name, $datos);

            $zip = new ZipArchive;

            if ($zip->open($zip_name) === TRUE){

                //Si no hay zips dentro del primer zip descargado
                if ($zip->numFiles > 100){
                    for ($x=0; $x<$zip->numFiles; $x++){
                        switch (true){
                            case (substr($zip->statIndex($x)['name'], 0, 12)==$kest):
                                $zip->extractTo("KEST $timestamp", array($zip->statIndex($x)['name']));
                                break;
                                
                            case (substr($zip->statIndex($x)['name'], 0, 13)==$mecanismo_ajuste):
                                $zip->extractTo("MECANISMO AJUSTE $timestamp", array($zip->statIndex($x)['name']));
                                break;
                        }
                    }
                    $zip->close();

                //Si hay zips dentro del primer zip descargado
                } else {

                    $temp_dir = "ZIP $timestamp";
                    $zip->extractTo($temp_dir);
                    $zip->close();

                    $zips = scandir($temp_dir);

                    foreach ($zips as $filenum=>$file){
                        if (pathinfo($file, PATHINFO_EXTENSION)=='zip'){

                            $zip->open("$temp_dir/$file");
                            for ($x=0; $x<$zip->numFiles; $x++){
                                switch (true){
                                    case (substr($zip->statIndex($x)['name'], 0, 12)==$kest):
                                        $zip->extractTo("KEST $timestamp", array($zip->statIndex($x)['name']));
                                        break;

                                    case (substr($zip->statIndex($x)['name'], 0, 13)==$mecanismo_ajuste):
                                        $zip->extractTo("MECANISMO AJUSTE $timestamp", array($zip->statIndex($x)['name']));
                                        break;
                                }
                            }
                            $zip->close();
                            unlink("$temp_dir/$file");
                        }
                    }
                    rmdir($temp_dir);
                }
            }
            unlink($zip_name);

            //KEST
            $files = @scandir("KEST $timestamp");

            if (isset($files) && !empty($files)){
                foreach($files as $file){

                    if ($file=='.' || $file=='..'){continue;}

                    $fecha = explode('_', $file);

                    $date->stringToDate($fecha[2], 'Ymd');
                    $date->stringToDate($date->format('Y-m-d 00:00:00'), 'Y-m-d H:i:s');

                    $fopen = fopen("KEST $timestamp/$file", 'r');

                    while (!feof($fopen)) {

                        $line = fgetcsv($fopen, 0, ';');

                        switch (trim(substr($line[0], 0, 1))){
                            case 'K':
                            case '2':
                            case '*':
                            case '':
                                unset($line);
                                continue(2);
                        }

                        for ($x=1; $x<=24; $x++){
                            $date->add(0,0,0,1);
                            $final[] = (!empty($line[$x])) 	? array('fecha'=>$date->format('Y-m-d H:i:s'), 'kest'=>$line[$x])
                                                            : array('fecha'=>$date->format('Y-m-d H:i:s'), 'kest'=>$line[$x-1]);
                        }

                    }
                    fclose($fopen);
                    unset($fopen);
                    unlink("KEST $timestamp/$file");
                }

                rmdir("KEST $timestamp");
            }


            //Carga los datos
            if (isset($final) && !empty($final)){
                foreach ($final as $num_row=>$row){
                    $values[] = "'".implode("','", $row)."'";

                    if((count($values)%1000) == 0){

                        $str_values = "(".implode("),(", $values).")";
                        $values = array();

                        $StrSQL = "INSERT INTO componentes_precio (fecha, kest) VALUES $str_values ON DUPLICATE KEY UPDATE kest = VALUES(kest)";
                        $Conn->Query($StrSQL);
                    }
                }

                if (!empty($values)){
                    $str_values = "(".implode("),(", $values).")";
                    $values = array();

                    $StrSQL = "INSERT INTO componentes_precio (fecha, kest) VALUES $str_values ON DUPLICATE KEY UPDATE kest = VALUES(kest)";
                    $Conn->Query($StrSQL);
                }
                unset($final);
            }
            
            
            //MECANISMO AJUSTE
            $files = @scandir("MECANISMO AJUSTE $timestamp");

            if (isset($files) && !empty($files)){
                foreach($files as $file){

                    if ($file=='.' || $file=='..'){continue;}

                    $fecha = explode('_', $file);

                    $date->stringToDate($fecha[2], 'Ymd');
                    $date->stringToDate($date->format('Y-m-d 00:00:00'), 'Y-m-d H:i:s');

                    $fopen = fopen("MECANISMO AJUSTE $timestamp/$file", 'r');

                    while (!feof($fopen)) {

                        $line = fgetcsv($fopen, 0, ';');
                        
                        $dato = (trim($line[0]));
                        switch (true){
                            case ($dato == 'grcosdec'):
                            case ($dato == '*'):
                            case ($dato == ''):
                            case (strlen($dato) == 4):
                                unset($line, $dato);
                                continue(2);
                        }
                        
                        if (!empty($line[0])){
                            $final[] = array('fecha'=>$date->fromToFormat($line[0], 'Ym d H', 'Y-m-d H:i:s'), 'mecanismo'=>$line[12]);
                        }
                        unset($desde_julio, $hasta_julio);
                    }
                    fclose($fopen);
                    unset($fopen);
                    unlink("MECANISMO AJUSTE $timestamp/$file");
                }

                rmdir("MECANISMO AJUSTE $timestamp");
            }


            //Carga los datos
            if (isset($final) && !empty($final)){
                foreach ($final as $num_row=>$row){
                    $values[] = "'".implode("','", $row)."'";

                    if((count($values)%1000) == 0){

                        $str_values = "(".implode("),(", $values).")";
                        $values = array();

                        $StrSQL = "INSERT INTO componentes_precio (fecha, mecanismo_ajuste) VALUES $str_values ON DUPLICATE KEY UPDATE mecanismo_ajuste = VALUES(mecanismo_ajuste)";
                        $Conn->Query($StrSQL);
                    }
                }

                if (!empty($values)){
                    $str_values = "(".implode("),(", $values).")";
                    $values = array();

                    $StrSQL = "INSERT INTO componentes_precio (fecha, mecanismo_ajuste) VALUES $str_values ON DUPLICATE KEY UPDATE mecanismo_ajuste = VALUES(mecanismo_ajuste)";
                    $Conn->Query($StrSQL);
                }
                unset($final);
            }
            unset($StrSQL);
        }
		
        $Conn->Query("UPDATE componentes_precio SET mecanismo_ajuste=0 WHERE fecha<'2022-06-15'");
        
		$fecha_kest = $date->fromToFormat($Conn->oneData("SELECT MAX(fecha) FROM componentes_precio WHERE kest!='NULL'"), 'Y-m-d H:i:s', 'd/m/Y');
		
        
        
		
//PERFILES ------------------------------------------------------
		
        $desde = new DateClass;
        $hasta = new DateClass;
		$desde->stringToDate($_POST['desde'], 'd/m/Y');
		$hasta->stringToDate($_POST['hasta'], 'd/m/Y');
		
		//Saca los datos
		while (($desde->vardate)<($hasta->vardate)){
			
			$fecha = $desde->format('Ym');
			
            $curl->url("https://www.ree.es/sites/default/files/simel/perff/PERFF_$fecha.gz");
            $curl->follow(true);
			$datos = $curl->execute();
			
			if (!empty($datos)){
				
				$datos = preg_split('/\r\n|\r|\n/', @gzdecode($datos));
				
				foreach ($datos as $num_row=>$row){
					
					$line = str_getcsv($row, ';');
					if (empty($line[0]) || $line[1]=='MES'){continue;}
					
					$fecha = $line[0].'-'.$line[1].'-'.$line[2].' '.($line[3]).':0:0';
					$fecha = new DateTime($fecha);
					
					$final[] = array(
						'fecha'		=>date_format($fecha, 'Y-m-d H:i:s'),
						'perfil_a'	=>$line[5],
						'perfil_b'	=>$line[6],
						'perfil_c'	=>$line[7],
						'perfil_d'	=>$line[8]
									);
					unset($fecha, $line);
				}
				unset($datos);
			}
			$desde->add(0,1);
		}
        
		unset($fecha, $desde, $hasta);
		
        
		//Carga los datos
		if (isset($final) && !empty($final)){
                
            $str_values = implode_values($final);

            $StrSQL = "INSERT INTO componentes_precio (fecha, perfil_a, perfil_b, perfil_c, perfil_d) VALUES $str_values
                        ON DUPLICATE KEY UPDATE perfil_a = VALUES(perfil_a),
                                                perfil_b = VALUES(perfil_b),
                                                perfil_c = VALUES(perfil_c),
                                                perfil_d = VALUES(perfil_d)
                                                ";
            $Conn->Query($StrSQL);
            unset($final, $StrSQL);
		}
		
		$fecha_perfiles = $date->fromToFormat($Conn->oneData("SELECT MAX(fecha) FROM componentes_precio WHERE perfil_a!='NULL'"), 'Y-m-d H:i:s', 'd/m/Y');
        
        $fecha_pvpc = $date->fromToFormat($Conn->oneData("SELECT MAX(fecha) FROM componentes_precio WHERE PVPC_20A!='NULL'"), 'Y-m-d H:i:s', 'd/m/Y');
        unset($Conn, $curl, $date);
        
		echo "$fecha_componentes|$fecha_kest|$fecha_perfiles|$fecha_pvpc";
		
		break;
		
	case 'download_componentes':
		
		if (!isset($_POST['desde']) || !isset($_POST['hasta']) || empty($_POST['desde']) || empty($_POST['hasta'])){
			header ("Location: consulta_mercado.php");
			die;
		}
		$date = new DateClass;
		$desde = $date->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d 00:00:00');
		$hasta = $date->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d 00:00:00');
        unset($date);
		
		$Conn = new Conn('local', 'enertrade');
		$datos = $Conn->getArray("SELECT * FROM componentes_precio WHERE fecha BETWEEN '$desde' AND '$hasta' ORDER BY fecha");
		unset($Conn);
		
		if (isset($datos) && !empty($datos)){
			
			foreach($datos as $num_row=>$row){$datos[$num_row]['fecha'] = str_replace("-", "/", $row['fecha']);}
			$SprdSht = new SprdSht;
			$SprdSht->nuevo();
			$SprdSht->putArray($datos, true);
			$SprdSht->directDownload('Componentes precio');
			unset($SprdSht);
		}
        header ("Location: consulta_mercado.php");
		
		break;
		
	case 'precio_objetivo':
		
		if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: consulta_mercado.php"); die;}
		if (!isset($_POST['normal']) && !isset($_POST['cdc']) && !isset($_POST['sqlite'])){header ("Location: consulta_mercado.php"); die;}
        
		foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){
				
				$SprdSht = new SprdSht;
				$SprdSht->load($file);
				
				$SprdSht->getSheet('LISTADO');
				$ps = $SprdSht->getArray(true);
				
				$SprdSht->getSheet('CONSUMOS');
				$consumos = $SprdSht->getArray(true);
				unset($SprdSht);
			}
		}
        
        $nuevo = (isset($_POST['nuevo_precio'])) ? true : false;
        
        switch (true){
            case (isset($_POST['normal'])): $tipo = 'normal';   break;
            case (isset($_POST['cdc'])):    $tipo = 'cdc';      break;
            case (isset($_POST['sqlite'])): $tipo = 'sqlite';   break;
        }
		$po = new PrecioObjetivo($ps, $consumos, $nuevo, $tipo);
		//$po->temp_curves();
		$po->directDownload('PRECIO');
        
		break;
        
    case 'perfiles':
        
        if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: consulta_mercado.php"); die;}
		
        $Conn = new Conn('local', 'enertrade');
        
		foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){
				
				$datos = file_get_contents($file);
                $datos = preg_split('/\r\n|\r|\n/', gzdecode($datos));
				
				foreach ($datos as $num_row=>$row){
					
					$line = str_getcsv($row, ';');
					if (empty($line[0]) || $line[1]=='MES'){continue;}
					
					$fecha = $line[0].'-'.$line[1].'-'.$line[2].' '.($line[3]).':0:0';
					$fecha = new DateTime($fecha);
					
					$final[] = array(
						'fecha'		=>date_format($fecha, 'Y-m-d H:i:s'),
						'perfil_a'	=>$line[5],
						'perfil_b'	=>$line[6],
						'perfil_c'	=>$line[7],
						'perfil_d'	=>$line[8]
									);
					unset($fecha, $line);
				}
				unset($datos);
                
                //Carga los datos
                if (isset($final) && !empty($final)){
                    foreach ($final as $num_row=>$row){
                        $values[] = "'".implode("','", $row)."'";

                        if((count($values)%1000) == 0){

                            $str_values = "(".implode("),(", $values).")";
                            $values = array();

                            $StrSQL = "INSERT INTO componentes_precio (fecha, perfil_a, perfil_b, perfil_c, perfil_d) VALUES $str_values
                                        ON DUPLICATE KEY UPDATE perfil_a = VALUES(perfil_a),
                                                                perfil_b = VALUES(perfil_b),
                                                                perfil_c = VALUES(perfil_c),
                                                                perfil_d = VALUES(perfil_d)
                                                                ";
                            $Conn->Query($StrSQL);
                        }
                    }

                    if (!empty($values)){
                        $str_values = "(".implode("),(", $values).")";
                        $values = array();

                        $StrSQL = "INSERT INTO componentes_precio (fecha, perfil_a, perfil_b, perfil_c, perfil_d) VALUES $str_values
                                        ON DUPLICATE KEY UPDATE perfil_a = VALUES(perfil_a),
                                                                perfil_b = VALUES(perfil_b),
                                                                perfil_c = VALUES(perfil_c),
                                                                perfil_d = VALUES(perfil_d)
                                                                ";
                        $Conn->Query($StrSQL);
                    }
                    unset($final);
                }
                unset($StrSQL);
			}
		}
        unset($Conn);
        
        header ("Location: consulta_mercado.php");
        
        break;
        
    case 'actualizacion_diaria':
		
		$Mercado = new Mercado;
		$Mercado->InformeDiario();
		unset($Mercado);
        
		break;
        
    case 'download_tabla_resumen':
        
        $TablasResumen = new TablasResumen($_POST['ano']);
        $tabla = $TablasResumen->getTabla($_POST['dato'], true, $_POST['producto']);
        unset($TablasResumen);
        
        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->putArray($tabla, true);
        $SprdSht->directDownload($_POST['dato']);
        unset($SprdSht, $tabla);
        
        break;
        
    case 'update_tablas_resumen':
        
        $TablasResumen = new TablasResumen($_POST['ano']);
        $TablasResumen->update($_POST['dato']);
        
        break;
        
    case 'download_futuros':
        
        $date = new DateClass;
        $CalculosSimples = new CalculosSimples;
        $desde = (empty($_POST['desde'])) ? $CalculosSimples->lunesDiaAnterior() : $date->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d');
        
        $date->resetDate();
        $hasta = (empty($_POST['hasta'])) ? $date->format() : $date->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d');
        
        $Conn = new Conn('local', 'enertrade');
        $datos = $Conn->getArray("SELECT * FROM montel_futuros WHERE FECHA>='$desde' AND FECHA<'$hasta'", true);
        unset($Conn, $CalculosSimples);
        
        if (!isset($datos) || empty($datos)){header ("Location: consulta_mercado.php");}
        
        foreach ($datos as $num_row=>$row){
            unset($datos[$num_row]['ID']);
            $datos[$num_row]['FECHA'] = $date->fromToFormat($row['FECHA']);
        }
        
        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->putArray($datos, true);
        $SprdSht->directDownload('Mercado a futuros');
        unset($datos, $SprdSht, $date);
        
        break;
        
    case 'download_spot':
        
        $date = new DateClass;
        $desde = (empty($_POST['desde'])) ? $date->format() : $date->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d');
        $date->add(0,0,1);
        $hasta = (empty($_POST['hasta'])) ? $date->format() : $date->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d');
        
        $Conn = new Conn('local', 'enertrade');
        $datos = $Conn->getArray("SELECT * FROM spot WHERE FECHA>='$desde' AND FECHA<'$hasta'", true);
        unset($Conn);
        
        if (!isset($datos) || empty($datos)){header ("Location: consulta_mercado.php");}
        
        foreach ($datos as $num_row=>$row){
            $datos[$num_row]['FECHA'] = $date->fromToFormat($row['FECHA']);
        }
        
        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->putArray($datos, true);
        $SprdSht->directDownload('Mercado spot');
        unset($datos, $SprdSht, $date);
        
        break;
        
    case 'download_omip_mibgas':
        
        $date = new DateClass;
        $CalculosSimples = new CalculosSimples;
        $desde = (empty($_POST['desde'])) ? $CalculosSimples->lunesDiaAnterior() : $date->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d');
        
        $date->resetDate();
        $hasta = (empty($_POST['hasta'])) ? $date->format() : $date->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d');
        
        $Conn = new Conn('local', 'enertrade');
        $datos = $Conn->getArray("SELECT * FROM omip WHERE FECHA>='$desde' AND FECHA<'$hasta'", true);
        unset($Conn, $CalculosSimples);
        
        if (!isset($datos) || empty($datos)){header ("Location: consulta_mercado.php");}
        
        foreach ($datos as $num_row=>$row){
            unset($datos[$num_row]['ID']);
            $datos[$num_row]['FECHA'] = $date->fromToFormat($row['FECHA']);
        }
        
        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->putArray($datos, true);
        $SprdSht->directDownload('OMIP-MIBGAS');
        unset($datos, $SprdSht);
        
        break;
}



?>