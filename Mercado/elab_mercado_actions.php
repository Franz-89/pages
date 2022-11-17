<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

switch ($_POST['action']){
		
    case 'informe_diario':
		
		$Mercado = new Mercado;
		$Mercado->directDownload('Informe_Diario');
		unset($Mercado);
		
		break;
        
    case 'sidenor_elec_gas':
        
        //Ultimo valor TTF_MA
        $Conn = new Conn('local', 'enertrade');
        $TTF_MA      = $Conn->getArray('SELECT DATE_FORMAT(FECHA, "%d/%m/%Y"), TTF_MA FROM spot WHERE TTF_MA IS NOT NULL ORDER BY FECHA ASC');
        $TTF_DA_OMIE = $Conn->getArray('SELECT DATE_FORMAT(FECHA, "%d/%m/%Y"), TTF, OMIE_MED, OMIE_AJUSTES FROM spot WHERE TTF IS NOT NULL AND OMIE_MED IS NOT NULL ORDER BY FECHA ASC');
        
        $date = new DateClass;
        $ano = $date->format('Y');
        $ano_corto = $date->format('y');
        $mes_actual = $date->format('n');
        
        //Tabla resumen TTF_MA
        $TablasResumen = new TablasResumen($ano);
        $tabla_resumen_TTF_MA = $TablasResumen->getTabla('TTF_MA', true, 'M');
        
        //Rellena con los datos a futuros
        $CalculosSimples = new CalculosSimples;
        $dia_anterior = $CalculosSimples->lunesDiaAnterior($date->format());
        
        $meses = array();
        foreach ($tabla_resumen_TTF_MA as $num_row=>$row){
            if (!empty($row[$ano]) && $num_row!=($mes_actual+1)){
                unset($tabla_resumen_TTF_MA[$num_row][$ano]);
                continue;
            }
            if ($num_row==($mes_actual+1)){continue;}
            $meses[] = "$ano-".sprintf('%02d', $row['PRODUCTO']).'-01';
        }
        
        $meses = "('".implode("', '", $meses)."')";
        
        $TTF_MA_futuros = $Conn->getArray("SELECT FECHA, PRODUCTO, ENDEX_DUTCH_TTF FROM montel_futuros WHERE FECHA='$dia_anterior' AND PRODUCTO IN $meses");
        unset($meses, $dia_anterior);
        
        
        
        $fecha = new DateClass;
        foreach ($TTF_MA_futuros as $num_row=>$row){
            $mes_producto = $fecha->fromToFormat($row['PRODUCTO'], 'Y-m-d', 'n');
            $tabla_resumen_TTF_MA[$mes_producto][$ano] = $row['ENDEX_DUTCH_TTF'];
        }
        unset($TTF_MA_futuros, $mes_producto);
        
        foreach ($tabla_resumen_TTF_MA as $num_row=>$row){
            unset($tabla_resumen_TTF_MA[$num_row]['PRODUCTO']);
            $tabla_resumen_TTF_MA[$num_row] = array_filter($tabla_resumen_TTF_MA[$num_row]);
        }
        $tabla_resumen_TTF_MA = array_filter($tabla_resumen_TTF_MA);
        $tabla_resumen_TTF_MA = array_values($tabla_resumen_TTF_MA);
        
        //Crea el encabezado de la tabla elec y gas
        $encabezado = array();
        for ($x=1; $x<=12; $x++){$encabezado[] = $date->format('01/'.sprintf('%02d', $x)."/$ano");}
        for ($x=1; $x<=2; $x++) {$encabezado[] = 'YR'.($ano_corto+$x);}
        
        $tabla_elec                 = array();
        $tabla_elec['ESPAÑA']       = array_fill_keys($encabezado, '');
        $tabla_elec['COMPLEMENTO']  = array_fill_keys($encabezado, '');
        $tabla_elec['SUMA']         = array_fill_keys($encabezado, '');
        $tabla_elec['FRANCIA']      = array_fill_keys($encabezado, '');
        $tabla_elec['ALEMANIA']     = array_fill_keys($encabezado, '');
        
        $tabla_gas = array();
        $tabla_gas['MIBGAS']    = array_fill_keys($encabezado, '');
        $tabla_gas['TTF']       = array_fill_keys($encabezado, '');
        $tabla_gas['UK']        = array_fill_keys($encabezado, '');
        $tabla_gas['BRENT_303'] = array_fill_keys($encabezado, '');
        
        unset($encabezados);
        
        //ELECTRICIDAD <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //Elec ESPAÑA (OMIP) ----------------------------------------------------------
        $TablasResumen->changeYear($ano);
        
        //Inserta los datos mensuales ya cotizados
        $OMIE_MED_resumen       = $TablasResumen->getTabla('OMIE_MED', true, 'M');
        $OMIE_AJUSTES_resumen   = $TablasResumen->getTabla('OMIE_AJUSTES', true, 'M');
        $OMIE_SUMA_resumen      = $TablasResumen->getTabla('OMIE_SUMA', true, 'M');
        $OMIP_resumen           = $TablasResumen->getTabla('OMIP', true);
        
        foreach ($OMIE_MED_resumen as $mes=>$row){
            if (!empty($row[$ano])){continue;}
            $OMIE_MED_resumen[$mes][$ano] = $OMIP_resumen[$mes][$ano];
        }
        
        //Rellena con los Q donde no hay datos de los meses
        foreach ($OMIE_MED_resumen as $mes=>$row){
            if (!empty($row[$ano])){continue;}
            $Q = $CalculosSimples->getQ($mes);
            $OMIE_MED_resumen[$mes][$ano] = $OMIP_resumen["Q$Q"][$ano];
        }
        //Rellena con los YR donde no hay datos de los meses
        foreach ($OMIE_MED_resumen as $mes=>$row){
            if (!empty($row[$ano])){continue;}
            $OMIE_MED_resumen[$mes][$ano] = $OMIP_resumen["YR$ano_corto"][$ano];
        }
        
        //Rellena la tabla final
        foreach ($OMIE_MED_resumen as $mes=>$row){
            $mes = $date->format('01/'.sprintf('%02d', $mes)."/$ano");
            $tabla_elec['ESPAÑA'][$mes] = $row[$ano];
        }
        
        $tabla_elec['ESPAÑA']['YR'.($ano_corto+1)] = $OMIP_resumen['YR'][($ano+1)];
        $tabla_elec['ESPAÑA']['YR'.($ano_corto+2)] = $OMIP_resumen['YR'][($ano+2)];
        
        foreach ($OMIE_AJUSTES_resumen as $mes=>$row){
            $mes = $date->format('01/'.sprintf('%02d', $mes)."/$ano");
            $tabla_elec['COMPLEMENTO'][$mes] = $row[$ano];
        }
        
        foreach ($OMIE_SUMA_resumen as $mes=>$row){
            $mes = $date->format('01/'.sprintf('%02d', $mes)."/$ano");
            $tabla_elec['SUMA'][$mes] = $row[$ano];
        }
        
        unset($OMIE_MED_resumen, $OMIP_resumen, $OMIE_AJUSTES_resumen, $OMIE_SUMA_resumen, $Q);
        
        
        //Elec FRANCIA/ALEMANIA ------------------------------------------------------
        $FR_AL = array('FRANCIA', 'ALEMANIA');
        
        foreach ($FR_AL as $nombre){
            //Inserta los datos mensuales ya cotizados
            $tabla_resumen_mes = $TablasResumen->getTabla($nombre, true, 'M');

            foreach ($tabla_resumen_mes as $num_row=>$row){
                $row['PRODUCTO'] = $date->format('01/'.sprintf('%02d', $row['PRODUCTO'])."/$ano");
                $tabla_elec[$nombre][$row['PRODUCTO']] = $row[$ano];
            }
            unset($tabla_resumen_mes);
            
            $fecha = new DateClass;
            $dia_anterior = $CalculosSimples->lunesDiaAnterior($fecha->format());
            
            //Inserta los datos mes e YR a futuros
            $strSQL = "SELECT FECHA, PRODUCTO, EEX_$nombre FROM montel_futuros
                        WHERE EEX_$nombre IS NOT NULL
                        AND FECHA='$dia_anterior'";
            $ultimo_valor = $Conn->getArray($strSQL, true);
            
            foreach ($ultimo_valor as $num_row=>$row){
                if (substr($row['PRODUCTO'], 0, 1)=='Q'){continue;}
                if (substr($row['PRODUCTO'], 0, 1)=='C'){
                    if (substr($row['PRODUCTO'], -2)>($ano_corto+2)){continue;}
                    $tabla_elec[$nombre]['YR'.substr($row['PRODUCTO'], -2)] = $row["EEX_$nombre"];
                    continue;
                }

                $date->stringToDate($row['PRODUCTO']);
                if ($date->format('Y')>$ano){continue;}

                $row['PRODUCTO'] = $date->fromToFormat($row['PRODUCTO']);
                if (!empty($tabla_elec[$nombre][$row['PRODUCTO']])){continue;}

                $tabla_elec[$nombre][$row['PRODUCTO']] = $row["EEX_$nombre"];
            }
            
            //Inserta los datos Q a futuros
            foreach ($tabla_elec[$nombre] as $producto=>$valor){
                if (substr($producto, 0, 1)=='Y' || !empty($valor)){continue;}

                $date->stringToDate($producto, 'd/m/Y');
                $Q = $CalculosSimples->getQ($date->format('n'));

                foreach ($ultimo_valor as $num_row=>$row){
                    if ($row['PRODUCTO']=="Q$Q$ano_corto"){
                        $tabla_elec[$nombre][$producto] = $row["EEX_$nombre"];
                        break;
                    }
                }
            }

            //Inserta los datos Y a futuros en los meses que no tienen ni mes ni Q cotizado
            foreach ($ultimo_valor as $num_row=>$row){
                if ($row['PRODUCTO']=="YR$ano_corto"){
                    foreach ($tabla_elec[$nombre] as $producto=>$valor){
                        if (!empty($valor)){continue;}
                        $tabla_elec[$nombre][$producto] = $row["EEX_$nombre"];
                    }
                    break;
                }
            }
            unset($ultimo_valor, $Q);
        }
        unset($FR_AL);
        
        
        //GAS <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        
        //MIBGAS ---------------------------------------------------------------------
        //Inserta los datos mensuales ya cotizados
        $MIBGAS_M_resumen = $TablasResumen->getTabla('MIBGAS', true, 'M');
        $MIBGAS_resumen   = $TablasResumen->getTabla('MIBGAS', true);
        
        //Rellena con los Q donde no hay datos de los meses
        foreach ($MIBGAS_M_resumen as $mes=>$row){
            if (!empty($row[$ano])){continue;}
            $Q = $CalculosSimples->getQ($mes);
            $MIBGAS_M_resumen[$mes][$ano] = $MIBGAS_resumen["Q$Q"][$ano];
        }
        
        //Rellena con los YR donde no hay datos de los meses
        foreach ($MIBGAS_M_resumen as $mes=>$row){
            if (!empty($row[$ano])){continue;}
            $MIBGAS_M_resumen[$mes][$ano] = $MIBGAS_resumen['YR'][$ano];
        }
        
        //Rellena la tabla final
        foreach ($MIBGAS_M_resumen as $mes=>$row){
            $mes = $date->format('01/'.sprintf('%02d', $mes)."/$ano");
            $tabla_gas['MIBGAS'][$mes] = $row[$ano];
        }
        
        $tabla_gas['MIBGAS']['YR'.($ano_corto+1)] = $MIBGAS_resumen['YR'][($ano+1)];
        $tabla_gas['MIBGAS']['YR'.($ano_corto+2)] = $MIBGAS_resumen['YR'][($ano+2)];
        
        unset($Q, $MIBGAS_M_resumen, $MIBGAS_resumen);
        
        
        //TTF_SPOT --------------------------------------------------------------------------
        //Inserta los datos mensuales ya cotizados
        $tabla_resumen_mes = $TablasResumen->getTabla('TTF', true, 'M');

        foreach ($tabla_resumen_mes as $num_row=>$row){
            $row['PRODUCTO'] = $date->format('01/'.sprintf('%02d', $row['PRODUCTO'])."/$ano");
            $tabla_gas['TTF'][$row['PRODUCTO']] = $row[$ano];
        }
        unset($tabla_resumen_mes);

        $fecha = new DateClass;
        $dia_anterior = $CalculosSimples->lunesDiaAnterior($fecha->format());

        //Inserta los datos mes e YR a futuros
        $strSQL = "SELECT FECHA, PRODUCTO, ENDEX_DUTCH_TTF FROM montel_futuros
                    WHERE ENDEX_DUTCH_TTF IS NOT NULL
                    AND FECHA='$dia_anterior'";
        $ultimo_valor = $Conn->getArray($strSQL, true);
        
        foreach ($ultimo_valor as $num_row=>$row){
            if (substr($row['PRODUCTO'], 0, 1)=='Q'){continue;}
            if (substr($row['PRODUCTO'], 0, 1)=='C'){
                if (substr($row['PRODUCTO'], -2)>($ano_corto+2)){continue;}
                $tabla_gas['TTF']['YR'.substr($row['PRODUCTO'], -2)] = $row['ENDEX_DUTCH_TTF'];
                continue;
            }

            $date->stringToDate($row['PRODUCTO']);
            if ($date->format('Y')>$ano){continue;}

            $row['PRODUCTO'] = $date->fromToFormat($row['PRODUCTO']);
            if (!empty($tabla_gas['TTF'][$row['PRODUCTO']])){continue;}

            $tabla_gas['TTF'][$row['PRODUCTO']] = $row['ENDEX_DUTCH_TTF'];
        }

        //Inserta los datos Q a futuros
        foreach ($tabla_gas['TTF'] as $producto=>$valor){
            if (substr($producto, 0, 1)=='Y' || !empty($valor)){continue;}

            $date->stringToDate($producto, 'd/m/Y');
            $Q = $CalculosSimples->getQ($date->format('n'));

            foreach ($ultimo_valor as $num_row=>$row){
                if ($row['PRODUCTO']=="Q$Q$ano_corto"){
                    $tabla_gas['TTF'][$producto] = $row['ENDEX_DUTCH_TTF'];
                    break;
                }
            }
        }

        //Inserta los datos Y a futuros en los meses que no tienen ni mes ni Q cotizado
        foreach ($ultimo_valor as $num_row=>$row){
            if ($row['PRODUCTO']=="YR$ano_corto"){
                foreach ($tabla_gas['TTF'] as $producto=>$valor){
                    if (!empty($valor)){continue;}
                    $tabla_gas['TTF'][$producto] = $row['ENDEX_DUTCH_TTF'];
                }
                break;
            }
        }
        unset($ultimo_valor, $Q);
        
        //BRENT_303 -------------------------------
        //Inserta los datos mensuales
        $tabla_resumen_mes = $TablasResumen->getTabla('BRENT_303', true, 'M');
        foreach ($tabla_resumen_mes as $num_row=>$row){
            $row['PRODUCTO'] = $date->format('01/'.sprintf('%02d', $row['PRODUCTO'])."/$ano");
            $tabla_gas['BRENT_303'][$row['PRODUCTO']] = $row[$ano];
        }
        
        //Inserta los datos YR
        $tabla_resumen_mes = $TablasResumen->getTabla('BRENT_303', true, 'Y');
        for ($x=1; $x<=2; $x++){
            foreach ($tabla_resumen_mes as $num_row=>$row){
                $producto = 'YR'.($ano_corto+$x);
                if (!isset($tabla_gas['BRENT_303'][$producto])){continue;}
                $tabla_gas['BRENT_303'][$producto] = $row[($ano+$x)];
            }
        }
        
        unset($TablasResumen, $tabla_resumen_mes, $CalculosSimples, $Conn);
        
        //Mete los datos y descarga el fichero
        $SprdSht = new SprdSht;
        $SprdSht->load('plantillas/Datos Sidenor.xlsx', false);
        $SprdSht->getSheet('TTF_MA');
        $SprdSht->putArray($TTF_MA, false, 'A4');
        unset($TTF_MA);
        
        $SprdSht->getSheet('TTF_Mensual');
        $celda = 'I'.(19+$mes_actual);
        $SprdSht->putArray($tabla_resumen_TTF_MA, false, $celda);
        unset($tabla_resumen_TTF_MA, $mes_actual);
        
        $SprdSht->getSheet('Maite');
        $SprdSht->putArray($tabla_elec, false, 'D5');
        $SprdSht->putArray($tabla_gas, false, 'D14');
        unset($tabla_gas, $tabla_elec);
        
        $SprdSht->getSheet('TTF_DA_OMIE');
        $SprdSht->putArray($TTF_DA_OMIE, false, 'A4');
        unset($TTF_DA_OMIE);
        
        
        $SprdSht->save('Datos Sidenor.xlsx');
        unset($SprdSht);
        
        $A = (!isset($_POST['destinatario']) || empty($_POST['destinatario'])) ? $usuario : $_POST['destinatario'];
        $A = array_filter(explode(';', $A));
        
        $CC = (!isset($_POST['copia']) || empty($_POST['copia'])) ? NULL : $_POST['copia'];
        if ($CC != NULL){$CC = array_filter(explode(';', $CC));}
        
        $SUJETO = 'DATOS GAS SIDENOR '.date('d/m/Y');

        $CUERPO = 	"Buenos días,<br><br>
                    Adjunto los datos de gas a dia de hoy.<br><br>";
        
        $ATTACH = array('Datos Sidenor.xlsx');

        mailDeA($SUJETO, $CUERPO, $A, $CC, NULL, false, $ATTACH);
        unlink('Datos Sidenor.xlsx');
        
        break;
        
    case 'froneri_elec_gas':
        
        $date = new DateClass;
        $ano  = $date->format('Y');
        
        //ELEC --------------------------------------------------------------------------------------
        $TablasResumen    = new TablasResumen($ano);
        $OMIE_MED_resumen = $TablasResumen->getTabla('OMIE_MED', true, 'M');
        $OMIP_resumen     = $TablasResumen->getTabla('OMIP', true);
        
        //Para el año en curso y el siguiente
        for ($x=0; $x<=1; $x++){
            foreach ($OMIE_MED_resumen as $mes=>$row){
                if (!empty($row[($ano+$x)])){continue;}
                $OMIE_MED_resumen[$mes][($ano+$x)] = $OMIP_resumen[$mes][($ano+$x)];
            }
        }
        
        //Rellena con los Q donde no hay datos de los meses
        $CalculosSimples = new CalculosSimples;
        for ($x=0; $x<=1; $x++){
            foreach ($OMIE_MED_resumen as $mes=>$row){
                if (!empty($row[($ano+$x)])){continue;}
                $Q = $CalculosSimples->getQ($mes);
                $OMIE_MED_resumen[$mes][($ano+$x)] = $OMIP_resumen["Q$Q"][($ano+$x)];
            }
        }
        
        //Rellena con los YR donde no hay datos de los meses
        $CalculosSimples = new CalculosSimples;
        for ($x=0; $x<=1; $x++){
            foreach ($OMIE_MED_resumen as $mes=>$row){
                if (!empty($row[($ano+$x)])){continue;}
                $OMIE_MED_resumen[$mes][($ano+$x)] = $OMIP_resumen['YR'][($ano+$x)];
            }
        }
        unset($Q, $OMIP_resumen);
        
        
        //GAS --------------------------------------------------------------------------------------
        $MIBGAS_M_resumen = $TablasResumen->getTabla('MIBGAS', true, 'M');
        $MIBGAS_resumen   = $TablasResumen->getTabla('MIBGAS', true);
        
        //Rellena con los Q donde no hay datos de los meses
        foreach ($MIBGAS_M_resumen as $mes=>$row){
            if (!empty($row[$ano])){continue;}
            $Q = $CalculosSimples->getQ($mes);
            $MIBGAS_M_resumen[$mes][$ano] = $MIBGAS_resumen["Q$Q"][$ano];
        }
        
        //Rellena con los YR donde no hay datos de los meses
        foreach ($MIBGAS_M_resumen as $mes=>$row){
            if (!empty($row[$ano])){continue;}
            $MIBGAS_M_resumen[$mes][$ano] = $MIBGAS_resumen['YR'][$ano];
        }
        unset($Q, $CalculosSimples, $OMIP_resumen, $TablasResumen);
        
        
        $SprdSht = new SprdSht;
        $SprdSht->load('plantillas/FRONERI GAS ELEC.xlsx', false);
        $SprdSht->getSheet('Resumen_ELEC');
        $SprdSht->putArray(array_chunk(array_column($OMIE_MED_resumen, $ano), 1), false, 'O6');
        $SprdSht->putArray(array_chunk(array_column($OMIE_MED_resumen, ($ano+1)), 1), false, 'O23');
        
        $SprdSht->getSheet('Resumen_GAS');
        $SprdSht->putArray(array_chunk(array_column($MIBGAS_M_resumen, $ano), 1), false, 'D6');
        unset($OMIE_MED_resumen, $MIBGAS_M_resumen);
        
        $SprdSht->directDownload('FRONERI GAS ELEC');
        
        break;
        
    case 'reinosa_elec_gas':
        
        $date = new DateClass;
        $ano = $date->format('Y');
        
        $ajuste_estimado = (empty($_POST['ajuste_estimado'])) ? 0 : $_POST['ajuste_estimado'];
        
        //GAS  ---------------------------------------------------------------------------------------------------
        $TablasResumen = new TablasResumen($ano);
        $TTF_MA_resumen = $TablasResumen->getTabla('TTF_MA', true, 'M');
        
        $CalculosSimples = new CalculosSimples;
        $dia_anterior = $CalculosSimples->lunesDiaAnterior();
        
        //Ultimo valor TTF_MA a futuros
        $Conn = new Conn('local', 'enertrade');
        $TTF_MA_futuros = $Conn->getArray("SELECT FECHA, PRODUCTO, ENDEX_DUTCH_TTF FROM montel_futuros WHERE FECHA='$dia_anterior'");
        unset($Conn);
        
        foreach ($TTF_MA_futuros as $num_row=>$row){
            switch (substr($row['PRODUCTO'], 0, 1)){
                case 'Q':
                case 'C':
                    continue(2);
                default:
                    $date->stringToDate($row['PRODUCTO']);
                    $mes = $date->format('n');
                    
                    if (empty($TTF_MA_resumen[$mes][$ano])){$TTF_MA_resumen[$mes][$ano] = $row['ENDEX_DUTCH_TTF'];}
            }
        }
        unset($TTF_MA_futuros);
        
        //ELEC  ---------------------------------------------------------------------------------------------------
        //Inserta los datos mensuales ya cotizados
        $OMIE_MED_resumen = $TablasResumen->getTabla('OMIE_SUMA', true, 'M');
        $OMIP_resumen     = $TablasResumen->getTabla('OMIP', true);
        
        foreach ($OMIE_MED_resumen as $mes=>$row){
            if (!empty($row[$ano])){continue;}
            $OMIE_MED_resumen[$mes][$ano] = $OMIP_resumen[$mes][$ano];
        }
        
        //Rellena con los Q donde no hay datos de los meses
        foreach ($OMIE_MED_resumen as $mes=>$row){
            if (!empty($row[$ano])){continue;}
            $Q = $CalculosSimples->getQ($mes);
            $OMIE_MED_resumen[$mes][$ano] = $OMIP_resumen["Q$Q"][$ano];
        }
        //Rellena con los YR donde no hay datos de los meses
        foreach ($OMIE_MED_resumen as $mes=>$row){
            if (!empty($row[$ano])){continue;}
            $OMIE_MED_resumen[$mes][$ano] = $OMIP_resumen["YR$ano_corto"][$ano];
        }
        //Suma el ajuste estimado a los valores futuros
        foreach ($OMIE_MED_resumen as $mes=>$row){
            if ($mes<=date('m')){continue;}
            $OMIE_MED_resumen[$mes][$ano] += $ajuste_estimado;
        }
        unset($Q, $OMIP_resumen, $TablasResumen, $CalculosSimples);
        
        
        $SprdSht = new SprdSht;
        $SprdSht->load('plantillas/REINOSA GAS ELEC.xlsx', false);
        $SprdSht->getSheet('Resumen_GAS');
        $SprdSht->putArray(array_chunk(array_column($TTF_MA_resumen, $ano), 1), false, 'F4');
        $SprdSht->getSheet('Resumen_ELEC');
        $SprdSht->putArray(array_chunk(array_column($OMIE_MED_resumen, $ano), 1), false, 'O6');
        $SprdSht->save('REINOSA GAS ELEC');
        unset($SprdSht, $TTF_MA_resumen, $OMIE_MED_resumen);
        
        $A = (!isset($_POST['destinatario']) || empty($_POST['destinatario'])) ? $usuario : $_POST['destinatario'];
        $A = array_filter(explode(';', $A));
        
        $CC = (!isset($_POST['copia']) || empty($_POST['copia'])) ? NULL : $_POST['copia'];
        if ($CC != NULL){$CC = array_filter(explode(';', $CC));}
        
        $SUJETO = 'Seguimiento Presupuesto de Gas REINOSA FORGINGS AND CASTINGS '.date('d/m/Y');

        $CUERPO = 	"Buenos días,<br><br>
                    Adjuntamos el seguimiento del presupuesto actualizado y su desviación versus Budget.<br>
                    <br>
                    Un saludo";
        
        $ATTACH = array('REINOSA GAS ELEC.xlsx');

        mailDeA($SUJETO, $CUERPO, $A, $CC, NULL, false, $ATTACH);
        unlink('REINOSA GAS ELEC.xlsx');
        
        break;
        
    case 'precio_objetivo':
		
		if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: elab_mercado.php"); die;}
		if (!isset($_POST['normal']) && !isset($_POST['cdc']) && !isset($_POST['sqlite'])){header ("Location: elab_mercado.php"); die;}
        
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
		$po->directDownload('PRECIO');
}

header ("Location: elab_mercado.php");

?>