<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

session_start();
if (isset($_SESSION['email'])){
	$usuario = $_SESSION['email'];
} else {
	header ("Location: /Enertrade/index.php");
}
session_write_close();

switch ($_POST['action']){
        
    //SEGUIMIENTO CLIENTE ------------------------------------------------------
    case 'upload_ff':
    case 'upload_informe':
        
        $date = new DateClass;
        
        session_start();
        $cli = $_SESSION['seguimiento_cliente']['cli'];
        $mes = $date->fromToFormat($_SESSION['seguimiento_cliente']['mes'], 'd/m/Y', 'Y-m-d');
        session_write_close();
        
        switch ($_POST['action']){
            case 'upload_ff':
                $comercializadora    = $_POST['comercializadora'];
                $fecha_recepcion_ff  = $date->fromToFormat($_POST['fecha_recepcion_ff'], 'd/m/Y', 'Y-m-d');
                $fecha_carga_ff      = (isset($_POST['fecha_carga_ff']) & !empty($_POST['fecha_carga_ff'])) ? $date->fromToFormat($_POST['fecha_carga_ff'], 'd/m/Y', 'Y-m-d') : '';
                $fecha_validacion_ff = (isset($_POST['fecha_validacion_ff']) & !empty($_POST['fecha_validacion_ff'])) ? $date->fromToFormat($_POST['fecha_validacion_ff'], 'd/m/Y', 'Y-m-d') : '';
                $fras_cargadas       = $_POST['fras_cargadas'];
                $abonos_cargados     = $_POST['abonos_cargados'];
                $comentarios         = $_POST['comentarios_ff'];
                $ruta                = $_POST['ruta_ff'];
                $checkCarpeta        = 'CARGA BBDD';
                
                if ($abonos_cargados>$fras_cargadas){
                    echo "Error. Las facturas cargadas ($fras_cargadas) no pueden ser menores que los abonos cargados ($abonos_cargados).";
                    die;
                }
                
                break;
                
            case 'upload_informe':
                $informe             = $_POST['informe'];
                $redactado           = $date->fromToFormat($_POST['fecha_redactado_informe'], 'd/m/Y', 'Y-m-d');
                $enviado             = $date->fromToFormat($_POST['fecha_envio_informe'], 'd/m/Y', 'Y-m-d');
                $comentarios         = $_POST['comentarios_informe'];
                $ruta                = $_POST['ruta_informe'];
                $checkCarpeta        = 'INFORMES';
                break;
        }
        
        $Carpetas = new Carpetas;
        
        //Crea la carpeta en la NAS si no existe
        $date->stringToDate($mes);
        $mes_puro = $date->format('n');
        $ano      = $date->format('Y');
        
        $dir      = $Carpetas->checkInformesMensuales($cli, $checkCarpeta, $ano, $mes_puro);
        $strLinks = array();
        
        //Sube los ficheros de facturación
        $filenum = 0;
        foreach($_FILES['fichero']['tmp_name'] as $file){
            
            $filename = $_FILES['fichero']['name'][$filenum];
			if (is_uploaded_file($file)){
                $Carpetas->uploadFile($file, "$dir/$filename", true);
                $strLinks[] = '<a href="js_actions.php?action=getFile&url='."$dir/$filename".'">'.$filename.'</a>';
                $strDirFilename[] = "$dir/$filename";
            }
            ++$filenum;
        }
        
        //Crea los links
        $strLinks       = (!empty($strLinks)) ? implode(', ', $strLinks) : '';
        $strDirFilename = (isset($strDirFilename)) ? implode(',', $strDirFilename) : implode(',', array("$dir/$filename"));
        
        //Sube los datos a la BBDD
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("SET GLOBAL sql_mode = 'NO_BACKSLASH_ESCAPES'");
        
        switch ($_POST['action']){
            case 'upload_ff':
                $Conn->Query("INSERT INTO seguimiento_cliente_ff (CLIENTE, COMERCIALIZADORA, FECHA_RECEPCION, FECHA_CARGA, FECHA_VALIDACION, FACTURAS_CARGADAS, ABONOS_CARGADOS, COMENTARIOS, RUTA, LINK_FF, MES, DIR) VALUES ('$cli', '$comercializadora', '$fecha_recepcion_ff', '$fecha_carga_ff', '$fecha_validacion_ff', '$fras_cargadas', '$abonos_cargados', '$comentarios', '$ruta', '$strLinks', '$mes', '$strDirFilename')");
                unset($comercializadora, $fecha_recepcion_ff, $fecha_carga_ff, $fecha_validacion_ff);
                
                break;
                
            case 'upload_informe':
                $Conn->Query("INSERT INTO seguimiento_cliente_informes (CLIENTE, INFORME, REDACTADO, ENVIADO, COMENTARIOS, RUTA, LINK_INFORME, MES, DIR) VALUES ('$cli', '$informe', '$redactado', '$enviado', '$comentarios', '$ruta', '$strLinks', '$mes', '$strDirFilename')");
                
                unset($informe, $redactado, $enviado);
                
                break;
        }
        unset($Conn, $cli, $comentarios, $ruta, $strLinks, $mes, $mes_puro, $ano, $date, $dir, $Carpetas, $strDirFilename, $checkCarpeta);
        
        header ('Location: seguimiento_cliente.php');
        
        break;
        
    case 'mod_ff_seguimiento':
        
        $id               = $_POST['id'];
        $comentarios      = $_POST['comentarios'];
        $fras_cargadas    = $_POST['fras_cargadas'];
        $abonos_cargados  = $_POST['abonos_cargados'];
        
        if ($abonos_cargados>$fras_cargadas){
            echo "Error. Las facturas cargadas ($fras_cargadas) no pueden ser menores que los abonos cargados ($abonos_cargados).";
            die;
        }
        
        $date             = new DateClass;
        $fecha_carga      = $date->fromToFormat($_POST['fecha_carga_ff'], 'd/m/Y', 'Y-m-d');
        $fecha_validacion = $date->fromToFormat($_POST['fecha_validacion_ff'], 'd/m/Y', 'Y-m-d');
        
        unset($date);
        
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("UPDATE seguimiento_cliente_ff SET FECHA_CARGA='$fecha_carga', FECHA_VALIDACION='$fecha_validacion', FACTURAS_CARGADAS='$fras_cargadas', ABONOS_CARGADOS='$abonos_cargados', COMENTARIOS='$comentarios' WHERE ID=$id");
        unset($Conn);
        
        header ('Location: seguimiento_cliente.php');
        
        break;
        
    case 'mod_informe_seguimiento':
        
        $id               = $_POST['id'];
        $comentarios      = $_POST['comentarios'];
        
        $date             = new DateClass;
        $fecha_envio      = $date->fromToFormat($_POST['fecha_envio_informe'], 'd/m/Y', 'Y-m-d');
        unset($date);
        
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("UPDATE seguimiento_cliente_informes SET ENVIADO='$fecha_envio', COMENTARIOS='$comentarios' WHERE ID=$id");
        unset($Conn);
        
        header ('Location: seguimiento_cliente.php');
        
        break;
        
//TAREAS ------------------------------------------------------
        
    case 'add_tarea':
        
        $nombre             = $_POST['nombre'];
        $empleado           = $_POST['empleado'];
        $fecha_caducidad    = $_POST['fecha_caducidad'];
        $prioridad          = $_POST['prioridad'];
        $descripcion        = $_POST['descripcion'];
        $comentarios        = $_POST['comentarios'];
        
        $date = new DateClass;
        $fecha_caducidad = $date->fromToFormat($fecha_caducidad, 'd/m/Y', 'Y-m-d');
        unset($date);
        
        $fecha_apertura = date('Y-m-d');
        $Conn = new Conn('local', 'enertrade');
        $Conn->Query("INSERT INTO tareas (NOMBRE, EMPLEADO, FECHA_CADUCIDAD, FECHA_APERTURA, PRIORIDAD, DESCRIPCION, COMENTARIOS, PROGRESO, ASIGNADO_POR) VALUES ('$nombre', '$empleado', '$fecha_caducidad', '$fecha_apertura', $prioridad, '$descripcion', '$comentarios', 0, '$usuario')");
        
        switch ($usuario){
            case 'vmrodriguez@enertrade.es':
            case 'mmontero@enertrade.es':
            case 'slizarrlade@enertrade.es':
                if ($empleado==$usuario){break;}
                
                $A = array($empleado);
                $SUJETO = "NUEVA TAREA: $nombre";
                $CUERPO = "Hola,<br><br>
                
                Te he asignado una ".'<a href="http://192.168.0.252/Enertrade/pages/Info/tareas.php">nueva tarea</a>'.":<br>
                $nombre<br>
                $descripcion<br><br>
                
                Quedo a disposición para cualquier aclaración
                ";
                
                mailDeA($SUJETO, $CUERPO, $A);
                break;
        }
        
        $id = $Conn->lastId();
        
        $Carpetas = new Carpetas;
        
        $dir      = '//192.168.0.250/NAS/TAREAS';
        $Carpetas->createIfNotExists($dir);
        $dir .= "/$id";
        $Carpetas->createIfNotExists($dir);
        
        $strLinks = array();
        
        //Sube los ficheros
        $filenum = 0;
        if (isset($_FILES['fichero']['tmp_name'][0])){
            foreach($_FILES['fichero']['tmp_name'] as $file){
            
                $filename = $_FILES['fichero']['name'][$filenum];
                if (is_uploaded_file($file)){
                    $Carpetas->uploadFile($file, "$dir/$filename", true);
                    $strLinks[] = '<a href="js_actions.php?action=getFile&url='."$dir/$filename".'">'.$filename.'</a>';
                }
                ++$filenum;
            }
        }
        
        //Crea los links
        $strLinks       = (!empty($strLinks)) ? implode(', ', $strLinks) : '';
        
        $Conn->Query("UPDATE tareas SET LINKS='$strLinks' WHERE ID=$id");
        header ('Location: tareas.php');
        
        break;
        
    case 'mod_tarea':
        
        $id               = $_POST['id'];
        $fecha_caducidad  = $_POST['fecha_caducidad'];
        $prioridad        = $_POST['prioridad'];
        $descripcion      = $_POST['descripcion'];
        $comentarios      = $_POST['comentarios'];
        $progreso         = $_POST['progreso'];
        
        $date             = new DateClass;
        $fecha_caducidad  = $date->fromToFormat($_POST['fecha_caducidad'], 'd/m/Y', 'Y-m-d');
        unset($date);
        
        $Conn = new Conn('local', 'enertrade');
        
        $row = $Conn->oneRow("SELECT * FROM tareas WHERE ID=$id");
        $nombre = $row['NOMBRE'];
        $empleado = $row['EMPLEADO'];
        
        switch ($usuario){
            case 'vmrodriguez@enertrade.es':
            case 'mmontero@enertrade.es':
            case 'slizarrlade@enertrade.es':
                if ($empleado==$usuario){break;}
                
                $A = array($empleado);
                $SUJETO = "TAREA MODIFICADA: $nombre";
                $CUERPO = "Hola,<br><br>
                
                He modificado ".'<a href="http://192.168.0.252/Enertrade/pages/Info/tareas.php">esta</a>'." tarea:<br>
                $nombre<br>
                $descripcion<br><br>
                
                Quedo a disposición para cualquier aclaración
                ";
                
                mailDeA($SUJETO, $CUERPO, $A);
                break;
        }
        
        $Conn->Query("UPDATE tareas SET FECHA_CADUCIDAD='$fecha_caducidad', PRIORIDAD='$prioridad', DESCRIPCION='$descripcion', COMENTARIOS='$comentarios', PROGRESO=$progreso WHERE ID=$id");
        unset($Conn);
        
        header ('Location: tareas.php');
        
        break;
        
    case 'duplicate_tarea':
        
        $id               = $_POST['id'];
        $fecha_caducidad  = $_POST['fecha_caducidad'];
        
        $date             = new DateClass;
        $fecha_caducidad  = $date->fromToFormat($_POST['fecha_caducidad'], 'd/m/Y', 'Y-m-d');
        unset($date);
        
        $Conn = new Conn('local', 'enertrade');
        $row = $Conn->oneRow("SELECT * FROM tareas WHERE ID=$id");
        
        $nombre = $row['NOMBRE'];
        $empleado = $row['EMPLEADO'];
        $prioridad = $row['PRIORIDAD'];
        $descripcion = $row['DESCRIPCION'];
        $comentarios = $row['COMENTARIOS'];
        $str_links = $row['LINKS'];
        
        switch ($usuario){
            case 'vmrodriguez@enertrade.es':
            case 'mmontero@enertrade.es':
            case 'slizarrlade@enertrade.es':
                if ($empleado==$usuario){break;}
                
                $A = array($empleado);
                $SUJETO = "TAREA DUPLICADA: $nombre";
                $CUERPO = "Hola,<br><br>
                
                He duplicado ".'<a href="http://192.168.0.252/Enertrade/pages/Info/tareas.php">esta</a>'." tarea:<br>
                $nombre<br>
                $descripcion<br><br>
                
                Quedo a disposición para cualquier aclaración
                ";
                
                mailDeA($SUJETO, $CUERPO, $A);
                break;
        }
        
        $fecha_apertura = date('Y-m-d');
        $Conn->Query("INSERT INTO tareas (NOMBRE, EMPLEADO, FECHA_CADUCIDAD, FECHA_APERTURA, PRIORIDAD, DESCRIPCION, COMENTARIOS, PROGRESO, ASIGNADO_POR) VALUES ('$nombre', '$empleado', '$fecha_caducidad', '$fecha_apertura', $prioridad, '$descripcion', '$comentarios', 0, '$usuario')");
        
        $last_id = $Conn->lastId();
        
        
        
        $Carpetas = new Carpetas;
        
        $dir      = '//192.168.0.250/NAS/TAREAS';
        $Carpetas->createIfNotExists($dir);
        $dir .= "/$last_id";
        $Carpetas->createIfNotExists($dir);
        
        $dir      = '//192.168.0.250/NAS/TAREAS';
        $files = glob("$dir/$id/*");

        foreach($files as $file){
            
            $filename = $Carpetas->getFilenameFromAddress($file);
            if(is_file($file)){
                copy($file, "$dir/$last_id/$filename");
            }
        }
        
        $str_links = str_replace("/$id/", "/$last_id/", $str_links);
        $Conn->Query("UPDATE tareas SET LINKS='$str_links' WHERE ID=$last_id");
        
        unset($Conn, $dir, $files, $id, $Carpetas, $last_id);
        
        header ('Location: tareas.php');
        
        break;
        
}