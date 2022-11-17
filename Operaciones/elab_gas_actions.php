<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

switch ($_POST['action']){
		
	case 'validacion_gas':
		
        //Recupera las variables
		$cli = $_POST['cli'];
        
        $desde = new DateClass;
        $hasta = new DateClass;
        
        if (isset($_POST['desde']) && !empty($_POST['desde'])){
            $desde->stringToDate($_POST['desde'], 'd/m/Y');
        } else {
            header("Location: elab_gas.php");
            die;
        }
        
        if (isset($_POST['hasta']) && !empty($_POST['hasta'])){
            $hasta->stringToDate($_POST['hasta'], 'd/m/Y');
        } else {
            $hasta->stringToDate($_POST['desde'], 'd/m/Y');
            $hasta->add(0,1);
        }
        
        
        $Intranet = new Intranet;
        $id_cli = $Intranet->getCustomerID($cli);
        unset($Intranet);
        
        $Conn = new Conn('local', 'enertrade');
        $precios_gas = $Conn->getArray('SELECT * FROM precios_gas');
        foreach ($precios_gas as $num_row=>$row){
            $precios_gas[$row['tarifa']] = $row;
            unset($precios_gas[$row['tarifa']]['tarifa'], $precios_gas[$num_row]);
        }
        
        $Conn = new Conn('mainsip', 'develop');
        
        //CUPS cliente
        $cups_contratos = $Conn->getArray("SELECT DISTINCT cups FROM gas_contratos WHERE gestion_clientes_id=$id_cli");
        $str_cups_contratos = "('".implode("', '",array_column($cups_contratos, 'cups'))."')";
        
        $strSQL = "SELECT
                        cups,
                        numero_factura,
                        gas_contratos_id,
                        fecha_factura,
                        fecha_desde,
                        fecha_hasta,
                        q_registrada,
                        consumo_total,
                        termino_fijo,
                        termino_variable_precio_variable,
                        volumen_precio_fijo,
                        termino_variable_precio_fijo,
                        varios,
                        ieh_industrial,
                        equipo_medida,
                        base_iva,
                        subtotal_iva,
                        total
                    FROM gas_facturas
                    WHERE cups IN $str_cups_contratos
                    AND fecha_factura BETWEEN '".$desde->format()."' AND '".$hasta->format()."'";
        
        $facturas = $Conn->getArray($strSQL);
        
        foreach ($facturas as $num_row=>$factura){
            
            $datos_contrato = $Conn->oneRow("SELECT tarifa, q_contratada, tipo_precio, precio_fijo_mes FROM gas_contratos WHERE id=".$factura['gas_contratos_id']);
            
            $tarifa = $datos_contrato['tarifa'];
            
            $ter_variable_calculado = ($datos_contrato['tipo_precio'] == 'formula') ? $factura['termino_variable_precio_fijo'] + $factura['termino_variable_precio_variable'] : $factura['consumo_total']*$datos_contrato['precio_fijo_mes'];
            $factura['dif_ter_variable'] = ($factura['termino_variable_precio_fijo'] + $factura['termino_variable_precio_variable']) - $ter_variable_calculado;
            
            switch ($tarifa){
                case '2.1':
                case '2.3':
                case '2.4':
                case '2.5':
                case '2.6':
                case '3.5':
                    
                    $Qaplicado = pot_a_facturar($datos_contrato['q_contratada'], $factura['q_registrada']);
                    $TF_calculado = $Qaplicado*$precios_gas[$tarifa]['termino_fijo'];
                    $factura['dif_TF'] = $factura['termino_fijo'] - $TF_calculado;
                    
                    break;
                    
                case '2.2':
                    
                    //Saca el promedio de los ultimos 3 meses
                    $date = new DateClass;
                    $date->stringToDate($factura['fecha_desde']);
                    $mes = $date->format('Y-m-01');
                    $date->subtract(0,1);
                    $mes_menos1 = $date->format('Y-m-01');
                    $date->subtract(0,1);
                    $mes_menos2 = $date->format('Y-m-01');
                    unset($date);
                    
                    $strSQL = "SELECT MAX(consumo_total/dias) FROM gas_consumos WHERE cups ='". $factura['cups'] ."' AND mes IN ('$mes', $mes_menos1', '$mes_menos2')";
                    $Qmedio = $Conn->oneData($strSQL);
                    $Qaplicado = ($Qmedio<$datos_contrato['q_contratada']) ? $datos_contrato['q_contratada'] : $Qmedio;
                    unset($Qmedio);
                    
                    $TF_calculado = $Qaplicado*$precios_gas[$tarifa]['termino_fijo'];
                    $factura['dif_TF'] = $factura['termino_fijo'] - $TF_calculado;
                    
                    break;
                    
                case '3.1':
                case '3.2':
                case '3.3':
                case '3.4':
                    
                    $TF_calculado = $precios_gas[$tarifa]['termino_fijo'];
                    
                    break;
            }
            
            $ieh_calculado = $factura['consumo_total'] * $precios_gas[$tarifa]['ieh'];
            $factura['dif_ieh'] = $factura['ieh_industrial'] - $ieh_calculado;
            
            $BI_calculada = $TF_calculado + $ter_variable_calculado + $ieh_calculado + $factura['equipo_medida'] + $factura['varios'];
            
            $factura['dif_BI'] = $factura['base_iva'] - $BI_calculada;
            
            $facturas[$num_row] = $factura;
            
        }
        
        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->putArray($facturas, true);
        $SprdSht->directDownload("Validación gas $cli.xlsx");
        unset($SprdSht, $facturas);
        
		break;
}

?>