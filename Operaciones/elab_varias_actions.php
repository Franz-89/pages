<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

switch ($_POST['action']){
        
    case 'optimizacion':
		
        set_time_limit(3600);
        
		$cli = $_POST['cli'];
		$ano_precios_excesos = $_POST['ano_precios_excesos'];
		$opt = new OptimizacionBT($cli, $ano_precios_excesos);
		$opt->optimizar();
		unset($opt);
		
		break;
        
    case 'consumos_anitugos_reorganizados':
        
        set_time_limit(300);
        
        $cli    = $_POST['cli'];
        $desde  = $_POST['desde'];
        $hasta  = $_POST['hasta'];
        
        $date  = new DateClass;
        $desde = $date->fromToFormat($desde, 'd/m/Y', 'Y-m-d');
        $hasta = $date->fromToFormat($hasta, 'd/m/Y', 'Y-m-d');
        
        $Conn   = new Conn('mainsip', 'develop');
        
        $strSQL = "SELECT
                        CONCAT('$cli', '') grupo,
                        b.Empresa,
                        b.dar,
                        b.cups,
                        b.codigo_oficina,
                        b.tarifa,
                        b.estado,
                        b.poblacion,
                        b.provincia,
                        CONCAT('', '') zona,
                        a.mes,
                        a.dias,
                        CONCAT('', '') consumo_p1,
                        CONCAT('', '') consumo_p2,
                        CONCAT('', '') consumo_p3,
                        CONCAT('', '') consumo_p4,
                        CONCAT('', '') consumo_p5,
                        CONCAT('', '') consumo_p6,
                        CONCAT('', '') precio_energia_p1,
                        CONCAT('', '') precio_energia_p2,
                        CONCAT('', '') precio_energia_p3,
                        CONCAT('', '') precio_energia_p4,
                        CONCAT('', '') precio_energia_p5,
                        CONCAT('', '') precio_energia_p6,
                        b.p1 pot1,
                        b.p2 pot2,
                        b.p3 pot3,
                        b.p4 pot4,
                        b.p5 pot5,
                        b.p6 pot6,
                        CONCAT('', '') termino_energia,
                        CONCAT('', '') termino_potencia,
                        ROUND(a.excesos_reactiva, 2) excesos_reactiva,
                        ROUND(a.derechos_enganche, 2) derechos_enganche,
                        ROUND(a.derechos_acceso, 2) derechos_acceso,
                        ROUND(a.alquiler_equipo, 2) alquiler_equipo,
                        ROUND(a.ajuste_equipo, 2) ajuste_equipo,
                        ROUND(a.concepto_regulado_termino_variable, 2) concepto_regulado_termino_variable,
                        ROUND(a.regularizacion_conversion_tarifas, 2) regularizacion_conversion_tarifas,
                        ROUND(a.otros_conceptos_con_iva, 2) otros_conceptos_con_iva,
                        ROUND(a.otros_conceptos_sin_iva, 2) otros_conceptos_sin_iva,
                        ROUND(a.total, 2) total,
                        CONCAT('', '') base_ie,
                        CONCAT('', '') ie,
                        CONCAT('', '') base_iva,
                        CONCAT('', '') iva,
                        CONCAT('', '') total_factura,
                        CONCAT('', '') comentarios
                    FROM clientes b
                    INNER JOIN (
                        SELECT
                            cups,
                            mes,
                            dias,
                            excesos_reactiva,
                            derechos_enganche,
                            derechos_acceso,
                            alquiler_equipo,
                            ajuste_equipo,
                            concepto_regulado_termino_variable,
                            regularizacion_conversion_tarifas,
                            otros_conceptos_con_iva,
                            otros_conceptos_sin_iva,
                            total
                        FROM datos_notelemedidas
                        WHERE GRUPO = '$cli'
                        AND mes >= '$desde'
                        AND mes < '$hasta'
                    ) a
                    ON b.cups = a.cups
                    
                    INNER JOIN(
                        SELECT CUPS, MAX(fecha_alta) maxalta
                        FROM clientes
                        WHERE Grupo='$cli' AND estado='EN VIGOR' GROUP BY CUPS
                    ) c
                    ON b.CUPS=c.CUPS AND b.fecha_alta=c.maxalta
                    
                    WHERE b.grupo='$cli'
                    AND b.estado = 'EN VIGOR'
                    ORDER BY b.cups, a.mes DESC";
        
        
        /*
        $strSQL = "SELECT
                        CONCAT('$cli', '') grupo,
                        b.Empresa,
                        b.dar,
                        b.cups,
                        b.codigo_oficina,
                        b.tarifa,
                        b.estado,
                        b.poblacion,
                        b.provincia,
                        CONCAT('', '') zona,
                        a.mes,
                        a.dias,
                        CONCAT('', '') consumo_p1,
                        CONCAT('', '') consumo_p2,
                        CONCAT('', '') consumo_p3,
                        CONCAT('', '') consumo_p4,
                        CONCAT('', '') consumo_p5,
                        CONCAT('', '') consumo_p6,
                        CONCAT('', '') precio_energia_p1,
                        CONCAT('', '') precio_energia_p2,
                        CONCAT('', '') precio_energia_p3,
                        CONCAT('', '') precio_energia_p4,
                        CONCAT('', '') precio_energia_p5,
                        CONCAT('', '') precio_energia_p6,
                        b.p1 pot1,
                        b.p2 pot2,
                        b.p3 pot3,
                        b.p4 pot4,
                        b.p5 pot5,
                        b.p6 pot6,
                        CONCAT('', '') termino_energia,
                        CONCAT('', '') termino_potencia,
                        ROUND(a.excesos_reactiva, 2) excesos_reactiva,
                        ROUND(a.derechos_enganche, 2) derechos_enganche,
                        ROUND(a.derechos_acceso, 2) derechos_acceso,
                        ROUND(a.alquiler_equipo, 2) alquiler_equipo,
                        ROUND(a.ajuste_equipo, 2) ajuste_equipo,
                        ROUND(a.concepto_regulado_termino_variable, 2) concepto_regulado_termino_variable,
                        ROUND(a.regularizacion_conversion_tarifas, 2) regularizacion_conversion_tarifas,
                        ROUND(a.otros_conceptos_con_iva, 2) otros_conceptos_con_iva,
                        ROUND(a.otros_conceptos_sin_iva, 2) otros_conceptos_sin_iva,
                        ROUND(a.total, 2) total,
                        CONCAT('', '') base_ie,
                        CONCAT('', '') ie,
                        CONCAT('', '') base_iva,
                        CONCAT('', '') iva,
                        CONCAT('', '') total_factura,
                        CONCAT('', '') comentarios
                    FROM datos_notelemedidas a
                    RIGHT JOIN (
                        SELECT
                            Empresa,
                            dar,
                            cups,
                            codigo_oficina,
                            tarifa,
                            estado,
                            poblacion,
                            provincia,
                            p1,
                            p2,
                            p3,
                            p4,
                            p5,
                            p6
                        FROM clientes
                        WHERE GRUPO = '$cli'
                        AND fecha_inicio = '2021-06-01'
                        AND estado = 'EN VIGOR'
                    ) b
                    ON b.cups = a.cups
                    WHERE a.grupo='$cli'
                    AND a.mes BETWEEN '$desde' AND '$hasta'
                    ORDER BY b.cups, a.mes DESC";
                    
                    
            */
        
        $datos         = $Conn->getArray($strSQL, true);
        
        $strSQL        = "SELECT CUPS FROM clientes WHERE Grupo='$cli' AND fecha_inicio='2021-06-01' AND estado='EN VIGOR'";
        $cups_completo = $Conn->getArray($strSQL, true);
        unset($Conn);
        
        
        $cups = array();
        $Indexado = new Indexado;
        foreach ($datos as $num_row=>$row){
            $zona = $Indexado->getZoneFromCups($row['cups']);
            $datos[$num_row]['zona'] = $zona;
            unset($zona);
            
            if (!in_array($row['cups'], $cups)){$cups[] = $row['cups'];}
        }
        unset($Indexado);
        
        $Conn = new Conn('local', 'enertrade');
        $porcentajes = $Conn->getArray('SELECT * FROM nueva_division_consumo', true);
        $precios_pot = $Conn->getArray('SELECT * FROM precios_potencia', true);
        foreach ($porcentajes as $num_row=>$row){
            for ($x=1; $x<=6; $x++){$porcentajes[$row['ZONA']][$row['TARIFA']][$row['MES']]["P$x"] = $row["P$x"];}
            unset($porcentajes[$num_row]);
        }
        foreach ($precios_pot as $num_row=>$row){
            for ($x=1; $x<=6; $x++){$precios_pot[$row['TARIFA']]["P$x"] = $row["P$x"];}
            unset($precios_pot[$num_row]);
        }
        
        foreach ($cups as $cup){
            
            //Saca los primeros 12 meses disponibles
            $meses = array(1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9, 10=>10, 11=>11, 12=>12);
            foreach ($datos as $num_row=>$row){
                
                if (empty($meses) || empty($row['mes'])){break;}
                if ($row['cups'] != $cup){continue;}
                
                $dias = $date->fromToFormat($row['mes'], 'Y-m-d', 't');
                $mes  = $date->fromToFormat($row['mes'], 'Y-m-d', 'n');
                
                if ($row['dias']<$dias || !in_array($mes, $meses)){continue;}
                
                //Divide el consumo según su % y calcula el TP
                $zona        = $row['zona'];
                $tarifa      = $row['tarifa'];
                
                
                $row['termino_potencia'] = 0;
                for ($x=1; $x<=6; $x++){
                    $row["consumo_p$x"] = round($row['total']*$porcentajes[$zona][$tarifa][$mes]["P$x"], 2);
                    $row['termino_potencia']  += ($row["pot$x"]==0) ? 0 : round(($row["pot$x"]*$precios_pot[$tarifa]["P$x"]*$dias)/365, 2);
                }
                
                $row['mes'] = $date->fromToFormat($row['mes']);
                
                foreach($row as $key=>$value){
                    switch ($key){
                        case 'consumo_p1':
                        case 'consumo_p2':
                        case 'consumo_p3':
                        case 'consumo_p4':
                        case 'consumo_p5':
                        case 'consumo_p6':
                        case 'pot1':
                        case 'pot2':
                        case 'pot3':
                        case 'pot4':
                        case 'pot5':
                        case 'pot6':
                        case 'termino_potencia':
                        case 'excesos_reactiva':
                        case 'derechos_enganche':
                        case 'derechos_acceso':
                        case 'alquiler_equipo':
                        case 'ajuste_equipo':
                        case 'concepto_regulado_termino_variable':
                        case 'regularizacion_conversion_tarifas':
                        case 'otros_conceptos_con_iva':
                        case 'otros_conceptos_sin_iva':
                        case 'total':
                            $row[$key] = str_replace('.', ',', $value);
                            break;
                    }
                }
                $final[] = $row;
                
                unset($meses["$mes"], $mes, $dias, $zona, $tarifa, $datos[$num_row], $row);
            }
            
            //Indica si falta algún mes
            if (!empty($meses)){
                $fecha_desde = new DateClass;
                $fecha_desde->stringToDate($desde);
                $fecha_desde->subtract(0,1);
                foreach ($meses as $mes){
                    while ($fecha_desde->format('n') != $mes){$fecha_desde->subtract(0,1);}
                    $meses_que_faltan[] = array('CUPS'=>$cup, 'MES QUE FALTA'=>$fecha_desde->format());
                }
            }
        }
        unset($datos, $Conn, $porcentajes, $precios_pot, $fecha_desde);
        
        $cups = array_column($final, 'cups');
        foreach ($cups_completo as $num_row=>$cup){
            if (!in_array($cup['CUPS'], $cups)){
                $meses_que_faltan[] = array('CUPS'=>$cup['CUPS'], 'MES QUE FALTA'=>'TODOS');
            }
        }
        unset($cups_completo, $cups, $cup);
        
        if (isset($final) && !empty($final)){
            
            $fopen = fopen("CONVERSION CONSUMOS $cli.csv", 'a');
            fputcsv($fopen, array_keys($final[0]), ';');
			foreach ($final as $num_row=>$row){fputcsv($fopen, $row, ';');}
			fclose($fopen);
			unset($fopen, $final);
            
            $files[] = "CONVERSION CONSUMOS $cli.csv";
            
            if (isset($meses_que_faltan) && !empty($meses_que_faltan)){
                $SprdSht = new SprdSht;
                $SprdSht->nuevo();
                $SprdSht->putArray($meses_que_faltan, true);
                $SprdSht->save("COMENTARIOS $cli.xlsx");
                unset($meses_que_faltan, $SprdSht);
                $files[] = "COMENTARIOS $cli.xlsx";
            } 
        }
        
        merge_and_dwd_zip('Conversion consumos.zip', $files);
        
        header ("Location: elab_varias.php");
        
        break;
        
        
    case 'validacion_ieenuevo_iva':
        
        set_time_limit(300);
        
        $cli    = $_POST['cli'];
        $desde  = $_POST['desde'];
        $hasta  = $_POST['hasta'];
        
        $date  = new DateClass;
        $desde = $date->fromToFormat($desde, 'd/m/Y', 'Y-m-d');
        $hasta = $date->fromToFormat($hasta, 'd/m/Y', 'Y-m-d');
        
        $Conn   = new Conn('mainsip', 'develop');
        $id_cliente = $Conn->oneData("SELECT id FROM gestion_clientes WHERE name='$cli'");
        
        $strSQL = "SELECT
                        a.cups,
                        b.Tarifa,
                        a.numero_factura,
                        a.Fecha_factura,
                        a.Fecha_desde,
                        a.Fecha_hasta,
                        a.consumo_total,
                        (a.Ter_potencia + a.Ter_energia + a.Excesos_potencia + a.Excesos_reactiva + a.otros_conceptos_con_iva_iee) BI_IEE_calculada,
                        a.base_imponible_ie,
                        CONCAT('') IEE_calculado,
                        a.impuesto_electricidad,
                        (a.Der_acceso + a.Der_enganche + a.alq_equipo + a.ajuste_equipo + a.concepto_regulado_termino_variable + a.otros_conceptos_con_iva + a.otros_conceptos_sin_iva) BI_calculada,
                        a.Base_imponible,
                        CONCAT('') IVA_calculada,
                        a.Valor_IVA,
                        CONCAT('') TOTAL_calculado,
                        a.Total_factura
                    FROM facturas a
                    INNER JOIN (
                        SELECT
                            cups,
                            fecha_inicio,
                            fecha_fin,
                            Tarifa
                        FROM clientes
                        WHERE (Grupo='$cli')
                    ) b
                    ON a.cups=b.CUPS
                    AND (a.Fecha_desde BETWEEN b.fecha_inicio AND b.fecha_fin)
                    WHERE (a.id_cliente='$id_cliente')
                    AND a.Fecha_factura>='$desde'
                    AND a.Fecha_factura<='$hasta'
                    ORDER BY a.cups, a.Fecha_factura, a.Fecha_desde";
        
        $fras = $Conn->getArray($strSQL);
        unset($Conn, $id_cliente, $desde, $hasta);
        
        foreach ($fras as $num_row=>$row){
            
            //CALCULO IEE
            $fras[$num_row]['IEE_calculado'] = round($fras[$num_row]['BI_IEE_calculada']*0.00525565, 2);  //CALCULO BI
            switch ($row['Tarifa']){
                case '6.1TD':
                case '6.2TD':
                case '6.3TD':
                case '6.4TD':
                    if ($fras[$num_row]['IEE_calculado']/$row['consumo_total'] < 0.0005){
                        $fras[$num_row]['IEE_calculado'] = round(0.0005*$row['consumo_total'], 2);
                    };
                    break;
            }
            
            $fras[$num_row]['BI_calculada'] += $fras[$num_row]['BI_IEE_calculada'] + $fras[$num_row]['IEE_calculado'];  //CALCULO BI
            $fras[$num_row]['IVA_calculada'] = round($fras[$num_row]['BI_calculada']*0.1, 2);                           //CALCULO IVA
            $fras[$num_row]['TOTAL_calculado'] = $fras[$num_row]['BI_calculada'] + $fras[$num_row]['IVA_calculada'];    //CALCULO TOTAL FRA
            
            //DIFERENCIAS
            $fras[$num_row]['Diferencia_IE'] = $fras[$num_row]['impuesto_electricidad'] - $fras[$num_row]['IEE_calculado'];
            $fras[$num_row]['Diferencia_BI'] = $fras[$num_row]['Base_imponible'] - $fras[$num_row]['BI_calculada'];
            $fras[$num_row]['Diferencia_IVA'] = $fras[$num_row]['Valor_IVA'] - $fras[$num_row]['IVA_calculada'];
            $fras[$num_row]['Diferencia_TOTAL'] = $fras[$num_row]['Total_factura'] - $fras[$num_row]['TOTAL_calculado'];
            
            //FORMATO FECHAS
            $fras[$num_row]['Fecha_factura'] = $date->fromToFormat($fras[$num_row]['Fecha_factura']);
            $fras[$num_row]['Fecha_desde'] = $date->fromToFormat($fras[$num_row]['Fecha_desde']);
            $fras[$num_row]['Fecha_hasta'] = $date->fromToFormat($fras[$num_row]['Fecha_hasta']);
            
        }
        unset($date, $num_row, $row);
        
        $SprdSht = new Sprdsht;
        $SprdSht->nuevo();
        $SprdSht->putArray($fras, true);
        foreach ($fras as $num_row=>$row){$SprdSht->setValueAsText('C'.($num_row+2), $row['numero_factura']);}
        $SprdSht->directDownload("VALIDACIÓN FRAS $cli.xlsx");
        unset($SprdSht);
        
        
        break;
}

?>