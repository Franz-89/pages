<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

switch ($_POST['action']){
		
	case "aon_auditadas":
		
		$headers = array('Customer name',
				 'Address line 1',
				 'Unique Identifier',
				 'Supplier',
				 'Fuel',
				 'Invoice Number',
				 'Invoice Date',
				 'Invoice Start Date',
				 'Invoice End Date',
				 'Consumption (kWh)',
				 'Commodity Cost',
				 'Supplier costs',
				 'Distribution Cost',
				 'Tranmission Cost',
				 'Environmental Levy Cost',
				 'Total Cost (ex VAT)'
				);
		
		if (!isset($_POST['desde']) || !isset($_POST['hasta']) || empty($_POST['desde']) || empty($_POST['hasta'])){header ("Location: elab_clientes.php");}
		
		$desde = date_create_from_format('d/m/Y', $_POST['desde']);
		$hasta = date_create_from_format('d/m/Y', $_POST['hasta']);
		
		//ATR
		$Conn = new Conn('local', 'enertrade');
		$query = $Conn->Query("SELECT * FROM atr_te");
		while ($row = mysqli_fetch_assoc($query)){$ATR[] = $row;}
		unset($Conn);
		
		//FRAS
		$Conn = new Conn('mainsip', 'develop');
		$strSQL = "SELECT
						b.Empresa,
						a.cups,
						b.Tarifa,
						b.Comercializadora,
						CONCAT(b.direccion, ' ',  b.provincia) address,
						a.numero_factura,
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
						a.Ter_Energia,
						a.Ter_potencia,
						a.impuesto_electricidad,
						a.Base_imponible,
						a.Aud_fecha
				FROM facturas a
				INNER JOIN (
						SELECT
							cups,
							fecha_inicio,
							fecha_fin,
							Tarifa,
							Empresa,
							direccion,
							provincia,
							Comercializadora
						FROM clientes
						WHERE Grupo='AON') b
				ON a.cups=b.CUPS
				AND (a.Fecha_desde BETWEEN b.fecha_inicio AND b.fecha_fin)
				WHERE (a.id_cliente='97')
				AND (a.Aud_fecha BETWEEN '".date_format($desde, 'Y-m-d')."' AND '".date_format($hasta, 'Y-m-d')."')
				ORDER BY CUPS,
				Fecha_factura,
				Fecha_desde";

		$query = $Conn->Query($strSQL);
		
		//Rellena los datos
		while ($row = mysqli_fetch_assoc($query)){

			$linea = array_fill_keys($headers, '');
			
			$fra = explode("_", $row['numero_factura']);
			
			$linea['Customer name'] 			= $row['Empresa'];
			$linea['Address line 1'] 			= $row['address'];
			$linea['Unique Identifier'] 		= $row['cups'];
			$linea['Supplier'] 					= $row['Comercializadora'];
			$linea['Fuel'] 						= "Power";
			$linea['Invoice Number'] 			= $fra[0]." ";
			$linea['Invoice Date'] 				= date_sql_to_php($row['Fecha_factura']);
			$linea['Invoice Start Date'] 		= date_sql_to_php($row['Fecha_desde']);
			$linea['Invoice End Date'] 			= date_sql_to_php($row['Fecha_hasta']);
			$linea['Consumption (kWh)'] 		= $row['consumo_total'];

			$TE_ATR = 0;
			for ($x=1; $x<=6; $x++){$TE_ATR += $ATR[array_search($row['Tarifa'], array_column($ATR, "TARIFA"))]["P$x"]/100*$row["consumo_energia_p$x"];}

			$linea['Supplier costs'] 			= $row['Ter_Energia'] - $TE_ATR;
			$linea['Distribution Cost'] 		= $row['Ter_potencia'] + $TE_ATR;
			$linea['Environmental Levy Cost'] 	= $row['impuesto_electricidad'];
			$linea['Total Cost (ex VAT)'] 		= $row['Base_imponible'];


			$datos[] = $linea;
			unset($linea);
		}

		if (!isset($datos) || empty($datos)){header ("Location: elab_clientes.php");}

		unset($Conn);
		
		$SprdSht = new SprdSht;
		$SprdSht->nuevo();
		$SprdSht->putArray($datos, true);
		$SprdSht->directDownload("Aon informe auditadas");
		unset($SprdSht);
		
		//@descarga($datos, $headers, "Aon informe auditadas");
		break;
		
	case "eusk_emitidas":
		
		if (!isset($_POST['desde']) || !isset($_POST['hasta']) || empty($_POST['desde']) || empty($_POST['hasta'])){header ("Location: elab_clientes.php");}
		
        set_time_limit(0);
        
        $Conn = new Conn('local', 'enertrade');
        $ccaa = $Conn->getArray('SELECT * FROM ccaa');
        unset($Conn);
        
        $CalculosSimples = new CalculosSimples;
        $id_cli = $CalculosSimples->getIdCliente('EUSKALTEL');
        unset($CalculosSimples);
        
        $desde = new DateClass;
        $desde->stringToDate($_POST['desde'], 'd/m/Y');
        $desde->diaUno();
        $hasta = new DateClass;
        $hasta->stringToDate($_POST['hasta'], 'd/m/Y');
        $hasta->diaUno();
        $desde_mas_uno = new DateClass;
        
        $Conn = new Conn('mainsip', 'develop');
        
        while ($desde!=$hasta){
            
            $desde_mas_uno->stringToDate($desde->format());
            $desde_mas_uno->add(0,1);
            
            $Conn->query('CREATE TEMPORARY TABLE local.temp_euskaltel (
                                id varchar (50) PRIMARY KEY,
                                cups text DEFAULT NULL,
                                mes date DEFAULT NULL,
                                suma_dias int DEFAULT NULL,
                                suma_Consumo float DEFAULT NULL,
                                suma_BI float DEFAULT NULL,
                                prorr_dias float DEFAULT 0,
                                prorr_consumo float DEFAULT 0,
                                prorr_iva float DEFAULT 0,
                                COMERCIALIZADORA text DEFAULT NULL,
                                provincia text DEFAULT NULL,
                                libre_3 text DEFAULT NULL,
                                libre_4 text DEFAULT NULL,
                                libre_5 text DEFAULT NULL,
                                libre_6 text DEFAULT NULL)
                            COLLATE utf8mb4_unicode_ci');
            
            //Datos de facturación
            $Conn->query("INSERT INTO local.temp_euskaltel (id, cups, mes, suma_dias, suma_Consumo, suma_BI)
                            SELECT
                                    CONCAT(cups, DATE_FORMAT(Fecha_factura, '%Y-%m-01')) id,
                                    cups,
                                    DATE_FORMAT(Fecha_factura, '%Y-%m-01') Mes,
                                    SUM(DATEDIFF(Fecha_hasta, Fecha_desde)+1) suma_dias,
                                    SUM(consumo_total) suma_Consumo,
                                    SUM(Base_imponible_total) suma_BI
                            FROM develop.facturas
                            WHERE (id_cliente='106')
                            AND (Fecha_factura>='".$desde->format()."' AND Fecha_factura<'".$desde_mas_uno->format()."')
                            GROUP BY cups, Mes");
            
            //Datos prorrateados
            $Conn->query("INSERT INTO local.temp_euskaltel (id, cups, mes, prorr_dias, prorr_consumo, prorr_iva)
                            SELECT
                                CONCAT(b.cups, b.mes) id,
                                b.cups,
                                b.mes,
                                b.dias,
                                b.total prorr_consumo,
                                b.base_imponible_total
                            FROM develop.datos_notelemedidas b
                            WHERE grupos_cliente_id='106'
                            AND mes BETWEEN '".$desde->format()."' AND '".$desde->format('Y-m-t')."'
                        ON DUPLICATE KEY UPDATE prorr_dias=VALUES(prorr_dias), prorr_consumo=VALUES(prorr_consumo), prorr_iva=VALUES(prorr_iva)
                        ");
            /*
            //Datos ps
            $Conn->query("UPDATE local.temp_euskaltel a,
                            (SELECT
                                COMERCIALIZADORA,
                                CUPS,
                                provincia,
                                fecha_inicio,
                                fecha_fin, 
                                libre_3,
                                libre_4,
                                libre_5,
                                libre_6
                            FROM develop.clientes
                            WHERE Grupo='EUSKALTEL') b
                        SET a.COMERCIALIZADORA=b.COMERCIALIZADORA,
                        a.provincia=b.provincia,
                        a.libre_3=b.libre_3,
                        a.libre_4=b.libre_4,
                        a.libre_5=b.libre_5
                        WHERE a.cups=b.CUPS
                        AND a.mes BETWEEN b.fecha_inicio AND b.fecha_fin
                        ");
            */
            //CCAA
            foreach ($ccaa as $num_row=>$row){$Conn->query("UPDATE local.temp_euskaltel SET provincia='".$row['CCAA']."' WHERE provincia='".$row['PROVINCIA']."'");}
            

            $final = $Conn->getArray("SELECT
                                        id,
                                        cups,
                                        DATE_FORMAT(mes, '%d/%m/%Y') fecha,
                                        suma_dias,
                                        suma_consumo,
                                        suma_BI,
                                        prorr_dias,
                                        prorr_consumo,
                                        prorr_iva,
                                        COMERCIALIZADORA,
                                        provincia CCAA,
                                        libre_3,
                                        libre_4,
                                        libre_5,
                                        libre_6
                                    FROM local.temp_euskaltel");
            
            $Conn->query('DROP TEMPORARY TABLE local.temp_euskaltel');
            
            $date = new DateClass;
            foreach ($final as $num_row=>$row){
                $date->stringToDate($row['fecha'], 'd/m/Y');
                switch (true){
                    case ($row['prorr_dias']>=$date->format('t')):  $final[$num_row]['mes'] = 'COMPLETO';   break;
                    case ($row['prorr_dias']==0):                   $final[$num_row]['mes'] = 'FALTA';      break;
                    default:                                        $final[$num_row]['mes'] = 'INCOMPLETO'; break;
                }
            }
            unset($date);
            
            $SprdSht = new SprdSht;
            $SprdSht->nuevo();
            $SprdSht->putArray($final, true);
            unset($final);
            $SprdSht->save($desde->format().".xlsx");
            unset($SprdSht);
            
            $files[] = $desde->format().".xlsx";
            
            $desde->add(0,1);
        }
        
        $strSQL = "SELECT
                        a.CUPS,
						d.cif,
						a.Empresa,
						a.COMERCIALIZADORA,
						a.estado,
                        DATE_FORMAT(b.mininicio, '%d/%m/%Y') FECHA_MIN,
						DATE_FORMAT(a.fecha_fin, '%d/%m/%Y') FECHA_MAX,
						a.provincia CCAA,
						a.TARIFA,
                        a.libre_1 RESTO,
                        a.direccion,
                        a.poblacion,
                        a.provincia,
                        c.tipo_contrato,
                        a.numero_contrato,
                        a.codigo_oficina,
                        a.dar,
                        a.tipo_edificio,
                        a.libre_3,
                        a.libre_4,
                        a.libre_5,
                        a.libre_6
					FROM clientes a
					INNER JOIN(
							SELECT
								CUPS,
                                MIN(fecha_inicio) mininicio,
								MAX(fecha_alta) maxalta
							FROM clientes
							WHERE (Grupo='EUSKALTEL')
							GROUP BY CUPS) b
					ON a.CUPS=b.CUPS
					AND a.fecha_alta=b.maxalta
                    INNER JOIN (
                        SELECT
                            id,
                            nombre tipo_contrato
                        FROM plantillas_validacion
                    ) c
                    ON a.plantilla_validacion_id=c.id
                    INNER JOIN (SELECT
                                id,
                                cif
                            FROM grupos
                            WHERE id_gestion_cliente=$id_cli) d
                    ON a.grupos_empresa_id=d.id
					WHERE a.Grupo='EUSKALTEL'";
        
        $ps = $Conn->getArray($strSQL);
        foreach ($ccaa as $num_row=>$row){$ccaa[$row['PROVINCIA']] = $row['CCAA'];}
        foreach ($ps as $num_row=>$row){$ps[$num_row]['provincia'] = $ccaa[$row['provincia']];}
        unset($ccaa);
        
        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->putArray($ps, true);
        unset($ps);
        $SprdSht->save("PS.xlsx");
        unset($SprdSht);

        $files[] = "PS.xlsx";
        
        merge_and_dwd_zip('Informe Esukaltel.zip', $files);
        
		break;
        
    case 'eusk_txt':
        
        if (!isset($_POST['desde']) || !isset($_POST['hasta']) || empty($_POST['desde']) || empty($_POST['hasta'])){header ("Location: elab_clientes.php");}
		
        set_time_limit(3600);
        
        $desde = new DateClass;
        $desde->stringToDate($_POST['desde'], 'd/m/Y');
        $desde = $desde->format();
        $hasta = new DateClass;
        $hasta->stringToDate($_POST['hasta'], 'd/m/Y');
        $hasta = $hasta->format();
        
        $CalculosSimples = new CalculosSimples;
        $id_cliente = $CalculosSimples->getIdCliente('EUSKALTEL');
        unset($CalculosSimples);
        
        $Conn = new Conn('mainsip', 'develop');
        $strSQL = "SELECT
                        a.cups,
                        d.Comercializadora,
                        a.numero_factura,
                        a.fecha_factura,
                        a.Fecha_desde,
                        a.Fecha_hasta,
                        (a.valor_iva_general + a.valor_iva_reducido + a.valor_iva_superreducido) Valor_IVA,
                        a.Total_factura,
                        d.libre_7,
                        d.cif
                    FROM facturas a
                    INNER JOIN (
                        SELECT
                            b.cups,
                            b.fecha_inicio,
                            b.fecha_fin,
                            c.cif,
                            b.Comercializadora, 
                            b.libre_7
                        FROM clientes b
                        INNER JOIN (
                            SELECT
                                id,
                                cif
                            FROM grupos
                            WHERE id_gestion_cliente=$id_cliente) c
                            ON b.grupos_empresa_id=c.id
                        WHERE Grupo='EUSKALTEL') d
                    ON a.cups=d.CUPS
                    AND (a.Fecha_desde BETWEEN d.fecha_inicio AND d.fecha_fin)
                    
                    WHERE id_cliente=$id_cliente
                    AND a.fecha_factura BETWEEN '$desde' AND '$hasta'";
        
        $facturas = $Conn->getArray($strSQL);
        unset($Conn);
        
        if (!isset($facturas) || empty($facturas)){header ("Location: elab_clientes.php");}
        
        foreach ($facturas as $num_row=>$row){
            //Saca el numero de factura agrupada
            $row['numero_factura'] = str_replace('fi_', '', $row['numero_factura']);
            $fra = explode('_', $row['numero_factura']);
            $facturas[$num_row]['numero_factura'] = $fra[0];
            unset($fra);
            
            //Calcula si el IVA es del 10% o del 21% o del 5%
            $BI = $row['Total_factura'] - $row['Valor_IVA'];
            $IVA_10 = round($BI*0.1, 2);
            $IVA_05 = round($BI*0.05, 2);
            
            switch (true){
                case (round($row['Valor_IVA'], 2)==$IVA_10):
                    $facturas[$num_row]['tipo_IVA'] = 'IVA_10';
                    break;
                case (round($row['Valor_IVA'], 2)==$IVA_05):
                    $facturas[$num_row]['tipo_IVA'] = 'IVA_05';
                    break;
                default:
                    $facturas[$num_row]['tipo_IVA'] = 'IVA_21';
                    break;
            }
            unset($BI, $IVA_10, $IVA_05);
        }
        
        //TABLA
		$strSQL = "CREATE TEMPORARY TABLE temp_euskaltel (
						  cups text NOT NULL,
						  comercializadora text NOT NULL,
						  numero_factura text NOT NULL,
						  fecha_factura date NOT NULL,
						  Fecha_desde date NOT NULL,
						  Fecha_hasta date NOT NULL,
						  Valor_IVA float NOT NULL,
						  Total_factura float NOT NULL,
						  libre_7 text NOT NULL,
						  cif text NOT NULL,
						  tipo_IVA text NOT NULL
						)";
        $Conn = new Conn('local', 'enertrade');
		$Conn->Query($strSQL);
        
        //Inserta os valores en una tabla temporal
        $values_fras = implode_values($facturas);
        $Conn->Query("INSERT INTO temp_euskaltel (cups, comercializadora, numero_factura, fecha_factura, Fecha_desde, Fecha_hasta, Valor_IVA, Total_factura, libre_7, cif, tipo_IVA) VALUES $values_fras");
        unset($values_fras, $facturas);
        
        //Saca las lineas de las facturas que NO son abonos
        $lineas_no_abonos = $Conn->getArray("SELECT COUNT(cups) cuenta_cups, cups, comercializadora, numero_factura, fecha_factura, Fecha_desde, Fecha_hasta, SUM(Total_factura) suma_total, libre_7, cif, tipo_IVA FROM temp_euskaltel WHERE Total_factura>=0 GROUP BY numero_factura, comercializadora, libre_7, cif, tipo_IVA", true);
        
        //Saca las lineas de las facturas que SON abonos
        $lineas_abonos = $Conn->getArray("SELECT COUNT(cups) cuenta_cups, cups, comercializadora, numero_factura, fecha_factura, Fecha_desde, Fecha_hasta, SUM(Total_factura) suma_total, libre_7, cif, tipo_IVA FROM temp_euskaltel WHERE Total_factura<0 GROUP BY numero_factura, comercializadora, libre_7, cif, tipo_IVA", true);
        
        $Conn->Query('DROP TEMPORARY TABLE temp_euskaltel');
        unset($Conn);
        
        //Une los dos arrays
        $total = array();
        foreach ($lineas_no_abonos as $num_row=>$row){$total[] = $row;}
        foreach ($lineas_abonos as $num_row=>$row){$total[] = $row;}
        unset($lineas_no_abonos, $lineas_abonos);
        
        
        $headers = array('NUM', 'SDAD', 'FECHA_FRA', 'FECHA_CONTABILIDAD', 'CLASE_DOCUMENTO', 'NUM_FRA0', 'CUENTA_CONTRATO', 'COD_PROVEEDOR', 'CLAVE_CONTABLE', '1', 'MONEDA', '2', '3', '4', '5', 'IMPORTE', '6', 'COND_PAGO', 'F_BASE', 'VIA_DE_PAGO', '7', '8', '9', '10', '11', '12', 'CODIGO_IVA', 'NUM_FRA', 'NUM_FRA2', '13', '14', '15', '16', '17', 'ORDEN_PRESUPUESTARIA', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', 'TRANSACION_SAP');
        
        $date = new DateClass;
        $final = array();
        $facturas = array();
        $x = 1;
        foreach ($total as $num_row=>$row){
            $linea = array_fill_keys($headers, '');
            
            $abono = ($row['suma_total']<0) ? true : false;
            
            //Secuencial de la fra
            if (!array_key_exists($row['numero_factura'], $facturas)){
                $facturas[$row['numero_factura']] = $x;
                ++$x;
            }
            
            $linea['NUM'] = $facturas[$row['numero_factura']];
            
            //Codigo cliente
            switch ($row['cif']){
                case 'A48766695':
                case 'A95554630':
                    $linea['SDAD'] = 'ET00';
                    break;
                    
                case 'A15474281':
                    $linea['SDAD'] = 'R000';
                    break;
                    
                case 'A33445917':
                    $linea['SDAD'] = 'TC00';
                    break;
            }
            
            
            $linea['FECHA_FRA'] = $date->fromToFormat($row['fecha_factura'], 'Y-m-d', 'd.m.Y');
            $date->stringToDate($row['fecha_factura'], 'Y-m-d');
            $linea['FECHA_CONTABILIDAD'] = $date->format('t.m.Y');
            
            $linea['CLASE_DOCUMENTO'] = ($abono) ? 'KG' : 'KB';
            $linea['NUM_FRA0']         = $row['numero_factura'];
            $linea['CUENTA_CONTRATO']  = ($abono) ? 'ABONO' : '';
            
            switch ($row['comercializadora']){
                case 'EDP ENERGÍA, S.A.U.':
                    $linea['COD_PROVEEDOR'] = 12006;
                    $linea['COND_PAGO']     = 'Z102';
                    $linea['VIA_DE_PAGO']   = 'T';
                    break;
                    
                case 'IBERDROLA CLIENTES, S.A.U.':
                    $linea['COD_PROVEEDOR'] = 8277;
                    $linea['COND_PAGO']     = '0001';
                    $linea['VIA_DE_PAGO']   = 'D';
                    break;
                    
                case 'IBERDROLA COMERCIALIZACIÓN DE ÚLTIMO RECURSO, S.A.U.':
                    $linea['COD_PROVEEDOR'] = 6939;
                    $linea['COND_PAGO']     = '0001';
                    $linea['VIA_DE_PAGO']   = 'D';
                    break;
                    
                case 'GAS NATURAL COMERCIALIZADORA, S.A.':
                    $linea['COD_PROVEEDOR'] = 9990;
                    $linea['COND_PAGO']     = 'R030';
                    $linea['VIA_DE_PAGO']   = 'D';
                    break;
                    
                case 'PEPEENERGY S.L.':
                    $linea['COD_PROVEEDOR'] = 12454;
                    $linea['COND_PAGO']     = '0001';
                    $linea['VIA_DE_PAGO']   = 'D';
                    break;
            }
            
            $linea['CLAVE_CONTABLE']  = ($abono) ? 21 : 31;
            $linea['MONEDA']          = 'EUR';
            $linea['IMPORTE']         = round(abs($row['suma_total']), 2);
            $linea['F_BASE']          = $linea['FECHA_CONTABILIDAD'];
            
            $linea['NUM_FRA']         = ($row['cuenta_cups']==1) ? $row['cups']." de ".$date->fromToFormat($row['Fecha_desde'])." a ".$date->fromToFormat($row['Fecha_hasta']) : $row['numero_factura'];
            $linea['NUM_FRA2']        = $row['numero_factura'];
            $linea['TRANSACION_SAP']  = 'FB01';
            
            $final[] = $linea;
            
            //Segunda linea
            $linea['COD_PROVEEDOR']        = 628010;
            $linea['CLAVE_CONTABLE']       = ($abono) ? 50 : 40;
            $linea['COND_PAGO']            = '';
            $linea['F_BASE']               = '';
            $linea['VIA_DE_PAGO']          = '';
            
            switch ($row['tipo_IVA']){
                case 'IVA_21': $linea['CODIGO_IVA'] = 'G9'; break;
                case 'IVA_10': $linea['CODIGO_IVA'] = 'G5'; break;
                case 'IVA_05': $linea['CODIGO_IVA'] = 'GA'; break;
            }
            
            $linea['ORDEN_PRESUPUESTARIA'] = $row['libre_7'];
            
            $final[] = $linea;
            
            unset($linea, $total[$num_row]);
        }
        unset($total, $headers, $facturas);
        
        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->putArray($final, true);
        $x = 2;
        foreach ($final as $num_row=>$row){
            $SprdSht->setValueAsText("F$x", $row['NUM_FRA0']);
            $SprdSht->setValueAsText("AB$x", $row['NUM_FRA']);
            $SprdSht->setValueAsText("AC$x", $row['NUM_FRA2']);
            $SprdSht->setValueAsText("AI$x", $row['ORDEN_PRESUPUESTARIA']);
            ++$x;
        }
        $SprdSht->save('EUSKALTEL');
        unset($SprdSht);
        $files[] = 'EUSKALTEL.xlsx';
        
        if (in_array('ET00', array_column($final, 'SDAD'))){$fopen_eusk = fopen('TXT_EUSKALTEL.txt', 'a');}
        if (in_array('R000', array_column($final, 'SDAD'))){$fopen_rcab = fopen('TXT_RCABLE.txt', 'a');}
        
        foreach ($final as $num_row=>$row){
            
            switch ($row['SDAD']){
                case 'ET00': fputcsv($fopen_eusk, $row, "\t"); break;
                default:     fputcsv($fopen_rcab, $row, "\t"); break;
            }
            
            unset($final[$num_row], $valores);
        }
        unset($final);
        
        if ($fopen_eusk){
            fclose($fopen_eusk);
            $files[] = 'TXT_EUSKALTEL.txt';
        }
        if ($fopen_rcab){
            fclose($fopen_rcab);
            $files[] = 'TXT_RCABLE.txt';
        }
        
        merge_and_dwd_zip('TXT_EUSKALTEL.zip', $files);
        
        break;
		
	case "csv_bt_dia":
		
		if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: elab_clientes.php");}
		
		$array_header = array(
			'Linea',
			'ID CIF',
			'SA',
			'Emisión',
			'Fecha contable',
			'EUR',
			'Vacio 1',
			'Fra',
			'Emisor',
			'S',
			'Id. Cuenta',
			'Vacio 2',
			'Haber total negativo',
			'Haber total positivo',
			'Vacio 3',
			'Vacio 4',
			'Vacio 5',
			'Vacio 6',
			'Vacio 7',
			'Vacio 8',
			'CECO',
			'Vacio 9',
			'Desde-Hasta',
			'CUPS',
			'Vacio 10',
			'Vacio 11',
			'Vacio 12',
			'Vacio 13',
			'Vacio 14',
			'Vacio 15',
			'Vacio 16',
			'Vacio 17',
			'Vacio 18',
			'Vacio 19',
			'Vacio 20',
			'Vacio 21',
			'Vacio 22',
			'Vacio 23',
			'Activa total'
		);


		$array_final = array();
		$filenum = 0;
		foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){
				
				$SprdSht = new SprdSht;
				$SprdSht->load($file, true);

				//Para cada hoja
				for ($i = 0; $i < ($SprdSht->getShtCnt()); $i++){

					$array_tot = array();
					
					$SprdSht->getSheet($i);
					$array 	= $SprdSht->getArray();

					//Fra
					$fra = trim($SprdSht->getCellValue("B12"));

					//Emisión
					$emision = $SprdSht->getCellValue("A9");
					if (empty ($emision)){$emision = $SprdSht->getCellValue("B9");}

					$fecha_contable = date_format(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($emision), 'tmY');
					$emision 		= date_format(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($emision), 'dmY');

					//CIF
					$CIF = $SprdSht->getCellValue("E5");
					if (empty ($CIF)){$CIF = $SprdSht->getCellValue("G5");}
					if (empty ($CIF)){$CIF = $SprdSht->getCellValue("H5");}

					$CIF = trim(str_replace("CIF:", "", $CIF));


					//Obtiene encabezados y datos
					for ($x = 13; $x < (count($array)); $x++){$array_tot[] = $array[$x];}
					unset($array);

					$array_assoc = array_to_assoc($array_tot);
					unset ($array_tot);

					$encabezado = array_fill_keys($array_header, '');
					$encabezado['Linea'] = '1';

					//Código asociado al CIF
					switch ($CIF){
						case 'A28164754':	//DIA
							$encabezado['ID CIF'] 	= '165';
							$encabezado['CECO'] 	= '99909999';
							break;
						case 'A80782519':	//	TWINS
							$encabezado['ID CIF'] 	= '170';
							$encabezado['CECO'] 	= '99909999P';
							break;
						case 'A43227628':	//BBD
						case 'P0410200J':	//BBD
							$encabezado['ID CIF'] 	= '190';
							$encabezado['CECO'] 	= '99909999S';
							break;
						case 'A80223258':	//GEA
							$encabezado['ID CIF'] 	= '200';
							$encabezado['CECO'] 	= '99909999A';
							break;
					}

					$encabezado['SA'] = 'SA';
					$encabezado['Emisión'] = $emision;
					$encabezado['Fecha contable'] = $fecha_contable;
					$encabezado['EUR'] = 'EUR';
					$encabezado['Fra'] = $fra;
					$encabezado['Emisor'] = 'ENDESA';
					$encabezado['Id. Cuenta'] = '4170001';
					$encabezado['S'] = 'S';

					//BI total
					$BI = $array_assoc[count($array_assoc)-1]['BASE IMPONIBLE'];
					settype($BI, "float");
					switch (true){
						case ($BI<0)	: $encabezado['Haber total negativo'] = abs($BI); break;
						case ($BI>=0)	: $encabezado['Haber total positivo'] = abs($BI); break;
					}

					$array_final[$fra] = $encabezado;
					unset($encabezado, $CIF, $BI, $emision, $fecha_contable);

					//Facturas
					for ($x=0; $x<count($array_assoc)-1; $x++){
						$factura = array_fill_keys($array_header, '');

						$factura['Id. Cuenta'] 		= '6282000';
						$factura['Linea'] 			= '2';
						$factura['S'] 				= 'S';
						$factura['CUPS'] 			= substr(trim($array_assoc[$x]['CUPS 22']), 0, 20);
						$factura['Activa total'] 	= $array_assoc[$x]['ACT.TOT (kWh)'];

						//Desde-Hasta
						$desde = date_format(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($array_assoc[$x]['F.DESDE']), 'd/m/y');
						$hasta = date_format(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($array_assoc[$x]['F.HASTA']), 'd/m/y');

						$factura['Desde-Hasta'] = "$desde-$hasta";
						unset($desde, $hasta);

						//BI
						$BI = $array_assoc[$x]['BASE IMPONIBLE'];
						settype($BI, "float");
						switch (true){
							case ($BI<0)	: $factura['Haber total positivo'] 	= abs($BI); break;
							case ($BI>=0)	: $factura['Haber total negativo'] 	= abs($BI); break;
						}
						unset($BI);

						//Derechos
						if (array_key_exists('Depósito de Garantía Distribuidora ', $array_assoc[$x])){
							if ($array_assoc[$x]['Depósito de Garantía Distribuidora '] != 0){

								$derechos = array_fill_keys($array_header, '');

								$derechos['Id. Cuenta'] 	= '6282000';
								$derechos['Linea'] 			= '2';
								$derechos['Desde-Hasta'] 	= "Derechos";
								$derechos['S'] 				= 'S';
								$derechos['CUPS'] 			= $factura['CUPS'];
								$derechos['Activa total'] 	= $factura['Activa total'];
								$derechos['CECO'] 			= $factura['CECO'];

								$importe_derechos = $array_assoc[$x]['Depósito de Garantía Distribuidora '];
								settype($importe_derechos, "float");

								//Resta los derechos de la fra
								switch (true){
									case (!empty($factura['Haber total positivo'])): //Si la BI de la fra es negativo

										$factura['Haber total positivo'] -= abs($importe_derechos);
										if ($factura['Haber total positivo']<0){
											$factura['Haber total negativo'] = abs($factura['Haber total positivo']);
											$factura['Haber total positivo'] = "";
										}
										break;
                                        
									case (!empty($factura['Haber total negativo'])): //Si la BI de la fra es positivo

										$factura['Haber total negativo'] -= abs($importe_derechos);
										if ($factura['Haber total negativo']<0){
											$factura['Haber total positivo'] = abs($factura['Haber total negativo']);
											$factura['Haber total negativo'] = "";
										}
										break;
								}
                                
								//Importe derechos
								switch (true){
									case ($importe_derechos<0): $derechos['Haber total positivo'] = abs($importe_derechos); break;
									case ($importe_derechos>0): $derechos['Haber total negativo'] = abs($importe_derechos); break;
								}
								unset($importe_derechos);
                                
								$array_final[] = $derechos;
								unset($derechos);
							} //Si derechos != 0
						} //Si hay derechos


						//Depósito de garantía
						for ($y=1; $y<=4; $y++){
							$clave = "CONCEPTO $y";
							$valor = "IMPORTE CONCEPTO $y";

							if (array_key_exists($clave, $array_assoc[$x])){
								switch (substr($array_assoc[$x][$clave], 0, 4)){
									case ""		: break;
									case "FIAD"	:	//Depósito de garantía

										$deposito = array_fill_keys($array_header, '');

										$deposito['Id. Cuenta'] 	= '2602000';
										$deposito['Linea'] 			= '2';
										$deposito['S'] 				= 'S';
										$deposito['Desde-Hasta'] 	= "Depósito";
										$deposito['CUPS'] 			= $factura['CUPS'];
										$deposito['Activa total'] 	= $factura['Activa total'];
										$deposito['CECO'] 			= $factura['CECO'];

										$importe_deposito = $array_assoc[$x][$valor];
										settype($importe_deposito, "float");
                                        
                                        
										//Añade el deposito al encabezado
										switch (true){
											case (!empty($array_final[$fra]['Haber total positivo'])): //Si la BI del encabezado es positiva
                                                
												$array_final[$fra]['Haber total positivo'] += abs($importe_deposito);
												if ($array_final[$fra]['Haber total positivo']<0){
													$array_final[$fra]['Haber total negativo'] = abs($array_final[$fra]['Haber total positivo']);
													$array_final[$fra]['Haber total positivo'] = "";
												}
												break;
                                                
											case (!empty($array_final[$fra]['Haber total negativo'])): //Si la BI del encabezado es negativa

												$array_final[$fra]['Haber total negativo'] += abs($importe_deposito);
												if ($array_final[$fra]['Haber total negativo']<0){
													$array_final[$fra]['Haber total positivo'] = abs($array_final[$fra]['Haber total negativo']);
													$array_final[$fra]['Haber total negativo'] = "";
												}
												break;
										}
                                        
										//Importe deposito de garantía
										switch (true){
											case ($importe_deposito<0): $deposito['Haber total positivo'] = abs($importe_deposito); break;
											case ($importe_deposito>0): $deposito['Haber total negativo'] = abs($importe_deposito); break;
										}
										unset($importe_deposito);

										$array_final[] = $deposito;
										unset($deposito);

										break(2);

									default		: break;
								} //Casos de otros conceptos
							} //Si existe la columna de los otros conceptos
						} //De 1 a 4 (otros conceptos)
                        
						$array_final[] = $factura;
						unset($factura);
					} //Para cada factura
                    unset($fra);
				} //Para cada hoja
			} //Si uploaded
			
			unset($SprdSht);
			
			//CECOs
			$conn 	= connect_server('mainsip', 'develop');

			$strSQL = "SELECT
							a.CUPS,
							a.codigo_oficina
						FROM clientes a
						INNER JOIN(
								SELECT
										CUPS,
										MAX(fecha_alta) maxalta
								FROM clientes
								WHERE (Grupo='GRUPO DIA') 
								GROUP BY CUPS) b
						ON
							a.CUPS=b.CUPS
							AND a.fecha_alta=b.maxalta
						WHERE (a.Grupo='GRUPO DIA')";

			$query 	= mysqli_query($conn, $strSQL);

			//Crea array assoc CUPS=>CECO
			while ($row = mysqli_fetch_assoc($query)){$CECO[$row['CUPS']] = $row['codigo_oficina'];}
			unset($query);

			//Asigna los valores
			foreach($array_final as $num_row=>$row){
				if (!empty($row['CUPS'])){
					if (array_key_exists($row['CUPS'], $CECO)){$array_final[$num_row]['CECO'] = $CECO[$row['CUPS']];}
				}
			}
			unset($CECO);


			//Guarda el fichero
			$filename = str_replace(".xlsx", "", $_FILES['fichero']['name'][$filenum]);
			++$filenum;
			$files[] = $filename.".xlsx";
			
			$SprdSht = new SprdSht;
			$SprdSht->nuevo();
			$SprdSht->putArray($array_final, true);
			$SprdSht->save($filename);
			unset($SprdSht, $array_final);
		} //Para cada ficero

		merge_and_dwd_zip("CSV BT GRUPO DIA.zip", $files);
		
		break;
		
	case "csv_mt_dia":
		
		if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: elaboracion_ff.php");}
		
		$array_header = array(
			'Linea',
			'ID CIF',
			'SA',
			'Emisión',
			'Fecha contable',
			'EUR',
			'Vacio 1',
			'Fra',
			'Emisor',
			'S',
			'Id. Cuenta',
			'Vacio 2',
			'Haber total negativo',
			'Haber total positivo',
			'Vacio 3',
			'Vacio 4',
			'Vacio 5',
			'Vacio 6',
			'Vacio 7',
			'Vacio 8',
			'CECO',
			'Vacio 9',
			'Desde-Hasta',
			'CUPS',
			'Vacio 10',
			'Vacio 11',
			'Vacio 12',
			'Vacio 13',
			'Vacio 14',
			'Vacio 15',
			'Vacio 16',
			'Vacio 17',
			'Vacio 18',
			'Vacio 19',
			'Vacio 20',
			'Vacio 21',
			'Vacio 22',
			'Vacio 23',
			'Activa total'
		);


		$fras = array();
		$fras_count = 0;
		$filenum = 0;
		foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){

				$datos = array();
				$fopen = fopen($file, 'r');
				while (!feof($fopen)) {
					$line=fgets($fopen);
					$line=trim($line);
					$datos[]=explode(';', $line);
				}
				unset ($fopen);

				//Encabezado
				$encabezado = array_fill_keys($array_header, '');

				$emision = date_create_from_format('Ymd', $datos[0][1]);
				$fecha_contable = date_format($emision, 'tmY');
				$emision = date_format($emision, 'dmY');

				$encabezado['Linea'] 			= '1';
				$encabezado['SA'] 				= 'SA';
				$encabezado['Emisión'] 			= $emision;
				$encabezado['Fecha contable'] 	= $fecha_contable;
				$encabezado['EUR'] 				= 'EUR';
				$encabezado['Emisor'] 			= 'ENDESA';
				$encabezado['Id. Cuenta'] 		= '4170001';
				$encabezado['S'] 				= 'S';
				
				$d = (str_replace(" ", "", $datos[2][6]) == "") ? 1 : 0;
				$fra = trim($datos[2][13+$d]);
				$encabezado['Fra'] = $fra;

				$CIF = trim($datos[2][4+$d]);
				//Código asociado al CIF
				switch ($CIF){
						case 'A28164754':	//DIA
							$encabezado['ID CIF'] 	= '165';
							$encabezado['CECO'] 	= '99909999';
							break;
						case 'A80782519':	//	TWINS
							$encabezado['ID CIF'] 	= '170';
							$encabezado['CECO'] 	= '99909999P';
							break;
						case 'A43227628':	//BBD
							$encabezado['ID CIF'] 	= '190';
							$encabezado['CECO'] 	= '99909999S';
							break;
						case 'A80223258':	//GEA
							$encabezado['ID CIF'] 	= '200';
							$encabezado['CECO'] 	= '99909999A';
							break;
					}

				$array_final[$fra] = $encabezado;

				$BI_tot = 0;
				for($x=0; $x<count($datos); $x++){
					if (!isset($datos[$x][0])){continue;}
					switch ($datos[$x][0]){
						/*
						case 25:

							//Si está desplazado de una celda
							if (str_replace(" ", "", $datos[$x][6])=="" || str_replace(" ", "", $datos[$x][6])=="CI"){$d = 1;} else {$d = 0;}

							settype($datos[$x][24+$d], "float");
							$importe_otros = $datos[$x][24+$d]/10000;

							$BI_tot += $importe_otros;
							$BI_fra += $importe_otros;

							switch (true){
								case ($BI_fra<0)	: $factura['Haber total positivo'] 	= abs($BI_fra); break;
								case ($BI_fra>=0)	: $factura['Haber total negativo'] 	= abs($BI_fra); break;
							}
							unset($BI_fra);

							$array_final[] = $factura;
							unset($factura);

							unset($importe_otros);

							break;
						*/
						case 10:

							//Añade linea a arr_final
							if (isset($factura)){
								switch (true){
									case ($BI_fra<0)	: $factura['Haber total positivo'] 	= abs($BI_fra); break;
									case ($BI_fra>=0)	: $factura['Haber total negativo'] 	= abs($BI_fra); break;
								}
								unset($BI_fra);

								$array_final[] = $factura;
								unset($factura);
							}

							//Si está desplazado de una celda
							$d = (str_replace(" ", "", $datos[$x][6]) == "") ? 1 : 0;

							$factura = array_fill_keys($array_header, '');

							$factura['Id. Cuenta'] 		= '6282000';
							$factura['Linea'] 			= '2';
							$factura['S'] 				= 'S';
							$factura['CUPS'] 			= substr(trim($datos[$x][1]), 0, 20);

							//Activa
							$act_tot = 0;
							settype($datos[$x+1][12], "float");
							settype($datos[$x+4][12], "float");
							settype($datos[$x+7][12], "float");
							settype($datos[$x+10][12], "float");
							settype($datos[$x+13][12], "float");
							settype($datos[$x+16][12], "float");
							$act_tot 	= $datos[$x+1][12]/100;
							$act_tot 	+= $datos[$x+4][12]/100;
							$act_tot 	+= $datos[$x+7][12]/100;
							$act_tot 	+= $datos[$x+10][12]/100;
							$act_tot 	+= $datos[$x+13][12]/100;
							$act_tot 	+= $datos[$x+16][12]/100;
							$factura['Activa total'] = $act_tot;

							//Desde-hasta
							$desde = date_format(date_create_from_format('Ymd', $datos[$x][23+$d]), 'd/m/y');
							$hasta = date_format(date_create_from_format('Ymd', $datos[$x][24+$d]), 'd/m/y');
							$factura['Desde-Hasta'] = "$desde-$hasta";
							unset($desde, $hasta);

							//BI
							$BI_fra = $datos[$x][142+$d]/10000;
							settype($BI_fra, "float");
							$BI_tot += $BI_fra;

							break;
					} //Switch 25/10
				} //Para cada linea

				//Añade linea a arr_final
				if (isset($factura)){
					switch (true){
						case ($BI_fra<0)	: $factura['Haber total positivo'] 	= abs($BI_fra); break;
						case ($BI_fra>=0)	: $factura['Haber total negativo'] 	= abs($BI_fra); break;
					}
					unset($BI_fra);

					$array_final[] = $factura;
					unset($factura);
				}

				
				if ($BI_tot<0)	{$array_final[$fra]['Haber total negativo'] = abs($BI_tot);}
				else 			{$array_final[$fra]['Haber total positivo'] = abs($BI_tot);}
				unset($fra);
			} //Si uploaded
		}//Para cada fichero


		//CECOs
		$Conn = new Conn('mainsip', 'develop');

		$strSQL = "SELECT
						a.CUPS,
						a.codigo_oficina
					FROM clientes a
					INNER JOIN(
							SELECT
									CUPS,
									MAX(fecha_alta) maxalta
							FROM clientes
							WHERE (Grupo='GRUPO DIA') 
							GROUP BY CUPS) b
					ON
						a.CUPS=b.CUPS
						AND a.fecha_alta=b.maxalta
					WHERE (a.Grupo='GRUPO DIA')";

		$query = $Conn->Query($strSQL);

		//Crea array assoc CUPS=>CECO
		while ($row = mysqli_fetch_assoc($query)){$CECO[$row['CUPS']] = $row['codigo_oficina'];}
		unset($Conn);

		//Asigna los valores
		foreach($array_final as $num_row=>$row){
			if (!empty($row['CUPS'])){
				if (array_key_exists($row['CUPS'], $CECO)){$array_final[$num_row]['CECO'] = $CECO[$row['CUPS']];}
			}
		}
		unset($CECO);
		
		$SprdSht = new SprdSht;
		$SprdSht->nuevo();
		$SprdSht->putArray($array_final, true);
		$SprdSht->directDownload("CSV MT GRUPO DIA");
		unset($SprdSht);
		
		break;
		
	case "inditex_endesa_bt":
	case "inditex_endesa_mt":
		
		if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: elab_clientes.php");}
		
		$array_header = array(
			'CIF_PROVEEDOR',
			'SOCIEDAD_GRUPO',
			'CIF_SOCIEDAD',
			'NUM_FACTURA',
			'FECHA_FACTURA',
			'CONTRATO',
			'CUPS',
			'DIRECCION',
			'FDESDE',
			'FHASTA',
			'BASE_IMPONIBLE',
			'TIPO_IMPOSITIVO',
			'PORCENTAJE_IMPTO',
			'TOTAL_FACTURA',
            'CODIGO_OFICINA',
            'FECHA_DESDE_PS'
		);

		
		$array_final = array();
		$filenum = 0;
		
		switch ($_POST['action']){
			
			//ENDESA BT
			case "inditex_endesa_bt":
				$filename = "Elaboración Inditex BT";
				foreach($_FILES['fichero']['tmp_name'] as $file){
					if (is_uploaded_file($file)){

						$SprdSht = new SprdSht;
						$SprdSht->load($file, true);

						//Para cada hoja
						for ($i = 0; $i < ($SprdSht->getShtCnt()); $i++){

							$array_tot = array();

							$SprdSht->getSheet($i);
							$array 	= $SprdSht->getArray();

							//CIF CLIENTE
							$CIF = $SprdSht->getCellValue("E5");
							if (empty ($CIF)){$CIF = $SprdSht->getCellValue("F5");}
							if (empty ($CIF)){$CIF = $SprdSht->getCellValue("G5");}
							if (empty ($CIF)){$CIF = $SprdSht->getCellValue("H5");}

							$CIF = trim(str_replace("CIF:", "", $CIF));

							if ($CIF=='A15121031' || $CIF=='A15234065'){
								unset($array, $CIF);
								continue;
							}

							//Fra
							$fra = trim($SprdSht->getCellValue("B12"));

							//Emisión
							$emision = $SprdSht->getCellValue("A9");
							if (empty ($emision)){$emision = $SprdSht->getCellValue("B9");}

							//CIF PROVEEDOR
							$CIF_proveedor = $SprdSht->getCellValue("E2");
							if (empty ($CIF_proveedor)){$CIF_proveedor = $SprdSht->getCellValue("F2");}
							if (empty ($CIF_proveedor)){$CIF_proveedor = $SprdSht->getCellValue("G2");}
							if (empty ($CIF_proveedor)){$CIF_proveedor = $SprdSht->getCellValue("H2");}

							$CIF_proveedor = trim(str_replace("CIF:", "", $CIF_proveedor));

							//Razón social
							$cliente = trim(str_replace("CLIENTE:", "", $SprdSht->getCellValue("A5")));

							//Obtiene encabezados y datos
							for ($x = 13; $x < (count($array)-1); $x++){$array_tot[] = $array[$x];}
							unset($array);

							$array_assoc = array_to_assoc($array_tot);
							unset ($array_tot);

							foreach($array_assoc as $row){
                                if (empty(trim($row['CUPS 22']))){continue;}
								$linea = array_fill_keys($array_header, '');

								$linea['CIF_PROVEEDOR'] 		= $CIF_proveedor;
								$linea['SOCIEDAD_GRUPO'] 		= $cliente;
								$linea['CIF_SOCIEDAD'] 			= $CIF;
								$linea['NUM_FACTURA'] 			= $fra;
								$linea['FECHA_FACTURA'] 		= $emision;
								$linea['CONTRATO'] 				= $row['CÓDIGO DE CONTRATO PS'];
								$linea['CUPS'] 					= trim($row['CUPS 22']);
								$linea['DIRECCION'] 			= $row['DIREC'];
								$linea['FDESDE'] 				= $row['F.DESDE'];
								$linea['FHASTA'] 				= $row['F.HASTA'];
								$linea['BASE_IMPONIBLE'] 		= $row['BASE IMPONIBLE'];
								$linea['TOTAL_FACTURA'] 		= $row['TOTAL FACTURA (€)'];
                                
								if (substr($fra, 0, 1)!='C')	{
									$linea['TIPO_IMPOSITIVO'] 	= "IVA";
									$linea['PORCENTAJE_IMPTO'] 	= 21;
								} else {
									$linea['TIPO_IMPOSITIVO'] 	= "IGIC";
									$linea['PORCENTAJE_IMPTO'] 	= 6.5;
								}

								$array_final[] = $linea;

							} //Para cada linea
							unset($array_assoc, $CIF_proveedor, $cliente, $CIF, $emision, $fra);
						} //Para cada hoja
						unset($SprdSht);
					} //Si uploaded
				} //Para cada ficero
				break;
			
			//ENDESA MT
			case "inditex_endesa_mt":
				$filename = "Elaboración Inditex MT";
				foreach($_FILES['fichero']['tmp_name'] as $file){
					if (is_uploaded_file($file)){

						$SprdSht = new SprdSht;
						$SprdSht->load($file, true);

						//Para cada hoja
						for ($i = 1; $i < ($SprdSht->getShtCnt()); $i++){

							$array_tot = array();

							$SprdSht->getSheet($i);
							$array_assoc 	= $SprdSht->getArray(true);


							foreach($array_assoc as $num_row=>$row){

								//Si la linea está vacia o si el CIF es el de TEMPE o GOA INVEST
								if (substr(trim($row['CUPS EXTERNO']), 0, 20) == "" || $row['NIF']=='A15234065' || $row['NIF']=='A15121031'){continue;}

								$linea = array_fill_keys($array_header, '');

								$linea['CIF_PROVEEDOR'] 		= "A81948077";
								$linea['SOCIEDAD_GRUPO'] 		= $row['RAZÓN SOCIAL'];
								$linea['CIF_SOCIEDAD'] 			= $row['NIF'];
								$linea['NUM_FACTURA'] 			= trim($row['FACTURA']);
								$linea['FECHA_FACTURA'] 		= $row['FECHA FACTURA'];
								$linea['CONTRATO'] 				= $row['CONTRATO'];
								$linea['CUPS'] 					= trim($row['CUPS EXTERNO']);
								$linea['DIRECCION'] 			= $row['DIRECCIÓN']." ".$row['POBLACIÓN'];
								$linea['FDESDE'] 				= $row['FECHA DESDE'];
								$linea['FHASTA'] 				= $row['FECHA HASTA'];
								$linea['BASE_IMPONIBLE'] 		= $row['BASE IMPONIBLE'];
								$linea['TOTAL_FACTURA'] 		= $row['IMPORTE FACTURA IMPUESTOS INCLUIDOS'];

								if (substr($linea['NUM_FACTURA'], 0, 1)!='C')	{
									$linea['TIPO_IMPOSITIVO'] 	= "IVA";
									$linea['PORCENTAJE_IMPTO'] 	= 21;
								} else {
									$linea['TIPO_IMPOSITIVO'] 	= "IGIC";
									$linea['PORCENTAJE_IMPTO'] 	= 6.5;
								}

								$array_final[] = $linea;

							} //Para cada linea

							unset($array_assoc, $CIF_proveedor, $cliente, $CIF, $emision, $fra);
						} //Para cada hoja
						unset($SprdSht);
					} //Si uploaded
				} //Para cada ficero
				break;
		}
		
		unset($linea);

		$Conn = new Conn('mainsip', 'develop');
		
        //Saca datos del PS y los cruza con el FF para comprobar que no haya diferencias
		$query = $Conn->Query("SELECT
                                    a.estado,
                                    a.CUPS,
                                    a.numero_contrato
                                FROM clientes a
                                INNER JOIN(
                                    SELECT 	CUPS,
                                            MAX(fecha_alta) maxalta
                                    FROM clientes
                                    WHERE (Grupo='INDITEX')
                                    GROUP BY CUPS) b
                                ON a.CUPS=b.CUPS
                                AND a.fecha_alta=b.maxalta
                                WHERE (a.Grupo='INDITEX')");

		$header_verificaciones = array(
			'CUPS',
			'CONTRATO_ENERTRADE',
			'CONTRATO_FF',
			'ESTADO',
			'OBSERVACIONES'
		);
		$verificaciones = array();

		while ($row = mysqli_fetch_assoc($query)){$CUPS[$row['CUPS']] = $row;} //Para cada linea de la BBDD

		foreach ($array_final as $row){
			switch (true){
				case (in_array(substr($row['CUPS'], 0, 20), array_column($verificaciones, 'CUPS'))):
					break;
				case (!in_array(substr($row['CUPS'], 0, 20), array_column($CUPS, 'CUPS'))):
					$linea = array_fill_keys($header_verificaciones, '');

					$linea['CUPS'] 			= substr($row['CUPS'], 0, 20);
					$linea['OBSERVACIONES'] = "Este CUPS no existe en la BBDD";
					break;

				case (in_array(substr($row['CUPS'], 0, 20), array_column($CUPS, 'CUPS')) && !in_array($row['CONTRATO'], array_column($CUPS, 'numero_contrato'))):
					$linea = array_fill_keys($header_verificaciones, '');

					$linea['CUPS'] 					= substr($row['CUPS'], 0, 20);
					$linea['CONTRATO_ENERTRADE'] 	= $CUPS[substr($row['CUPS'], 0, 20)]['numero_contrato'];
					$linea['CONTRATO_FF'] 			= $row['CONTRATO'];
					$linea['ESTADO'] 				= $CUPS[substr($row['CUPS'], 0, 20)]['estado'];
					$linea['OBSERVACIONES'] 		= "Numero de contrato distinto";
					break;
			}
            
			if (isset($linea)){
				$verificaciones[] = $linea;
				unset($linea);
			}
		}
		unset($CUPS);
		
        //Añade código oficina y fecha desde de la linea del PS relativa a cada factura
        $date = new DateClass;
        foreach ($array_final as $num_row=>$row){
            $linea          = array();
            $linea['CUPS']  = substr($row['CUPS'], 0, 20);
            $date->fromXl($row['FDESDE']);
            $linea['FECHA'] = $date->format('Y-m-d');
            $cups_fechas[]  = $linea;
            unset($linea);
        }
        
        $Conn2 = new Conn('local', 'enertrade');
        
        $Conn2->Query("CREATE TEMPORARY TABLE temp_datos_inditex (
                          CUPS text DEFAULT NULL,
						  FECHA date NOT NULL
						)");
        
        $cups_fechas = implode_values($cups_fechas);
        $Conn2->Query("INSERT INTO temp_datos_inditex (CUPS, FECHA) VALUES $cups_fechas");
        unset($cups_fechas);
        
        $Conn2->Query("CREATE TEMPORARY TABLE temp_ps_inditex (
                          CUPS text DEFAULT NULL,
                          codigo_oficina text DEFAULT NULL,
						  fecha_inicio date NOT NULL,
						  fecha_fin date NOT NULL
						)");
        
        $ps_inditex = $Conn->getArray("SELECT
                                            CUPS,
                                            codigo_oficina,
                                            fecha_inicio,
                                            fecha_fin
                                        FROM clientes
                                        WHERE Grupo='INDITEX'");
        
        $ps_inditex = implode_values($ps_inditex);
        $Conn2->Query("INSERT INTO temp_ps_inditex (CUPS, codigo_oficina, fecha_inicio, fecha_fin) VALUES $ps_inditex");
        unset($ps_inditex);
        
        $ultimos_datos = $Conn2->getArray("SELECT
                                            CONCAT(a.CUPS, a.FECHA) id,
                                            a.CUPS,
                                            a.FECHA,
                                            b.codigo_oficina,
                                            b.fecha_inicio
                                        FROM temp_datos_inditex a
                                        INNER JOIN (
                                            SELECT
                                                CUPS,
                                                codigo_oficina,
                                                fecha_inicio,
                                                fecha_fin
                                            FROM temp_ps_inditex
                                        ) b
                                        ON a.CUPS=b.CUPS
                                        AND a.FECHA BETWEEN b.fecha_inicio AND b.fecha_fin
                                        GROUP BY a.CUPS, a.FECHA
                                            ", true);
        
        $Array = new ArrayClass($ultimos_datos);
        $ultimos_datos = $Array->assocFromColumn('id', true);
        unset($Array);
        
        foreach ($array_final as $num_row=>$row){
            $date = new DateClass;
            $date->fromXl($row['FDESDE']);
            $cups = substr($row['CUPS'], 0, 20);
            $fdesde = $date->format('Y-m-d');
            $array_final[$num_row]['FECHA_DESDE_PS'] = $date->fromToFormat($ultimos_datos[$cups.$fdesde]['fecha_inicio']);
            $array_final[$num_row]['CODIGO_OFICINA'] = $ultimos_datos[$cups.$fdesde]['codigo_oficina'];
            unset($fdesde, $date);
        }
        
        unset($Conn, $Conn2, $ultimos_datos);
        
		$SprdSht = new SprdSht;
		$SprdSht->nuevo();
		$SprdSht->putArray($array_final, true);
		
		$i=2;
		foreach ($array_final as $num_row=>$row){
			$SprdSht->setFormatAsDate("E$i");
			$SprdSht->setFormatAsDate("I$i");
			$SprdSht->setFormatAsDate("J$i");
			$SprdSht->setValueAsText("F$i", $row['CONTRATO']);
			++$i;
		}
		
		if (!empty($verificaciones)){
			$SprdSht->addSheet('Verificaciones');
			$SprdSht->putArray($verificaciones, true);
		}
		
		$SprdSht->directDownload($filename);
		unset($SprdSht);
		
		break;
		
	case "informe_tres_anos":
		
        set_time_limit(0);
        
		$cli = $_POST['cli'];
		$x = date('Y');
        $CalculosSimples = new CalculosSimples;
        $id_cli = $CalculosSimples->getIdCliente($cli);
        unset($CalculosSimples);
        
		$Conn = new Conn('mainsip', 'develop');
		
		$strSQL = "SELECT
						c.cif,
						a.Empresa,
						a.CUPS,
						a.dar,
						a.TARIFA,
						a.direccion,
						a.Poblacion,
						a.provincia
					FROM clientes a
					INNER JOIN(
						SELECT
							CUPS,
							MAX(fecha_alta) maxalta
						FROM clientes
						WHERE (Grupo='$cli')
						AND estado='EN VIGOR'
						GROUP BY CUPS) b
					ON a.CUPS=b.CUPS
					AND a.fecha_alta=b.maxalta
                    INNER JOIN (SELECT
                                    id,
                                    cif
                                FROM grupos
                                WHERE id_gestion_cliente=$id_cli) c
                    ON a.grupos_empresa_id=c.id
					WHERE a.Grupo='$cli'
					AND a.estado='EN VIGOR'
					ORDER BY a.CUPS, a.fecha_inicio";
		
		$PS = $Conn->getArray($strSQL);
		
		$strSQL = "SELECT
						cups,
						dias,
						mes,
						(p1+p2+p3+p4+p5+p6) CONSUMO,
						(base_imponible_iva_general + base_imponible_iva_reducido + base_imponible_iva_superreducido) base_imponible_iva
					FROM datos_notelemedidas
					WHERE grupo='$cli'
					AND estado='EN VIGOR'
					AND YEAR(mes)>($x-3)
					ORDER BY cups, mes";
		
		$consumos = $Conn->getArray($strSQL);
		unset($Conn);
		
		$datos_cons = array();
		$datos_BI = array();
		
		foreach($consumos as $num_row=>$row){
			if ($row['dias']<date_format(date_create_from_format('Y-m-d', $row['mes']), 't')){
				unset ($consumos[$num_row]);
			} else {
				$datos_cons[$row['cups']][$row['mes']] 	= $row['CONSUMO'];
				$datos_BI[$row['cups']][$row['mes']] 	= $row['base_imponible_iva'];
			}
		}
		unset($consumos);
		
		foreach ($PS as $num_row=>$row){
			
			$BI[$num_row] = $row;
			
			for ($Y=($x-2); $Y<=$x; $Y++){
				for ($m=1; $m<=12; $m++){
					$m = sprintf("%02d", $m);
					$fecha = "$Y-$m-01";
					
					$PS[$num_row][$fecha] = (array_key_exists($row['CUPS'], $datos_cons) && array_key_exists($fecha, $datos_cons[$row['CUPS']])) ?
						$datos_cons[$row['CUPS']][$fecha] : $PS[$num_row][$fecha] = "NO HAY DATO";
					
					$BI[$num_row][$fecha] = (array_key_exists($row['CUPS'], $datos_BI) && array_key_exists($fecha, $datos_BI[$row['CUPS']])) ?
						$datos_BI[$row['CUPS']][$fecha] : $PS[$num_row][$fecha] = "NO HAY DATO";
				}
			}
		}
		unset($datos_cons);
		
		$SprdSht = new SprdSht;
		$SprdSht->nuevo();
		$SprdSht->putArray($PS, true);
		$SprdSht->addSheet('BI');
		$SprdSht->putArray($BI, true);
		$SprdSht->directDownload("Informe tres años");
		unset($SprdSht);
		
		
		break;
		
	case 'deTresASeis':
		
        set_time_limit(0);
        
		$date 		= new DateClass;
		$desde 		= $date->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d');
		$hasta 		= $date->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d');
		$cli 		= $_POST['cli'];
		$Intranet 	= new Intranet;
		$id 		= $Intranet->getCustomerID($cli);
		unset($Intranet, $date);
		
		$strSQL = "
			SELECT
				c.cups,
				d.cif,
				d.Empresa,
				d.direccion,
				d.Poblacion,
				d.provincia,
				d.TARIFA,
                c.Fecha_desde,
                c.Fecha_hasta,
				c.potencia_registrada_p1 MAX_P1,
				c.potencia_registrada_p2 MAX_P2,
				c.potencia_registrada_p3 MAX_P3,
				d.P1,
				d.P2,
				d.P3
			FROM facturas c
			
			INNER JOIN (
				SELECT
					a.CUPS,
					a.cif,
					a.Empresa,
					a.direccion,
					a.Poblacion,
					a.provincia,
					a.TARIFA,
					a.P1,
					a.P2,
					a.P3
				FROM clientes a
				
				INNER JOIN(
					SELECT
						CUPS,
						MAX(fecha_alta) maxalta
					FROM clientes
					WHERE Grupo='$cli'
					AND Tarifa IN ('3.0A', '3.1A')
					AND estado='EN VIGOR'
					GROUP BY CUPS) b
					
				ON a.CUPS=b.CUPS
				AND a.fecha_alta=b.maxalta
				WHERE a.Grupo='$cli'
				AND a.Tarifa IN ('3.0A', '3.1A')
				AND a.estado='EN VIGOR'
				AND (a.P1>50 OR a.P2>50 OR a.P3>50)
				ORDER BY a.CUPS, a.fecha_inicio) d
				
			ON c.cups=d.CUPS
			WHERE c.id_cliente=$id
			AND c.fecha_factura>='$desde'
			AND c.fecha_factura<'$hasta'
            AND c.consumo_total	>= 0
            AND c.Fecha_desde	!= c.Fecha_Hasta
            AND c.Ter_potencia	!= 0
            ORDER BY c.cups, c.Fecha_desde DESC, c.Fecha_factura DESC
		";
		
		$Conn = new Conn('mainsip', 'develop');
		$datos_fras = $Conn->getArray($strSQL);
		
        //ELIMINA RECTIFICADORAS
        foreach ($datos_fras as $num_row=>$row){
            if (isset($datos_fras[$num_row-1])){
                if ($num_row &&
                    $row['Fecha_desde']==$datos_fras[$num_row-1]['Fecha_desde'] &&
                    $row['cups']==$datos_fras[$num_row-1]['cups']){
                    unset($datos_fras[$num_row]);
                }
            } elseif (isset($datos_fras[$num_row-2])){
                if ($num_row &&
                    $row['Fecha_desde']==$datos_fras[$num_row-2]['Fecha_desde'] &&
                    $row['cups']==$datos_fras[$num_row-2]['cups']){
                    unset($datos_fras[$num_row]);
                }
            }
            
        }
        
        //Selecciona la pot max por periodo
        $datos      = array();
        foreach ($datos_fras as $num_row=>$row){
            if (!array_key_exists($row['cups'], $datos)){
                $datos[$row['cups']]                = $row;
                $datos[$row['cups']]['SOBREPASA']   = 0;
            } else {
                if ($row['MAX_P1']>$datos[$row['cups']]['MAX_P1']){$datos[$row['cups']]['MAX_P1'] = $row['MAX_P1'];}
                if ($row['MAX_P2']>$datos[$row['cups']]['MAX_P2']){$datos[$row['cups']]['MAX_P2'] = $row['MAX_P2'];}
                if ($row['MAX_P3']>$datos[$row['cups']]['MAX_P3']){$datos[$row['cups']]['MAX_P3'] = $row['MAX_P3'];}
            }
            if ($row['MAX_P1']>$row['P1'] || $row['MAX_P2']>$row['P2'] || $row['MAX_P3']>$row['P3']){++$datos[$row['cups']]['SOBREPASA'];}
            unset($datos_fras[$num_row]);
        }
        
		foreach ($datos as $num_row=>$row){
			if (max($row['MAX_P1'], $row['MAX_P2'], $row['MAX_P3'])>max($row['P1'], $row['P2'], $row['P3'])){continue;}
			else																							{unset($datos[$num_row]);}
		}
		
        
		$Audax = new Audax;
		foreach ($datos as $num_row=>$row){
			
            unset($datos[$num_row]['Fecha_desde'], $datos[$num_row]['Fecha_hasta']);
            
			for ($x=1; $x<=5; $x++){
				unset($datos_cups);
				$datos_cups = $Audax->getData($row['cups']);
				if ($datos_cups){break;}
			}
			
			if ($datos_cups){
				$datos[$num_row]['Der_Extension'] 	= $datos_cups['PS']['Der_Extension'];
				$datos[$num_row]['Pot_Max_BIE'] 	= $datos_cups['PS']['Pot_Max_BIE'];
			} else {
				$datos[$num_row]['Der_Extension'] 	= 0;
				$datos[$num_row]['Pot_Max_BIE'] 	= 0;
			}
            
            $max = max($row['MAX_P1'], $row['MAX_P2'], $row['MAX_P3']);
			$comment = '';
			if ($max>$datos[$num_row]['Der_Extension']){$comment .= 'EXP.';}
			if ($max>$datos[$num_row]['Pot_Max_BIE']){$comment .= ($comment!='') ? ' - BIE' : 'BIE';}
			if ($comment==''){unset($datos[$num_row]);} else {$datos[$num_row]['Accion'] = $comment;}
		}
		
		
		if (!empty($datos)){
			$SprdSht = new SprdSht;
			$SprdSht->nuevo();
			$SprdSht->putArray($datos, true);
			$SprdSht->directDownload('Informe suministros de 3 a 6 periodos');
			unset ($datos, $SprdSht, $Conn);
		} else {
			header ('Location: elab_clientes.php');
		}
		
		
		break;
        
    case 'singulares_holdings':
        
        set_time_limit(0);
        
        $ano = $_POST['ano'];
        
        //Recupera y organiza los datos de facturación por año, tensión y tipo(singulares/holdings)
        function getSingHoldArray($ano, $tipo, $tension, $libro=false){
            
            $Conn = new Conn('local', 'enertrade');
            
            //LISTADO DE CUPS
            if (!$libro){
                $cups_id = $Conn->getArray("SELECT CUPS, ORDEN FROM singulares_holdings WHERE TIPO='$tipo' AND TENSION='$tension' AND ORDEN!=0");
                $cups = "'".implode("', '", array_column($cups_id, 'CUPS'))."'";
                
                foreach ($cups_id as $num_row=>$row){
                    $cups_id[$row['CUPS']] = $row['ORDEN'];
                    unset($cups_id[$num_row]);
                }
            } else {
                $cups_id = $Conn->getArray("SELECT CUPS, ORDEN_LIBRO FROM singulares_holdings WHERE TIPO='$tipo' AND TENSION='$tension' AND LIBRO=1");
                $cups = "'".implode("', '", array_column($cups_id, 'CUPS'))."'";

                foreach ($cups_id as $num_row=>$row){
                    $cups_id[$row['CUPS']] = $row['ORDEN_LIBRO'];
                    unset($cups_id[$num_row]);
                }
            }
            
            unset($Conn);
            
            
            //PRIMERO SACA LOS DATOS PRORRATEADOS
            $strSQL = "SELECT
                            cups,
                            mes,
                            dias,
                            total,
                            total_factura,
                            excesos_reactiva,
                            excesos_potencia
                        FROM datos_notelemedidas
                        WHERE cups IN ($cups)
                        AND grupos_cliente_id=2
                        AND mes BETWEEN '$ano-01-01' AND '$ano-12-31'";

            $Conn = new Conn('mainsip', 'develop');
            $datos = $Conn->getArray($strSQL);
            
            $date = new DateClass;
            
            $id_count = array_count_values($cups_id);
            
            foreach ($datos as $num_row=>$row){
                
                //Define el array
                if (!isset($final[$cups_id[$row['cups']]])){$final[$cups_id[$row['cups']]] = array();}
                if (!isset($final[$cups_id[$row['cups']]][$row['mes']])){
                    if ($libro){
                        $final[$cups_id[$row['cups']]][$row['mes']] = array_fill_keys(array('total', 'pot_contratada', 'max_pot', 'excesos_potencia', 'consumo_total_reactiva', 'excesos_reactiva', 'dias', 'mes', 'total_factura'), 0);
                    } else {
                        $final[$cups_id[$row['cups']]][$row['mes']] = array_fill_keys(array('total', 'total_factura', 'mes', 'dias', 'consumo_total_reactiva', 'excesos_reactiva', 'max_pot', 'excesos_potencia'), 0);
                    }
                    
                }

                //Rellena los datos del array final
                foreach ($final[$cups_id[$row['cups']]][$row['mes']] as $key=>$value){
                    switch ($key){
                            
                        case 'consumo_total_reactiva':
                        case 'max_pot':
                        case 'pot_contratada':
                            break;
                            
                        case 'mes':
                            $final[$cups_id[$row['cups']]][$row['mes']][$key] = $date->fromToFormat($row['mes']);
                            break;
                            
                        case 'dias':
                            $final[$cups_id[$row['cups']]][$row['mes']][$key] += round(($row[$key]/$date->fromToFormat($row['mes'], 'Y-m-d', 't'))/$id_count[$cups_id[$row['cups']]], 2);
                            break;

                        default:
                            $final[$cups_id[$row['cups']]][$row['mes']][$key] += $row[$key];
                            break;
                    }
                }
            }
            
            
            //LUEGO SACA LOS DATOS DE MAXIMA Y REACTIVA DESDE LAS FACTURAS
            $strSQL = "SELECT
                            a.cups,
                            a.Fecha_desde,
                            a.Fecha_hasta,
                            a.consumo_total_reactiva,
                            a.potencia_registrada_p1,
                            a.potencia_registrada_p2,
                            a.potencia_registrada_p3,
                            a.potencia_registrada_p4,
                            a.potencia_registrada_p5,
                            a.potencia_registrada_p6,
                            b.P1,
                            b.P2,
                            b.P3,
                            b.P4,
                            b.P5,
                            b.P6
                        FROM facturas a
                        INNER JOIN (
                            SELECT
                                cups,
                                fecha_inicio,
                                fecha_fin,
                                P1,
                                P2,
                                P3,
                                P4,
                                P5,
                                P6
                            FROM clientes
                            WHERE Grupo='BBVA') b
                        ON a.cups=b.CUPS
                        AND a.Fecha_desde BETWEEN b.fecha_inicio AND b.fecha_fin
                        WHERE a.cups IN ($cups)
                        AND a.id_cliente=2
                        AND a.Fecha_hasta BETWEEN '$ano-01-01' AND '". ($ano+1) ."-01-01'";

            $Conn = new Conn('mainsip', 'develop');
            $datos = $Conn->getArray($strSQL);
            
            
            foreach ($datos as $num_row=>$row){
                //Si es una factura con más dias en diciembre del año anterior
                
                $desde                      = new DateClass;
                $desde_diauno_messiguiente  = new DateClass;
                $hasta                      = new DateClass;
                $date                       = new DateClass;
                
                $desde->stringToDate($row['Fecha_desde']);
                if ($desde->format('j')<15 && $desde->format('Y')<$ano){continue;}
                
                $desde_diauno_messiguiente->stringToDate($row['Fecha_desde']);
                $hasta->stringToDate($row['Fecha_hasta']);
                
                $desde_diauno_messiguiente->diaUno();
                $desde_diauno_messiguiente->add(0,1);
                
                switch (true){
                        
                    case (($desde_diauno_messiguiente->vardate->diff($desde->vardate)->format('%a')-1) <= 0 && ($hasta->vardate->diff($desde->vardate)->format('%a')-1) > 1):
                        if ($desde->format('n')==12 && ($desde->format('Y')==$ano)){continue(2);} 
                        $mes = ($desde->format('n')==12) ? 1 : $desde->format('n')+1;
                        break;
                        
                    case (($desde_diauno_messiguiente->vardate->diff($desde->vardate)->format('%a')-1) <= 0 && ($hasta->vardate->diff($desde->vardate)->format('%a')-1) <= 1):
                    case (($desde_diauno_messiguiente->vardate->diff($desde->vardate)->format('%a')-1) >= ($hasta->vardate->diff($desde_diauno_messiguiente->vardate)->format('%a')+1)):
                        if ($desde->format('Y')<$ano){continue(2);}
                        $mes = $desde->format('n');
                        break;
                        
                    default:
                        if ($desde->format('n')==12 && ($desde->format('Y')==$ano)){continue(2);} 
                        $mes = ($desde->format('n')==12) ? 1 : $desde->format('n')+1;
                        break;
                }
                unset($desde_diauno_messiguiente);
                
                $mes = sprintf("%02d", $mes);
                
                //Máxima registrada
                $max = max($row['potencia_registrada_p1'], $row['potencia_registrada_p2'], $row['potencia_registrada_p3'], $row['potencia_registrada_p4'], $row['potencia_registrada_p5'], $row['potencia_registrada_p6']);
                $max_contratada = max($row['P1'], $row['P1'], $row['P3'], $row['P4'], $row['P5'], $row['P6']);
                
                if ($tension=='BT'){
                    for ($x=1; $x<=6; $x++){
                        if ($row["potencia_registrada_p$x"]>($row["P$x"]*1.05)){$comment = 'Excesos de potencia incluidos en el término de potencia'; break;}
                    }
                }

                //Define el array
                if (!isset($final[$cups_id[$row['cups']]])){$final[$cups_id[$row['cups']]] = array();}
                if (!isset($final[$cups_id[$row['cups']]]["$ano-$mes-01"])){
                    if ($libro){
                        $final[$cups_id[$row['cups']]]["$ano-$mes-01"] = array_fill_keys(array('total', 'pot_contratada', 'max_pot', 'excesos_potencia', 'consumo_total_reactiva', 'excesos_reactiva', 'dias', 'mes', 'total_factura'), 0);
                    } else {
                        $final[$cups_id[$row['cups']]]["$ano-$mes-01"] = array_fill_keys(array('total', 'total_factura', 'mes', 'dias', 'consumo_total_reactiva', 'excesos_reactiva', 'max_pot', 'excesos_potencia'), 0);
                    }
                    
                }

                //Rellena los datos del array final
                foreach ($final[$cups_id[$row['cups']]]["$ano-$mes-01"] as $key=>$value){
                    switch ($key){
                        case 'max_pot':
                            if ($max>$value){$final[$cups_id[$row['cups']]]["$ano-$mes-01"][$key] = $max;}
                            break;
                            
                        case 'consumo_total_reactiva':
                            $final[$cups_id[$row['cups']]]["$ano-$mes-01"][$key] += $row[$key];
                            break;
                            
                        case 'pot_contratada':
                            if ($max_contratada>$value){$final[$cups_id[$row['cups']]]["$ano-$mes-01"][$key] = $max_contratada;}
                            break;
                            
                        default:
                            break;
                    }
                    if (isset($comment)){
                        $final[$cups_id[$row['cups']]]["$ano-$mes-01"]['comment'] = $comment;
                        unset($comment);
                    }
                }
                unset($hasta, $emision, $max, $mes, $date);
            }
            
            
            unset($date);
            
            return $final;
        }

        //EMPIEZA A REDACTAR
        $tipos = array('SINGULARES', 'HOLDING');
        $tensiones = array('BT', 'MT', 'BLUE', '14001');
        
        //Columnas de los ficheros correspondientes a cada mes
        $monthToColumn = array(
            1=>'D',
            2=>'E',
            3=>'F',
            4=>'G',
            5=>'H',
            6=>'I',
            7=>'J',
            8=>'K',
            9=>'L',
            10=>'M',
            11=>'N',
            12=>'O'
        );
        
        //Recupera los datos de facturación
        foreach ($tipos as $tipo){
            foreach ($tensiones as $tension){
                $nombre_var = $tipo."_".$tension;
                $$nombre_var = getSingHoldArray($ano, $tipo, $tension);
            }
        }
        
        $timestamp = getMicrotimeString();
        
        //Redacta los ficheros
        foreach ($tipos as $tipo){
            $SprdSht = new SprdSht;
            $SprdSht->load($_SERVER['DOCUMENT_ROOT']."/Enertrade/pages/Operaciones/plantillas/$tipo.xlsx", false);
            
            //Pone los datos en cada hoja (BT/MT)
            foreach ($tensiones as $tension){
                
                
                $nombre_var = $tipo."_".$tension;
                
                switch ($nombre_var){
                    case 'HOLDING_BLUE':
                    case 'HOLDING_14001':
                        continue(2);
                }
                
                $SprdSht->getSheet($tension);
                
                $date = new DateClass;
                
                //Sigue el orden de los CUPS que viene en la intranet interna
                for ($x=1; $x<=max(array_keys($$nombre_var)); ++$x){

                    $row = 5 + ($x-1)*9;
                    if (!isset($$nombre_var[$x])){continue;}

                    foreach ($$nombre_var[$x] as $mes=>$valores){

                        if (!isset($valores)){continue;}
                        if (isset($valores['comment'])){
                            $comment = $valores['comment'];
                            unset($valores['comment']);
                        }
                        
                        $mes = $date->fromToFormat($mes, 'Y-m-d', 'n');
                        
                        $SprdSht->putArray(array_chunk($valores, 1), false, $monthToColumn[$mes].$row);
                        if (isset($comment)){
                            $SprdSht->putComment($monthToColumn[$mes].($row+7), $comment);
                            unset($comment);
                        }
                    }
                }
                unset($$nombre_var);
            }

            $SprdSht->save($tipo."$timestamp.xlsx");
            unset($SprdSht);

            $files[] = $tipo."$timestamp.xlsx";
        }
        
        $tensiones = array('BT', 'MT');
        
        //LIBRO HOLDING
        foreach ($tensiones as $tension){
            $nombre_var     = "LIBRO_HOLDING_".$tension;
            $$nombre_var    = getSingHoldArray($ano, $tipo, $tension, true);
        }
        
        $SprdSht = new SprdSht;
        $SprdSht->load($_SERVER['DOCUMENT_ROOT']."/Enertrade/pages/Operaciones/plantillas/LIBRO_HOLDING.xlsx", false);

        //Pone los datos en cada hoja (BT/MT)
        foreach ($tensiones as $tension){

            $nombre_var = "LIBRO_HOLDING_".$tension;

            $SprdSht->getSheet($tension);

            $date = new DateClass;

            //Sigue el orden de los CUPS que viene en la intranet interna
            for ($x=1; $x<=max(array_keys($$nombre_var)); ++$x){

                $row = 4 + ($x-1)*13;
                if (!isset($$nombre_var[$x])){continue;}
                
                foreach ($$nombre_var[$x] as $mes=>$valores){
                    
                    if (!isset($valores)){continue;}
                    if (isset($valores['comment'])){
                        $comment = $valores['comment'];
                        unset($valores['comment']);
                    }

                    $mes = $date->fromToFormat($mes, 'Y-m-d', 'n');

                    $SprdSht->putArray(array_chunk($valores, 1), false, $monthToColumn[$mes].$row);
                    if (isset($comment)){
                        $SprdSht->putComment($monthToColumn[$mes].($row+3), $comment);
                        unset($comment);
                    }
                }
            }
            unset($$nombre_var);
        }

        $SprdSht->save("LIBRO_HOLDING$timestamp.xlsx");
        unset($SprdSht);

        $files[] = "LIBRO_HOLDING$timestamp.xlsx";
        
        
        merge_and_dwd_zip('INFORMES.zip', $files, $timestamp);
        unset($files, $timestamp);
        
        break;
        
        
    case 'gasto_consumo_santander':
        
        $ano = $_POST['ano'];

        function get_gasto_consumo($tipo){
            
            $ano = $_POST['ano'];
            
            switch ($tipo){
                case 'todos':
                    $strSQL = "SELECT
                                    MONTH(mes) mes,
                                    total_factura,
                                    total,
                                    dias
                                FROM datos_notelemedidas
                                WHERE grupos_cliente_id = 131
                                AND mes BETWEEN '$ano-01-01' AND '$ano-12-31'
                                AND empresa IN ('BANCO POPULAR ESPAÑOL, S.A.', 'BANCO POPULAR PASTOR, S.A.', 'BANCO SANTANDER, S.A.')
                                AND cups NOT IN ('ES0022000008956801CJ', 'ES0022000005731619AY', 'ES0022000005731620AF', 'ES0022000005731516KH', 'ES0022000009106364ZF', 'ES0022000009106366ZD', 'ES0022000008956806CH')";
                    break;
                    
                    /*
                    160577 -> BANCO POPULAR ESPAÑOL, S.A.
                    160578 -> BANCO POPULAR PASTOR, S.A.
                    160580 -> BANCO SANTANDER, S.A.
                    */
                    
                case 'abelias':
                    $strSQL = "SELECT
                                    MONTH(mes) mes,
                                    total_factura,
                                    total
                                FROM datos_notelemedidas
                                WHERE grupos_cliente_id = 131
                                AND mes BETWEEN '$ano-01-01' AND '$ano-12-31'
                                AND cups IN ('ES0022000008956801CJ', 'ES0022000008956806CH')
                                ORDER BY mes";
                    break;
                    
                case 'josefa':
                    $strSQL = "SELECT
                                    MONTH(mes) mes,
                                    total_factura,
                                    total
                                FROM datos_notelemedidas
                                WHERE grupos_cliente_id = 131
                                AND mes BETWEEN '$ano-01-01' AND '$ano-12-31'
                                AND cups IN ('ES0022000005731619AY', 'ES0022000005731620AF')
                                ORDER BY mes";
                    break;
                    
                case 'recoletos':
                    $strSQL = "SELECT
                                    MONTH(mes) mes,
                                    total_factura,
                                    total
                                FROM datos_notelemedidas
                                WHERE grupos_cliente_id = 131
                                AND mes BETWEEN '$ano-01-01' AND '$ano-12-31'
                                AND cups = 'ES0022000005731516KH'
                                ORDER BY mes";
                    break;
                    
                case 'Luca9B':
                    $strSQL = "SELECT
                                    MONTH(mes) mes,
                                    total_factura,
                                    total
                                FROM datos_notelemedidas
                                WHERE grupos_cliente_id = 131
                                AND mes BETWEEN '$ano-01-01' AND '$ano-12-31'
                                AND cups = 'ES0022000009106366ZD'
                                ORDER BY mes";
                    break;
                    
                case 'Luca11B':
                    $strSQL = "SELECT
                                    MONTH(mes) mes,
                                    total_factura,
                                    total
                                FROM datos_notelemedidas
                                WHERE grupos_cliente_id = 131
                                AND mes BETWEEN '$ano-01-01' AND '$ano-12-31'
                                AND cups = 'ES0022000009106364ZF'
                                ORDER BY mes";
                    break;
            }
            
            $Conn = new Conn('mainsip', 'develop');
            $datos = $Conn->getArray($strSQL, true);
            unset($Conn);
            
            if (!isset($datos) || empty($datos) || !$datos){return array('CONSUMO'=>array(1=>0), 'TOTAL'=>array(1=>0), 'PORCENTAJE'=>array(1=>0));}
            
            for ($x=1; $x<=12; $x++){
                $total[$x] = 0;
                $consumo[$x] = 0;
                if ($tipo=='todos'){
                    $porcentaje[$x] = 0;
                    $cnt[$x] = 0;
                }
            }

            foreach ($datos as $num_row=>$row){
                settype($row['total_factura'], "float");
                settype($row['total'], "float");
                $total[$row['mes']] += $row['total_factura'];
                $consumo[$row['mes']] += $row['total'];
                if ($tipo=='todos'){
                    settype($row['dias'], "float");
                    $porcentaje[$row['mes']] += $row['dias'];
                    ++$cnt[$row['mes']];
                }
            }
            
            if ($tipo=='todos'){
                foreach ($porcentaje as $mes=>$value){
                    $value = $value/$cnt[$mes];

                    switch ($mes){
                        case 11:
                        case 4:
                        case 6:
                        case 9:
                            $porcentaje[$mes] = $value/30;
                            break;

                        case 2:
                            $porcentaje[$mes] = $value/28;
                            break;

                        default:
                            $porcentaje[$mes] = $value/31;
                            break;
                    }
                }
            }
            
            $final['TOTAL'] = $total;
            $final['CONSUMO'] = $consumo;
            if ($tipo=='todos'){
                $final['PORCENTAJE'] = $porcentaje;
                unset($porcentaje);
            }
            
            unset($total, $consumo);
            
            return $final;
        }
        
        $todos      = get_gasto_consumo('todos');
        $abelias    = get_gasto_consumo('abelias');
        $josefa     = get_gasto_consumo('josefa');
        $recoletos  = get_gasto_consumo('recoletos');
        $Luca9B     = get_gasto_consumo('Luca9B');
        $Luca11B    = get_gasto_consumo('Luca11B');
        
        $SprdSht = new SprdSht;
        $SprdSht->load('plantillas/Santander gasto y consumo.xlsx', false);
        
        $SprdSht->getSheet('GASTO Y CONSUMO');
        
        $SprdSht->setCellValue('D1', $ano);
        
        //GASTOS
        $SprdSht->putArray($todos['TOTAL'], false, 'C7');
        $SprdSht->putArray($abelias['TOTAL'], false, 'C8');
        $SprdSht->putArray($josefa['TOTAL'], false, 'C9');
        $SprdSht->putArray($recoletos['TOTAL'], false, 'C10');
        //CONSUMOS
        $SprdSht->putArray($todos['CONSUMO'], false, 'C16');
        $SprdSht->putArray($abelias['CONSUMO'], false, 'C17');
        $SprdSht->putArray($josefa['CONSUMO'], false, 'C18');
        $SprdSht->putArray($recoletos['CONSUMO'], false, 'C19');
        //PORCENTAJE
        $SprdSht->putArray($todos['PORCENTAJE'], false, 'C24');
        
        $SprdSht->getSheet('GASTO Y CONSUMO L. TENA');
        //GASTOS
        $SprdSht->putArray($Luca9B['TOTAL'], false, 'C7');
        $SprdSht->putArray($Luca11B['TOTAL'], false, 'C8');
        //CONSUMOS
        $SprdSht->putArray($Luca9B['CONSUMO'], false, 'C14');
        $SprdSht->putArray($Luca11B['CONSUMO'], false, 'C15');
        
        unset($todos, $abelias, $josefa, $recoletos, $Luca9B, $Luca11B);
        
        $SprdSht->directDownload("Santander gasto y consumo $ano.xlsx");
        
        break;
        
    case 'a_ok_santander':
        
        $date = new DateClass();
        $date->stringToDate(date('Y').'-01-01');
        $ano = $date->format();
        $date->subtract(1);
        $ano_menos_uno = $date->format();
        $hoy = new DateClass;
        $encabezados = array('cups', 'empresa', 'nombre', 'tarifa', 'poblacion', 'provincia');
        while ($date->vardate < $hoy->vardate){
            $encabezados[] = $date->format();
            $date->add(0,1);
        }
        
        $Conn = new Conn('mainsip', 'develop');
        
        //Obtiene los datos normales
        $strSQL = "SELECT cups, empresa, nombre, tarifa, poblacion, provincia, mes, total, total_factura FROM datos_notelemedidas WHERE grupo='SANTANDER' AND mes>='$ano_menos_uno' AND estado='EN VIGOR' ORDER BY cups, mes";
        $todo_temp = $Conn->getArray($strSQL, true);
        
        foreach ($todo_temp as $num_row=>$row){
            if (!isset($consumo[$row['cups']])){
                $consumo[$row['cups']]                = array_fill_keys($encabezados, '');
                $consumo[$row['cups']]['cups']        = $row['cups'];
                $consumo[$row['cups']]['empresa']     = $row['empresa'];
                $consumo[$row['cups']]['nombre']      = $row['nombre'];
                $consumo[$row['cups']]['tarifa']      = $row['tarifa'];
                $consumo[$row['cups']]['poblacion']   = $row['poblacion'];
                $consumo[$row['cups']]['provincia']   = $row['provincia'];
            }
            
            $consumo[$row['cups']][$row['mes']]   = $row['total'];
            
            if (!isset($total[$row['cups']])){
                $total[$row['cups']]                = array_fill_keys($encabezados, '');
                $total[$row['cups']]['cups']        = $row['cups'];
                $total[$row['cups']]['empresa']     = $row['empresa'];
                $total[$row['cups']]['nombre']      = $row['nombre'];
                $total[$row['cups']]['tarifa']      = $row['tarifa'];
                $total[$row['cups']]['poblacion']   = $row['poblacion'];
                $total[$row['cups']]['provincia']   = $row['provincia'];
            }
            
            $total[$row['cups']][$row['mes']]   = $row['total_factura'];
            
            unset($todo_temp[$num_row]);
        }
        unset($todo_temp);
        
        //Obtiene los datos a-ok
        $strSQL = "SELECT cups, empresa, nombre, tarifa, poblacion, provincia, mes, total, total_factura FROM datos_notelemedidas WHERE grupo='SANTANDER' AND mes>='$ano_menos_uno' AND nombre='A-OK' AND estado='EN VIGOR' ORDER BY cups, mes";
        $todo_temp = $Conn->getArray($strSQL, true);
        
        foreach ($todo_temp as $num_row=>$row){
            
            if (!isset($consumo_a_ok[$row['cups']])){
                $consumo_a_ok[$row['cups']]                = array_fill_keys($encabezados, '');
                $consumo_a_ok[$row['cups']]['cups']        = $row['cups'];
                $consumo_a_ok[$row['cups']]['empresa']     = $row['empresa'];
                $consumo_a_ok[$row['cups']]['nombre']      = $row['nombre'];
                $consumo_a_ok[$row['cups']]['tarifa']      = $row['tarifa'];
                $consumo_a_ok[$row['cups']]['poblacion']   = $row['poblacion'];
                $consumo_a_ok[$row['cups']]['provincia']   = $row['provincia'];
            }
            
            $consumo_a_ok[$row['cups']][$row['mes']]   = $row['total'];
            
            if (!isset($total_a_ok[$row['cups']])){
                $total_a_ok[$row['cups']]                = array_fill_keys($encabezados, '');
                $total_a_ok[$row['cups']]['cups']        = $row['cups'];
                $total_a_ok[$row['cups']]['empresa']     = $row['empresa'];
                $total_a_ok[$row['cups']]['nombre']      = $row['nombre'];
                $total_a_ok[$row['cups']]['tarifa']      = $row['tarifa'];
                $total_a_ok[$row['cups']]['poblacion']   = $row['poblacion'];
                $total_a_ok[$row['cups']]['provincia']   = $row['provincia'];
            }
            
            $total_a_ok[$row['cups']][$row['mes']]   = $row['total_factura'];
            
            unset($todo_temp[$num_row]);
        }
        
        //Saca la suma de a-ok
        $strSQL = "SELECT cups, SUM(total) suma_consumo, SUM(total_factura) suma_total FROM datos_notelemedidas WHERE grupo='SANTANDER' AND mes>='$ano_menos_uno' AND nombre='A-OK' AND estado='EN VIGOR' GROUP BY cups ORDER BY cups";
        $suma_a_ok = $Conn->getArray($strSQL, true);
        
        foreach ($suma_a_ok as $num_row=>$row){
            $suma_a_ok[$row['cups']] = $row;
            unset($suma_a_ok[$num_row]);
        }
        
        
        //Saca la suma de todos
        $strSQL = "SELECT cups, SUM(total) suma_consumo, SUM(total_factura) suma_total FROM datos_notelemedidas WHERE grupo='SANTANDER' AND mes>='$ano_menos_uno' AND estado='EN VIGOR' GROUP BY cups ORDER BY cups";
        $suma_todo = $Conn->getArray($strSQL, true);
        
        foreach ($suma_todo as $num_row=>$row){
            $suma_todo[$row['cups']] = $row;
            unset($suma_todo[$num_row]);
        }
        
        foreach ($suma_a_ok as $cups=>$row){
            if (isset($suma_todo[$cups])){
                $resta[$cups]['cups']       = $cups;
                $resta[$cups]['consumo']    = $suma_todo[$cups]['suma_consumo'] - $row['suma_consumo'];
                $resta[$cups]['total']      = $suma_todo[$cups]['suma_total'] - $row['suma_total'];
                unset($suma_todo[$cups]);
            } else {
                $resta[$cups]['cups']       = $cups;
                $resta[$cups]['consumo']    = $row['suma_consumo'];
                $resta[$cups]['total']      = $row['suma_total'];
            }
            unset($suma_a_ok[$cups]);
        }
        
        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->addSheet('CONSUMO TODOS');
        $SprdSht->putArray($consumo, true);
        $SprdSht->addSheet('TOTAL TODOS');
        $SprdSht->putArray($total, true);
        $SprdSht->addSheet('CONSUMO A-OK');
        $SprdSht->putArray($consumo_a_ok, true);
        $SprdSht->addSheet('TOTAL A-OK');
        $SprdSht->putArray($total_a_ok, true);
        $SprdSht->addSheet('RESTA');
        $SprdSht->putArray($resta, true);
        $SprdSht->directDownload('Informe a-ok SANTANDER.xlsx');
        
        break;
        
    case 'txt_bi_iva_correos':
        
        if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: elab_clientes.php");}
        
        foreach ($_FILES['fichero']['tmp_name'] as $file){
            $fopen = fopen($file, 'r');
            while (!feof($fopen)) {
                $line=fgets($fopen);
                $line=trim($line);
                $datos[]=array_filter(explode(' ', $line));
            }
            fclose($fopen);
            unset ($fopen);
            
            $finish = false;
            foreach ($datos as $num_row=>$row){
                $codigo = substr($row[0], 0, 2);
                
                switch ($codigo){
                        
                    case '02':
                        
                        if (isset($fra) && isset($total)){
                            
                            if ($abono && isset($total)){
                                $total  = -$total;
                                $BI     = -$BI;
                                $IVA    = -$IVA;
                            }
                            
                            $linea = array();
                            $linea['FRA']   = $fra;
                            $linea['BI']    = $BI;
                            $linea['IVA']   = $IVA;
                            $linea['TOTAL'] = $total;

                            $final[] = $linea;
                            unset($linea, $fra, $BI, $IVA, $total);
                        }
                        
                        $fra = substr($row[0], 8, 16);
                        $abono = (substr($row[143], -8, 1)=='D') ? true : false;
                        
                        $total = explode(substr($row[143], -8, 1), $row[143]);
                        $total = $total[0];
                        settype($total, 'float');
                        $total = $total/100;
                        $BI    = 0;
                        $IVA   = 0;
                        break;
                        
                    case '05':
                        $temp_IVA = substr($row[0], -12);
                        settype($temp_IVA, 'float');
                        
                        switch (substr($row[0], 2, 3)){
                            case 'EXE': break;
                            default:    $IVA += $temp_IVA/100; break;
                        }
                        
                        $temp_BI = substr($row[0], 9, 15);
                        settype($temp_BI, 'float');
                        $BI += $temp_BI/100;
                        unset($temp_BI, $temp_IVA);
                        
                        break;
                }
            }
            
            if (isset($fra) && isset($total)){
                            
                if ($abono && isset($total)){
                    $total  = -$total;
                    $BI     = -$BI;
                    $IVA    = -$IVA;
                }

                $linea = array();
                $linea['FRA']   = $fra;
                $linea['BI']    = $BI;
                $linea['IVA']   = $IVA;
                $linea['TOTAL'] = $total;

                $final[] = $linea;
                unset($linea, $fra, $BI, $IVA, $total);
            }
        }
        
        
        
        if (isset($final)){
            $SprdSht = new SprdSht;
            $SprdSht->nuevo();
            $SprdSht->putArray($final, true);
            $SprdSht->directDownload('Valores txts');
            unset($SprdSht);
        }
        
        
        break;
        
}

header ("Location: elab_clientes.php");
?>