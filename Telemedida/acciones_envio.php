<?php
function create_path($path){
	$puntos = str_replace('C:\xampp\htdocs\Enertrade', "", __DIR__);
	$arr = explode(DIRECTORY_SEPARATOR ,$puntos);
	foreach ($arr as $dato){$puntos = str_replace($dato, "..", $puntos);}
	if (substr($puntos, 0, 1)==DIRECTORY_SEPARATOR){
		$puntos = substr($puntos, 1, strlen($puntos)-1);
	}
	$puntos = str_replace(DIRECTORY_SEPARATOR, "/", $puntos);
	if ($puntos!=""){$puntos = $puntos."/";}
	return $puntos.$path;
}

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$sql_table 	= $_GET['table'];
$action 	= $_GET['action'];

if (isset($_POST['id']))			{$id 			= $_POST['id'];} 			else {$id 				= "";}
if (isset($_POST['grupo_envio']))	{$grupo_envio 	= $_POST['grupo_envio'];} 	else {$grupo_envio 		= "";}
if (isset($_POST['prioridad']))		{$prioridad 	= $_POST['prioridad'];} 	else {$prioridad 		= 1;}
if (isset($_POST['encargado']))		{$encargado 	= $_POST['encargado'];} 	else {$encargado 		= "";}
if (isset($_POST['a']))				{$a 			= $_POST['a'];} 			else {$a 				= "";}
if (isset($_POST['copia']))			{$copia 		= $_POST['copia'];} 		else {$copia 			= "";}
if (isset($_POST['observaciones']))	{$observaciones = $_POST['observaciones'];} else {$observaciones 	= "";}


if (empty($grupo_envio) || empty($a)) goto Campos_vacios;

$conn 	= connect_server("local", 'enertrade');

switch ($action){
	case "add":
		
		//Comprueba si el grupo ya existe
		$duplicado = get_registry($conn, $sql_table, 'GRUPO_ENVIO', $grupo_envio);
		if ($duplicado){
			header ("Location: /Enertrade/pages/Telemedida/mod$sql_table.php?error=duplicado&id=$id&grupo_envio=$grupo_envio&prioridad=$prioridad&encargado=$encargado&a=$a&copia=$copia&observaciones=$observaciones");
			exit();
		}
		
		$strSQL = "INSERT INTO $sql_table (GRUPO_ENVIO, PRIORIDAD, A, COPIA, ENCARGADO, OBSERVACIONES) VALUES('$grupo_envio', '$prioridad', '$a', '$copia', '$encargado', '$observaciones')";
		
		break;

	case "mod":
		$strSQL = "UPDATE $sql_table SET GRUPO_ENVIO='$grupo_envio', PRIORIDAD='$prioridad', A='$a', COPIA='$copia', ENCARGADO='$encargado', OBSERVACIONES='$observaciones' WHERE id=$id";
		break;
}

//Redirección final
if (!mysqli_query($conn, $strSQL)){
	print_r ($strSQL);
	echo "Imposible realizar la acción!";
	exit();
} else {
	header("Location: /Enertrade/pages/Telemedida/datos_contadores.php?$action=1");
	exit();
}

Campos_vacios:
header ("Location: /Enertrade/pages/Telemedida/mod$sql_table.php?error=camposvacios&id=$id&grupo_envio=$grupo_envio&prioridad=$prioridad&encargado=$encargado&a=$a&copia=$copia&observaciones=$observaciones");
exit();

?>