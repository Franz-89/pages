<?php

if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: elaboracion_ff.php");}

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$array_header = encabezados_carga();

set_time_limit(1200);

switch ($_POST['action']){
        
    case 'electricidad':
        
        //FUNCIONES XLSX ------------------------
        //FUNCIÓN que comprueba si hay más de un valor separado por #
        function check_if_array($value_info){
            $result = (strpos($value_info, '#')) ? explode('#', $value_info) : $value_info;
            return (is_array($result)) ? array_filter($result) : $result;
        }

        //FUNCIÓN comprueba si es numero o texto
        function check_value($info, $value_info, $row, $is_num){

            switch (true){
                case (substr($value_info, 0, 5)=='const'):
                    if ($is_num && !is_numeric($info[$value_info])){settype($info[$value_info], "float");}
                    $result = ($is_num) ? $info[$value_info] : trim($info[$value_info]);
                    break;

                case (isset($row[$value_info])):
                    if ($is_num && !is_numeric($row[$value_info])){
                        $row[$value_info] = str_replace(',', '.', $row[$value_info]);
                        settype($row[$value_info], "float");
                    }
                    $result = ($is_num) ? $row[$value_info] : trim($row[$value_info]);
                    break;

                default:
                    $result = ($is_num) ? 0 : '';
                    break;
            }

            return $result;
        }
        
        function adaptFloat($value){    //Para IB_TXT
            $value = str_replace(',', '.', (0 . $value));
            settype($value, "float");
            return $value;
        }
        
        //FIN FUNCIONES INTERNAS ------------------------------------------------------------


        //FUNCIÓN con la cual se elabora una hoja entera - devuelve un array con todas las lineas
        function elab_ff_xlsx($info, $SprdSht){


            //Comprueba las constantes
            for ($x=1;$x<=5;$x++){

                $info["const$x"] = check_if_array($info["const$x"]);

                //Si hay más de una referencia para cada constante intenta con cada una de ellas. Si está vacia la primera va a la segunda
                if (is_array($info["const$x"])){
                    foreach ($info["const$x"] as $key=>$value){
                        $const = trim($SprdSht->getCellValue($value));
                        if (!empty($const)){break;}
                    }
                    $info["const$x"] = $const;
                    unset($const);
                } else {
                    $info["const$x"] = (!empty($info["const$x"])) ? trim($SprdSht->getCellValue($info["const$x"])) : '';
                }
            }


            $lineas_ff = $SprdSht->getArray(false);
            $Array     = new ArrayClass($lineas_ff);
            $lineas_ff = $Array->arrayToAssoc($info['linea_encabezado_base_0']);
            unset($Array);
            
            
            foreach ($lineas_ff as $num_row=>$row){
                
                foreach ($info as $key_info=>$value_info){
                    
                    switch ($key_info){

                        case 'ID':
                        case 'existe_hoja':
                        case 'celda':
                        case 'valor_celda':
                        case 'fichero':
                        case 'linea_encabezado_base_0':
                        case 'para_cada_hoja':
                        case 'fichero':
                        case 'tipo_fichero':
                        case 'const1':
                        case 'const2':
                        case 'const3':
                        case 'const4':
                        case 'const5':
                            break;

                        case 'CUPS':
                        case 'Numero_de_contrato':
                        case 'Fecha_factura':
                        case 'Fecha_desde':
                        case 'Fecha_hasta':
                        case 'Fecha_desde_(potencia)':
                        case 'Fecha_hasta_(potencia)':
                        case 'Numero_de_factura':
                        case 'Tipo_factura':
                        case 'Factura_rectificada':
                        case 'Periodo_de_facturacion':
                        case 'Reclamada':
                        case 'Observaciones':
                        case 'CIF':

                            $is_num      = false;
                            $linea[$key_info] = '';

                            if (empty($value_info)){continue(2);}

                            //Comprueba si hay más de un valor a concatenar
                            $value_info = check_if_array($value_info);

                            //Si hay más de un valor concatena todos los valores con _
                            if (is_array($value_info)){

                                foreach ($value_info as $key_useless=>$value2){
                                    $valor_def = check_value($info, $value2, $row, $is_num);
                                    $linea[$key_info] .= (!empty($linea[$key_info]) && !(substr($linea[$key_info], -1)=='_')) ? '_'.$valor_def : $valor_def;
                                }
                                //Si es numero de factura sin secuencial
                                if ($key_info=='Numero_de_factura' && count($value_info)<=2){
                                    $linea[$key_info] .= "_$num_row";
                                }

                            } else {
                                $linea[$key_info] .= check_value($info, $value_info, $row, $is_num);
                            }
                            
                            //Cups
                            if ($key_info=='CUPS'){
                                $linea[$key_info] = trim(substr($linea[$key_info], 0, 20));
                                
                                if (empty($linea[$key_info])){
                                    unset($linea);
                                    continue(3);
                                }
                            }
                            
                            
                            
                            break;

                        default:

                            $is_num           = true;
                            $linea[$key_info] = 0;

                            if (empty($value_info)){continue(2);}

                            //Comprueba si hay más de un valor a sumar
                            $value_info = check_if_array($value_info);

                            //Si hay más de un valor suma todos los valores
                            if (is_array($value_info)){
                                foreach ($value_info as $key_useless=>$value2){

                                    //Si hay más de un valor para el máximetro busca el max entre los valores
                                    switch ($key_info){
                                        case 'Maximetro_P1':
                                        case 'Maximetro_P2':
                                        case 'Maximetro_P3':
                                        case 'Maximetro_P4':
                                        case 'Maximetro_P5':
                                        case 'Maximetro_P6':
                                            $linea[$key_info] = max($linea[$key_info], check_value($info, $value2, $row, $is_num));
                                            break;
                                        default:
                                            $linea[$key_info] += check_value($info, $value2, $row, $is_num);
                                    }
                                }
                            } else {
                                $linea[$key_info] += check_value($info, $value_info, $row, $is_num);
                            }

                            break;
                    }//switch info
                }//foreach info
                
                //AREGLOS SEMI FINALES
                //Activa
                if (!$linea['Consumo_total']){
                    for ($x=1;$x<=6;$x++){
                        $linea['Consumo_total'] += $linea["Consumo_energia_P$x"];
                    }
                }
                //Reactiva
                if (!$linea['Consumo_total_reactiva']){
                    for ($x=1;$x<=6;$x++){
                        $linea['Consumo_total_reactiva'] += $linea["Consumo_reactiva_P$x"];
                    }
                }
                
//CASOS PARTICULARES DESPUÉS de la elaboración por cada linea del FF
                switch ($info['ID']){
                    case 'ENDESA_BT_AGRUPADA':
                    case 'ENDESA_DETALLADO':
                        
                        //Procedimientos individuales para cada uno
                        switch ($info['ID']){
                            case 'ENDESA_BT_AGRUPADA':
                                $linea['CIF'] = trim(str_replace("CIF:", "", $linea['CIF']));

                                for ($x=1;$x<=6;$x++){
                                    $linea["Maximetro_P$x"] = 	$linea["Maximetro_P$x"]/1000;
                                }
                                
                                $val1 = check_value($info, 'IMP. EX. POT (€)', $row, true);
                                $val2 = check_value($info, 'IMP. EX. POT', $row, true);
                                $linea['Excesos_de_potencia_(€)'] = ($val1) ? $val1 : $val2;
                                unset($val1, $val2);
                                
                                break;
                                
                            case 'ENDESA_DETALLADO':
                                $conv_fecha = new DateClass;
                                $linea['Fecha_factura'] = $conv_fecha->fromToFormat($linea['Fecha_factura'], 'Ymd', 'd/m/Y');
                                $linea['Fecha_desde'] = $conv_fecha->fromToFormat($linea['Fecha_desde'], 'Ymd', 'd/m/Y');
                                $linea['Fecha_hasta'] = $conv_fecha->fromToFormat($linea['Fecha_hasta'], 'Ymd', 'd/m/Y');
                                $linea['Fecha_desde_(potencia)'] = $linea['Fecha_desde'];
                                $linea['Fecha_hasta_(potencia)'] = $linea['Fecha_hasta'];
                                unset($conv_fecha);
                                break;
                        }
                        
                        //Procedimientos en común
                        //Comprueba las abreviaciones de los otros conceptos
                        for ($x=1; $x<=20; $x++){
                            $clave = "CONCEPTO $x";
                            $valor = "IMPORTE CONCEPTO $x";

                            if (array_key_exists($clave, $row)){
                                switch (substr($row[$clave], 0, 4)){
                                    case ""		:
                                        break;
                                    case "EPI1"	:	//Activa indexado
                                    case "EPI2"	:	//Activa indexado
                                    case "EPI3"	:	//Activa indexado
                                    case "EPI4"	:	//Activa indexado
                                    case "EPI5"	:	//Activa indexado
                                    case "EPI6"	:	//Activa indexado
                                    case "ACE "	:	//Abono consumo estimado
                                    case "ACEP"	:	//Abono consumo estimado punta
                                    case "ACEL"	:	//Abono consumo estimado llano
                                    case "ACEV"	:	//Abono consumo estimado valle
                                    case "ACIS"	:	//Abono calidad de suministro
                                    case "GCIS"	:	//Abono calidad de suministro
                                    case "FCLR"	:	//Fact. Energía entre reales
                                    case "DTO "	:	//Descuento
                                    case "FCRP"	:	//Fact. Energía entre reales punta
                                    case "FCRL"	:	//Fact. Energía entre reales llano
                                    case "FCRV"	:	//Fact. Energía entre reales valle
                                        settype($row[$valor], "float");
                                        $linea['Termino_Energia_(€)'] += $row[$valor];
                                        break;
                                    case "BSOC"	:	//Bonificación suministro de socorro
                                        settype($row[$valor], "float");
                                        $linea['Termino_Potencia_(€)'] += $row[$valor];
                                        break;
                                    case "FIAD"	:	//Depósito de garantía
                                        settype($row[$valor], "float");
                                        $linea['Otros_conceptos_(exentos_IVA_€)'] += $row[$valor];
                                        $linea['Observaciones'] .= "Depósito de garantía: ".$row[$valor]."€ ";
                                        break;
                                    case "ST19"	:	//Suplemento territorial
                                    case "REPO"	:	//Regularización potencia
                                    case "RENE"	:	//Regularización energía
                                    case "FBOS":    //Financiación bono social
                                        settype($row[$valor], "float");
                                        $linea['Otros_conceptos_(con_IVA_e_IEE_€)'] += $row[$valor];
                                        break;
                                    case "FCAP"	:	//Coste del tope de gas
                                        settype($row[$valor], "float");
                                        $linea['Tope_de_gas'] += $row[$valor];
                                        break;
                                    default		:
                                        settype($row[$valor], "float");
                                        $linea['Otros_conceptos_(con_IVA_€)'] += $row[$valor];
                                        break;
                                }
                            }
                        }
                        break;

                    case 'ENDESA_MT_AGRUPADA':

                        //Si Total < 0 (Si es un abono)
                        if ($linea['Total_factura_(€)'] < 0){
                            for ($x=1; $x<=6; $x++){
                                $linea["Consumo_energia_P$x"] 	=	$linea["Consumo_energia_P$x"]*-1;
                                $linea["Consumo_reactiva_P$x"]  =	$linea["Consumo_reactiva_P$x"]*-1;
                            }
                            $linea['Consumo_total']		     = $linea['Consumo_total']*-1;
                            $linea['Consumo_total_reactiva'] = $linea['Consumo_total_reactiva']*-1;
                        }

                        break;

                    case 'ELECTRA_ENERGIA':
                    case 'EDP_AGRUPADA':

                        foreach ($linea as $key=>$value){
                            $linea[$key] = (is_numeric($value)) ? round($value, 2) : $value;
                        }

                        break;

                    case 'ELEIA':

                        $val1 = check_value($info, 'Importe Energía Acceso', $row, true);
                        $val2 = check_value($info, 'Importe Energía Variable', $row, true);
                        $val3 = check_value($info, 'Coste Activa', $row, true);

                        $linea['Termino_Energia_(€)'] += (($val1+$val2)==0) ? $val3 : ($val1+$val2);
                        unset($val1, $val2, $val3);

                        break;

                    case 'ENDESA_MT_NO_AGRUPADA_2':

                        for ($y=1; $y<=50; $y++){
                            if (!isset($row["ICONFAC$y"])){continue;}
                            switch ($row["TCONFAC$y"]){
                                case 1722: $valor = 'Termino_Energia_(€)';      break;
                                case 1065: $valor = 'Termino_Potencia_(€)';     break;
                                case 1066: $valor = 'Excesos_de_potencia_(€)';  break;
                                case 671:  $valor = 'Alquiler_contador_(€)';    break;
                                case 5162: $valor = 'Impuesto_electrico_(€)';   break;
                                default: break;
                            }
                            if (!isset($valor)){continue;}

                            if ($valor == 'Impuesto_electrico_(€)'){
                                $linea[$valor] += (!$linea[$valor]) ? check_value($info, "ICONFAC$y", $row, true) : 0;
                            } else {
                                $linea[$valor] = check_value($info, "ICONFAC$y", $row, true);
                            }
                            unset($valor);
                        }

                        break;

                    case 'IB_ACORDEON':

                        if ($row['TIPO_FACT']=='GAS'){
                            unset($linea);
                            continue(2);
                        }

                        if($linea['Fecha_desde']=='01/01/9999'){$linea['Fecha_desde'] = $linea['Fecha_factura'];}
                        if($linea['Fecha_hasta']=='01/01/9999'){$linea['Fecha_hasta'] = $linea['Fecha_desde'];}
                        if($linea['Fecha_desde_(potencia)']=='01/01/9999'){$linea['Fecha_desde_(potencia)'] = $linea['Fecha_desde'];}
                        if($linea['Fecha_hasta_(potencia)']=='01/01/9999'){$linea['Fecha_hasta_(potencia)'] = $linea['Fecha_hasta'];}


                        //Cambia el formato de las fechas
                        /*
                        $date = new DateClass;
                        $linea['Fecha_factura']          = $date->fromToFormat($linea['Fecha_factura'], 'd/m/Y G:i:s', 'd/m/Y');
                        $linea['Fecha_desde']            = $date->fromToFormat($linea['Fecha_desde'], 'd/m/Y G:i:s', 'd/m/Y');
                        $linea['Fecha_hasta']            = $date->fromToFormat($linea['Fecha_hasta'], 'd/m/Y G:i:s', 'd/m/Y');
                        $linea['Fecha_desde_(potencia)'] = $date->fromToFormat($linea['Fecha_desde_(potencia)'], 'd/m/Y G:i:s', 'd/m/Y');
                        $linea['Fecha_hasta_(potencia)'] = $date->fromToFormat($linea['Fecha_hasta_(potencia)'], 'd/m/Y G:i:s', 'd/m/Y');
                        unset($date);
                        */
                        $linea['Factura_rectificada'] =	str_replace(array('-', '.'), array('', ''), $linea['Factura_rectificada']);

                        //Consumos
                        for ($y=1; $y<=5; $y++){
                            for ($x=1; $x<=6; $x++){

                                $linea["Consumo_energia_P$x"]  += check_value($info, "0$y"."_TE$x"."_kWh", $row, true);

                                $linea["Consumo_reactiva_P$x"] += check_value($info, "0$y"."_CRFP$x"."_Lect_KVArh", $row, true);
                                $linea["Consumo_reactiva_P$x"] += check_value($info, "0$y"."_CRFP$x"." _Lect_KVArh", $row, true);

                                $linea["Maximetro_P$x"] 	= max($linea["Maximetro_P$x"], check_value($info, "0$y"."_MAFP$x"."_Lect_kW", $row, true));
                                $linea["Maximetro_P$x"] 	= max($linea["Maximetro_P$x"], check_value($info, "0$y"."_MAFP$x"." _Lect_kW", $row, true));
                            }
                        }

                        //Activa 2a fase
                        for ($x=1; $x<=6; $x++){
                            if ($linea["Consumo_energia_P$x"]==0){
                                for ($y=1; $y<=5; $y++){
                                    $linea["Consumo_energia_P$x"]  += check_value($info, "0$y"."_TEP$x"."_kWh", $row, true);
                                }
                            }
                        }


                        if ($linea['Base_imponible_IE_(€)']==0){$linea['Base_imponible_IE_(€)'] += check_value($info, 'Total_Base_IE_Euros', $row, true);}
                        if ($linea['Base_imponible_total_(€)']==0){$linea['Base_imponible_total_(€)'] += check_value($info, 'IVAred1_BASE_EUROS', $row, true) + check_value($info, 'IVAred2_BASE_EUROS', $row, true);}
                        if ($linea['Total_IVA_general_(€)']==0){$linea['Total_IVA_general_(€)'] += check_value($info, 'IVAred1_IMPORTE_EUROS', $row, true) + check_value($info, 'IVAred2_IMPORTE_EUROS', $row, true);}

                        //ADIF
                        /*
                        switch ($linea['CIF']){
                            case 'B95938577':
                            case 'B92902642':
                            case 'B26293613':
                            case 'B93045136':
                                $linea['Termino_Energia_(€)'] = round(check_value($info, 'Total_TEpe_Euros', $row, true)*0.051127, 2);
                                break;
                        }
                        */

                        break;


                    case 'ENDESA_SGP':

                        $linea['Numero_de_contrato'] = str_replace("'", "", $linea['Numero_de_contrato']);
                        $linea['Numero_de_factura']  = str_replace("'", "", $linea['Numero_de_factura']);

                        break;

                    case 'NEXUS':

                        $albaran = check_value($info, 'Albarán de factura', $row, false);
                        $linea['Numero_de_factura'] = ($albaran) ? substr($albaran, -12) : check_value($info, 'Nº documento oficial', $row, false);

                        switch (true){
                            case (isset($row['Importe Termino Potencia'])):
                                $linea['Termino_Potencia_(€)'] += check_value($info, 'Importe Termino Potencia', $row, true);
                                break;
                            case (isset($row['Total Importe Potencia cargo + peaje'])):
                                $linea['Termino_Potencia_(€)'] += check_value($info, 'Total Importe Potencia cargo + peaje', $row, true);
                                break;
                            default:
                                $linea['Termino_Potencia_(€)'] += check_value($info, 'Total Importe Cargo Potencia + Peaje por', $row, true);
                                break;

                        }

                        $val1 = check_value($info, 'Importe Total Otros Conceptos', $row, true);
                        $val2 = check_value($info, 'Importe Abono por calidad de suministro', $row, true);
                        $linea['Otros_conceptos_(con IVA_€)'] = $val1 - $val2;

                        $linea['Total_IVA_general_(€)'] = $linea['Total_factura_(€)'] - $linea['Base_imponible_total_(€)'];

                        break;

                    case 'ENDESA_MT_SANTANDER':

                        $linea['Total_IVA_general_(€)']	-=	check_value($info, 'Imp IGIC norm (euros)', $row, true);

                        break;

                    case 'ENDESA_MT_NO_AGRUPADA':
                    case 'ENDESA_BT_3':

                        $val1 = check_value($info, 'Nº factura agrupada', $row, false);
                        $val2 = check_value($info, 'Nº factura', $row, false);
                        $val3 = check_value($info, 'Secuencial', $row, false);

                        switch (true){
                            case (!empty($val1)):
                                $linea['Numero_de_factura'] = $val1.'_'.$linea['Numero_de_contrato'].'_'.$val3;
                                break;

                            case (!empty($val2)):
                                $linea['Numero_de_factura'] = $val2;
                                break;

                            default:
                                $linea['Numero_de_factura'] = $linea['CUPS'].'_'.$linea['Numero_de_contrato'].'_'.$val3;
                                break;
                        }
                        unset($val1, $val2, $val3);

                        break;

                        //OTROS CASOS SI HAY
                }
//FIN CASOS PARTICULARES DESPUÉS de la elaboración por cada linea del FF


                //ARREGLOS FINALES

                //Repite la suma del total activa o reactiva en el caso de que los arreglos especiales hayan cambiado algo
                //Activa
                if (!$linea['Consumo_total']){
                    for ($x=1;$x<=6;$x++){
                        $linea['Consumo_total'] += $linea["Consumo_energia_P$x"];
                    }
                }
                //Reactiva
                if (!$linea['Consumo_total_reactiva']){
                    for ($x=1;$x<=6;$x++){
                        $linea['Consumo_total_reactiva'] += $linea["Consumo_reactiva_P$x"];
                    }
                }


                //Si no hay BIE hace la suma
                $linea['Base_imponible_IE_(€)'] = (!$linea['Base_imponible_IE_(€)']) ? ($linea['Otros_conceptos_(con_IVA_e_IEE_€)'] + $linea['Termino_Potencia_(€)'] + $linea['Termino_Energia_(€)'] + $linea['Excesos_reactiva_(€)'] + $linea['Excesos_de_potencia_(€)'] + $linea['Tope_de_gas'] + $linea['Mecanismo_de_ajuste']) : $linea['Base_imponible_IE_(€)'];

                //Si no hay BI suma los tres valores y si la suma es 0 hace la resta entre total e IVA
                $linea['Base_imponible_total_(€)'] = (!$linea['Base_imponible_total_(€)']) ? ($linea['Base_imponible_(IVA_general_€)'] + $linea['Base_imponible_(IVA_reducido_€)']) : $linea['Base_imponible_(IVA_superreducido_€)'];
                if (!$linea['Base_imponible_total_(€)']){
                    $BI = $linea['Total_factura_(€)'] - ($linea['Total_IVA_general_(€)'] + $linea['Total_IVA_reducido_(€)'] + $linea['Total_IVA_superreducido_(€)']);
                    $linea['Base_imponible_total_(€)'] = $BI;
                    $linea['Base_imponible_(IVA_general_€)'] = $BI;
                    unset($BI);
                }
                
                
                //Si hay derechos lo pone en las observaciones
                $temp_headers = array('Derechos_de_enganche_(€)', 'Derechos_de_acceso_(€)');
                foreach ($temp_headers as $header){
                    $linea['Observaciones'] = ($linea[$header]) ? "$header: ".str_replace('.', ',', $linea[$header]).'€ ' : $linea['Observaciones'];
                }
                
                $final[] = $linea;
                unset($linea);

            }//foreach linea_ff
            
            return (isset($final)) ? $final : NULL;

        }//FIN FUNCIÓN XLSX
        
        
        //Empieza a analizar cada fichero
        $filenum = 0;
		foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){
				
                $filename = $_FILES['fichero']['name'][$filenum];
                $Carpetas = new Carpetas;
                $extension = $Carpetas->getExtensionFromFilename($filename);
                unset($Carpetas);
                
                $Conn = new Conn('local', 'enertrade');
                $plantillas = $Conn->getArray("SELECT * FROM plantillas_conversion_ff WHERE tipo_fichero='$extension'", true);
                
                
                switch ($extension){
//XLSX
                    case 'xlsx':
                        
//VERDADERO CODIGO QUE LLAMA LAS FUNCIONES DE ARRIBA
                        $SprdSht = new SprdSht;
                        $SprdSht->load($file);

                        foreach ($plantillas as $num_row=>$row){
                            
                            $row['valor_celda'] = check_if_array($row['valor_celda']);
                            if (empty($row['existe_hoja'])){
                                
                                //Si hay que comprobar varios valores en una misma celda
                                if (is_array($row['valor_celda'])){
                                    foreach ($row['valor_celda'] as $num=>$value){
                                        if ($SprdSht->getCellValue($row['celda'])==$value){
                                            $info = $row;
                                            break(2);
                                        }
                                    }
                                } else {
                                    if ($SprdSht->getCellValue($row['celda'])==$row['valor_celda']){
                                        $info = $row;
                                        break;
                                    }
                                }
                                
                            } else {
                                if ($SprdSht->sheetExists($row['existe_hoja'])){
                                    $info = $row;
                                    break;
                                }
                            }
                        }//para cada plantilla
                        unset($plantillas);
                        
                        
                        $final = array();
                        switch (true){
                            // Si no encuentra ninguna referencia
                            case (!isset($info)):
                                break(2);
                                
                            //Selecciona la hoja especifica para cada caso particular
                                
                            case ($info['ID']=='ENDESA_MT_NO_AGRUPADA'):
                                $SprdSht->getSheet('Informe MT');
                                $final = elab_ff_xlsx($info, $SprdSht);
                                break;
                                
                            case ($info['ID']=='ENDESA_BT_3'):
                                $SprdSht->getSheet('Informe BT');
                                $final = elab_ff_xlsx($info, $SprdSht);
                                break;
                                
                            case ($info['ID']=='ENDESA_BT_2'):
                                $SprdSht->getSheet('Informe facturación');
                                $final = elab_ff_xlsx($info, $SprdSht);
                                break;
                                
                            case ($info['para_cada_hoja']):

                                for ($i = 0; $i < ($SprdSht->getShtCnt()); $i++){
                                    $SprdSht->getSheet($i);
                                    $temp = elab_ff_xlsx($info, $SprdSht);
                                    if (isset($temp) && !empty($temp)){$final = array_merge($final, $temp);}
                                    unset($temp);
                                }
                                break;
                                
                            default:
                                $final = elab_ff_xlsx($info, $SprdSht);
                                break;
                        }//switch true
                        unset($SprdSht);
                        
                        break; // FIN XLSX
                        
//CSV
                    case 'csv':
                        
                        //PEPE ENERGY <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                        $date = new DateClass;

                        $fopen = fopen($file, 'r');
                        while (!feof($fopen)) {
                            $line=fgets($fopen);
                            $line=explode(';', trim(str_replace(array('.', ','), array('', '.'), $line)));

                            if ($line[0]==''){continue;}

                            if ($line[0]=='Num Factura'){
                                $headers = $line;
                                continue;
                            }
                            $linea = array_combine($headers, $line);
                            $array_assoc[]=$linea;
                            unset($linea);
                        }
                        unset ($fopen, $headers);

                        $facturas = array();
                        //Coloca los datos
                        foreach ($array_assoc as $num_row=>$row){

                            $linea = array_fill_keys($array_header, '');

                            $linea['CUPS'] 						= 	substr(trim($row['CUPS']), 0, 20);

                            if (in_array($row['Num Factura'], array_keys($facturas))){
                                $facturas[$row['Num Factura']] = $facturas[$row['Num Factura']] + 1;
                            } else {
                                $facturas[$row['Num Factura']] = 1;
                            }

                            $linea['Número de factura']			=	trim($row['Num Factura']).'_'.$linea['CUPS'].'_'.$facturas[$row['Num Factura']];

                            $linea['Fecha factura'] 				= 	$row['Fecha Factura'];
                            $linea['Fecha desde'] 					= 	$row['Periodo desde'];
                            $linea['Fecha desde (potencia)'] 		= 	$row['Periodo desde'];
                            $linea['Fecha hasta'] 					= 	$row['Periodo hasta'];
                            $linea['Fecha hasta (potencia)'] 		= 	$row['Periodo hasta'];

                            //Consumos
                            $linea['Consumo total reactiva'] = 0;
                            for ($x=1; $x<=6; $x++){
                                settype($row["Energía Activa en Contador P$x"], 'float');
                                settype($row["Energía Reactiva P$x"], 'float');
                                settype($row["Maximetro P$x"], 'float');
                                $linea["Consumo energía P$x"] 	  =  $row["Energía Activa en Contador P$x"];
                                $linea["Consumo reactiva P$x"]   =  $row["Energía Reactiva P$x"];
                                $linea["Maxímetro P$x"] 		  =  $row["Maximetro P$x"];
                                $linea['Consumo total reactiva'] += $row["Energía Reactiva P$x"];;
                            }

                            settype($row['Total Energía Contador'], 'float');
                            $linea['Consumo total'] 							= $row['Total Energía Contador'];

                            settype($row['Importe Energía Activa'], 'float');
                            settype($row['Importe Energía Reactiva'], 'float');
                            settype($row['Importe potencia contratada'], 'float');
                            settype($row['Importe Exceso de Potencia'], 'float');

                            $linea['Término Energía (€)'] 						= $row['Importe Energía Activa'];
                            $linea['Excesos reactiva (€)'] 					    = $row['Importe Energía Reactiva'];
                            $linea['Término Potencia (€)']						= $row['Importe potencia contratada'];
                            $linea['Excesos de potencia (€)'] 					= $row['Exceso de Potencia'];

                            $linea['Derechos de acceso (€)'] 			        = 0;
                            $linea['Derechos de enganche (€)'] 			        = 0;

                            $linea['Otros conceptos (con IVA e IEE, €)']		= 0;

                            settype($row['Bono social'], 'float');
                            $linea['Otros conceptos (con IVA, €)'] 			    = $row['Bono social'];

                            $linea['Base imponible IE (€)'] 					= $row['Importe Energía Activa'] + $row['Importe Energía Reactiva'] + $row['Importe potencia contratada'] + $row['Importe Exceso de Potencia'];

                            settype($row['Impuesto eléctrico'], 'float');
                            settype($row['Total base imponible'], 'float');
                            settype($row['Impuesto'], 'float');
                            $linea['Impuesto eléctrico (€)'] 					= $row['Impuesto eléctrico'];
                            $linea['Base imponible total (€)'] 						= $row['Total base imponible'];
                            $linea['Total IVA general (€)'] 							= $row['Impuesto'];

                            $linea['Otros conceptos (exentos IVA, €)'] 	    = 0;

                            settype($row['Alquiler contador'], 'float');
                            settype($row['Total Factura'], 'float');
                            $linea['Alquiler contador (€)'] 					= $row['Alquiler contador'];
                            $linea['Total factura (€)'] 						= $row['Total Factura'];

                            //Añade la linea al array total
                            $final[] = $linea;
                            unset($linea);
                        } //Para cada linea
                        unset($array_assoc);

                        break; //FIN CSV
                        
//TXT
                    case 'txt':
                        
                        $fopen = fopen($file, 'r');
                        while (!feof($fopen)) {
                            $line=fgets($fopen);
                            $line=trim($line);
                            $datos[]=explode('#', $line);
                        }
                        unset ($fopen);
                        
                        switch (true){
                            case ($datos[0][0]==1):
                                $tipo_fichero = 'IB_TXT';
                                break;
                            default:
                                $tipo_fichero = 'ENDESA_MT_TXT';
                                break;
                        }
                        
                        unset($datos);
                        
            //TIPOS FICHEROS TXT
                        switch ($tipo_fichero){
                            
                            case 'ENDESA_MT_TXT':

                                $final = array();
                                $fras_count = 0;

                                $datos = array();
                                $fopen = fopen($file, 'r');
                                while (!feof($fopen)) {
                                    $line=fgets($fopen);
                                    $line=trim($line);
                                    $datos[]=explode(';', $line);
                                }
                                unset ($fopen);

                                //Para cada linea
                                for($x=0; $x<count($datos); $x++){
                                    if (!isset($datos[$x][0])){continue;}
                                    switch ($datos[$x][0]){
                                        case 25:

                                            //Si está desplazado de una celda
                                            $d = (str_replace(" ", "", $datos[$x][6]) == "" || str_replace(" ", "", $datos[$x][6])=="CI") ? 1 : 0;

                                            settype($datos[$x][24+$d], "float");
                                            $final[$fras_count-1]['Otros conceptos (con IVA e IEE, €)'] += $datos[$x][24+$d]/10000;
                                            break;

                                        case 10:

                                            $final[$fras_count] = array_fill_keys($array_header, '');

                                            //Si está desplazado de una celda
                                            $d = (str_replace(" ", "", $datos[$x][6]) == "") ? 1 : 0;

                                            $final[$fras_count]['CUPS'] 					= substr(trim($datos[$x][1]), 0, 20);
                                            $final[$fras_count]['Número de contrato'] 	= trim($datos[$x][6+$d]);
                                            $final[$fras_count]['Número de factura'] 	= trim($datos[$x][13+$d])."_".trim($datos[$x][6+$d])."_".str_replace(trim($datos[$x][6+$d]), "", trim($datos[$x][7+$d]));

                                            $final[$fras_count]['Fecha factura'] = date_format(date_create_from_format('Ymd', $datos[$x][11+$d]), 'd/m/Y');

                                            $final[$fras_count]['Fecha desde'] = date_format(date_create_from_format('Ymd', $datos[$x][23+$d]), 'd/m/Y');
                                            $final[$fras_count]['Fecha hasta'] = date_format(date_create_from_format('Ymd', $datos[$x][24+$d]), 'd/m/Y');
                                            $final[$fras_count]['Fecha desde (potencia)'] = $final[$fras_count]['Fecha desde'];
                                            $final[$fras_count]['Fecha hasta (potencia)'] = $final[$fras_count]['Fecha hasta'];

                                            //Activa
                                            settype($datos[$x+1][12], "float");
                                            settype($datos[$x+4][12], "float");
                                            settype($datos[$x+7][12], "float");
                                            settype($datos[$x+10][12], "float");
                                            settype($datos[$x+13][12], "float");
                                            settype($datos[$x+16][12], "float");
                                            $final[$fras_count]['Consumo energía P1'] = $datos[$x+1][12]/100;
                                            $final[$fras_count]['Consumo energía P2'] = $datos[$x+4][12]/100;
                                            $final[$fras_count]['Consumo energía P3'] = $datos[$x+7][12]/100;
                                            $final[$fras_count]['Consumo energía P4'] = $datos[$x+10][12]/100;
                                            $final[$fras_count]['Consumo energía P5'] = $datos[$x+13][12]/100;
                                            $final[$fras_count]['Consumo energía P6'] = $datos[$x+16][12]/100;
                                            $final[$fras_count]['Consumo total'] = $final[$fras_count]['Consumo energía P1'];
                                            $final[$fras_count]['Consumo total'] += $final[$fras_count]['Consumo energía P2'];
                                            $final[$fras_count]['Consumo total'] += $final[$fras_count]['Consumo energía P3'];
                                            $final[$fras_count]['Consumo total'] += $final[$fras_count]['Consumo energía P4'];
                                            $final[$fras_count]['Consumo total'] += $final[$fras_count]['Consumo energía P5'];
                                            $final[$fras_count]['Consumo total'] += $final[$fras_count]['Consumo energía P6'];

                                            //Reactiva
                                            settype($datos[$x+2][12], "float");
                                            settype($datos[$x+5][12], "float");
                                            settype($datos[$x+8][12], "float");
                                            settype($datos[$x+11][12], "float");
                                            settype($datos[$x+14][12], "float");
                                            settype($datos[$x+17][12], "float");
                                            $final[$fras_count]['Consumo reactiva P1'] = $datos[$x+2][12]/100;
                                            $final[$fras_count]['Consumo reactiva P2'] = $datos[$x+5][12]/100;
                                            $final[$fras_count]['Consumo reactiva P3'] = $datos[$x+8][12]/100;
                                            $final[$fras_count]['Consumo reactiva P4'] = $datos[$x+11][12]/100;
                                            $final[$fras_count]['Consumo reactiva P5'] = $datos[$x+14][12]/100;
                                            $final[$fras_count]['Consumo reactiva P6'] = $datos[$x+17][12]/100;
                                            $final[$fras_count]['Consumo total reactiva'] = $final[$fras_count]['Consumo reactiva P1'];
                                            $final[$fras_count]['Consumo total reactiva'] += $final[$fras_count]['Consumo reactiva P2'];
                                            $final[$fras_count]['Consumo total reactiva'] += $final[$fras_count]['Consumo reactiva P3'];
                                            $final[$fras_count]['Consumo total reactiva'] += $final[$fras_count]['Consumo reactiva P4'];
                                            $final[$fras_count]['Consumo total reactiva'] += $final[$fras_count]['Consumo reactiva P5'];
                                            $final[$fras_count]['Consumo total reactiva'] += $final[$fras_count]['Consumo reactiva P6'];

                                            //Máxima
                                            settype($datos[$x+3][12], "float");
                                            settype($datos[$x+6][12], "float");
                                            settype($datos[$x+9][12], "float");
                                            settype($datos[$x+12][12], "float");
                                            settype($datos[$x+15][12], "float");
                                            settype($datos[$x+18][12], "float");
                                            $final[$fras_count]['Maxímetro P1'] = $datos[$x+3][12]/100;
                                            $final[$fras_count]['Maxímetro P2'] = $datos[$x+6][12]/100;
                                            $final[$fras_count]['Maxímetro P3'] = $datos[$x+9][12]/100;
                                            $final[$fras_count]['Maxímetro P4'] = $datos[$x+12][12]/100;
                                            $final[$fras_count]['Maxímetro P5'] = $datos[$x+15][12]/100;
                                            $final[$fras_count]['Maxímetro P6'] = $datos[$x+18][12]/100;

                                            settype($datos[$x][80+$d], "float");
                                            settype($datos[$x][26+$d], "float");
                                            settype($datos[$x][69+$d], "float");
                                            settype($datos[$x][107+$d], "float");
                                            $final[$fras_count]['Término Energía (€)'] 		= $datos[$x][80+$d]/10000;
                                            $final[$fras_count]['Término Potencia (€)'] 		= $datos[$x][26+$d]/10000;
                                            $final[$fras_count]['Excesos de potencia (€)'] 	= $datos[$x][69+$d]/10000;
                                            $final[$fras_count]['Excesos reactiva (€)'] 		= $datos[$x][107+$d]/10000;

                                            settype($datos[$x][134+$d], "float");
                                            settype($datos[$x][130+$d], "float");
                                            settype($datos[$x][133+$d], "float");
                                            settype($datos[$x][142+$d], "float");
                                            settype($datos[$x][143+$d], "float");
                                            settype($datos[$x][15+$d], "float");
                                            $final[$fras_count]['Alquiler contador (€)'] 	= $datos[$x][134+$d]/10000;
                                            $final[$fras_count]['Base imponible IE (€)'] 	= $datos[$x][130+$d]/10000;
                                            $final[$fras_count]['Impuesto eléctrico (€)'] = $datos[$x][133+$d]/10000;
                                            $final[$fras_count]['Base imponible (IVA general €)'] 	= $datos[$x][142+$d]/10000;
                                            $final[$fras_count]['Base imponible total (€)'] = $datos[$x][142+$d]/10000;
                                            $final[$fras_count]['Total IVA general (€)'] 	= $datos[$x][144+$d]/10000;
                                            $final[$fras_count]['Total factura (€)'] 		= $datos[$x][15+$d]/100;
                                            

                                            $final[$fras_count]['Derechos de acceso (€)'] 					= 	0;
                                            $final[$fras_count]['Derechos de enganche (€)'] 					= 	0;
                                            $final[$fras_count]['Porcentaje IVA general (%)'] 						= 	0;
                                            $final[$fras_count]['Otros conceptos (exentos IVA, €)'] 			= 	0;
                                            $final[$fras_count]['Otros conceptos (con IVA, €)'] 				= 	0;
                                            $final[$fras_count]['Ajuste alquiler (€)'] 						= 	0;
                                            $final[$fras_count]['Regularización conversión de tarifas (€)'] 	= 	0;
                                            $final[$fras_count]['Concepto regulado término variable (€)'] 	= 	0;
                                            $final[$fras_count]['Otros conceptos (con IVA e IEE, €)']		=	0;

                                            //Abono
                                            if ($final[$fras_count]['Total factura (€)'] < 0){
                                                for ($y=1; $y<=6; $y++){
                                                    $final[$fras_count]["Consumo energía P$y"] 	= $final[$fras_count]["Consumo energía P$y"]*-1;
                                                    $final[$fras_count]["Consumo reactiva P$y"] 	= $final[$fras_count]["Consumo reactiva P$y"]*-1;
                                                    $final[$fras_count]["Maxímetro P$y"] 		    = $final[$fras_count]["Maxímetro P$y"]*-1;
                                                }

                                                $final[$fras_count]['Consumo total reactiva'] 	= $final[$fras_count]['Consumo total reactiva']*-1;
                                                $final[$fras_count]['Consumo total'] 			    = $final[$fras_count]['Consumo total']*-1;
                                            }

                                            ++$fras_count;
                                            break;

                                        default: break;
                                    }
                                }
                                unset ($datos);

                                break;
                                
                                
                            case 'IB_TXT':
                                
                                $datos = array();
                                $fopen = fopen($file, 'r');
                                while (!feof($fopen)) {
                                    $line=fgets($fopen);
                                    $line=str_replace('.', '', trim($line));
                                    $datos[]=explode('#', $line);
                                }
                                unset ($fopen);

                                //Referecias periodos (TARIFA_TENSIÓN=>PERIODOS)
                                $REF = array(
                                    '2.1_BT'    =>1,
                                    'TUR20_BT'  =>1,
                                    'TL1E_BT'   =>1,
                                    '_BT'       =>1,
                                    'TL2E_BT'   =>2,
                                    'TUR2P_BT'  =>2,
                                    '3.1A_AT'   =>3,
                                    'TLPLV_AT'  =>3,
                                    'TLPLV_BT'  =>3,
                                    '3.0A_BT'   =>3,
                                    'LX40_BT'   =>3,
                                    'LNB1_BT'   =>3,
                                    'LX21_BT'   =>3,
                                    'TL1E_BT'   =>3,
                                    'TL19_BT'   =>6,
                                    'TL19_AT'   =>6,
                                    '6.2_AT'    =>6,
                                    '6.1_AT'    =>6,
                                    '6.1_AB'    =>6,
                                    '_AT'       =>6,
                                    'TL20_AT'   =>6,
                                    'TL19_AB'   =>6
                                );
                                
                                $date = new DateClass;
                                
                                foreach ($datos as $num_row=>$row){

                                    if (empty($row[0])){continue;}

                                    //Si no es el encabezado de la fra
                                    switch ($row[0]){
                                        case 1:
                                            $num_fra_general = $row[9];
                                        case 2:
                                        case 4:
                                        case 5:
                                        case 6:
                                        case 7:
                                            continue(2);
                                    }

                                    //Si no es una fra de elec
                                    switch ($row[8]){
                                        case 'PYS':
                                        case 'AJU':
                                        case 'GAS':
                                            continue(2);
                                    }


                                    $linea = array_fill_keys($array_header, '');
                                    $linea['CUPS'] 					= substr(trim($row[169]), 0, 20);
                                    $linea['Número de contrato'] 	= trim($row[2]);
                                    //Num fra ficticio si no hay num fra
                                    $linea['Número de factura'] 	= (empty(trim($row[3]))) ? $num_fra_general.'_'.date('Ymd')."_$num_row" : $row[3];

                                    $linea['Fecha factura'] = $row[11];
                                    $linea['Tipo factura'] = ($row[9]=='RA') ? 'RA' : '';

                                    $linea['Fecha desde'] = ($row[43]=='01/01/9999') ? $row[11] : $row[43];
                                    $linea['Fecha hasta'] = ($row[44]=='01/01/9999') ? $row[11] : $row[44];
                                    $linea['Fecha desde (potencia)'] = $linea['Fecha desde'];
                                    $linea['Fecha hasta (potencia)'] = $linea['Fecha hasta'];

                                    $linea['Total IVA general (€)'] 			    = adaptFloat($row[150]) + adaptFloat($row[162]);
                                    $linea['Total IVA general (€)'] 			    = ($linea['Total IVA general (€)']==0) ? adaptFloat($row[104]) : $linea['Total IVA general (€)'];
                                    $linea['Base imponible (IVA general €)'] = adaptFloat($row[148]) + adaptFloat($row[164]);
                                    $linea['Base imponible total (€)'] = $linea['Base imponible (IVA general €)'];
                                    $linea['Total factura (€)'] 		    = adaptFloat($row[145]);


                                    $linea['Derechos de acceso (€)'] 					= 	0;
                                    $linea['Derechos de enganche (€)'] 					= 	0;
                                    $linea['Porcentaje IVA (%)'] 						= 	0;
                                    $linea['Otros conceptos (exentos IVA, €)'] 			= 	0;
                                    $linea['Otros conceptos (con IVA, €)'] 				= 	0;
                                    $linea['Ajuste alquiler (€)'] 						= 	0;
                                    $linea['Regularización conversión de tarifas (€)'] 	= 	0;
                                    $linea['Concepto regulado término variable (€)'] 	= 	0;
                                    $linea['Otros conceptos (con IVA e IEE, €)']		=	0;
                                    $linea['Otros conceptos (con IVA, €)'] 	            =   0;

                                    switch ($row[8]){
                                        case 'DER':
                                            $linea['Derechos de acceso (€)'] 	        = adaptFloat($row[102]);
                                            $final[] = $linea;
                                            unset($linea);
                                            continue(2);

                                        default:
                                            $linea['Otros conceptos (con IVA, €)']      = adaptFloat($row[102]);
                                            break;
                                    }


                                    $linea['Término Energía (€)'] 		= adaptFloat($row[76]);
                                    
                                    //TP
                                    $linea['Término Potencia (€)'] 		= 0;
                                    for ($x=0; $x<6; $x++){
                                        $linea['Término Potencia (€)']  += adaptFloat($row[53+$x]);
                                    }

                                    $row[51] = adaptFloat($row[51]);
                                    switch (true){
                                        case ($linea['Término Potencia (€)']==0):
                                        case ($linea['Término Potencia (€)']<$row[51]):
                                            $linea['Término Potencia (€)'] = $row[51];
                                            break;
                                    }


                                    $linea['Excesos reactiva (€)']          = adaptFloat($row[92]);
                                    $linea['Excesos de potencia (€)']       = adaptFloat($row[100]);

                                    $linea['Alquiler contador (€)'] 	    = adaptFloat($row[110]);

                                    $linea['Consumo total']                 = adaptFloat($row[134]);
                                    $linea['Impuesto eléctrico (€)']        = adaptFloat($row[107]);

                                    $linea['Base imponible IE (€)'] = $linea['Término Energía (€)'] + $linea['Término Potencia (€)'] + $linea['Excesos reactiva (€)'] + $linea['Excesos de potencia (€)'];


                                    //Calcula cuantas lineas componen la fra ($x)
                                    for ($x=1;$x<=12;$x++){
                                        if (isset($datos[$num_row+$x][0])){
                                            if ($datos[$num_row+$x][0]==3){break;}
                                        } else {
                                            continue(2);
                                        }
                                        
                                    }

                                    //Según el numero de linea que tiene hay seguir un procedimiento
                                    switch (true){
                                        case ($x>3):

                                            //Maxima
                                            for ($y=0;$y<6;$y++){
                                                $linea['Maxímetro P'.($y+1)] = max(adaptFloat($datos[$num_row+1][79+(9*$y)]), adaptFloat($datos[$num_row+2][79+(9*$y)]), adaptFloat($datos[$num_row+3][79+(9*$y)]))/1000;
                                            }

                                            //Activa
                                            //P2
                                            if (($datos[$num_row+2][9]==0 || $datos[$num_row+2][9]=='') && $datos[$num_row+2][18]!=''){
                                                $linea['Consumo energía P2'] = adaptFloat($datos[$num_row+1][17]) + adaptFloat($datos[$num_row+1][18]);
                                            } else {

                                                for ($y=1;$y<=3;$y++){
                                                    $datos[$num_row+$y][17] = adaptFloat($datos[$num_row+$y][17]);
                                                    $datos[$num_row+$y][18] = adaptFloat($datos[$num_row+$y][18]);
                                                }

                                                $linea['Consumo energía P2'] = $datos[$num_row+1][17] + $datos[$num_row+1][18] + $datos[$num_row+2][17] + $datos[$num_row+2][18] + $datos[$num_row+3][17] + $datos[$num_row+3][18];
                                            }

                                            $linea['Consumo energía P1'] = 0;
                                            $linea['Consumo energía P3'] = 0;
                                            $linea['Consumo energía P4'] = 0;
                                            $linea['Consumo energía P5'] = 0;
                                            $linea['Consumo energía P6'] = 0;
                                            for ($y=1; $y<=3; $y++){
                                                $linea['Consumo energía P1'] += adaptFloat($datos[$num_row+$y][8]) + adaptFloat($datos[$num_row+$y][9]);
                                                $linea['Consumo energía P3'] += adaptFloat($datos[$num_row+$y][25]) + adaptFloat($datos[$num_row+$y][26]);
                                                $linea['Consumo energía P4'] += adaptFloat($datos[$num_row+$y][34]) + adaptFloat($datos[$num_row+$y][35]);
                                                $linea['Consumo energía P5'] += adaptFloat($datos[$num_row+$y][43]) + adaptFloat($datos[$num_row+$y][44]);
                                                $linea['Consumo energía P6'] += adaptFloat($datos[$num_row+$y][52]) + adaptFloat($datos[$num_row+$y][53]);
                                            }

                                            break;

                                        case ($x<=2):

                                            for ($y=0;$y<6;$y++){
                                                $linea['Maxímetro P'.($y+1)] = adaptFloat($datos[$num_row+1][79+($y*9)])/1000;
                                            }

                                            $linea['Consumo energía P1'] = adaptFloat($datos[$num_row+1][8]) + adaptFloat($datos[$num_row+1][9]);
                                            $linea['Consumo energía P2'] = adaptFloat($datos[$num_row+1][17]) + adaptFloat($datos[$num_row+1][18]);
                                            $linea['Consumo energía P3'] = adaptFloat($datos[$num_row+1][25]) + adaptFloat($datos[$num_row+1][26]);
                                            $linea['Consumo energía P4'] = adaptFloat($datos[$num_row+1][34]) + adaptFloat($datos[$num_row+1][35]);
                                            $linea['Consumo energía P5'] = adaptFloat($datos[$num_row+1][43]) + adaptFloat($datos[$num_row+1][44]);
                                            $linea['Consumo energía P6'] = adaptFloat($datos[$num_row+1][52]) + adaptFloat($datos[$num_row+1][53]);

                                            break;

                                        case ($x<=3):

                                            //Maxima
                                            for ($y=0;$y<6;$y++){
                                                $linea['Maxímetro P'.($y+1)] = max(adaptFloat($datos[$num_row+1][79+(9*$y)]), adaptFloat($datos[$num_row+2][79+(9*$y)]))/1000;
                                            }

                                            //Activa
                                            $linea['Consumo energía P1'] = 0;
                                            $linea['Consumo energía P2'] = 0;
                                            $linea['Consumo energía P3'] = 0;
                                            $linea['Consumo energía P4'] = 0;
                                            $linea['Consumo energía P5'] = 0;
                                            $linea['Consumo energía P6'] = 0;

                                            for ($y=1;$y<=2;$y++){
                                                $linea['Consumo energía P1'] = adaptFloat($datos[$num_row+$y][8]) + adaptFloat($datos[$num_row+$y][9]);
                                                $linea['Consumo energía P2'] = adaptFloat($datos[$num_row+$y][17]) + adaptFloat($datos[$num_row+$y][18]);
                                                $linea['Consumo energía P3'] = adaptFloat($datos[$num_row+$y][25]) + adaptFloat($datos[$num_row+$y][26]);
                                                $linea['Consumo energía P4'] = adaptFloat($datos[$num_row+$y][34]) + adaptFloat($datos[$num_row+$y][35]);
                                                $linea['Consumo energía P5'] = adaptFloat($datos[$num_row+$y][43]) + adaptFloat($datos[$num_row+$y][44]);
                                                $linea['Consumo energía P6'] = adaptFloat($datos[$num_row+$y][52]) + adaptFloat($datos[$num_row+$y][53]);
                                            }

                                            break;
                                    }


                                    //Reactiva
                                    switch ($x){
                                        case 13:

                                            $linea['Consumo reactiva P1'] = adaptFloat($datos[$num_row+1][63]) + adaptFloat($datos[$num_row+2][63]);
                                            $linea['Consumo reactiva P2'] = adaptFloat($datos[$num_row+3][63]) + adaptFloat($datos[$num_row+4][63]);
                                            $linea['Consumo reactiva P3'] = adaptFloat($datos[$num_row+5][63]) + adaptFloat($datos[$num_row+6][63]);
                                            $linea['Consumo reactiva P4'] = adaptFloat($datos[$num_row+7][63]) + adaptFloat($datos[$num_row+8][63]);
                                            $linea['Consumo reactiva P5'] = adaptFloat($datos[$num_row+9][63]) + adaptFloat($datos[$num_row+10][63]);
                                            $linea['Consumo reactiva P6'] = adaptFloat($datos[$num_row+11][63]) + adaptFloat($datos[$num_row+12][63]);

                                            break;

                                        default:

                                            for ($y=1;$y<=6;$y++){
                                                $linea["Consumo reactiva P$y"] = ($x>$y) ? adaptFloat($datos[$num_row+$y][63]) : 0;
                                            }
                                            break;
                                    }

                                    $linea['Consumo total reactiva'] = 0;
                                    for ($y=1;$y<=6;$y++){
                                        $linea['Consumo total reactiva'] += $linea["Consumo reactiva P$y"];
                                    }


                                    $ref_concat = $row[29].'_'.$row[170];
                                    if (array_key_exists($ref_concat, $REF) && $REF[$ref_concat]==3){
                                        for ($y=1;$y<=3;$y++){
                                            $linea["Consumo energía P$y"] = $linea["Consumo energía P$y"] + $linea['Consumo energía P'.($y+3)];
                                            $linea["Consumo reactiva P$y"] = $linea["Consumo reactiva P$y"] + $linea['Consumo reactiva P'.($y+3)];
                                            $linea["Maxímetro P$y"] = max($linea["Maxímetro P$y"], $linea['Maxímetro P'.($y+3)]);
                                        }
                                        for ($y=4;$y<=6;$y++){
                                            $linea["Consumo energía P$y"] = 0;
                                            $linea["Consumo reactiva P$y"] = 0;
                                            $linea["Maxímetro P$y"] = 0;
                                        }
                                    }
                                    
                                    $final[] = $linea;
                                    unset($linea, $ref_concat);

                                }//Foreach $datos as $num_row=>$row

                                break;
                        }//Switch tipo fichero
                        
                        
                        break; //FIN TXT
                        
                }//switch extension
                
                
                //Guarda el fichero
                if (isset($final) && !empty($final)){
                    
                    $filename 	= str_replace(".$extension", '', $filename).' TRANSFORMADO.xlsx';
                    $files[] = $filename;

                    $SprdSht = new SprdSht;
                    $SprdSht->nuevo();
                    $SprdSht->putArray($final, true, "A1", true);
                    $SprdSht->setColumnsAutoWidth();
                    $SprdSht->save($filename);
                    unset($SprdSht, $final);
                }
                
            }//If uploaded
            ++$filenum;
        }//Foreach $_FILES
        
        if (isset($files) && !empty($files)){merge_and_dwd_zip('TRANSFORMADOS.zip', $files);}
        
        header ('Location: elaboracion_ff.php');
        
        
        break;
        

    case "ib_acordeon_xlsx_adif":
		
		$filenum = 0;
		foreach($_FILES['fichero']['tmp_name'] as $file){
			
			if (is_uploaded_file($file)){
				
				$SprdSht = new SprdSht;
				$SprdSht->load($file, true);
				
                $date = new DateClass;
                
				//Para cada hoja
				for ($i = 0; $i < ($SprdSht->getShtCnt()); $i++){
					
					$SprdSht->getSheet($i);
					$array_assoc = $SprdSht->getArray(true);
                    
					//Coloca los datos
					foreach ($array_assoc as $num_row=>$row){

                        if (trim($row['TIPO_FACT'])=='GAS'){continue;}

                        $temp_array = array_fill_keys($array_header, '');

                        $temp_array['CUPS'] 						= 	substr(trim($row['CUPS']), 0, 20);
                        $temp_array['Número de contrato'] 			= 	trim($row['CTO_ENERGIA']);

                        $temp_array['Número de factura']			=	trim($row['NUM_FACTURA_IVA']);

                        $temp_array['Fecha factura'] 				= 	date_format(date_create_from_format('d/m/Y H:i:s', $row['FEC_FACTURA']), 'd/m/Y');
                        $temp_array['Fecha desde'] 					= 	date_format(date_create_from_format('d/m/Y H:i:s', $row['FEC_MIN_TER_ENE']), 'd/m/Y');
                        $temp_array['Fecha hasta'] 					= 	date_format(date_create_from_format('d/m/Y H:i:s', $row['FEC_MAX_TER_ENE']), 'd/m/Y');
                        $temp_array['Fecha desde (potencia)'] 		= 	date_format(date_create_from_format('d/m/Y H:i:s', $row['FEC_MIN_TER_POT']), 'd/m/Y');
                        $temp_array['Fecha hasta (potencia)'] 		= 	date_format(date_create_from_format('d/m/Y H:i:s', $row['FEC_MAX_TER_POT']), 'd/m/Y');

                        if ($temp_array['Fecha desde'] 				== '01/01/9999'){$temp_array['Fecha desde'] 			= $temp_array['Fecha factura'];}
                        if ($temp_array['Fecha hasta'] 				== '01/01/9999'){$temp_array['Fecha hasta'] 			= $temp_array['Fecha desde'];}
                        if ($temp_array['Fecha desde (potencia)'] 	== '01/01/9999'){$temp_array['Fecha desde (potencia)'] 	= $temp_array['Fecha desde'];}
                        if ($temp_array['Fecha hasta (potencia)'] 	== '01/01/9999'){$temp_array['Fecha hasta (potencia)'] 	= $temp_array['Fecha hasta'];}

                        $temp_array['Tipo factura'] 				=	$row['TIP_RECTIF_FCA'];
                        $temp_array['Factura rectificada'] 			=	str_replace("-", "",str_replace(".", "", trim($row['NUM_FACTU_RECTIF'])));

                        //Consumos
                        for ($x=1; $x<=6; $x++){
                            $temp_array["Consumo energía P$x"] 	= 0;
                            $temp_array["Consumo reactiva P$x"] 	= 0;
                            $temp_array["Maxímetro P$x"] 		= 0;
                        }

                        $temp_array['Consumo total reactiva'] = 0;

                        for ($y=1; $y<=5; $y++){

                            for ($x=1; $x<=6; $x++){

                                $act_ff		= "0$y"."_TE$x"."_kWh";
                                $react_ff	= "0$y"."_CRFP$x _Lect_KVArh";
                                $max_ff		= "0$y"."_MAFP$x _Lect_kW";

                                //Activa
                                if (array_key_exists($act_ff, $row)){
                                    settype($row[$act_ff], "float");
                                    $temp_array["Consumo energía P$x"] 		+= $row[$act_ff];
                                }

                                //Reactiva
                                if (array_key_exists($react_ff, $row)){
                                    settype($row[$react_ff], "float");
                                    $temp_array["Consumo reactiva P$x"] 	+= $row[$react_ff];
                                    $temp_array['Consumo total reactiva'] 	+= $row[$react_ff];
                                }

                                //Máxima
                                if (array_key_exists($max_ff, $row)){
                                    settype($row[$max_ff], "float");
                                    $temp_array["Maxímetro P$x"] 			= max($temp_array["Maxímetro P$x"], $row[$max_ff]);
                                }
                            }

                        }

                        $temp_array['Consumo total'] 							= $row['kWh_Total'];

                        settype($row['Total_TEpe_Euros'], "float");
                        settype($row['Total_TE_Euros'], "float");
                        $temp_array['Término Energía (€)'] 						= round(($row['Total_TEpe_Euros']/1.051127) + $row['Total_TE_Euros'], 2);

                        settype($row['Total_TP_Euros'], "float");
                        settype($row['EX-TP_€'], "float");
                        settype($row['Total_TR_Euros'], "float");
                        $temp_array['Término Potencia (€)']						= $row['Total_TP_Euros'];
                        $temp_array['Excesos de potencia (€)'] 					= $row['EX-TP_€'];
                        $temp_array['Excesos reactiva (€)'] 					= $row['Total_TR_Euros'];
                        
                        $temp_array['Derechos de acceso (€)'] 			        = 0;
                        if (array_key_exists('Derechos de Acceso_€', $row)){
                            settype($row['Derechos de Acceso_€'], "float");
                            $temp_array['Derechos de acceso (€)'] 				= 	$row['Derechos de Acceso_€'];
                        }
                        $temp_array['Derechos de enganche (€)'] 			    = 0;
                        if (array_key_exists('Derechos de enganche_€', $row)){
                            settype($row['Derechos de enganche_€'], "float");
                            $temp_array['Derechos de enganche (€)'] 			= 	$row['Derechos de enganche_€'];
                        }

                        $temp_array['Observaciones'] = ($temp_array['Derechos de acceso (€)'] != 0) ? "Derechos de acceso: ".$temp_array['Derechos de acceso (€)']."€ " : "";
                        if ($temp_array['Derechos de enganche (€)'] != 0){
                                $temp_array['Observaciones'] .= "Derechos de enganche: ".$temp_array['Derechos de enganche (€)']."€ ";
                        }

                        $temp_array['Otros conceptos (con IVA e IEE, €)']		= 0;
                        if (array_key_exists('Importe a sumar/restar en base IVA-OFICO_€', $row)){
                            settype($row['Importe a sumar/restar en base IVA-OFICO_€'], "float");
                            $temp_array['Otros conceptos (con IVA e IEE, €)'] 			+= $row['Importe a sumar/restar en base IVA-OFICO_€'];
                        }

                        $temp_array['Otros conceptos (con IVA, €)'] 			= 0;
                        if (array_key_exists('Derechos de Extensión_€', $row)){
                            settype($row['Derechos de Extensión_€'], "float");
                            $temp_array['Otros conceptos (con IVA, €)'] 			+= $row['Derechos de Extensión_€'];
                            if ($temp_array['Otros conceptos (con IVA, €)'] != 0){
                                    $temp_array['Observaciones'] .= " Derechos de extensión: ".$temp_array['Otros conceptos (con IVA, €)']."€";
                            }
                        }
                        if (array_key_exists('01_DTOCA_€', $row)){
                            settype($row['01_DTOCA_€'], "float");
                            $temp_array['Otros conceptos (con IVA, €)'] 			+= $row['01_DTOCA_€'];
                        }
                        if (array_key_exists('Actuaciones en la medida_€', $row)){
                            settype($row['Actuaciones en la medida_€'], "float");
                            $temp_array['Otros conceptos (con IVA, €)'] 			+= $row['Actuaciones en la medida_€'];
                        }

                        settype($row['IE_BASE_EUROS'], "float");
                        settype($row['IE_IMPORTE_EUROS'], "float");
                        settype($row['IVA_BASE_EUROS'], "float");
                        settype($row['IVA_IMPORTE_EUROS'], "float");
                        $temp_array['Base imponible IE (€)'] 					= ($temp_array['Término Energía (€)']==0) ? 0 : ($temp_array['Término Energía (€)'] + $temp_array['Término Potencia (€)'] + $temp_array['Excesos de potencia (€)'] + $temp_array['Excesos reactiva (€)']);
                        $temp_array['Impuesto eléctrico (€)'] 					= $row['IE_IMPORTE_EUROS'];
                        $temp_array['Base imponible (€)'] 						= $row['IVA_BASE_EUROS'];
                        $temp_array['Total IVA (€)'] 							= $row['IVA_IMPORTE_EUROS'];
                        
                        $temp_array['Otros conceptos (exentos IVA, €)'] 	    = 0;
                        if (array_key_exists('Depósito garantía facturado por Distrib._€', $row)){
                            settype($row['Depósito garantía facturado por Distrib._€'], "float");
                            $temp_array['Otros conceptos (exentos IVA, €)'] 	= $row['Depósito garantía facturado por Distrib._€'];
                        }
                        if ($temp_array['Otros conceptos (exentos IVA, €)'] != 0){
                                $temp_array['Observaciones'] .= "Depósito de garantía: ".$temp_array['Otros conceptos (exentos IVA, €)']."€ ";
                        }

                        settype($row['Alquiler_Euros'], "float");
                        $temp_array['Alquiler contador (€)'] 					= $row['Alquiler_Euros'];
                        if (array_key_exists('Regularización de alquileres_€', $row)){
                            settype($row['Regularización de alquileres_€'], "float");
                            $temp_array['Alquiler contador (€)'] 				+= $row['Regularización de alquileres_€'];
                        }

                        settype($row['Total_Factura_Euros'], "float");
                        $temp_array['Total factura (€)'] 						= $row['Total_Factura_Euros'];
                        
                        //Añade la linea al array total
                        //$array_final[] = array_values($temp_array);
                        $array_final[] = $temp_array;
                        unset($temp_array);

                        //Si ha llegado a las 1000 fras
                        if (count($array_final) == 1000){
                            $filename 	= $num_row."_".$_FILES['fichero']['name'][$filenum];
                            $files[] 	= $filename;

                            $SprdSht2 = new SprdSht;
                            $SprdSht2->nuevo();
                            $SprdSht2->putArray($array_final, true, "A1", true);
                            $SprdSht2->save($filename);
                            unset($SprdSht2, $array_final);
                            $array_final = array();
                        }
					} //Para cada linea
					unset($array_assoc);
				} //Para cada hoja
				unset($SprdSht);
			} //Si se ha subido el fichero
			
            if (!empty($array_final)){
                //Guarda el fichero
                $filename 	= str_replace('.xlsx', '', $_FILES['fichero']['name'][$filenum]).' TRANSFORMADO.xlsx';
                ++$filenum;
                $files[] = $filename;

                $SprdSht = new SprdSht;
                $SprdSht->nuevo();
                $SprdSht->putArray($array_final, true, "A1", true);
                $SprdSht->save($filename);
                unset($SprdSht, $array_final);
            }
			
		} //Para cada fichero
		
		if (isset($files)){merge_and_dwd_zip('Acordeones elaborados.zip', $files);}
		
		break;
        
    case 'gas':
        
        $headers_gas = array('CUPS',
                             'Cliente',
                             'Número Contrato',
                             'Número Factura',
                             'Fecha Factura',
                             'Fecha desde',
                             'Fecha Hasta',
                             'Tarifa',
                             'Q Registrada',
                             'Consumo Total',
                             'Término Fijo',
                             'Volumen Precio Variable',
                             'Término Variable Precio Variable',
                             'Volumen Precio Fijo',
                             'Término Variable Precio Fijo',
                             'Volumen Cogeneración',
                             'Volumen Industrial',
                             'Varios',
                             'IEH Cogeneración',
                             'IEH Industrial',
                             'Equipo Medida',
                             'Base IVA',
                             'Porcentaje IVA',
                             'Subtotal IVA',
                             'Total',
                             'Observaciones');
        
        $filenum = 0;
        foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){
                $filename = $_FILES['fichero']['name'][$filenum];
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                
                switch ($extension){
                    case 'xlsx':    //XLSX ----------------------
                        
                        $SprdSht = new SprdSht;
				        $SprdSht->load($file);
                        
                        //Detecta que tipo de fichero es
                        switch (true){
                                
//GAS KUTXABANK ENDESA <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                            case ($SprdSht->sheetExists('Informe facturación')):
                                
                                $SprdSht->getSheet('Informe facturación');
                                $datos = $SprdSht->getArray();
                                unset($SprdSht);
                                
                                $Array = new ArrayClass($datos);
                                $datos = $Array->arrayToAssoc(15);
                                unset($Array);
                                
                                $date = new DateClass;

                                foreach ($datos as $num_row=>$row){
                                    
                                    if (empty(trim($row['Cups 22']))){continue;}
                                    
                                    $linea = array_fill_keys($headers_gas, '');

                                    $linea['CUPS']                              = substr(trim($row['Cups 22']), 0, 20);
                                    $linea['Cliente']                           = $row['Razón social'];

                                    $linea['Número Contrato']                   = $row['Contrato PS'];
                                    $linea['Número Factura']                    = $row['Nº factura'];
                                    $linea['Fecha Factura']                     = $row['Fecha de emisión'];
                                    $linea['Fecha desde']                       = $row['Fecha desde'];
                                    $linea['Fecha Hasta']                       = $row['Fecha hasta'];
                                    $linea['Tarifa']                            = $row['Tarifa de acceso'];
                                    $linea['Q Registrada']                      = $row['Caudal máximo'];
                                    $linea['Consumo Total']                     = $row['Consumo total (kWh)'];
                                    $linea['Término Fijo']                      = $row['Término fijo'] + $row['Descuento término fijo gas'];
                                    $linea['Volumen Precio Variable']           = 0;
                                    $linea['Término Variable Precio Variable']  = 0;
                                    $linea['Volumen Precio Fijo']               = $row['Consumo total (kWh)'];
                                    $linea['Término Variable Precio Fijo']      = $row['Término energía gas'] + $row['Descuento sobre el término de energía'];
                                    $linea['Volumen Cogeneración']              = 0;
                                    $linea['Volumen Industrial']                = $row['Consumo total (kWh)'];
                                    $linea['Varios']                            = $row['Importe por fianzas'] + $row['Importe derechos de contratación'] + $row['Importe sgp'];
                                    $linea['IEH Cogeneración']                  = $row['Impuesto Hidrocarburos general'] + $row['Impuesto Hidrocarburos reducido']; 
                                    $linea['IEH Industrial']                    = 0;
                                    $linea['Equipo Medida']                     = $row['Importe alquiler de equipos'] + $row['Importe ajuste equipos'];
                                    $linea['Base IVA']                          = $row['Imp. total antes de impuestos '];
                                    $linea['Porcentaje IVA']                    = 21;
                                    $linea['Subtotal IVA']                      = $row['IVA / IGIC Normal'];
                                    $linea['Total']                             = $row['Importe total'];

                                    $final[] = $linea;
                                    unset($linea);
                                }
                                
                                break;
                                
//GAS CORREOS <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                            case ($SprdSht->getCellValue('A1')=='Cliente / Contrato'):
                                
                                $datos = $SprdSht->getArray(true);
                                unset($SprdSht);

                                $date = new DateClass;

                                foreach ($datos as $num_row=>$row){
                                    $linea = array_fill_keys($headers_gas, '');

                                    $linea['CUPS']                              = $row['CUPS'];
                                    $linea['Cliente']                           = 'CORREOS';

                                    //SOLUCION TEMPORAL -------------------------------------------------------------------------------------
                                    $date->fromXl($row['Fecha Final']);
                                    $linea['Número Contrato']                   = $row['Cliente / Contrato'].'_'.$date->format('y');
                                    //-------------------------------------------------------------------------------------------------------


                                    //$linea['Número Contrato']                   = $row['Cliente / Contrato'].'_'.$date->fromToFormat($row['Fecha Final'], 'd/m/Y', 'y');
                                    $linea['Número Factura']                    = $row['Nº Factura'];
                                    $linea['Fecha Factura']                     = $row['Fecha Emision'];
                                    $linea['Fecha desde']                       = $row['Fecha Anterior'];
                                    $linea['Fecha Hasta']                       = $row['Fecha Final'];
                                    $linea['Tarifa']                            = $row['Tarifa'];
                                    $linea['Q Registrada']                      = 0;
                                    $linea['Consumo Total']                     = $row['kWh Gas'];
                                    $linea['Término Fijo']                      = $row['T. Fijo'];
                                    $linea['Volumen Precio Variable']           = 0;
                                    $linea['Término Variable Precio Variable']  = $row['Precio TV'];
                                    $linea['Volumen Precio Fijo']               = $row['kWh Gas'];
                                    $linea['Término Variable Precio Fijo']      = $row['Precio TF'];
                                    $linea['Volumen Cogeneración']              = 0;
                                    $linea['Volumen Industrial']                = 0;
                                    $linea['Varios']                            = $row['IH y Otros Gastos'];
                                    $linea['IEH Cogeneración']                  = 0;
                                    $linea['IEH Industrial']                    = 0;
                                    $linea['Equipo Medida']                     = $row['Alquiler'];
                                    $linea['Base IVA']                          = $row['B.I.'];
                                    $linea['Porcentaje IVA']                    = 21;
                                    $linea['Subtotal IVA']                      = $row['IVA'];
                                    $linea['Total']                             = $row['TOTAL FACTURA'];

                                    $final[] = $linea;
                                    unset($linea);
                                }
                                
                                break;
                                
//GAS NATURGY <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                            case ($SprdSht->getCellValue('A1')=='Nombre Agrupador'):
                                
                                $datos = $SprdSht->getArray(true);
                                unset($SprdSht);
                                
                                $date = new DateClass;
                                foreach ($datos as $num_row=>$row){
                                    $linea = array_fill_keys($headers_gas, '');

                                    $linea['CUPS']                              = $row['CUPS'];
                                    $linea['Cliente']                           = '';

                                    $linea['Número Contrato']                   = $row['Contrato'];
                                    $linea['Número Factura']                    = $row['Numero Factura'];

                                    $date->fromXl($row['Fecha Emision']);
                                    $linea['Fecha Factura']                     = $date->format('d/m/Y');

                                    $date->fromXl($row['Fecha Inicio']);
                                    $linea['Fecha desde']                       = $date->format('d/m/Y');

                                    $date->fromXl($row['Fecha Fin']);
                                    $linea['Fecha Hasta']                       = $date->format('d/m/Y');

                                    $linea['Tarifa']                            = $row['Tarifa ATR'];
                                    $linea['Q Registrada']                      = 0;
                                    $linea['Consumo Total']                     = $row['Consumo kwh'];
                                    $linea['Término Fijo']                      = $row['Termino Fijo'];
                                    $linea['Volumen Precio Variable']           = 0;
                                    $linea['Término Variable Precio Variable']  = 0;
                                    $linea['Volumen Precio Fijo']               = $row['Consumo kwh'];
                                    $linea['Término Variable Precio Fijo']      = $row['Importe Energia'];
                                    $linea['Volumen Cogeneración']              = 0;
                                    $linea['Volumen Industrial']                = $row['Consumo kwh'];
                                    $linea['Varios']                            = $row['Otros Conceptos'];
                                    $linea['IEH Cogeneración']                  = 0;
                                    $linea['IEH Industrial']                    = 0;
                                    $linea['Equipo Medida']                     = $row['Alquiler'];
                                    $linea['Base IVA']                          = $row['Importe Factura'] - $row['IVA'];
                                    $linea['Porcentaje IVA']                    = 21;
                                    $linea['Subtotal IVA']                      = $row['IVA'];
                                    $linea['Total']                             = $row['Importe Factura'];

                                    $final[] = $linea;
                                    unset($linea);
                                }
                                unset($date);
                                break;
                                
//GAS ALPIQ <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                            case ($SprdSht->getCellValue('A1')=='Invoice number'):
                                
                                $datos = $SprdSht->getArray(true);
                                unset($SprdSht);
                                
                                $date = new DateClass;
                                foreach ($datos as $num_row=>$row){
                                    $linea = array_fill_keys($headers_gas, '');

                                    $linea['CUPS']                              = $row['Metering point location Id'];
                                    $linea['Cliente']                           = $row['Customer'];

                                    $linea['Número Contrato']                   = $row['CRM contract number'];
                                    $linea['Número Factura']                    = $row['Invoice number'];

                                    $date->fromXl($row['Invoice date']);
                                    $linea['Fecha Factura']                     = $date->format('d/m/Y');

                                    $date->fromXl($row['Start of invoice period']);
                                    $linea['Fecha desde']                       = $date->format('d/m/Y');

                                    $date->fromXl($row['End of invoice period']);
                                    $linea['Fecha Hasta']                       = $date->format('d/m/Y');

                                    $linea['Tarifa']                            = $row['Tariff'];
                                    $linea['Q Registrada']                      = $row['Qd máxima (MWh)'];
                                    $linea['Consumo Total']                     = $row['Gas Consumption (MWh)']*1000;
                                    $linea['Término Fijo']                      = $row['ATR fix local charge (EUR)'] + $row['ATR fix transport charge (EUR)'] + $row['ATR fix regasification (EUR)'] + $row['ATR fix other regasification (EUR)'];
                                    $linea['Volumen Precio Variable']           = 0;
                                    $linea['Término Variable Precio Variable']  = 0;
                                    $linea['Volumen Precio Fijo']               = $linea['Consumo Total'];
                                    
                                    $linea['Término Variable Precio Fijo']      = $row['ATR variable local charge (EUR)'] + $row['ATR variable transport charge (EUR)'] + $row['ATR variable regasification (EUR)'] + $row['ATR variable excess local flow (EUR)'] + $row['ATR variable excess transport flow (EUR)'] + $row['Gas consumption charge (EUR)'];
                                    
                                    $linea['Volumen Cogeneración']              = 0;
                                    $linea['Volumen Industrial']                = $linea['Consumo Total'];
                                    $linea['Varios']                            = $row['Charges (EUR)'] + $row['GTS fee (EUR)'] + $row['CNMC fee (EUR)'] + $row['MITIRED fee (EUR)'];
                                    $linea['IEH Cogeneración']                  = 0;
                                    $linea['IEH Industrial']                    = $row['Hydrocarbons reduced tax (EUR)'] + $row['Hydrocarbon vehicles tax (EUR)'];
                                    $linea['Equipo Medida']                     = $row['ATR metering charge (EUR)'];
                                    $linea['Base IVA']                          = $row['VAT base (EUR)'];
                                    $linea['Porcentaje IVA']                    = 21;
                                    $linea['Subtotal IVA']                      = $row['VAT (EUR)'];
                                    $linea['Total']                             = $row['Total amount due (EUR)'];

                                    $final[] = $linea;
                                    unset($linea);
                                }
                                unset($date);
                                break;
                                
//GAS INDITEX <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                            case ($SprdSht->getCellValue('A1')=='Factura'):
                                
                                $datos = $SprdSht->getArray(true);
                                unset($SprdSht);
                                
                                $date = new DateClass;
                
                                foreach ($datos as $num_row=>$row){
                                    $linea = array_fill_keys($headers_gas, '');

                                    $linea['CUPS']                              = $row['CUPS (Cliente)'];
                                    $linea['Cliente']                           = 'INDITEX';


                                    $linea['Número Contrato']                   = $row['Contrato'];
                                    $linea['Número Factura']                    = $row['Código factura'];
                                    $linea['Fecha Factura']                     = $row['Fecha de emisión'];
                                    $linea['Fecha desde']                       = $row['Fecha inicio facturación'];
                                    $linea['Fecha Hasta']                       = $row['Fecha fin facturación'];
                                    $linea['Tarifa']                            = '';
                                    $linea['Q Registrada']                      = $row['Qd máximo (kWh/día)'];
                                    $linea['Consumo Total']                     = $row['Consumo mensual facturado (kWh)'];
                                    $linea['Término Fijo']                      = $row['Término fijo'];
                                    $linea['Volumen Precio Variable']           = 0;
                                    $linea['Término Variable Precio Variable']  = 0;
                                    $linea['Volumen Precio Fijo']               = $row['Consumo mensual facturado (kWh)'];
                                    $linea['Término Variable Precio Fijo']      = $row['Término variable'];
                                    $linea['Volumen Cogeneración']              = 0;
                                    $linea['Volumen Industrial']                = $row['Consumo mensual facturado (kWh)'];
                                    
                                    $linea['Varios'] = (isset($row['Importe TF Cargos del sistema gasista'])) ? $row['Importe TF Cargos del sistema gasista'] : 0;
                                    $linea['Varios'] += (isset($row['Importe Exceso Capacidad Demandada'])) ? $row['Importe Exceso Capacidad Demandada'] : 0;
                                    
                                    $linea['IEH Cogeneración']                  = 0;
                                    $linea['IEH Industrial']                    = $row['Importe Consumo Tipo General (€)'] + $row['Importe Consumo Tipo Reducido (€)'];
                                    $linea['Equipo Medida']                     = $row['Alquiler equipos de medida'];
                                    $linea['Base IVA']                          = $row['Importe total sin IVA €'];
                                    $linea['Porcentaje IVA']                    = 21;
                                    $linea['Subtotal IVA']                      = $row['Importe IVA €'];
                                    $linea['Total']                             = $row['Importe total con IVA €'];

                                    $final[] = $linea;
                                    unset($linea);
                                }
                                
                                break;
                                
                        }//Tipo de fichero
                }//Comprueba la extensión
            }//Si se ha cargado
            
            if (isset($final) && !empty($final)){
                $SprdSht = new SprdSht;
                $SprdSht->nuevo();
                $SprdSht->putArray($final, true);
                $x = 2;
                foreach ($final as $num_row=>$row){
                    $SprdSht->setFormatAsDate("E$x");
                    $SprdSht->setFormatAsDate("F$x");
                    $SprdSht->setFormatAsDate("G$x");
                    ++$x;
                }
                $filename 	= str_replace('.xlsx', '', $_FILES['fichero']['name'][$filenum]).' TRANSFORMADO.xlsx';
                $SprdSht->save($filename);
                $files[] = $filename;
                unset($filename, $SprdSht, $final, $x);
            }
            
            ++$filenum;
        }//Por cada fichero
        
        if (isset($files)){merge_and_dwd_zip('GAS ELABORADOS.zip', $files);}
        
        break;
        
        
    case 'gas_naturgy_xlsx':
        
        $filenum = 0;
        foreach($_FILES['fichero']['tmp_name'] as $file){
			
			if (is_uploaded_file($file)){
				
				$SprdSht = new SprdSht;
				$SprdSht->load($file, true);
                $datos = $SprdSht->getArray(true);
                unset($SprdSht);
                
                $headers_gas = array('CUPS',
                                     'Cliente',
                                     'Número Contrato',
                                     'Número Factura',
                                     'Fecha Factura',
                                     'Fecha desde',
                                     'Fecha Hasta',
                                     'Tarifa',
                                     'Q Registrada',
                                     'Consumo Total',
                                     'Término Fijo',
                                     'Volumen Precio Variable',
                                     'Término Variable Precio Variable',
                                     'Volumen Precio Fijo',
                                     'Término Variable Precio Fijo',
                                     'Volumen Cogeneración',
                                     'Volumen Industrial',
                                     'Varios',
                                     'IEH Cogeneración',
                                     'IEH Industrial',
                                     'Equipo Medida',
                                     'Base IVA',
                                     'Porcentaje IVA',
                                     'Subtotal IVA',
                                     'Total',
                                     'Observaciones');
                
                $date = new DateClass;
                $CalculosSimples = new CalculosSimples;
                foreach ($datos as $num_row=>$row){
                    $linea = array_fill_keys($headers_gas, '');
                    
                    $linea['CUPS']                              = $row['CUPS'];
                    $linea['Cliente']                           = '';
                    
                    $linea['Número Contrato']                   = $row['Contrato'];
                    $linea['Número Factura']                    = $row['Numero Factura'];
                    
                    $fecha = explode('-', $row['Fecha Emision']);
                    $mes = $CalculosSimples->textToNumMonth($fecha[1]);
                    $linea['Fecha Factura']                     = $fecha[0]."/".$mes."/20".$fecha[2];
                    
                    $fecha = explode('-', $row['Fecha Inicio']);
                    $mes = $CalculosSimples->textToNumMonth($fecha[1]);
                    $linea['Fecha desde']                       = $fecha[0]."/".$mes."/20".$fecha[2];
                    
                    $fecha = explode('-', $row['Fecha Fin']);
                    $mes = $CalculosSimples->textToNumMonth($fecha[1]);
                    $linea['Fecha Hasta']                       = $fecha[0]."/".$mes."/20".$fecha[2];
                    
                    $linea['Tarifa']                            = $row['Tarifa ATR'];
                    $linea['Q Registrada']                      = 0;
                    $linea['Consumo Total']                     = $row['Consumo kwh'];
                    $linea['Término Fijo']                      = $row['Termino Fijo'];
                    $linea['Volumen Precio Variable']           = 0;
                    $linea['Término Variable Precio Variable']  = 0;
                    $linea['Volumen Precio Fijo']               = $row['Consumo kwh'];
                    $linea['Término Variable Precio Fijo']      = $row['Importe Energia'];
                    $linea['Volumen Cogeneración']              = 0;
                    $linea['Volumen Industrial']                = $row['Consumo kwh'];
                    $linea['Varios']                            = $row['Otros Conceptos'];
                    $linea['IEH Cogeneración']                  = 0;
                    $linea['IEH Industrial']                    = 0;
                    $linea['Equipo Medida']                     = $row['Alquiler'];
                    $linea['Base IVA']                          = $row['Importe Factura'] - $row['IVA'];
                    $linea['Porcentaje IVA']                    = 21;
                    $linea['Subtotal IVA']                      = $row['IVA'];
                    $linea['Total']                             = $row['Importe Factura'];
                    
                    $final[] = $linea;
                    unset($linea);
                }
                
                $SprdSht = new SprdSht;
                $SprdSht->nuevo();
                $SprdSht->putArray($final, true);
                $x = 1;
                foreach ($final as $num_row=>$row){
                    $SprdSht->setFormatAsDate("E$x");
                    $SprdSht->setFormatAsDate("F$x");
                    $SprdSht->setFormatAsDate("G$x");
                    ++$x;
                }
                unset($final, $x);
                
                $filename 	= str_replace('.xlsx', '', $_FILES['fichero']['name'][$filenum]).' TRANSFORMADO.xlsx';
                $SprdSht->save($filename);
                unset($SprdSht);
                $files[] = $filename;
                unset($filename);
                
            }
            
            ++$filenum;
        }
        
        if (isset($files)){merge_and_dwd_zip('FACTURAS GAS.zip', $files);}
        
        break;
}


?>

