<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

//set_time_limit(0);

function distance_m ($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo){
    
    $earthRadius = 6371000;
    
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    
    return round((2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)))) * $earthRadius);
}

//Si no es POST
if (!isset($_POST['action'])){goto GET_ACTIONS;}

switch ($_POST['action']){
		
	case 'download_coordenadas':
		if (empty($_FILES['fichero']['tmp_name'][0])){break;}
        
        set_time_limit(0);
        
        $SprdSht = new SprdSht;
        $SprdSht->load($_FILES['fichero']['tmp_name'][0]);
        $direcciones = $SprdSht->getArray(true);
        unset($SprdSht);
        
        if (!isset($direcciones) || empty($direcciones)){break;}
        
        $curl = new curlClass;
        foreach ($direcciones as $num_row=>$row){
            
            usleep(200000);
            
            $direccion = urlencode($row['DIRECCION']);
            
            $curl->url("https://www.google.es/search?tbm=map&authuser=0&hl=es&gl=es&pb=!4m12!1m3!1d13916.814636555444!2d-3.6724748499999995!3d40.44437325!2m3!1f0!2f0!3f0!3m2!1i1303!2i880!4f13.1!7i20!10b1!12m8!1m1!18b1!2m3!5m1!6e2!20e3!10b1!16b1!19m4!2m3!1i360!2i120!4i8!20m57!2m2!1i203!2i100!3m2!2i4!5b1!6m6!1m2!1i86!2i86!1m2!1i408!2i240!7m42!1m3!1e1!2b0!3e3!1m3!1e2!2b1!3e2!1m3!1e2!2b0!3e3!1m3!1e8!2b0!3e3!1m3!1e10!2b0!3e3!1m3!1e10!2b1!3e2!1m3!1e9!2b1!3e2!1m3!1e10!2b0!3e3!1m3!1e10!2b1!3e2!1m3!1e10!2b0!3e4!2b1!4b1!9b0!22m6!1sGY8DYuruN5WVxc8Pmo2QqAc%3A2!2zMWk6Myx0OjExODg3LGU6MixwOkdZOERZdXJ1TjVXVnhjOFBtbzJRcUFjOjI!7e81!12e3!17sGY8DYuruN5WVxc8Pmo2QqAc%3A46!18e15!24m65!1m21!13m8!2b1!3b1!4b1!6i1!8b1!9b1!14b1!20b1!18m11!3b1!4b1!5b1!6b1!9b1!12b0!13b1!14b1!15b0!17b1!20b1!2b1!5m5!2b1!3b1!5b1!6b1!7b1!10m1!8e3!14m1!3b1!17b1!20m2!1e3!1e6!24b1!25b1!26b1!29b1!30m1!2b1!36b1!43b1!52b1!54m1!1b1!55b1!56m2!1b1!3b1!65m5!3m4!1m3!1m2!1i224!2i298!71b1!72m4!1m2!3b1!5b1!4b1!89b1!26m4!2m3!1i80!2i92!4i8!30m28!1m6!1m2!1i0!2i0!2m2!1i458!2i880!1m6!1m2!1i1253!2i0!2m2!1i1303!2i880!1m6!1m2!1i0!2i0!2m2!1i1303!2i20!1m6!1m2!1i0!2i860!2m2!1i1303!2i880!34m17!2b1!3b1!4b1!6b1!8m5!1b1!3b1!4b1!5b1!6b1!9b1!12b1!14b1!20b1!23b1!25b1!26b1!37m1!1e81!42b1!47m0!49m5!3b1!6m1!1b1!7m1!1e3!50m4!2e2!3m2!1b1!3b1!67m2!7b1!10b1!69i589&q=$direccion&oq=$direccion&gs_l=maps.12..38i442i443i428k1.142719.142719.1.146525.5.5.0.0.0.0.124.124.0j1.4.0....0...1ac.1.64.maps..1.1.124.0...2.&tch=1&ech=1&psi=GY8DYuruN5WVxc8Pmo2QqAc.1644400410982.1");
            
            $datos = $curl->execute();
            
            preg_match('/\[\[\[2],\[\[null,null,(.*?)]/', $datos, $datos);
            
            if (!isset($datos[1])){continue;}
            
            $datos = explode(',', $datos[1]);
            $direcciones[$num_row]['LATITUD'] = $datos[0];
            $direcciones[$num_row]['LONGITUD'] = $datos[1];
        }
        unset($curl, $datos, $direccion);

        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->putArray($direcciones, true);
        unset($direcciones);
        $SprdSht->directDownload('COORDENADAS');
        unset($SprdSht);
        
		break;
        
    case 'download_cruce_coordenadas':
        
        if (empty($_FILES['fichero']['tmp_name'][0])){break;}
        
        $SprdSht = new SprdSht;
        $SprdSht->load($_FILES['fichero']['tmp_name'][0]);
        $SprdSht->getSheet('REFERENCIAS');
        $referencias = $SprdSht->getArray(true);
        $SprdSht->getSheet('RESULTADOS');
        $resultados = $SprdSht->getArray(true);
        unset($SprdSht);
        
        if (!isset($resultados) || !isset($referencias) || empty($resultados) || empty($referencias)){break;}
        
        
        foreach ($resultados as $num_row=>$row){

            if (empty($row['LATITUD'])){continue;}

            //Calcula todas las distancia y encuentra la minima
            foreach ($referencias as $num_ref=>$ref){
                $referencias[$num_ref]['DISTANCIA'] = '';
                
                if ($ref['LATITUD']==''){continue;}
                
                $distancia = distance_m($row['LATITUD'], $row['LONGITUD'], $ref['LATITUD'], $ref['LONGITUD']);
                $referencias[$num_ref]['DISTANCIA'] = $distancia;
                $distancia_min = (!isset($distancia_min)) ? $distancia : min($distancia_min, $distancia);
            }
            
            $columns = array_column($referencias, 'DISTANCIA');
            array_multisort(array_column($referencias, 'DISTANCIA'), SORT_ASC, $referencias);
            
            //Busca la dirección
            for ($x=1; $x<=3; $x++){
                if (empty($ref['LATITUD'])){continue;}
                $resultados[$num_row]["REFERENCIA$x"] = $referencias[($x-1)]['REFERENCIA'];
                $resultados[$num_row]["DISTANCIA$x"] = $referencias[($x-1)]['DISTANCIA'];
            }
        }
        unset($referencias);

        $SprdSht = new SprdSht;
        $SprdSht->nuevo();
        $SprdSht->putArray($resultados, true);
        $SprdSht->directDownload('DISTANCIAS.xlsx');
        unset($SprdSht);
        
        break;
}

GET_ACTIONS:

header ("Location: Geografia.php");

?>