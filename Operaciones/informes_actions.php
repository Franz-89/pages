<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

switch ($_POST['action']){
		
	case 'reclamaciones':
		
        set_time_limit(3600);
        
        $cli = $_POST['cli'];
        $temp_mes = $_POST['mes'];
        
        $date = new DateClass;
        
        $mes = new DateClass;
        $mes->hourZero();
        $mes->stringToDate($temp_mes, 'd/m/Y');
        
        $mes_mas_uno = new DateClass;
        $mes_mas_uno->hourZero();
        $mes_mas_uno->stringToDate($temp_mes, 'd/m/Y');
        $mes_mas_uno->add(0, 1);
        
        $CalculosSimples = new CalculosSimples;
        $id_cliente = $CalculosSimples->getIdCliente($cli);
        unset($CalculosSimples);
        
        //Recupera el historico de reclamaciones
        $strSQL = "SELECT
                        a.id,
                        a.estado,
                        a.fecha_apertura,
                        a.fecha_cierre,
                        '$cli',
                        c.Empresa,
                        a.cups,
                        c.Direccion,
                        c.Poblacion,
                        c.provincia,
                        c.dar,
                        c.codigo_oficina,
                        c.tipo_edificio,
                        a.cups,
                        a.numero_factura,
                        c.Tarifa,
                        CONCAT('', '') fecha_fra,
                        CONCAT('', '') desde,
                        CONCAT('', '') hasta,
                        CONCAT('', '') Total_factura,
                        a.tipo,
                        a.descripcion,
                        a.comentarios_cliente,
                        a.importe_reclamado,
                        a.importe_recuperado,
                        a.updated_at,
                        b.tramites
                    FROM incidencias a
                    LEFT JOIN (SELECT
                                    incidencias_id,
                                    GROUP_CONCAT(CONCAT (DATE_FORMAT(fecha, '%d/%m/%Y'), ' - ', comentarios) SEPARATOR '\n') tramites
                                FROM incidencias_tramites
                                GROUP BY incidencias_id) b
                    ON a.id=b.incidencias_id
                    LEFT JOIN (SELECT
                                    g.cups,
                                    g.Empresa,
                                    g.Tarifa,
                                    g.Direccion,
                                    g.Poblacion,
                                    g.Provincia,
                                    g.dar,
                                    g.codigo_oficina,
                                    g.tipo_edificio
                                FROM clientes g
                                INNER JOIN (SELECT
                                                cups,
                                                MAX(fecha_alta) maxalta
                                            FROM clientes
                                            WHERE Grupo='$cli'
                                            GROUP BY cups) h
                                ON g.cups=h.cups
                                AND g.fecha_alta=h.maxalta
                                WHERE Grupo='$cli'
                                ) c
                    ON a.cups=c.cups
                    WHERE a.gestion_clientes_id='$id_cliente'
                    AND a.deleted_at IS NULL";
        
        $Conn              = new Conn('mainsip', 'develop');
        $Conn->Query('SET group_concat_max_len = 100000');
        $historico         = $Conn->getArray("$strSQL GROUP BY a.id", true);
        
        //Recupera desde el historico las reclamaciones abiertas en el mes seleccionado
        $reclamaciones_mes = array();
        foreach ($historico as $num_row=>$row){
            
            if (!empty($row['fecha_cierre'])){
                $date->stringToDate($row['fecha_cierre'], 'Y-m-d H:i:s');
                $historico[$num_row]['fecha_cierre'] = $date->toXl();
                $row['fecha_cierre'] = $date->toXl();
            }
            if (!empty($row['updated_at'])){
                $date->stringToDate($row['updated_at'], 'Y-m-d H:i:s');
                $historico[$num_row]['updated_at'] = $date->toXl();
                $row['updated_at'] = $date->toXl();
            }
            
            $date->stringToDate($row['fecha_apertura'], 'Y-m-d H:i:s');
            $historico[$num_row]['fecha_apertura'] = $date->toXl();
            $row['fecha_apertura'] = $date->toXl();
            
            if ($row['estado']=='finalizada'){continue;}
            
            if ($date->vardate>=$mes->vardate && $date->vardate<$mes_mas_uno->vardate){
                $reclamaciones_mes[] = $row;
            }
        }
        
        //Recupera los numeros de factura de las reclamaciones
        $fras  = array_filter(array_unique(array_column($historico, 'numero_factura')));
        
        //Si hay facturas recupera los datos de las mismas
        if (!empty($fras)){
            $Array = new ArrayClass($fras);
            $fras  = $Array->implode_values(true);

            $strSQL = "SELECT
                            a.cups,
                            a.numero_factura,
                            b.Tarifa,
                            a.Fecha_factura,
                            a.Fecha_desde,
                            a.Fecha_hasta,
                            a.Total_factura
                        FROM facturas a
                        INNER JOIN (SELECT
                                        cups,
                                        fecha_inicio,
                                        fecha_fin,
                                        Tarifa
                                    FROM clientes
                                    WHERE Grupo='$cli') b
                        ON a.cups=b.CUPS AND a.Fecha_desde BETWEEN b.fecha_inicio AND b.fecha_fin
                        WHERE a.id_cliente='$id_cliente'
                        AND a.numero_factura IN $fras
                        ";

            $fras = $Conn->getArray($strSQL, true);
            
            $Array->add($fras);
            $fras = $Array->assocFromColumn('numero_factura', true);
            
            foreach ($reclamaciones_mes as $num_row=>$row){
                if (empty($row['numero_factura'])){continue;}
                if (!isset($fras[$row['numero_factura']])){continue;}
                
                $reclamaciones_mes[$num_row]['Tarifa']        = $fras[$row['numero_factura']]['Tarifa'];
                $reclamaciones_mes[$num_row]['fecha_fra']     = $fras[$row['numero_factura']]['Fecha_factura'];
                $reclamaciones_mes[$num_row]['desde']         = $fras[$row['numero_factura']]['Fecha_desde'];
                $reclamaciones_mes[$num_row]['hasta']         = $fras[$row['numero_factura']]['Fecha_hasta'];
                $reclamaciones_mes[$num_row]['Total_factura'] = $fras[$row['numero_factura']]['Total_factura'];
            }
            
            foreach ($historico as $num_row=>$row){
                if (empty($row['numero_factura'])){continue;}
                if (!isset($fras[$row['numero_factura']])){continue;}
                
                $historico[$num_row]['Tarifa']        = $fras[$row['numero_factura']]['Tarifa'];
                $historico[$num_row]['fecha_fra']     = $fras[$row['numero_factura']]['Fecha_factura'];
                $historico[$num_row]['desde']         = $fras[$row['numero_factura']]['Fecha_desde'];
                $historico[$num_row]['hasta']         = $fras[$row['numero_factura']]['Fecha_hasta'];
                $historico[$num_row]['Total_factura'] = $fras[$row['numero_factura']]['Total_factura'];
            }
        }
        unset($Conn, $fras);
        
        ob_clean();
        
        //Crea el informe y arregla los formatos
        foreach ($historico as $num_row=>$row){
            if (empty($row['numero_factura'])){continue;}
            
            $date->stringToDate($row['fecha_fra']);
            $historico[$num_row]['fecha_fra'] = $date->toXl();
            
            $date->stringToDate($row['desde']);
            $historico[$num_row]['desde'] = $date->toXl();
            
            $date->stringToDate($row['hasta']);
            $historico[$num_row]['hasta'] = $date->toXl();
        }
        
        foreach ($reclamaciones_mes as $num_row=>$row){
            if (empty($row['numero_factura'])){continue;}
            
            $date->stringToDate($row['fecha_fra']);
            $reclamaciones_mes[$num_row]['fecha_fra'] = $date->toXl();
            
            $date->stringToDate($row['desde']);
            $reclamaciones_mes[$num_row]['desde'] = $date->toXl();
            
            $date->stringToDate($row['hasta']);
            $reclamaciones_mes[$num_row]['hasta'] = $date->toXl();
        }
        
        
        $SprdSht = new SprdSht;
        $SprdSht->load('plantillas/Informe reclamaciones.xlsx', false);
        
        //RECLAMACIONES MES
        if (isset($reclamaciones_mes) && !empty($reclamaciones_mes)){
            $SprdSht->getSheet('Reclamaciones mes');
            $SprdSht->putArray($reclamaciones_mes, false, 'A2');
            $cnt = count($reclamaciones_mes)+1;
            $SprdSht->addBorders("A1:Z$cnt");
            
            //Num fra en formato texto
            foreach ($reclamaciones_mes as $num_row=>$row){
                if (empty($row['numero_factura'])){continue;}
                $SprdSht->setValueAsText('N'.($num_row+2), $row['numero_factura']);
            }
            $SprdSht->setFormatAsDate("C2:D$cnt");
            $SprdSht->setFormatAsDate("P2:R$cnt");
            $SprdSht->setFormatAsDate("Y2:Y$cnt");
            
            $SprdSht->setFormatAsNumber("S2:S$cnt", 2);
            $SprdSht->setFormatAsNumber("W2:W$cnt", 2);
            $SprdSht->setFormatAsNumber("X2:X$cnt", 2);
            
            $SprdSht->setColumnsAutoWidth();
            $SprdSht->setColumnWidth('U', 100);
            $SprdSht->setColumnWidth('Z', 100);
        }
        
        //HISTORICO
        $SprdSht->getSheet('Historico');
        $SprdSht->putArray($historico, false, 'A2');
        $cnt = count($historico)+1;
        $SprdSht->addBorders("A1:Z$cnt");
        
        
        //Num fra en formato texto
        foreach ($historico as $num_row=>$row){
            if (empty($row['numero_factura'])){continue;}
            $SprdSht->setValueAsText('N'.($num_row+2), $row['numero_factura']);
        }
        $SprdSht->setFormatAsDate("C2:D$cnt");
        $SprdSht->setFormatAsDate("P2:R$cnt");
        $SprdSht->setFormatAsDate("Y2:Y$cnt");
        
        $SprdSht->setFormatAsNumber("S2:S$cnt", 2);
        $SprdSht->setFormatAsNumber("W2:W$cnt", 2);
        $SprdSht->setFormatAsNumber("X2:X$cnt", 2);
        
        $SprdSht->setColumnsAutoWidth();
        $SprdSht->setColumnWidth('U', 100);
        $SprdSht->setColumnWidth('Z', 100);
        
        $Carpetas = new Carpetas;
        $dir = $Carpetas->checkInformesMensuales($cli, 'VALIDACIONES Y RECLAMACIONES');
        unset($Carpetas);
        
        $filename = "Informe reclamaciones $cli.xlsx";
        $SprdSht->save("$dir/$filename");
        
        //Crea la referencia para el seguimiento del cliente
        $date = new DateClass;
        $redactado = $date->format();
        
        $date->diaUno();
        $mes = $date->format();
        
        $enviado        = '';
        $comentarios    = '';
        $strLinks       = '<a href="js_actions.php?action=getFile&url='."$dir/$filename".'">'.$filename.'</a>';
        $strDirFilename = "$dir/$filename";
        $informe        = 'RECLAMACIONES';
        
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("INSERT INTO seguimiento_cliente_informes (CLIENTE, INFORME, REDACTADO, ENVIADO, COMENTARIOS, LINK_INFORME, MES, DIR) VALUES ('$cli', '$informe', '$redactado', '$enviado', '$comentarios', '$strLinks', '$mes', '$strDirFilename')");
        unset($Conn);
        unset($Conn);
        
        $SprdSht->directDownload($filename);
        unset($historico, $reclamaciones_mes, $SprdSht);
        
        break;
        
    case 'mensual':
        
        ini_set('memory_limit', '2048M');
        
        //Prepara las variables
        $cli                    = $_POST['cli'];
        $ano                    = $_POST['ano'];
        $anos_a_restar          = date('Y') - $ano;
        
        $CalculosSimples = new CalculosSimples;
        $id_cli = $CalculosSimples->getIdCliente($cli);
        unset($CalculosSimples);
        
        $columnas_adicionales   = (isset($_POST['columnas'])) ? $_POST['columnas'] : NULL;
        $strColumnas            = 'CONCAT(cups, mes) id,
                                    cups,
                                    mes,
                                    total,
                                    termino_potencia,
                                    termino_energia,
                                    excesos_reactiva,
                                    excesos_potencia,
                                    base_imponible_total,
                                    total_factura';
        
        if (isset($columnas_adicionales) && !empty($columnas_adicionales)){$strColumnas .= ', '.implode(', ', $columnas_adicionales);}
        
        //Datos de los suministros
        $desde_contrato = new DateClass;
        $desde_contrato->subtract($anos_a_restar);
        
        $strSQL = "SELECT
                        c.cif,
                        a.Empresa,
                        a.cups,
                        a.numero_contrato,
                        DATE_FORMAT(a.fecha_fin, '%d/%m/%Y') fecha,
                        a.dar,
                        a.codigo_oficina,
                        a.direccion,
                        a.Poblacion,
                        a.provincia,
                        a.COMERCIALIZADORA,
                        a.TARIFA,
                        a.P1,
                        a.P2,
                        a.P3,
                        a.P4,
                        a.P5,
                        a.P6,
                        a.observaciones
                FROM clientes a
                INNER JOIN (SELECT
                                cups,
                                MAX(fecha_alta) maxalta
                            FROM clientes
                            WHERE Grupo='$cli'
                            AND fecha_fin>='".$desde_contrato->format('Y-01-01')."'
                            GROUP BY cups) b
                ON a.cups=b.cups
                AND a.fecha_alta=b.maxalta
                INNER JOIN (SELECT
                                id,
                                cif
                            FROM grupos
                            WHERE id_gestion_cliente=$id_cli) c
                ON a.grupos_empresa_id=c.id
                WHERE a.Grupo='$cli'
                AND a.fecha_fin>='".$desde_contrato->format('Y-01-01')."'
                ORDER BY a.cups, a.fecha_inicio";
        
        $Conn = new Conn('mainsip', 'develop');
        $ps = $Conn->getArray($strSQL, true);
        
        //Datos de consumo
        $desde_consumos = new DateClass;
        $hasta_consumos = new DateClass;
        $desde_consumos->subtract((1+$anos_a_restar));
        $hasta_consumos->add((1-$anos_a_restar));
        
        $strSQL = "SELECT
                        $strColumnas
                    FROM datos_notelemedidas
                    WHERE grupo='$cli'
                    AND mes>='".$desde_consumos->format('Y-01-01')."'
                    AND mes<'".$hasta_consumos->format('Y-01-01')."'
                    ORDER BY cups, mes";
        
        $consumos = $Conn->getArray($strSQL, true);
        
        $Array = new ArrayClass($consumos);
        $consumos = $Array->assocFromColumn('id', true);
        unset($Array, $Conn, $strColumnas, $desde_consumos, $hasta_consumos);
        
        
        //Inserta los datos de los suminsitros en el excel
        $SprdSht = new SprdSht;
        $SprdSht->load('plantillas/Informe mensual.xlsx', false);
        $SprdSht->getSheet('RESUMEN DE CONTRATOS');
        $SprdSht->putArray($ps, false, 'A3');
        $SprdSht->addBorders('A3:S'.(count($ps)+2));
        $SprdSht->setCellValue('A1', $ano);
        
        foreach ($ps as $num_row=>$row){
            foreach ($row as $key=>$value){
                switch ($key){
                    case 'numero_contrato':
                    case 'fecha':
                    case 'COMERCIALIZADORA':
                    case 'P1':
                    case 'P2':
                    case 'P3':
                    case 'P4':
                    case 'P5':
                    case 'P6':
                    case 'observaciones':
                        unset($ps[$num_row][$key]);
                        break;
                }
            }
        }
        
        
        //Ordena los datos de consumo
        $fecha_limite = new DateClass;
        $fecha_limite->add(1-$anos_a_restar);
        $ano = $fecha_limite->format('Y');
        $fecha_limite->stringToDate("$ano-01-01");
        
        $datos_consumo = array(
            'total'                 =>'CONSUMO ENERGÍA ACTIVA',
            'termino_potencia'      =>'IMPORTE TP (EUROS)',
            'excesos_potencia'      =>'IMPORTE EXC POT (EUROS)',
            'termino_energia'       =>'IMPORTE TE (EUROS)',
            'excesos_reactiva'      =>'IMPORTE REACTIVA (EUROS)',
            'base_imponible_total'  =>'IMPORTE BI (EUROS)',
            'total_factura'         =>'IMPORTE TOTAL (EUROS)'
        );
        
        //Añade las columnas adicionales
        if (isset($columnas_adicionales)){
            foreach ($columnas_adicionales as $key=>$value){$datos_consumo[$value] = strtoupper($value);}
        }
        
        //Inserta los datos
        foreach ($datos_consumo as $column=>$sheet){
            foreach ($ps as $num_row=>$row){

                $fecha = new DateClass;
                $fecha->subtract(1+$anos_a_restar);
                $ano   = $fecha->format('Y');
                $fecha->stringToDate("$ano-01-01");

                $cups  = $row['cups'];

                $tot = 0;
                while ($fecha->vardate<$fecha_limite->vardate){

                    //Cuando cambia el año pone el sumatorio
                    if ($ano!=$fecha->format('Y')){
                        $ps[$num_row]["tot_$ano"] = $tot;
                        $tot                      = 0;
                        $ano                      = $fecha->format('Y');
                    }

                    $fecha_txt = $fecha->format();
                    
                    $ps[$num_row][$fecha_txt] = (isset($consumos[$cups.$fecha_txt])) ? $consumos[$cups.$fecha_txt][$column] : 0;
                    
                    $tot += $ps[$num_row][$fecha_txt];
                    $fecha->add(0,1);
                }
                $ps[$num_row]["tot_$ano"] = $tot;
            }
            
            //Si es una columna adicional copia una hoja existente
            if (isset($columnas_adicionales) && in_array($column, $columnas_adicionales)){
                $SprdSht->getSheet('IMPORTE TOTAL (EUROS)');
                $SprdSht->copySheet('DATO EXTRA', $sheet, 9);
            } else {
                $SprdSht->getSheet($sheet);
            }
            
            $SprdSht->putArray($ps, false, 'A3');
            $SprdSht->addBorders('A3:AJ'.(count($ps)+2));
            switch ($column){
                case 'total':
                case 'dias':
                case 'p1':
                case 'p2':
                case 'p3':
                case 'p4':
                case 'p5':
                case 'p6':
                    $SprdSht->setFormatAsNumber('J3:AJ'.(count($ps)+2));
                    break;
                default:
                    $SprdSht->setFormatAsCurrency('J3:AJ'.(count($ps)+2));
                    break;
            }
        }
        
        $SprdSht->delSheet('DATO EXTRA');
        
        //Crea las graficas
        $hojas_graficas = array('GRAFICA DE COMPARACIÓN CIF', 'GRAFICA DE COMPARACIÓN CUPS', 'GRAFICA TOTAL');
        $SprdSht->newChart();
        
        $cups = array_chunk(array_column($ps, 'cups'), 1);
        $cifs = array_chunk(array_unique(array_column($ps, 'cif')), 1);
        unset($ps);
        
        foreach ($hojas_graficas as $key=>$hoja){
            $SprdSht->getSheet($hoja);
            
            //Pone el listado de CIFs o CUPS a partir de la celda S100
            switch ($hoja){
                case 'GRAFICA DE COMPARACIÓN CIF':  $SprdSht->putArray($cifs, false, 'S100'); break;
                case 'GRAFICA DE COMPARACIÓN CUPS': $SprdSht->putArray($cups, false, 'S100'); break;
            }
            
            $SprdSht->chartXLabels("'$hoja'!A23:A34", 12);
            
            $x = 'B';
            $y = 1;
            while ($x!='N'){
                
                $color = (($y % 2)==0) ? '9e413e' : '40699c'; //40699c = azul, 9e413e = rojo
                
                $SprdSht->chartDataSeries("'$hoja'!$x"."23:$x".'34', 12, "'$hoja'!$x".'22', $color); //ej. 'Worksheet'!B23:B34, 12, 'Worksheet'!B22
                ++$x;
                ++$y;
            }
            
            $SprdSht->chartCreate($hoja, 'A20', 'O46');
        }
        
        $SprdSht->setActiveSheet('RESUMEN DE CONTRATOS');
        
        //Crea la carpeta si no existe para guardar el informe
        $Carpetas = new Carpetas;
        $dir = $Carpetas->checkInformesMensuales($cli, 'INFORMES');
        
        $CalculosSimples = new CalculosSimples;
        $mes = $CalculosSimples->numToTextMonth(date('n'));
        
        //Guarda el fichero
        $ano = $_POST['ano'];
        $filename = "INFORME $cli $mes $ano.xlsx";
        $SprdSht->save("$dir/$filename");
        unset($SprdSht);
        
        //Crea la referencia para el seguimiento del cliente
        $date = new DateClass;
        $redactado = $date->format();
        
        $date->diaUno();
        $mes = $date->format();
        
        $enviado        = '';
        $comentarios    = '';
        $strLinks       = '<a href="js_actions.php?action=getFile&url='."$dir/$filename".'">'.$filename.'</a>';
        $strDirFilename = "$dir/$filename";
        $informe        = 'MULTIPUNTO MENSUAL';
        
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("INSERT INTO seguimiento_cliente_informes (CLIENTE, INFORME, REDACTADO, ENVIADO, COMENTARIOS, LINK_INFORME, MES, DIR) VALUES ('$cli', '$informe', '$redactado', '$enviado', '$comentarios', '$strLinks', '$mes', '$strDirFilename')");
        
        unset($Conn, $mes, $dir, $Carpetas, $consumos, $CalculosSimples, $hojas_graficas, $cups, $cifs);
        
        header ("Location: informes.php");
        
        break;
        
    case 'getFolderMensual':
        
        $cli = $_POST['cli'];
        
        $Carpetas = new Carpetas;
        $dir = $Carpetas->checkInformesMensuales($cli, 'INFORMES');
        unset($Carpetas);
        
        $dir = str_replace('/', '\\', $dir);
        
        echo $dir;
        
        break;
}


?>