<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$Conn = new Conn('local', 'enertrade');

if (isset($_POST['action'])){
	switch ($_POST['action']){
		case 'upload_apuntamientos':
			
			if (!isset($_POST['id']) || empty($_POST['id'])){echo 'Es necesario guardar la formula antes de subir los apuntamientos'; die;}
			
			$id = $_POST['id'];
            
			foreach($_FILES['fichero']['tmp_name'] as $file){
				if (is_uploaded_file($file)){
					
					$SprdSht = new SprdSht;
					$SprdSht->load($file);
					$apu = $SprdSht->getArray(true);
					unset($SprdSht);
				}
			}
			
			if (!isset($apu)){echo 'No se ha seleccionado ningún fichero!'; die;}
			
			session_start();
			$desde_orig = $_SESSION['INDEXADO']['desde'];
			$hasta_orig = $_SESSION['INDEXADO']['hasta'];
			session_write_close();
			
			$date   = new DateClass;
			$ano    = $date->fromToFormat($desde_orig, 'd/m/Y', 'Y');
			$desde  = $date->fromToFormat($desde_orig, 'd/m/Y', 'Y-m-d');
			$hasta  = $date->fromToFormat($hasta_orig, 'd/m/Y', 'Y-m-d');
			
			foreach ($apu as $num_row=>$row){
				$apu[$num_row]['ID_FORMULA'] = $id;
				$producto = $row['PRODUCTO'];
				
				switch (substr($producto, 0, 2)){
					case 'Q1':
						$desde    = "$ano-01-01";
						$hasta    = "$ano-04-01";
						$interval = 3;
						break;
						
					case 'Q2':
						$desde    = "$ano-04-01";
						$hasta    = "$ano-07-01";
						$interval = 3;
						break;
						
					case 'Q3':
						$desde    = "$ano-07-01";
						$hasta    = "$ano-10-01";
						$interval = 3;
						break;
						
					case 'Q4':
						$desde    = "$ano-10-01";
						$hasta    = ($ano+1)."-01-01";
						$interval = 3;
						break;
						
					case 'YR':
						$desde    = "$ano-01-01";
						$hasta    = ($ano+1)."-01-01";
						$interval = 12;
						break;
						
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                    case 10:
                    case 11:
                    case 12:
                        $desde = "$ano-".$producto."-01";
							
                        if($producto==12){$hasta = ($ano+1).'-01-01';}
                        else 			 {$hasta = "$ano-".($producto+1)."-01";}
                        
                        $interval = 1;
                        break;
                        
                    default:
                        $desde    = $date->fromToFormat($desde_orig, 'd/m/Y', 'Y-m-d');
                        $hasta    = $date->fromToFormat($hasta_orig, 'd/m/Y', 'Y-m-d');
						$interval = 0;
						break;
				}
				
				$apu[$num_row]['DESDE'] 	= $desde;
				$apu[$num_row]['HASTA'] 	= $hasta;
				$apu[$num_row]['INTERVALO'] = $interval;
			}
			unset($date);
            
			$values = implode_values($apu);
			
			$Conn->Query("DELETE FROM apuntamientos_indexado WHERE ID_FORMULA=$id");
			$Conn->Query("INSERT INTO apuntamientos_indexado (PRODUCTO, P1, P2, P3, P4, P5, P6, ID_FORMULA, DESDE, HASTA, INTERVALO) VALUES $values");
			
			header ('Location: formulas.php');
			
			break;
			
		case 'download_apuntamientos':
			
			if (!isset($_POST['id']) || empty($_POST['id'])){echo 'No se ha seleccionado ninguna formula!'; die;}
			
			$id = $_POST['id'];
			
			$apu = $Conn->getArray("SELECT PRODUCTO, P1, P2, P3, P4, P5, P6 FROM apuntamientos_indexado WHERE ID_FORMULA=$id");
			
			if (!$apu){header ('Location: indexado_actions.php');}
			
			$SprdSht = new SprdSht;
			$SprdSht->nuevo();
			$SprdSht->putArray($apu, true);
			$SprdSht->directDownload('Apuntamientos');
			unset($SprdSht);
			
			break;
            
        case 'validacion_indexado':
        case 'validacion_indexado_nueva':
        
            $date = new DateClass;

            $cliente     = $_POST['cliente'];
            $enum        = $_POST['enum'];
            $tarifa      = $_POST['tarifa'];
            $desdeMasUno = (isset($_POST['desdeMasUno'])) ? true : false;
            $limite_fras = (isset($_POST['limite_fras'])) ? 200 : 0;
            $desde       = $date->fromToFormat($_POST['desde'], 'd/m/Y', 'Y-m-d');
            $hasta       = $date->fromToFormat($_POST['hasta'], 'd/m/Y', 'Y-m-d');
            
            set_time_limit(0);

            $Indexado = new Indexado;
            
            if ($_POST['action'] == 'validacion_indexado'){
                $Indexado->validacionFras($cliente, $desde, $hasta, $enum, $tarifa, $desdeMasUno, $limite_fras);
            } else {
                $Indexado->validacionFrasNueva($cliente, $desde, $hasta, $enum, $tarifa, $desdeMasUno, $limite_fras);
            }
            

            break;
            
        case 'upload_cierres':
            
            //Recupera los cierres desde el fichero
            foreach($_FILES['fichero']['tmp_name'] as $file){
				if (is_uploaded_file($file)){
					
					$SprdSht = new SprdSht;
					$SprdSht->load($file);
					$cierres = $SprdSht->getArray(true);
					unset($SprdSht);
				}
			}
            
            unset($Conn);
            $Conn = new Conn('mainsip', 'develop');
            $clientes = array_column($Conn->getArray('SELECT name FROM gestion_clientes ORDER BY name'), 'name');
            unset($Conn);
            
            $Conn = new Conn('local', 'enertrade');
            
            foreach ($cierres as $num_row=>$row){
                
                //Comprueba si se ha puesto bien el cliente
                if (in_array($row['CLIENTE'], $clientes)){$cliente = $row['CLIENTE'];}
                else {
                    $row['error'] = 'No existe este cliente';
                    $errores[] = $row;
                    continue;
                }
                
                //Comprueba si se ha puesto bien ELECTRICIDAD/GAS
                if ($row['ELECTRICIDAD/GAS']=='ELECTRICIDAD' || $row['ELECTRICIDAD/GAS']=='GAS'){
                    $tipo = $row['ELECTRICIDAD/GAS'];
                } else {
                    $row['error'] = 'Hay que especificar ELECTRICIDAD/GAS';
                    $errores[] = $row;
                    continue;
                }
                //Comprueba si se ha puesto bien el PORCENTAJE
                if ($row['PORCENTAJE']<1){
                    $row['error'] = 'PORCENTAJE incorrecto';
                    $errores[] = $row;
                    continue;
                } else {$porcentaje = $row['PORCENTAJE'];}
                
                $date 		= new DateClass;
                
                $enum       = ($row['ENUMERACIÓN']=='') ? 1 : $row['ENUMERACIÓN'];
                $date->fromXl($row['FECHA']);
                $fecha      = $date->format('d/m/Y');
                $producto   = $row['PRODUCTO'];
                $precio     = $row['PRECIO'];
                $volumen    = $row['VOLUMEN'];
                
                
                //Adapta y carga los datos
                
                switch (substr($producto, 0, 2)){
                    case 'Q1':
                        $desde 		= str_replace('Q1', '01/01/20', $producto);
                        $hasta 		= str_replace('Q1', '01/04/20', $producto);
                        $interval 	= 3;
                        break;

                    case 'Q2':
                        $desde 		= str_replace('Q2', '01/04/20', $producto);
                        $hasta 		= str_replace('Q2', '01/07/20', $producto);
                        $interval 	= 3;
                        break;

                    case 'Q3':
                        $desde 		= str_replace('Q3', '01/07/20', $producto);
                        $hasta 		= str_replace('Q3', '01/10/20', $producto);
                        $interval 	= 3;
                        break;

                    case 'Q4':
                        $desde 		= str_replace('Q4', '01/10/20', $producto);
                        $hasta 		= str_replace('Q4', '', $producto);
                        $hasta 		= '01/01/20'.($hasta+1);
                        $interval 	= 3;
                        break;

                    case 'YR':
                        $desde 		= str_replace('YR', '01/01/20', $producto);
                        $hasta 		= str_replace('YR', '', $producto);
                        $hasta 		= '01/01/20'.($hasta+1);
                        $interval 	= 12;
                        break;

                    default:
                        $fecha_producto = new DateClass;
                        $fecha_producto->fromXl($row['PRODUCTO']);;
                        $producto   = $fecha_producto->format('d/m/Y');
                        unset($fecha_producto);
                        
                        $desde 		= $producto;
                        $hasta 		= $date->fromToFormat($desde, 'd/m/Y', 't/m/Y');
                        $date->stringToDate($hasta, 'd/m/Y');
                        $date->add(0,0,1);
                        $hasta      = $date->format('d/m/Y');
                        $interval 	= 1;
                        break;
                }

                $fecha 		= $date->fromToFormat($fecha, 'd/m/Y', 'Y-m-d');
                $desde 		= $date->fromToFormat($desde, 'd/m/Y', 'Y-m-d');
                $hasta 		= $date->fromToFormat($hasta, 'd/m/Y', 'Y-m-d');
                unset($date);

                $Conn->Query("INSERT INTO cierres (CLIENTE, FECHA, PRODUCTO, ENUMERACION, PRECIO, PORCENTAJE, VOLUMEN, TIPO, DESDE, HASTA, INTERVALO) VALUES ('$cliente', '$fecha', '$producto', '$enum', '$precio', '$porcentaje', '$volumen', '$tipo', '$desde', '$hasta', '$interval')");
                
            }
            
            if (isset($errores)){
                $SprdSht = new SprdSht;
                $SprdSht->nuevo();
                $SprdSht->putArray($errores, true);
                unset($errores);
                $SprdSht->directDownload('Errores_cierres.xlsx');
                unset($SprdSht);
            }
            
            header ('Location: cierres.php');
            
            break;
	}
}
elseif (!isset($_GET['action'])){die;}



switch ($_GET['action']){
		
	case 'getCierres':
		
		$datos = $Conn->getArray("SELECT ID, CLIENTE, DATE_FORMAT(FECHA, '%d/%m/%Y'), PRODUCTO, ENUMERACION, PRECIO, PORCENTAJE, VOLUMEN, TIPO FROM cierres", false);
		foreach ($datos as $num_row=>$row){
			$datos[$num_row][] = '<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row[0].' onclick="delCierre($(this).val())"></button>';
		}
		
		echo json_encode($datos);
		
		break;
		
	case 'anadir_cierre':
		
		$cliente 	= $_POST['cliente'];
		$fecha 		= $_POST['fecha'];
		$producto 	= $_POST['producto'];
		$enum 		= $_POST['enum'];
		$precio 	= $_POST['precio'];
		$porcentaje = $_POST['porcentaje'];
		$volumen 	= $_POST['volumen'];
		$tipo 		= $_POST['tipo'];
		
		$date 		= new DateClass;
		switch (substr($producto, 0, 2)){
			case 'Q1':
				$desde 		= str_replace('Q1', '01/01/20', $producto);
				$hasta 		= str_replace('Q1', '01/04/20', $producto);
				$interval 	= 3;
				break;

			case 'Q2':
				$desde 		= str_replace('Q2', '01/04/20', $producto);
				$hasta 		= str_replace('Q2', '01/07/20', $producto);
				$interval 	= 3;
				break;

			case 'Q3':
				$desde 		= str_replace('Q3', '01/07/20', $producto);
				$hasta 		= str_replace('Q3', '01/10/20', $producto);
				$interval 	= 3;
				break;

			case 'Q4':
				$desde 		= str_replace('Q4', '01/10/20', $producto);
				$hasta 		= str_replace('Q4', '', $producto);
				$hasta 		= '01/01/20'.($hasta+1);
				$interval 	= 3;
				break;

			case 'YR':
				$desde 		= str_replace('YR', '01/01/20', $producto);
				$hasta 		= str_replace('YR', '', $producto);
				$hasta 		= '01/01/20'.($hasta+1);
				$interval 	= 12;
				break;
				
			default:
				$desde 		= $producto;
				$hasta 		= $date->fromToFormat($producto, 'd/m/Y', 't/m/Y');
                $date->stringToDate($hasta, 'd/m/Y');
                $date->add(0,0,1);
                $hasta      = $date->format('d/m/Y');
				$interval 	= 1;
                break;
		}
		
		$fecha 		= $date->fromToFormat($fecha, 'd/m/Y', 'Y-m-d');
		$desde 		= $date->fromToFormat($desde, 'd/m/Y', 'Y-m-d');
		$hasta 		= $date->fromToFormat($hasta, 'd/m/Y', 'Y-m-d');
		unset($date);
		
		$Conn->Query("INSERT INTO cierres (CLIENTE, FECHA, PRODUCTO, ENUMERACION, PRECIO, PORCENTAJE, VOLUMEN, TIPO, DESDE, HASTA, INTERVALO) VALUES ('$cliente', '$fecha', '$producto', '$enum', '$precio', '$porcentaje', '$volumen', '$tipo', '$desde', '$hasta', '$interval')");
        
		break;
		
	case 'delCierre':
		
		$id = $_POST['id'];
		$Conn->Query("DELETE FROM cierres WHERE ID=$id");
		
		break;
		
	case 'downloadCierres':
		
		$datos = $Conn->getArray("SELECT * FROM cierres");
		
		$SprdSht = new SprdSht;
		$SprdSht->nuevo();
		$SprdSht->putArray($datos, true);
		$SprdSht->directDownload('Cierres');
		unset($SprdSht);
		
		break;
		
	case 'getVariables':
		
		$datos = $Conn->getArray('SELECT ID, VARIABLE, DEFINICION FROM variables_indexado', false);
		echo json_encode($datos);
		
		break;
		
	case 'getFormulas':
		
		$datos = $Conn->getArray("SELECT ID, CLIENTE, COMERCIALIZADORA, TARIFA, ENUM, DATE_FORMAT(DESDE, '%d/%m/%Y'), DATE_FORMAT(HASTA, '%d/%m/%Y'), COMENTARIOS, FORMULA FROM formulas_indexado", false);
		foreach ($datos as $num_row=>$row){
			$datos[$num_row][] = '<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row[0].' onclick="delFormula($(this).val())"></button>';
		}
		echo json_encode($datos);
		
		break;
		
	case 'delFormula':
		
		$id = $_POST['id'];
		$Conn->Query("DELETE FROM formulas_indexado WHERE ID=$id");
        $Conn->Query("DELETE FROM apuntamientos_indexado WHERE ID_FORMULA=$id");
		
		break;
		
	case 'translateFormula':
		
		$formula 	= $_POST['formula'];
		$datos 		= $Conn->getArray('SELECT * FROM variables_indexado');
		
		foreach ($datos as $num_row=>$row){$formula = str_replace($row['VARIABLE'], '#'.$row['ID'].'#', $formula);}
		foreach ($datos as $num_row=>$row){$formula = str_replace('#'.$row['ID'].'#', $row['DEFINICION'], $formula);}
		
		echo $formula;
		
		break;
		
	case 'saveFind':
		
		$cliente 			= $_POST['cliente'];
		$comercializadora 	= $_POST['comercializadora'];
		$tarifa 			= $_POST['tarifa'];
		$enum 				= $_POST['enum'];
		$desde 				= $_POST['desde'];
		$hasta 				= $_POST['hasta'];
		
		session_start();
		$_SESSION['INDEXADO']['cliente'] 			= $cliente;
		$_SESSION['INDEXADO']['comercializadora'] 	= $comercializadora;
		$_SESSION['INDEXADO']['tarifa'] 			= $tarifa;
		$_SESSION['INDEXADO']['enum'] 				= $enum;
		$_SESSION['INDEXADO']['desde'] 				= $desde;
		$_SESSION['INDEXADO']['hasta'] 				= $hasta;
		session_write_close();
		
		if (!isset($desde) || !isset($hasta) || empty($desde) || empty($hasta)){break;}
		
		$date = new DateClass;
		$desde = $date->fromToFormat($desde, 'd/m/Y', 'Y-m-d');
		$hasta = $date->fromToFormat($hasta, 'd/m/Y', 'Y-m-d');
		unset($date);
		
		$datos = $Conn->oneRow("SELECT ID, COMENTARIOS, FORMULA FROM formulas_indexado WHERE CLIENTE='$cliente' AND COMERCIALIZADORA='$comercializadora' AND TARIFA='$tarifa' AND ENUM='$enum' AND DESDE='$desde' AND HASTA='$hasta'");
		
        
		if ($datos){
            $apu = $Conn->oneData("SELECT ID_FORMULA FROM apuntamientos_indexado WHERE ID_FORMULA=".$datos['ID']." GROUP BY ID_FORMULA");
            echo json_encode($datos['ID']).'|'.json_encode($datos['COMENTARIOS']).'|'.json_encode($datos['FORMULA']).'|'.$apu;
        }
		break;
		
	case 'downloadFormulas':
		
		$datos = $Conn->getArray('SELECT * FROM formulas_indexado');
		if ($datos){
			$SprdSht = new SprdSht;
			$SprdSht->nuevo();
			$SprdSht->putArray($datos, true);
			$SprdSht->directDownload('Formulas_Indexado');
			unset($SprdSht, $datos);
		} else {
			header ("Location: formulas.php");
		}
		
		break;
		
	case 'saveFormula':
		
		session_start();
		foreach ($_SESSION['INDEXADO'] as $key=>$value){if (!isset($value) || empty($value)){session_write_close(); die;}}
		$cliente 			= $_SESSION['INDEXADO']['cliente'];
		$comercializadora 	= $_SESSION['INDEXADO']['comercializadora'];
		$tarifa 			= $_SESSION['INDEXADO']['tarifa'];
		$enum 				= $_SESSION['INDEXADO']['enum'];
		$desde 				= $_SESSION['INDEXADO']['desde'];
		$hasta 				= $_SESSION['INDEXADO']['hasta'];
		session_write_close();
		
		$date = new DateClass;
		$desde = $date->fromToFormat($desde, 'd/m/Y', 'Y-m-d');
		$hasta = $date->fromToFormat($hasta, 'd/m/Y', 'Y-m-d');
		unset($date);
		
		$id 			= $_POST['id'];
		$comentarios 	= $_POST['comentarios'];
		$formula 		= str_replace(',', '.', $_POST['formula']);
		
		if (isset($id) && !empty($id)){
			$strSQL = "UPDATE formulas_indexado SET COMENTARIOS='$comentarios', FORMULA='$formula' WHERE ID=$id";
			$Conn->Query($strSQL);
		} else {
			$strSQL = "INSERT INTO formulas_indexado (CLIENTE, COMERCIALIZADORA, TARIFA, ENUM, DESDE, HASTA, COMENTARIOS, FORMULA) VALUES ('$cliente', '$comercializadora', '$tarifa', '$enum', '$desde', '$hasta', '$comentarios', '$formula')";
			$Conn->Query($strSQL);
			
			$id = $Conn->oneData("SELECT ID FROM formulas_indexado WHERE CLIENTE='$cliente' AND COMERCIALIZADORA='$comercializadora' AND TARIFA='$tarifa' AND ENUM='$enum' AND DESDE='$desde' AND HASTA='$hasta'");
		}
		
		echo $id;
		
		break;
        
    case 'getValuesValidacion':
        
        $Conn   = new Conn('local', 'enertrade');
        $om     = $Conn->oneData("SELECT VALOR FROM variables_indexado WHERE DEFINICION='OM(€)'");
        $os     = $Conn->oneData("SELECT VALOR FROM variables_indexado WHERE DEFINICION='OS(€)'");
        $fnee   = $Conn->oneData("SELECT VALOR FROM variables_indexado WHERE DEFINICION='FNEE(€)'");
        unset($Conn);
        
        echo "$om|$os|$fnee";
        
        break;
        
    case 'saveValuesValidacion':
        
        $om     = str_replace(',', '.', $_POST['om']);
        $os     = str_replace(',', '.', $_POST['os']);
        $fnee   = str_replace(',', '.', $_POST['fnee']);
        
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("UPDATE variables_indexado SET VALOR=$om WHERE DEFINICION='OM(€)'");
        $Conn->Query("UPDATE variables_indexado SET VALOR=$os WHERE DEFINICION='OS(€)'");
        $Conn->Query("UPDATE variables_indexado SET VALOR=$fnee WHERE DEFINICION='FNEE(€)'");
        unset($Conn);
        
        break;
		
}

unset($Conn);

?>