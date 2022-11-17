<?php
require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

session_start();
if (isset($_SESSION['email'])){
	$usuario = $_SESSION['email'];
} else {
	header ("Location: /Enertrade/index.php");
}
session_write_close();

if (!isset($_GET['action'])){goto POST;}

$Conn = new Conn('local', 'enertrade');

switch ($_GET['action']){
		
	case 'getPermisos':
		
		$datos = $Conn->getArray("SELECT ID, email, mod_fichada, horario_desde, horario_hasta FROM usuarios");
		
		foreach ($datos as $num_row=>$row) {
			$checked = ($row['mod_fichada']==true) ? "checked" : "";
			if ($row['email'] == "mmontero@enertrade.es"){$checked = 'disabled checked';}
			$row['mod_fichada'] = '<div><input type="checkbox" class="flat-red" '.$checked.' value="'.$row['ID'].'" onclick="setPermiso($(this).val())"></div>';
			$final[] = array_values($row);
		}
		unset($datos);
		
		echo json_encode($final);
		
		break;
		
	case 'setPermiso':
		
		$timestamp = getMicrotimeString();
		$ID = $_POST['id'];
		
		$strSQL = "SELECT mod_fichada FROM usuarios WHERE ID=$ID";
		$datos = $Conn->getArray("SELECT mod_fichada FROM usuarios WHERE ID=$ID");
		
		if ($datos[0]['mod_fichada']){
			$strSQL = "UPDATE usuarios SET mod_fichada='0' WHERE ID=$ID";
		} else {
			$strSQL 	= "UPDATE usuarios SET mod_fichada='1' WHERE ID=$ID";
			$evento 	= "CREATE EVENT IF NOT EXISTS mod_fichada$timestamp
							ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 10 MINUTE
							DO
							  UPDATE usuarios SET mod_fichada='0' WHERE ID=$ID";
		}
		
		$Conn->Query($strSQL);
		if (isset($evento)){$Conn->Query($evento);}
		
		echo $ID;
		break;
        
    case 'setHorario':
		
		$usuario = $_POST['usuario'];
		$horario_desde = $_POST['horario_desde'];
		$horario_hasta = $_POST['horario_hasta'];
		
        $date = new DateClass;
        $horario_desde = $date->formatTime($horario_desde);
        $horario_hasta = $date->formatTime($horario_hasta);
        
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("UPDATE usuarios SET horario_desde='$horario_desde', horario_hasta='$horario_hasta'  WHERE email='$usuario'");
        
		break;
        
    case 'getPlantillasConversionFf':
        
        $Conn = new Conn('local', 'enertrade');
        $datos = $Conn->getArray("SELECT ID, fichero FROM plantillas_conversion_ff", true);
        unset($Conn);
        
        if (!isset($datos) || empty($datos)){
            $final[] = array('', '', '');
        } else {
            foreach ($datos as $num_row=>$row){
                $row[] = '<button class="btn btn-default btn-sm btn-danger fa fa-remove" id="del" value='.$row['ID'].' onclick="delPlantilla($(this).val())"></button>';
                $final[] = array_values($row);
            }
            unset($datos);
        }
        
        echo json_encode($final);
        
        break;
        
    case 'delPlantilla':
        
        $id = $_POST['id'];
        $Conn = new Conn('local', 'enertrade');
        
        $fichero_plantilla = $Conn->oneData("SELECT fichero FROM plantillas_conversion_ff WHERE ID='$id'");
        $Conn->Query("DELETE FROM plantillas_conversion_ff WHERE ID='$id'");
        
        $dir = "//192.168.0.250/NAS/ENERTRADE/PLANTILLAS CONVERSION FF/$id";
        $Carpetas = new Carpetas;
        $Carpetas->delDir($dir);
        unset($Carpetas, $dir, $Conn);
        
        break;
        
    case 'checkInfoPlantillaFF':
        
        $id = $_POST['id'];
        $row = $Conn->oneRow("SELECT * FROM plantillas_conversion_ff WHERE ID='$id'", true);
        foreach ($row as $key=>$value){
            $key = str_replace(array('(', ')', '%'), array('\(', '\)', '\%'), $key);
            $final[$key] = $value;
        }
        echo json_encode($final);
        
        break;
}

die;


POST:

switch ($_POST['action']){
        
    case 'add_update_plantilla_elaboracion_ff':
        
        $Conn = new Conn('local', 'enertrade');
        
        foreach ($_POST as $key=>$value){
            switch ($key){
                case 'action':
                case 'fichero':
                case 'MAX_FILE_SIZE':
                    continue(2);
                
                case 'ID':
                    $value = str_replace(' ', '_', $value);
                    $id = $value;
                    
                default:
                    $value = $Conn->realEscape($value);
                    $cols[] = $key;
                    $values[] = "'$value'";
                    $on_duplicate_update[] = "`$key`='$value'";
                    
                    break;
            }
        }
        
        $cols = "`".implode("`,`", $cols)."`";
        $values = implode(',', $values);
        $on_duplicate_update = implode(',', $on_duplicate_update);
        
        $Conn->Query("INSERT INTO plantillas_conversion_ff ($cols) VALUES ($values) ON DUPLICATE KEY UPDATE $on_duplicate_update");
        $Carpetas = new Carpetas;
        
        $dir      = '//192.168.0.250/NAS/ENERTRADE';
        $Carpetas->createIfNotExists($dir);
        $dir .= "/PLANTILLAS CONVERSION FF";
        $Carpetas->createIfNotExists($dir);
        $dir .= "/$id";
        $Carpetas->createIfNotExists($dir);
        
        $strLinks = array();
        
        //Sube los ficheros
        $filenum = 0;
        if (isset($_FILES['fichero']['tmp_name'][0])){
            foreach($_FILES['fichero']['tmp_name'] as $file){
            
                $filename = $_FILES['fichero']['name'][$filenum];
                $extension = $Carpetas->getExtensionFromFilename($filename);
                if (is_uploaded_file($file)){
                    $Carpetas->uploadFile($file, "$dir/$id.$extension", true);
                    $strLinks = '<a href="js_actions.php?action=getFile&url='."$dir/$id.$extension".'">'."$id.$extension".'</a>';
                }
                ++$filenum;
            }
        }
        
        $Conn->Query("UPDATE plantillas_conversion_ff SET fichero='$strLinks' WHERE ID='$id'");
        unset($Conn);
        
        header ('Location: plantillas_ff.php');
        
        break;
}


unset($Conn)
?>