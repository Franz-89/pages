
<?php
require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$Ps = new Ps('INDITEX');
$empresas = $Ps->getRazonSocial();
print_r($empresas);

die;

$filename = 'A.xlsx';

$Carpetas = new Carpetas;
$extension = $Carpetas->getExtensionFromFilename($filename);

$Conn = new Conn('local', 'enertrade');
$plantillas = $Conn->getArray("SELECT * FROM plantillas_conversion_ff WHERE tipo_fichero='$extension'", true);

switch ($extension){
    case 'xlsx':
        
        
        //FUNCIONES INTERNAS ------------------------------------------------------------
            
        //FUNCIÓN que comprueba si hay más de un valor separado por #
        function check_if_array($value_info){
            $result = (strpos($value_info, '#')) ? explode('#', $value_info) : $value_info;
            return (is_array($result)) ? array_filter($result) : $result;
        }

        //FUNCIÓN comprueba si es numero o texto
        function check_value($info, $value_info, $row, $is_num){
            
            switch (true){
                case (substr($value_info, 0, 5)=='const'):
                    if ($is_num){settype($info[$value_info], "float");}
                    $result = $info[$value_info];
                    break;
                    
                case (isset($row[$value_info])):
                    if ($is_num){settype($row[$value_info], "float");}
                    $result = $row[$value_info];
                    break;
                    
                default:
                    $result = ($is_num) ? 0 : '';
                    break;
            }
            
            return $result;
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
                                
                                if ($key_info=='CUPS'){
                                    $linea[$key_info] = trim(substr($linea[$key_info], 0, 20));
                                    if (empty($linea[$key_info])){
                                        unset($linea);
                                        continue(3);
                                    }
                                }
                                
                                //Si es numero de factura sin secuencial
                                if ($key_info=='Numero_de_factura' && is_array($value_info) && count($value_info)<=2){
                                    $linea[$key_info] .= "_$num_row";
                                }
                                
                            } else {
                                $linea[$key_info] .= check_value($info, $value_info, $row, $is_num);
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
                                    $linea[$key_info] += check_value($info, $value2, $row, $is_num);
                                }
                            } else {
                                $linea[$key_info] += check_value($info, $value_info, $row, $is_num);
                                
                            }
                            
                            break;
                    }
                }
                
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
                        
                        $linea['CIF'] = trim(str_replace("CIF:", "", $linea['CIF']));
                        
                        for ($x=1;$x<=6;$x++){
                            $linea["Maximetro_P$x"] = 	$linea["Maximetro_P$x"]/1000;
                        }
                        
                        //Comprueba las abreviaciones de los otros conceptos
                        for ($x=1; $x<=10; $x++){
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
                                        $linea['Observaciones'] .= "Depósito de garantía: ".$row[$valor]."€ "; break;
                                    case "ST19"	:	//Suplemento territorial
                                    case "REPO"	:	//Regularización potencia
                                    case "RENE"	:	//Regularización energía
                                    case "FCAP"	:	//Coste del tope de gas
                                    case "FBOS":    //Financiación bono social
                                        settype($row[$valor], "float");
                                        $linea['Otros_conceptos_(con_IVA_e_IEE_€)'] += $row[$valor];break;
                                    default		:
                                        settype($row[$valor], "float");
                                        $linea['Otros_conceptos_(con_IVA_€)'] += $row[$valor]; break;
                                }
                            }
                        }
                        
                        //Si existe IVA al 21% y al 5% solo se queda con el 21% como base
                        $linea['Total_IVA_(€)'] = (array_key_exists('IVA (€) 21%', $row) && array_key_exists('IVA (€) 5%', $row)) ? ($linea['Total_IVA_(€)'] - $row['IVA (€) 5%']) : $linea['Total_IVA_(€)'];
                        
                        $linea['Base_imponible_IE_(€)'] = ($linea['Otros_conceptos_(con_IVA_e_IEE_€)'] + $linea['Termino_Potencia_(€)'] + $linea['Termino_Energia_(€)'] + $linea['Excesos_reactiva_(€)'] + $linea['Excesos_de_potencia_(€)']);
                        
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
                        
                        
                        //OTROS CASOS SI HAY
                }
                
                
                
                
                
                //ARREGLOS FINALES
                
                //Si no hay BIE hace la suma
                $linea['Base_imponible_IE_(€)'] = (!$linea['Base_imponible_IE_(€)']) ? ($linea['Otros_conceptos_(con_IVA_e_IEE_€)'] + $linea['Termino_Potencia_(€)'] + $linea['Termino_Energia_(€)'] + $linea['Excesos_reactiva_(€)'] + $linea['Excesos_de_potencia_(€)']) : $linea['Base_imponible_IE_(€)'];
                
                //Si no hay BI hace la resta entre total e IVA
                $linea['Base_imponible_(€)'] = 	(!$linea['Base_imponible_(€)']) ? ($linea['Total_factura_(€)'] - $linea['Total_IVA_(€)']) : $linea['Base_imponible_(€)'];
                
                $temp_headers = array('Derechos_de_enganche_(€)', 'Derechos_de_acceso_(€)');
                foreach ($temp_headers as $header){
                    $linea['Observaciones'] = ($linea[$header]) ? "$header: ".str_replace('.', ',', $linea[$header]).'€ ' : $linea['Observaciones'];
                }
                
                $final[] = $linea;
                unset($linea);
                
            }
            
            return $final;
            
        }//FIN FUNCIÓN XLSX
        
        
        $SprdSht = new SprdSht;
        $SprdSht->load($filename);
        
        foreach ($plantillas as $num_row=>$row){
            if (empty($row['existe_hoja'])){
                if ($SprdSht->getCellValue($row['celda'])==$row['valor_celda']){
                    $info = $row;
                    break;
                }
            } else {
                if ($SprdSht->sheetExists($row['existe_hoja'])){
                    $info = $row;
                    break;
                }
            }
        }
        unset($plantillas);
        
        $final = array();
        switch (true){
            // Si no encuentra ninguna referencia
            case (!isset($info)):
                break(2);
                
            case ($info['para_cada_hoja']):
                
                for ($i = 0; $i < ($SprdSht->getShtCnt()); $i++){
                    $SprdSht->getSheet($i);
                    $temp = elab_ff_xlsx($info, $SprdSht);
                    $final = array_merge($final, $temp);
                    unset($temp);
                }
                break;
                
            case ($info['ID']=='ALGO'):
                
                //Selecciona la hoja necesaria
                break;
                
            default:
                $final = elab_ff_xlsx($info);
                break;
        }
        unset($SprdSht);
        
        
        break;
}


if (!empty($final)){
    //Guarda el fichero
    $filename 	= str_replace('.xlsx', '', $filename).' TRANSFORMADO.xlsx';
    $files[] = $filename;

    $SprdSht = new SprdSht;
    $SprdSht->nuevo();
    $SprdSht->putArray($final, true, "A1", true);
    $SprdSht->save($filename);
    unset($SprdSht, $final);
}

merge_and_dwd_zip('TRANSFORMADOS.zip', $files);


die;

$Conn = new Conn('local', 'enertrade');
$headers = $Conn->getHeaders('plantillas_conversion_ff');
unset($Conn);
$SprdSht = new SprdSht;
$SprdSht->nuevo();
$SprdSht->putArray($headers);
$SprdSht->directDownload('a.xlsx');

die;

$Ps = new Ps('CAIXABANK');

$columnas = array('P1', 'P2', 'P3', 'P4', 'P5', 'P6');

$final = $Ps->getCambios($columnas);
unset($Ps);

$SprdSht = new SprdSht;
$SprdSht->nuevo();
$SprdSht->putArray($final, true);
$SprdSht->directDownload('CAMBIO.xlsx');
unset($SprdSht, $final);


die;

//SIPS TOTAL
$curl = new curlClass;
$curl->url('https://sips.sigeenergia.com:61843/SIPSAPIvLast/api/v2/ClientesSips/GetClientesPost');
$headers = array(
    'accept: application/json, text/plain, */*',
    'accept-encoding: gzip, deflate, br',
    'accept-language: es-ES,es;q=0.9',
    'content-length: 233',
    'content-type: application/json;charset=UTF-8',
    'origin: https://agentes.totalenergies.es',
    'referer: https://agentes.totalenergies.es/',
    'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="99", "Google Chrome";v="99"',
    'sec-ch-ua-mobile: ?0',
    'sec-ch-ua-platform: "Windows"',
    'sec-fetch-dest: empty',
    'sec-fetch-mode: cors',
    'sec-fetch-site: cross-site',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.51 Safari/537.36'
);
$curl->httpHeaders($headers);
//$curl->follow(true);
$curl->POST('{"CodigoCUPS":"ES0031405565260001RB","NombreEmpresaDistribuidora":"","MunicipioPS":"","CodigoProvinciaPS":"","CodigoPostalPS":"","CodigoTarifaATREnVigor":"","ListCUPS":"","LoadAllDatosCliente":true,"LoadConsumos":true,"IsExist":true}');
print_r($curl->execute());

die;

$SprdSht = new SprdSht;
$SprdSht->load('CAIZA17.xlsx');
$curva = $SprdSht->getArray(true);
unset($SprdSht);

$encabezados = array('Fecha', 'kW_Compra', 'kW_Venta', 'KVAr_C1', 'KVAr_CAP_V', 'KVAr_IND_V', 'KVAr_C4', 'Flags');
$date = new DateClass;
foreach ($curva as $num_row=>$row){
    $linea = array_fill_keys($encabezados, 0);
    $linea['Fecha'] = $date->fromToFormat($row['Fecha'], 'd/m/Y H:i:s', 'Y-m-d H:i:s');
    $linea['kW_Compra'] = $row['kW_Compra'];
    $CdC[] = $linea;
    unset($curva[$num_row], $linea);
}
unset($curva, $date, $encabezados);

$values = implode_values($CdC);
$Conn = new Conn('local', 'cdc');

$Conn->Query("INSERT INTO CAIZA17 (Fecha, kW_Compra, kW_Venta, KVAr_C1, KVAr_CAP_V, KVAr_IND_V, KVAr_C4, Flags) VALUES $values ON DUPLICATE KEY UPDATE kW_Compra=VALUES(kW_Compra)");
print_r($Conn->error());


die;

?>