<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$Conn = new Conn('local', 'enertrade');

$comm 		= $_POST['comm'];
$periodo 	= $_POST['periodo'];
$tarifa 	= $_POST['tarifa'];

if (isset($_POST['mas_ie']) 		 && !empty($_POST['mas_ie']))		{$mas_ie 	= "checked";} 		else {$mas_ie 		= "";}
if (isset($_POST['menos_ie']) 	     && !empty($_POST['menos_ie']))		{$menos_ie 	= "checked";} 		else {$menos_ie 	= "";}

if (isset($_POST['ICP']) 		&& $_POST['ICP']			!=0 && !empty($_POST['ICP']))			{$ICP       = $_POST['ICP'];} 	    else {$ICP 			= 0;}
if (isset($_POST['descuento']) 	&& $_POST['descuento']	    !=0 && !empty($_POST['descuento']))		{$descuento = $_POST['descuento'];} 	else {$descuento 	= 0;}

$url = "";


switch ($_POST['action']){
    case 'calcular':
        
        for ($i=1;$i<=6;$i++){
            $precio_p = "precio_p$i";
            if (isset($_POST[$precio_p])){$$precio_p = $_POST[$precio_p];} else {$$precio_p = 0;}
            $precio[] = $$precio_p;
            $url .= "&$precio_p=".$$precio_p;
        }

        function calculate_interr($precio, $interr, $ICP, $descuento, $mas_ie, $menos_ie){

            if (empty($precio)){return (0);}

            if ($ICP !== 0)			{$ICP = $ICP/100;}
            if ($descuento !== 0)	{$descuento = 1-$descuento;} else {$descuento = 1;}

            if 		($mas_ie 	!== "")	{return (($precio*$ICP + $precio)*1.051127 + $interr) * $descuento;}
            elseif 	($menos_ie 	!== "")	{return (($precio*$ICP + $precio)/1.051127 + $interr) * $descuento;}
            else 						{return (($precio*$ICP + $precio) 		   + $interr) * $descuento;}
        }

        $strSQL = "SELECT P1, P2, P3, P4, P5, P6 FROM interrumpibilidad WHERE COMERCIALIZADORA='$comm' AND FECHA='$periodo' AND TARIFA='$tarifa'";
        $interr = array_values($Conn->oneRow($strSQL));

        for ($i=0;$i<=5;$i++){
            $interr_p = "interr_p".($i+1);
            $$interr_p = calculate_interr($precio[$i], $interr[$i], $ICP, $descuento, $mas_ie, $menos_ie);
            $url .= "&$interr_p=".$$interr_p;
        }
        
        break;
        
    case 'descargar':
        
        $interr = $Conn->getArray("SELECT * FROM interrumpibilidad WHERE COMERCIALIZADORA='$comm' AND FECHA='$periodo' ORDER BY TARIFA");
        
        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->putArray($interr, true);
        $SprdSht->directDownload("Interrumpibilidad $comm $periodo.xlsx");
        unset($Conn);
        
        break;
        
    case 'cargar':
        
            
        if (is_uploaded_file($_FILES['fichero']['tmp_name'])){

            $SprdSht = new SprdSht;
            $SprdSht->load($_FILES['fichero']['tmp_name']);
            $valores = $SprdSht->getArray(true);
            unset($SprdSht);

            $Conn = new Conn('local', 'enertrade');


            if (empty($valores[0]['ID'])){
                
                foreach ($valores as $num_row=>$row){unset($valores[$num_row]['ID']);}

                $str_valores = implode_values($valores);
                $Conn->Query("INSERT INTO interrumpibilidad (FECHA, COMERCIALIZADORA, TARIFA, P1, P2, P3, P4, P5, P6) VALUES $str_valores");

            } else {

                $str_valores = implode_values($valores);
                $Conn->Query("INSERT INTO interrumpibilidad (ID, FECHA, COMERCIALIZADORA, TARIFA, P1, P2, P3, P4, P5, P6) VALUES $str_valores ON DUPLICATE KEY UPDATE ID=VALUES(ID), FECHA=VALUES(FECHA), COMERCIALIZADORA=VALUES(COMERCIALIZADORA), P1=VALUES(P1), P2=VALUES(P2), P3=VALUES(P3), P4=VALUES(P4), P5=VALUES(P5), P6=VALUES(P6)");

            }
        }
        unset($Conn);
        
        break;
}

unset($Conn);
header ("Location: interrumpibilidad.php?comm=$comm&periodo=$periodo&tarifa=$tarifa&ICP=$ICP&descuento=$descuento&mas_ie=$mas_ie&menos_ie=$menos_ie".$url)





?>