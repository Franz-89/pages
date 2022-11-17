<?php
require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$Conn = new Conn('local', 'enertrade');

$query = $Conn->Query("SELECT CONTADOR FROM datos_contadores ORDER BY CONTADOR");
while ($contador = mysqli_fetch_assoc($query)){$contadores[] = $contador['CONTADOR'];}
unset($Conn);

$hasta = new DateTime(date('Y-m-d'));
$hasta = $hasta->modify('+2 day');

$desde = new DateTime(date('Y-m-d'));
$desde = $desde->modify('-1 day');


set_time_limit(0);

$array_header = array('Fecha', 'kW_Compra', 'kW_venta', 'KVAr_C1', 'KVAr_CAP_V', 'KVAr_IND_V', 'KVAr_C4', 'Flags', 'HORARIOS', 'kW_Compra_H', 'kW_venta_H', 'kVAr_C1_H', 'KVAr_CAP_V_H', 'KVAr_IND_V_H', 'KVAr_C4_H');

$Conn 		= new Conn('local', 'cdc');

foreach ($contadores as $contador){

	$contador = str_replace(" ", "_", $contador);

	//Crea tabla si no existe
	$strSQL = "CREATE TABLE IF NOT EXISTS `$contador` (
				  `Fecha` datetime NOT NULL,
				  `kW_Compra` int(11) NOT NULL,
				  `kW_Venta` int(11) NOT NULL,
				  `KVAr_C1` int(11) NOT NULL,
				  `KVAr_CAP_V` int(11) NOT NULL,
				  `KVAr_IND_V` int(11) NOT NULL,
				  `KVAr_C4` int(11) NOT NULL,
				  `Flags` int(11) NOT NULL,
				  `HORARIOS` int(11) DEFAULT NULL,
				  `kW_Compra_H` int(11) DEFAULT NULL,
				  `kW_Venta_H` int(11) DEFAULT NULL,
				  `KVAr_C1_H` int(11) DEFAULT NULL,
				  `KVAr_CAP_V_H` int(11) DEFAULT NULL,
				  `KVAr_IND_V_H` int(11) DEFAULT NULL,
				  `KVAr_C4_H` int(11) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf16;";
	$Conn->Query($strSQL);
	
	$Conn->Query("ALTER TABLE `$contador` ADD PRIMARY KEY (`Fecha`)");

	$values = array();
	// Para cada año
	for ($ano=date_format($desde, 'Y'); $ano<=date_format($hasta, 'Y'); $ano++){
		$dir = "C:/Gestinel2/Gestinel/".str_replace("_", " ", $contador)."/$ano/";
		$files = @scandir($dir);

		if (empty($files)){continue;} //Si no existe la carpeta

		foreach($files as $file){

			//Si es un fichero .dat
			if (substr($file,-3) == 'dat'){
				//Si el nombre del fichero está entre desde y hasta
				if (date_create_from_format('dmY', substr($file, 0, 4).$ano)>=$desde && date_create_from_format('dmY', substr($file, 0, 4).$ano)<=$hasta){
					$fopen = fopen($dir.$file, 'r');
					while (!feof($fopen)) {

						$line=trim(fgets($fopen));
						if (!empty($line)){
							$arr_line = explode("\t", $line);
							unset ($line);

							$linea = array_fill_keys(array('Fecha', 'kW_Compra', 'kW_venta', 'KVAr_C1', 'KVAr_CAP_V', 'KVAr_IND_V', 'KVAr_C4', 'Flags'), '');
							$linea['Fecha'] 		= date_format(date_create_from_format('d/m/Y H:i', $arr_line[1]), 'Y-m-d H:i:s');
							$linea['kW_Compra'] 	= $arr_line[2];
							$linea['kW_venta'] 		= $arr_line[3];
							$linea['KVAr_C1'] 		= $arr_line[4];
							$linea['KVAr_CAP_V'] 	= $arr_line[5];
							$linea['KVAr_IND_V'] 	= $arr_line[6];
							$linea['KVAr_C4'] 		= $arr_line[7];
							$linea['Flags'] 		= $arr_line[8];

							$values[] = "'".implode("','", $linea)."'";
							unset($linea);

							if((count($values)%1000) == 0){
								$str_values = "(".implode("),(", $values).")";
								$values = array();
								$strSQL = "INSERT INTO $contador (	Fecha, 
																	kW_Compra, 
																	kW_venta, 
																	KVAr_C1, 
																	KVAr_CAP_V, 
																	KVAr_IND_V, 
																	KVAr_C4, 
																	Flags)

														VALUES $str_values

											ON DUPLICATE KEY UPDATE Fecha		=VALUES(Fecha),
																	kW_Compra	=VALUES(kW_Compra),
																	kW_venta	=VALUES(kW_venta),
																	KVAr_C1		=VALUES(KVAr_C1),
																	KVAr_CAP_V	=VALUES(KVAr_CAP_V),
																	KVAr_IND_V	=VALUES(KVAr_IND_V),
																	KVAr_C4		=VALUES(KVAr_C4),
																	Flags		=VALUES(Flags)";
								$Conn->Query($strSQL);
							}
						}// Si linea vacía
					}// Hasta EOF
					fclose($fopen);
					unset ($fopen);
				} // Si el nombre del fichero está entre desde y hasta
			} // Si es .dat
		} // Por cada fichero

		if (!empty($values)){
			$str_values = "(".implode("),(", $values).")";
			unset($values);
			$strSQL = "INSERT INTO $contador (	Fecha, 
												kW_Compra, 
												kW_venta, 
												KVAr_C1, 
												KVAr_CAP_V, 
												KVAr_IND_V, 
												KVAr_C4, 
												Flags)

									VALUES $str_values

						ON DUPLICATE KEY UPDATE Fecha		=VALUES(Fecha),
												kW_Compra	=VALUES(kW_Compra),
												kW_venta	=VALUES(kW_venta),
												KVAr_C1		=VALUES(KVAr_C1),
												KVAr_CAP_V	=VALUES(KVAr_CAP_V),
												KVAr_IND_V	=VALUES(KVAr_IND_V),
												KVAr_C4		=VALUES(KVAr_C4),
												Flags		=VALUES(Flags)";
			$Conn->Query($strSQL);
		}
	} // Por cada año
	
	$fecha_maxima = $Conn->getArray("SELECT MAX(Fecha) maxima FROM $contador");

	if (isset($fecha_maxima[0]['maxima']) && !empty($fecha_maxima[0]['maxima'])){
		$fecha_maxima = date_create_from_format('Y-m-d H:i:s', $fecha_maxima[0]['maxima']);
		$fecha_maxima->modify('-2 years');
		$fecha_maxima->modify('-6 month');
		$Conn->Query("DELETE FROM $contador WHERE Fecha<'".date_format($fecha_maxima, 'Y-m-d')."'");
		unset($fecha_maxima);
	}
} // Por cada contador

unset($Conn);
?>