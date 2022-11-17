<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

session_start();
if (isset($_SESSION['email'])){
	$usuario = $_SESSION['email'];
} else {
	header ("Location: /Enertrade/index.php");
}
session_write_close();

$Conn = new Conn('local', 'enertrade');

switch ($_GET['action']){
    //CLAVES ------------------------------------------------------
    case 'addClaveCommDistr':
        
        $Conn        = new Conn('local', 'enertrade');
        
        $cli         = $_POST['cli'];
        $comm_distr  = $_POST['comm_distr'];
        $usu         = $Conn->realEscape($_POST['usu']);
        $pwd         = $Conn->realEscape($_POST['pwd']);
        $comentarios = $Conn->realEscape($_POST['comentarios']);
        $C_D         = $_POST['C_D'];
        
        $Conn->Query("INSERT INTO claves_comm_distr (CLIENTE, COMM_DISTR, USUARIO, CONTRASENA, COMENTARIOS, IDENTIFICATIVO) VALUES ('$cli', '$comm_distr', '$usu', '$pwd', '$comentarios', '$C_D')");
        unset($Conn);
        
        break;
        
	case 'getClavesCommDistr':
		
		$datos = $Conn->getArray("SELECT * FROM claves_comm_distr");
		
		foreach ($datos as $num_row=>$row){
			$datos[$num_row][] = '<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delClaveCommDistr($(this).val())"></button>';
			$datos[$num_row] = array_values($datos[$num_row]);
		}
		
		echo json_encode($datos);
		
		break;
		
	case 'delClaveCommDistr':
		
		$id = $_POST['id'];
		$Conn->Query("DELETE FROM claves_comm_distr WHERE ID=$id");
		break;
		
	//CLAVES VARIAS ------------------------------------------------------
    case 'addClaveVaria':
        
        $Conn        = new Conn('local', 'enertrade');
        
        $tipo        = $Conn->realEscape($_POST['tipo']);
        $usu         = $Conn->realEscape($_POST['usu']);
        $pwd         = $Conn->realEscape($_POST['pwd']);
        $comentarios = $Conn->realEscape($_POST['comentarios']);
        
        $Conn->Query("INSERT INTO claves_varias (TIPO, USUARIO, CONTRASENA, COMENTARIOS) VALUES ('$tipo', '$usu', '$pwd', '$comentarios')");
        unset($Conn);
        
        break;
        
	case 'getClavesVarias':
		
		$datos = $Conn->getArray("SELECT * FROM claves_varias");
		
		foreach ($datos as $num_row=>$row){
			$row[] = '<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delClaveVaria($(this).val())"></button>';
			$final[] = array_values($row);
		}
		unset($datos);
		
		echo json_encode($final);
		
		break;
		
	case 'delClaveVaria':
		$ID = $_POST['id'];
		$Conn->Query("DELETE FROM claves_varias WHERE ID=$ID");
		break;
		
	//CONTACTOS ------------------------------------------------------
    case 'addContacto':
        
        $Conn             = new Conn('local', 'enertrade');
        
        $cli              = $_POST['cli'];
        $contacto         = $Conn->realEscape($_POST['contacto']);
        $email            = $_POST['email'];
        $tel              = $_POST['tel'];
        $comentarios      = $Conn->realEscape($_POST['comentarios']);
        $comercializadora = $_POST['comercializadora'];
        $identificador    = (isset($comercializadora) && !empty($comercializadora)) ? 'G' : 'C';
        
        $Conn->Query("INSERT INTO contactos (CLIENTE, CONTACTO, EMAIL, TELEFONO, COMENTARIOS, COMERCIALIZADORA, IDENTIFICADOR) VALUES ('$cli', '$contacto', '$email', '$tel', '$comentarios', '$comercializadora', '$identificador')");
        unset($Conn);
        
        break;
        
        break;
        
	case 'getContactos':
		
		$datos = $Conn->getArray("SELECT * FROM contactos");
		
		foreach ($datos as $num_row=>$row){
			$row[] = '<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delContacto($(this).val())"></button>';
			$final[] = array_values($row);
		}
		unset($datos);
		
		echo json_encode($final);
		
		break;
		
	case 'delContacto':
		$ID = $_POST['id'];
		$Conn->Query("DELETE FROM contactos WHERE ID=$ID");
		break;
        
    //CONTACTOS DISTRIBUIDORA ------------------------------------------------------
    case "delContactoDistr":
		
		$ID = $_POST['id'];
		$Conn->Query("DELETE FROM contactos_distribuidoras WHERE ID=$ID");
		break;
        
    case "addContactoDistr":
		
		$distr       = $_POST['distr'];
		$contacto    = $_POST['contacto'];
		$email       = $_POST['email'];
		$tel         = $_POST['tel'];
		$comentarios = $_POST['comentarios'];
		$Conn->Query("INSERT INTO contactos_distribuidoras (DISTRIBUIDORA, CONTACTO, EMAIL, TELEFONO, COMENTARIOS) VALUES ('$distr', '$contacto', '$email', '$tel', '$comentarios')");
		break;
		
	case 'getContactosDistr':
		
		$datos = $Conn->getArray("SELECT * FROM contactos_distribuidoras");
		
		foreach ($datos as $num_row=>$row){
			$row[] = '<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delContactoDistr($(this).val())"></button>';
			$final[] = array_values($row);
		}
		unset($datos);
		
		echo json_encode($final);
		
		break;
		
	//DISTR CODE ------------------------------------------------------
	case 'getDistrCode':
		
		echo json_encode($Conn->getArray("SELECT * FROM distribuidoras", false));
		break;
		
    //TURNOS ------------------------------------------------------
    case "getEventsTurnos":
		$strSQL = ($_GET['usuario']=='Todos') ? "SELECT * FROM turnos" : "SELECT * FROM turnos WHERE title IN ('".$_GET['usuario']."', 'F_')";
		$datos = $Conn->getArray($strSQL);
		echo json_encode($datos);
		break;
        
    case "eliminarTurnos":
		$Conn->Query("DELETE FROM turnos WHERE ID=".$_POST['id']);
		break;
        
    case "addEventTurnos":
		$start 			= $_POST['start'];
		$end 			= $_POST['end'];
		$user 			= $_POST['user'];
		$extendedProps 	= $_POST['extendedProps'];
		$strSQL 		= "INSERT INTO turnos (start, end, title, extendedProps) VALUES ('$start', '$end', '$user', '$extendedProps')";
		$Conn->Query($strSQL);
		break;
        
	//VACACIONES ------------------------------------------------------
	case "getEvents":
		$strSQL = ($_GET['usuario']=='Todos') ? "SELECT * FROM vacaciones" : "SELECT * FROM vacaciones WHERE title IN ('".$_GET['usuario']."', 'F_')";
		$datos = $Conn->getArray($strSQL);
		echo json_encode($datos);
		break;
		
	case "eliminar":
		$Conn->Query("DELETE FROM vacaciones WHERE ID=".$_POST['id']);
		break;
		
	case "addEvent":
		$start 			= $_POST['start'];
		$end 			= $_POST['end'];
		$user 			= $_POST['user'];
		$extendedProps 	= $_POST['extendedProps'];
		$strSQL 		= "INSERT INTO vacaciones (start, end, title, extendedProps) VALUES ('$start', '$end', '$user', '$extendedProps')";
		$Conn->Query($strSQL);
		break;
		
	case "retrieveDays":
		$user 	= $_POST['user'];
		$ano	= $_POST['ano'];
		if ($user=='Todos'){echo 0; break;}
        
        $dias 	= $Conn->getArray("SELECT title, SUM(DATEDIFF(end, start)) suma FROM vacaciones WHERE title='$user' AND extendedProps='$ano' GROUP BY title");
        $dias_totales = $Conn->oneData("SELECT dias_vacas FROM usuarios WHERE email='$user'");
        
		if (!isset($dias[0]['title']) || empty($dias[0]['title'])){echo $dias_totales; break;}
		
		$quedan = $dias_totales-$dias[0]['suma'];
		echo $quedan;
		break;
		
	case "getSolicitudes":
		
		$query = $Conn->Query("SELECT * FROM solicitudes_vacas");
		while ($row = mysqli_fetch_assoc($query)){
			
			$row['DESDE'] = date_sql_to_php($row['DESDE']);
			$row['HASTA'] = date_sql_to_php($row['HASTA']);
			
			$row['Acciones'] = "";
			
			switch (true){
				case ($row['ACEPTADA']=='ACEPTADA'): 
					$row['Acciones'] = '<a disabled class="fa fa-check" />';
					if ($usuario == 'mmontero@enertrade.es' || $usuario == 'vmrodriguez@enertrade.es'){
						$row['Acciones'] .= '<button class="btn btn-default btn-sm btn-danger fa fa-remove pull-right" id="del" value='.$row['ID'].' onclick="delSolicitud($(this).val())"></button>';
					}
					break;
				case ($row['ACEPTADA']=='RECHAZADA'): $row['Acciones'] = '<a disabled class="fa fa-remove" />'; break;
				case ($usuario != 'mmontero@enertrade.es' && $usuario != 'vmrodriguez@enertrade.es'):
					
					$row['Acciones'] = ($row['USUARIO'] == $usuario) ? 
							'<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delSolicitud($(this).val())"></button>' : "";
					break;
					
				default:
					
					$row['Acciones'] = 
						'<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="rechazaSolicitud($(this).val())"></button>';
					$row['Acciones'] .=
						'<button class="btn btn-default btn-sm btn-success fa fa-check" id="accept" value='.$row['ID'].' onclick="acceptSolicitud($(this).val())"></button>';
					break;
			}
			
			unset($row['ACEPTADA']);
			$solicitudes[] = array_values($row);
		}
		
		if (!isset($solicitudes)){
			$solicitudes['ID'] = "";
			$solicitudes['USUARIO'] = "";
			$solicitudes['DESDE'] = "";
			$solicitudes['HASTA'] = "";
			$solicitudes['Acciones'] = "";
		}
		echo json_encode($solicitudes);
		break;
		
	case "delSolicitud":
		
		$Conn->Query("DELETE FROM solicitudes_vacas WHERE ID=".$_POST['id']);
		break;
		
	case "acceptSolicitud":
	case "rechazaSolicitud":
		
		$id 	= $_POST['id'];
		$user	= $Conn->getArray("SELECT USUARIO FROM solicitudes_vacas WHERE ID=$id");
		
		$A[] = $user[0]['USUARIO'];
		
		switch ($_GET['action']){
			case "acceptSolicitud":
				$Conn->Query("UPDATE solicitudes_vacas SET ACEPTADA='ACEPTADA' WHERE ID=$id");
				$sujeto = "Solicitud aceptada!";
				$cuerpo = "Tu solicitud de vacaciones ha sido aceptada";
				break;
			case "rechazaSolicitud":
				$Conn->Query("UPDATE solicitudes_vacas SET ACEPTADA='RECHAZADA' WHERE ID=$id");
				$sujeto = "Solicitud rechazada!";
				$cuerpo = "Tu solicitud de vacaciones ha sido rechazada";
				break;
		}

		mailDeA($sujeto, $cuerpo, $A);
		break;
		
	case "sendSolicitud":
		
		$desde = $_POST['start'];
		$hasta = $_POST['end'];
		
		$Conn->Query("INSERT INTO solicitudes_vacas (USUARIO, DESDE, HASTA) VALUES ('$usuario', '$desde', '$hasta')");
		
		$desde = date_sql_to_php($desde);
		$hasta = date_sql_to_php($hasta);
		
		$sujeto = "Solicitud de vacaciones!";
		$cuerpo = "$usuario ha enviado una nueva solicitud de vacaciones desde el $desde hasta el $hasta no incluido.";
		
		$result = $Conn->getArray("SELECT email FROM usuarios WHERE email!='jsalsas@enertrade.es'");
		
		foreach ($result as $num_row=>$row){$BCC[] = $row['email'];}
		
		mailDeA($sujeto, $cuerpo, NULL, NULL, $BCC, true);
		
		break;
		
//FICHA TECNICA COMM DISTR ------------------------------------------------------
        
	case "getDatosComm":
		
		$campo = $_GET['campo'];
		
		$datos = $Conn->getArray("SELECT COMERCIALIZADORA, DESCRIPCION, DATO FROM datos_comm WHERE CAMPO='$campo'", false);
		echo json_encode($datos);
		
		break;
		
	case "getDatosDistr":
		
		$campo = $_GET['campo'];
		
		$datos = $Conn->getArray("SELECT DISTRIBUIDORA, DESCRIPCION, DATO FROM datos_distr WHERE CAMPO='$campo'", false);
		echo json_encode($datos);
		
		break;
		
	case "getEnlacesComm":
		
		$comm = $_POST['comm'];
		
		$datos = $Conn->oneRow("SELECT PARTICULAR, EMPRESAS FROM comercializadoras WHERE COMERCIALIZADORA='$comm'", false);
		$datos = implode("|", $datos);
		echo $datos;
		
		break;
        
//FICHA TECNICA CLIENTE -----------------------------------------------------
    case 'saveVarsFichaCliente':
        
        session_start();
        $_SESSION['info_ficha_cliente']['cli'] = $_POST['cli'];
        session_write_close();
        
        break;
        
    case 'getCupsEnVigor':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();
        
        $Ps = new Ps($cli);
        $final = $Ps->getCups();
        unset($Ps);
        
        if (!$final){$final = 0;}
        
        echo $final;
        
        break;
        
    case 'getCupsEnVigorPorTarifa':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();
        
        $enVigor    = true;
        $porTarifa  = true;
        
        $Ps = new Ps($cli);
        $final = $Ps->getCups($enVigor, $porTarifa);
        unset($Ps);
        
        if (!$final){
            $final = array('2.0TD'=>'0', '3.0TD'=>'0', '6.1TD'=>'0', '6.2TD'=>'0', '6.3TD'=>'0', '6.4TD'=>'0');
        }
        
        echo json_encode(array(array_values($final)));
        
        break;
        
    case 'getDatosFicha':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();
        
        $Conn = new Conn('local', 'enertrade');
        $datos = $Conn->oneRow("SELECT * FROM ficha_cliente_general WHERE CLIENTE='$cli'", true);
        
        if (isset($datos) && !empty($datos)){
            unset($datos['ID'], $datos['CLIENTE']);
            
            foreach ($datos as $key=>$value){$datos[$key] = json_encode($value);}
            $datos = implode('|', $datos);
        } else {
            $datos = '';
        }
        
        echo $datos;
        
        break;
        
    case 'getConsumoAnual':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();
        
        $ano = date('Y')-2;

        $Conn = new Conn('mainsip', 'develop');
        $consumos = $Conn->getArray("SELECT DATE_FORMAT(mes, '%Y') ano, FORMAT(SUM(total), 0) FROM datos_notelemedidas WHERE grupo='$cli' AND MES>='$ano-01-01' GROUP BY ano");
        
        
        if (isset($consumos) && !empty($consumos) && $consumos!==false){
            foreach ($consumos as $num_row=>$row){
                $row['FORMAT(SUM(total), 0)'] = str_replace(',', '.', $row['FORMAT(SUM(total), 0)']);
                $final[] = array_values($row);
            }
        } else {
            $final[] = array('', '');
        }
        
        echo json_encode($final);
        
        break;
        
    case 'getConsumoAnualPorTarifa':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();
        
        $ano = date('Y')-2;

        $Conn = new Conn('mainsip', 'develop');
        $consumos = $Conn->getArray("SELECT DATE_FORMAT(mes, '%Y') ano, tarifa, FORMAT(SUM(total), 0) FROM datos_notelemedidas WHERE grupo='$cli' AND MES>='$ano-01-01' GROUP BY ano, tarifa");
        
        
        if (isset($consumos) && !empty($consumos) && $consumos!==false){
            foreach ($consumos as $num_row=>$row){
                if (!isset($temp_final[$row['ano']])){
                    $temp_final[$row['ano']] = array('ANO'=>$row['ano'], '2.0TD'=>'0', '3.0TD'=>'0', '6.1TD'=>'0', '6.2TD'=>'0', '6.3TD'=>'0', '6.4TD'=>'0');
                }
                
                $temp_final[$row['ano']][$row['tarifa']] = str_replace(',', '.', $row['FORMAT(SUM(total), 0)']);
            }
        } else {
            $temp_final[] = array('', '', '', '', '', '', '');
        }
        
        foreach ($temp_final as $num_row=>$row){
            $final[] = array_values($row);
        }
        
        echo json_encode($final);
        
        break;
    
    case 'getFf':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();
        
        $CalculosSimples = new CalculosSimples;
        $id_cli = $CalculosSimples->getIdCliente($cli);

        $Conn = new Conn('local', 'enertrade');
        $ff = $Conn->getArray("SELECT ID, COMERCIALIZADORA, RECEPCION_DESCARGA, COMENTARIOS, FECHA_RECEPCION_ESTIMADA FROM ficha_cliente_ff WHERE ID_CLI=$id_cli");
        
        if (isset($ff) && !empty($ff) && $ff!==false){
            $date = new DateClass;
            foreach ($ff as $num_row=>$row){
                $date->stringToDate($row['FECHA_RECEPCION_ESTIMADA']);
                $d = $date->format('d');
                $row['FECHA_RECEPCION_ESTIMADA'] = $date->fromToFormat($row['FECHA_RECEPCION_ESTIMADA'], 'Y-m-d', 'd').date('/m/Y');
                $row[] = '<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delFf($(this).val())"></button>';
                $final[] = array_values($row);
            }
            unset($date);
        } else {
            $final[] = array('', '', '', '', '', '');
        }
        
        echo json_encode($final);
        
        break;
        
    case 'getOtrosInformes':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();
        
        $CalculosSimples = new CalculosSimples;
        $id_cli = $CalculosSimples->getIdCliente($cli);

        $Conn = new Conn('local', 'enertrade');
        $otros_informes = $Conn->getArray("SELECT ID, INFORME, A, CC, COMENTARIOS, FECHA_ENTREGA_ESTIMADA FROM ficha_cliente_otros_informes WHERE ID_CLI=$id_cli");
        
        if (isset($otros_informes) && !empty($otros_informes) && $otros_informes!==false){
            $date = new DateClass;
            foreach ($otros_informes as $num_row=>$row){
                $date->stringToDate($row['FECHA_ENTREGA_ESTIMADA']);
                $d = $date->format('d');
                $row['FECHA_ENTREGA_ESTIMADA'] = $date->fromToFormat($row['FECHA_ENTREGA_ESTIMADA'], 'Y-m-d', 'd').date('/m/Y');
                $row[] = '<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delOtroInforme($(this).val())"></button>';
                $final[] = array_values($row);
            }
            unset($date);
        } else {
            $final[] = array('', '', '', '', '', '', '');
        }
        
        echo json_encode($final);
        
        break;
        
    case 'getEmpresas':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();

        $Ps = new Ps($cli);
        $empresas = $Ps->getRazonSocial();
        unset($Ps);
        
        if ($empresas){
            foreach ($empresas as $num_row=>$row){
                $final[] = array_values($row);
            }
        } else {
            $final[] = array('', '');
        }
        
        echo json_encode($final);
        
        break;
        
    case 'getContactosCliente':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();
        
        $CalculosSimples = new CalculosSimples;
        $id_cli = $CalculosSimples->getIdCliente($cli);

        $Conn = new Conn('local', 'enertrade');
        $cont_cli = $Conn->getArray("SELECT CONTACTO, EMAIL, TELEFONO, COMENTARIOS FROM contactos WHERE CLIENTE='$cli' AND COMERCIALIZADORA=''");
        
        if (isset($cont_cli) && !empty($cont_cli) && $cont_cli!==false){
            foreach ($cont_cli as $num_row=>$row){
                $final[] = array_values($row);
            }
        } else {
            $final[] = array('', '', '', '');
        }
        
        echo json_encode($final);
        
        break;
        
    case 'getContactosGestores':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();
        
        $CalculosSimples = new CalculosSimples;
        $id_cli = $CalculosSimples->getIdCliente($cli);
        unset($CalculosSimples);

        $Conn = new Conn('local', 'enertrade');
        $cont_gestor = $Conn->getArray("SELECT CONTACTO, EMAIL, TELEFONO, COMENTARIOS, COMERCIALIZADORA FROM contactos WHERE CLIENTE='$cli' AND COMERCIALIZADORA!=''");
        
        if (isset($cont_gestor) && !empty($cont_gestor) && $cont_gestor!==false){
            foreach ($cont_gestor as $num_row=>$row){
                $final[] = array_values($row);
            }
        } else {
            $final[] = array('', '', '', '', '');
        }
        
        echo json_encode($final);
        
        break;
        
    case 'saveDatoFichaCliente':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();
        
        $dato = $_POST['dato'];
        $tipo = $_POST['tipo'];
        
        $CalculosSimples = new CalculosSimples;
        $id_cli = $CalculosSimples->getIdCliente($cli);
        unset($CalculosSimples);
        
        $col = strtoupper($tipo);
        
        switch ($dato){
            case 'true': $dato = 1; break;
            case 'false': $dato = 0; break;
        }
        
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("INSERT INTO ficha_cliente_general (ID, CLIENTE, $col) VALUES($id_cli, '$cli', '$dato') ON DUPLICATE KEY UPDATE $col=VALUES($col)");
        
        break;
        
    case 'insertOtroInforme':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();

        $CalculosSimples = new CalculosSimples;
        $id_cli = $CalculosSimples->getIdCliente($cli);
        unset($CalculosSimples);

        $otro_informe = $_POST['otro_informe'];
        $comentarios_informe = $_POST['comentarios_informe'];
        $fecha_envio_informe = $_POST['fecha_envio_informe'];
        $A = $_POST['A'];
        $CC = $_POST['CC'];

        $date = new DateClass;
        $fecha_envio_informe = $date->fromToFormat($fecha_envio_informe, 'd/m/Y', 'Y-m-d');
        unset($date);

        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("INSERT INTO ficha_cliente_otros_informes (ID_CLI, INFORME, A, CC, COMENTARIOS, FECHA_ENTREGA_ESTIMADA) VALUES ($id_cli, '$otro_informe', '$A', '$CC', '$comentarios_informe', '$fecha_envio_informe')");
        
        break;
        
    case 'delOtroInforme':
        
        $id = $_POST['id'];
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("DELETE FROM ficha_cliente_otros_informes WHERE ID=$id");
        
        break;
        
        
    case 'insertFf':
        
        session_start();
        $cli = $_SESSION['info_ficha_cliente']['cli'];
        session_write_close();

        $CalculosSimples = new CalculosSimples;
        $id_cli = $CalculosSimples->getIdCliente($cli);
        unset($CalculosSimples);

        $comm_ff = $_POST['comm_ff'];
        $recepcion_descarga_ff = $_POST['recepcion_descarga_ff'];
        $comentarios_ff = $_POST['comentarios_ff'];

        $date = new DateClass;
        $fecha_ff = $date->fromToFormat($_POST['fecha_ff'], 'd/m/Y', 'Y-m-d');
        unset($date);

        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("INSERT INTO ficha_cliente_ff (ID_CLI, COMERCIALIZADORA, RECEPCION_DESCARGA, COMENTARIOS, FECHA_RECEPCION_ESTIMADA) VALUES ($id_cli, '$comm_ff', '$recepcion_descarga_ff', '$comentarios_ff', '$fecha_ff')");
        
        break;
        
    case 'delFf':
        
        $id = $_POST['id'];
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("DELETE FROM ficha_cliente_ff WHERE ID=$id");
        
        break;
        
//SEGUIMIENTO CLIENTE   ---------------------------------------------
        
    case 'getClientesGestionadosSeguimiento':
        
        $empleado = $_GET['empleado'];
        $rol      = $_GET['rol'];
        
        $Conn = new Conn('local', 'enertrade');
        $datos = $Conn->getArray("SELECT CLIENTE FROM ficha_cliente_general WHERE $rol='$empleado'", false);
        
        echo (json_encode($datos));
        
        break;
        
    case 'getFfSeguimiento':
        
        $date = new DateClass;
        
        session_start();
        $cli = $_SESSION['seguimiento_cliente']['cli'];
        $mes = $date->fromToFormat($_SESSION['seguimiento_cliente']['mes'], 'd/m/Y', 'Y-m-d');
        session_write_close();
        
        $Conn = new Conn('local', 'enertrade');
        $datos = $Conn->getArray("SELECT
                                    ID,
                                    COMERCIALIZADORA,
                                    DATE_FORMAT(FECHA_RECEPCION, '%d/%m/%Y'),
                                    DATE_FORMAT(FECHA_CARGA, '%d/%m/%Y'),
                                    DATE_FORMAT(FECHA_VALIDACION, '%d/%m/%Y'),
                                    FACTURAS_CARGADAS,
                                    ABONOS_CARGADOS,
                                    COMENTARIOS,
                                    RUTA,
                                    LINK_FF
                                FROM seguimiento_cliente_ff
                                WHERE CLIENTE='$cli'
                                AND MES='$mes'
                                    ", true);
        
        if (!isset($datos) || empty($datos) || !$datos){
            $datos[] = array('', '', '', '', '', '', '', '', '', '', '');
        } else {
            foreach ($datos as $num_row=>$row){
                $row[] = '<a class="btn btn-default btn-sm btn-warning fa fa-edit" href="modffseguimiento.php?id='.$row['ID'].'"></a><button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delFfSeguimiento($(this).val())"></button>';
                $datos[$num_row] = array_values($row);
            }
            
        }
        //unset($datos);
        echo (json_encode($datos));
        
        break;
        
    case 'getInformeSeguimiento':
        
        $date = new DateClass;
        
        session_start();
        $cli = $_SESSION['seguimiento_cliente']['cli'];
        $mes = $date->fromToFormat($_SESSION['seguimiento_cliente']['mes'], 'd/m/Y', 'Y-m-d');
        session_write_close();
        
        $Conn = new Conn('local', 'enertrade');
        $datos = $Conn->getArray("SELECT
                                    ID,
                                    INFORME,
                                    DATE_FORMAT(REDACTADO, '%d/%m/%Y'),
                                    DATE_FORMAT(ENVIADO, '%d/%m/%Y'),
                                    COMENTARIOS,
                                    RUTA,
                                    LINK_INFORME
                                FROM seguimiento_cliente_informes
                                WHERE CLIENTE='$cli'
                                AND MES='$mes'
                                    ", true);
        
        if (!isset($datos) || empty($datos) || !$datos){
            $datos[] = array('', '', '', '', '', '', '', '');
        } else {
            foreach ($datos as $num_row=>$row){
                $row[] = '<a class="btn btn-default btn-sm btn-warning fa fa-edit" href="modinformeseguimiento.php?id='.$row['ID'].'"></a><button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delInformeSeguimiento($(this).val())"></button>';
                $datos[$num_row] = array_values($row);
            }
            
        }
        
        echo (json_encode($datos));
        
        break;
        
    case 'getFile':
        
        $url      = $_GET['url'];
        
        $Carpetas = new Carpetas;
        $Carpetas->dwdFileFromUrl($url);
        unset($Carpetas);
        
        break;
        
    case 'delFfSeguimiento':
    case 'delInformeSeguimiento':
    case 'delInformeReclamacionesSeguimiento':
        
        switch ($_GET['action']){
            case 'delFfSeguimiento':                   $table = 'seguimiento_cliente_ff';                    break;
            case 'delInformeSeguimiento':              $table = 'seguimiento_cliente_informes';              break;
            case 'delInformeReclamacionesSeguimiento': $table = 'seguimiento_cliente_informe_reclamaciones'; break;
        }
        
        $id = $_POST['id'];
        
        $Conn = new Conn('local', 'enertrade');
        $dir = $Conn->oneData("SELECT DIR FROM $table WHERE ID=$id");
        $dir = explode(',', $dir);
        foreach ($dir as $key=>$value){unlink($value);}
        
        $Conn->Query("DELETE FROM $table WHERE ID=$id");
        unset($Conn, $id, $dir);
        
        break;
        
    case 'saveSeguimientoClienteVars':
        
        session_start();
        $_SESSION['seguimiento_cliente']['cli'] = $_POST['cli'];
        $_SESSION['seguimiento_cliente']['mes'] = $_POST['mes'];
        $_SESSION['seguimiento_cliente']['empleado'] = $_POST['empleado'];
        session_write_close();
        
        break;
        
//TAREAS   ---------------------------------------------
    
    case 'save_empleado':
        
        session_start();
        $_SESSION['info']['tareas']['empleado'] = $_POST['empleado'];
        session_write_close();
        
        break;
        
    case 'dwd_tareas_gestionadas':
    case 'dwd_tareas_en_curso':
        
        switch ($usuario){
            case 'vmrodriguez@enertrade.es':
            case 'mmontero@enertrade.es':
            case 'slizarrlade@enertrade.es':
                $end_str = '';
                break;
                
            default:
                $end_str = "WHERE EMPLEADO='$usuario'";
                break;
        }
        
        switch ($_GET['action']){
            case 'dwd_tareas_gestionadas':
                $end_str .= ($end_str=='') ? 'WHERE PROGRESO=100' : ' AND PROGRESO=100';
                break;
            case 'dwd_tareas_en_curso':
                $end_str .= ($end_str=='') ? 'WHERE PROGRESO<100' : ' AND PROGRESO<100';
                break;
        }
        
        $Conn = new Conn('local', 'enertrade');
        $strSQL = "SELECT
                        NOMBRE,
                        EMPLEADO,
                        DATE_FORMAT(FECHA_CADUCIDAD, '%d/%m/%Y') CADUCIDAD,
                        DATE_FORMAT(FECHA_APERTURA, '%d/%m/%Y') APERTURA,
                        DATE_FORMAT(FECHA_CIERRE, '%d/%m/%Y') CIERRE,
                        PRIORIDAD,
                        DESCRIPCION,
                        COMENTARIOS,
                        PROGRESO,
                        ASIGNADO_POR
                    FROM tareas
                    $end_str";
        
        $tareas = $Conn->getArray($strSQL, true);
        unset($Conn);
        
        if (isset($tareas) && !empty($tareas)){
            $SprdSht = new SprdSht;
            $SprdSht->nuevo();
            $SprdSht->putArray($tareas, true);
            $SprdSht->setColumnsAutoWidth();
            unset($tareas);

            $SprdSht->directDownload('TAREAS.xlsx');
            unset($SprdSht);
        }
        
        header('Location: tareas.php');
        
        break;
    
    case 'setTareaAsDone':
        
        $id = $_POST['id'];
        $Conn = new Conn('local', 'enertrade');
        
        $row = $Conn->oneRow("SELECT * FROM tareas WHERE ID=$id");
        $nombre      = $row['NOMBRE'];
        $empleado    = $row['EMPLEADO'];
        $descripcion = $row['DESCRIPCION'];
        
        switch ($row['ASIGNADO_POR']){
            case 'vmrodriguez@enertrade.es':
            case 'mmontero@enertrade.es':
            case 'slizarrlade@enertrade.es':
                if ($row['ASIGNADO_POR']==$usuario){break;}
                
                $nombre      = $row['NOMBRE'];
                $descripcion = $row['DESCRIPCION'];
                
                $A = array($row['ASIGNADO_POR']);
                $SUJETO = "TAREA TERMINADA: $nombre";
                $CUERPO = "Hola,<br><br>
                
                He acabado ".'<a href="http://192.168.0.252/Enertrade/pages/Info/tareas.php">esta</a>'." tarea:<br>
                $nombre<br>
                $descripcion<br><br>
                
                Un saludo
                ";
                
                mailDeA($SUJETO, $CUERPO, $A);
                break;
        }
        
        $fecha_cierre = date('Y-m-d');
        $Conn->Query("UPDATE tareas SET PROGRESO=100, FECHA_CIERRE='$fecha_cierre' WHERE ID=$id");
        
        break;
        
    case 'reactivateTarea':
        
        $id = $_POST['id'];
        $Conn = new Conn('local', 'enertrade');
        
        $row = $Conn->oneRow("SELECT * FROM tareas WHERE ID=$id");
        $nombre      = $row['NOMBRE'];
        $empleado    = $row['EMPLEADO'];
        $descripcion = $row['DESCRIPCION'];
        
        switch ($usuario){
            case 'vmrodriguez@enertrade.es':
            case 'mmontero@enertrade.es':
            case 'slizarrlade@enertrade.es':
                if ($empleado==$usuario){break;}
                
                $A = array($empleado);
                $SUJETO = "TAREA REACTIVADA: $nombre";
                $CUERPO = "Hola,<br><br>
                
                He reactivado ".'<a href="http://192.168.0.252/Enertrade/pages/Info/tareas.php">esta</a>'." tarea:<br>
                $nombre<br>
                $descripcion<br><br>
                
                Quedo a disposición para cualquier aclaración
                ";
                
                mailDeA($SUJETO, $CUERPO, $A);
                break;
        }
        
        $Conn->Query("UPDATE tareas SET PROGRESO=0, FECHA_CIERRE=NULL WHERE ID=$id");
        
        break;
        
    case 'delTarea':
        $id = $_POST['id'];
        $Conn = new Conn('local', 'enertrade');
        
        $row = $Conn->oneRow("SELECT * FROM tareas WHERE ID=$id");
        $nombre      = $row['NOMBRE'];
        $empleado    = $row['EMPLEADO'];
        $descripcion = $row['DESCRIPCION'];
        
        $Conn->Query("DELETE FROM tareas WHERE ID=$id");
        
        switch ($usuario){
            case 'vmrodriguez@enertrade.es':
            case 'mmontero@enertrade.es':
            case 'slizarrlade@enertrade.es':
                if ($empleado==$usuario){break;}
                
                $A = array($empleado);
                $SUJETO = "TAREA ELIMINADA: $nombre";
                $CUERPO = "Hola,<br><br>
                
                Ya no tienes que realizar ".'<a href="http://192.168.0.252/Enertrade/pages/Info/tareas.php">esta</a>'." tarea:<br>
                $nombre<br>
                $descripcion<br><br>
                
                Un saludo
                ";
                
                mailDeA($SUJETO, $CUERPO, $A);
                break;
        }
        
        $dir = "//192.168.0.250/NAS/TAREAS/$id";
        $Carpetas = new Carpetas;
        $Carpetas->delDir($dir);
        unset($Carpetas, $dir);
        
        break;
        
    case 'getTareasNoGestionadas':
    case 'getTareasGestionadas':
        
        switch ($_GET['action']){
            case 'getTareasNoGestionadas':
                $progreso = '<100';
                $cierre = '';
                break;
            case 'getTareasGestionadas':
                $progreso = '=100';
                $cierre = "DATE_FORMAT(FECHA_CIERRE, '%d/%m/%Y'),";
                break;
        }
        
        switch ($usuario){
                case 'vmrodriguez@enertrade.es':
                case 'mmontero@enertrade.es':
                case 'slizarralde@enertrade.es':
                    $usuarios = '';
                    break;
                default:
                    $usuarios = " AND EMPLEADO='$usuario'";
                    break;
        }
        
        $Conn = new Conn('local', 'enertrade');
        $datos = $Conn->getArray("SELECT
                                    ID,
                                    NOMBRE,
                                    EMPLEADO,
                                    ASIGNADO_POR,
                                    DATE_FORMAT(FECHA_APERTURA, '%d/%m/%Y'),
                                    $cierre
                                    DATE_FORMAT(FECHA_CADUCIDAD, '%d/%m/%Y'),
                                    PRIORIDAD,
                                    DESCRIPCION,
                                    COMENTARIOS,
                                    LINKS,
                                    PROGRESO
                                FROM tareas
                                WHERE PROGRESO$progreso
                                $usuarios
                                ORDER BY FECHA_APERTURA DESC, PRIORIDAD DESC
                                ", true);
        
        if (!isset($datos) || empty($datos) || !$datos){
            switch ($_GET['action']){
                case 'getTareasNoGestionadas':
                    $datos[] = array('', '', '', '', '', '', '', '', '', '', '', '');
                    break;
                case 'getTareasGestionadas':
                    $datos[] = array('', '', '', '', '', '', '', '', '', '', '', '', '');
                    break;
            }
        } else {
            
            foreach ($datos as $num_row=>$row){
                
                switch ($progreso){
                    case '<100':
                        
                        switch (true){
                                
                            case ($usuario=='vmrodriguez@enertrade.es'):
                            case ($usuario=='mmontero@enertrade.es'):
                            case ($usuario=='slizarralde@enertrade.es'):
                            case ($row['ASIGNADO_POR']==$usuario):
                                $del = '<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delTarea($(this).val())"></button>';
                                break;
                                
                            default:
                                $del = '';
                                break;
                        }
                        
                        $row[] = '<button class="btn btn-default btn-sm btn-success fa fa-check" value='.$row['ID'].' onclick="setTareaAsDone($(this).val())"></button><a class="btn btn-default btn-sm btn-warning fa fa-edit" href="modtarea.php?id='.$row['ID'].'"></a><a class="btn btn-default btn-sm btn-info fa fa-copy" href="modtarea.php?id='.$row['ID'].'&action=duplicate"></a>'.$del;
                        
                        break;
                        
                    case '=100':
                        
                        switch ($usuario){
                            case 'vmrodriguez@enertrade.es':
                            case 'mmontero@enertrade.es':
                            case 'slizarralde@enertrade.es':
                            case ($row['ASIGNADO_POR']==$usuario):
                                $reactivate = '<button class="btn btn-default btn-sm btn-primary fa fa-refresh" value='.$row['ID'].' onclick="reactivateTarea($(this).val())"></button>';
                                break;
                            default:
                                $reactivate = '';
                                break;
                        }
                        
                        $row[] = $reactivate.'<a class="btn btn-default btn-sm btn-info fa fa-copy" href="modtarea.php?id='.$row['ID'].'&action=duplicate"></a>';
                        
                        break;
                }
                
                $porcentaje = $row['PROGRESO'];
                switch (true){
                    case ($porcentaje<=30):
                        $color = 'danger';
                        break;
                    case ($porcentaje>33 && $porcentaje<=66):
                        $color = 'warning';
                        break;
                    case ($porcentaje>66 && $porcentaje<=99):
                        $color = 'info';
                        break;
                    case ($porcentaje==100):
                        $color = 'success';
                        break;
                }
                $row['PROGRESO'] = '<div class="progress active" id="progressbarview"><div class="progress-bar progress-bar-'.$color.' progress-bar-striped" role="progressbar" aria-valuenow="'.$porcentaje.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$porcentaje.'%"></div></div>';
                
                $datos[$num_row] = array_values($row);
            }
            
        }
        
        echo (json_encode($datos));
        break;
}

unset($Conn);
?>