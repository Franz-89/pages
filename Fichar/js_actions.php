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
	//CLAVES
	case 'getFichadas':
		
		if ($usuario == 'mmontero@enertrade.es' || $usuario == 'vmrodriguez@enertrade.es'){
			
			$datos = $Conn->getArray("SELECT * FROM fichadas");
			
			foreach ($datos as $num_row=>$row){
				$row[] = '<a class="btn btn-default btn-sm btn-warning fa fa-edit" href="modfichadas.php?id='.$row['ID'].'"></a><button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delFichada($(this).val())"></button>';
				$final[] = array_values($row);
			}
			
		} else {
			
			$datos = $Conn->getArray("SELECT * FROM fichadas WHERE USUARIO='$usuario'");
			
			//Si autoriado o no
			$autorizado = $Conn->getArray("SELECT mod_fichada FROM usuarios WHERE email='$usuario'");
			if ($autorizado[0]['mod_fichada']){
				foreach ($datos as $num_row=>$row){
					$row[] = '<a class="btn btn-default btn-sm btn-warning fa fa-edit" href="modfichadas.php?id='.$row['ID'].'"></a>';
					$final[] = array_values($row);
				}
			} else {
				foreach ($datos as $num_row=>$row){
					$row[] = '';
					$final[] = array_values($row);
				}
			}
		}
		unset($datos);
		echo json_encode($final);
		
		break;
		
	case 'entrada':
		
		$Conn->Query("INSERT INTO fichadas (USUARIO, IN_OUT, HORA) VALUES ('$usuario', 'ENTRADA', NOW())");
		break;
		
	case 'salida':
		
		$Conn->Query("INSERT INTO fichadas (USUARIO, IN_OUT, HORA) VALUES ('$usuario', 'SALIDA', NOW())");
		break;
		
	case 'delFichada':
		
		$ID = $_POST['id'];
		$Conn->Query("DELETE FROM fichadas WHERE ID=$ID");
		break;
}

unset($Conn);
?>