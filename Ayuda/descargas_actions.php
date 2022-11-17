<?php

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

if (!isset($_POST['action'])){header ('Location: descargas.php'); die;}

$curl = new curlClass;

switch ($_POST['action']){
	case 'download_fras':
		
		switch (true){
			case (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])):
			case (!isset($_POST['usuario']) || empty($_POST['usuario'])):
				header ("Location: descargas.php"); die;
		}
		
		$timestamp = getMicrotimeString();
		$Session = new Session;
		$Session->open($timestamp);
		
		session_start();
		$_SESSION['download_fras'] = $timestamp;
		session_write_close();
		
		
		set_time_limit(0);
		
		//Obtiene el listado de CUPS
		$filenum = 0;
		foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){
				
				$SprdSht = new SprdSht;
				$SprdSht->load($file);
				$FRAS = $SprdSht->getArray(true);
				unset($SprdSht);
			}
		}
		$total_fras = count($FRAS);
		
		//Efectua el acceso a la web
		$usuario 	= $_POST['usuario'];
		$comm 		= $_POST['comm'];
		$cli 		= $_POST['cli'];
        $dir        = '//192.168.0.250/NAS/FACTURAS/';
		if (isset($_POST['carpeta_fras']) && !empty($_POST['carpeta_fras'])){
            $Carpetas = new Carpetas;
            $num = 1;
            while (true){
                if (!is_dir($dir.$num)){
                    $Carpetas->createIfNotExists($dir.$num);
                    $dir .= "$num/";
                    break;
                }
                ++$num;
            }
            unset($Carpetas);
        }
        
		$Conn = new Conn('local', 'enertrade');
		$password = $Conn->oneData("SELECT CONTRASENA FROM claves_comm_distr WHERE CLIENTE='$cli' AND COMM_DISTR='$comm' AND USUARIO='$usuario'");
		unset($Conn);
		
		//Seleccionar la comercializadora
		switch ($comm){
				
			case 'ENDESA':
//ENDESA -------------------------------------------------------------
				//Recupera los datos de los cookies
				$curl->url("https://www.empresa.endesaclientes.com/empresa/login.do");
				$curl->POST("gestion_Usuario=&gestion_Estructura=&canalComercial=&idSeccion=&menu=&id=$usuario&clave=$password");
				$curl->execute();
				
				//Recupera los id de todos los contratos
				$curl->url("https://www.empresa.endesaclientes.com/empresa/contratosPosicionGlobal.do?nivel=&linea=1&historico=N&pager.offset=0&maxPageItems=10000&orderBy=&order");
				$curl->GET();
				$datos = $curl->execute();

				$dom = new DOMDocument;

				@$dom->loadHTML($datos);
				$dom->preserveWhiteSpace = false;
				$tables = $dom->getElementsByTagName('table');

				$info_contr = array();
				foreach ($tables as $table){
					if ($table->getAttribute('bgcolor') == '#FFFFFF'){
						$lineas = $table->getElementsByTagName('tr');

						foreach ($lineas as $linea){
							$datos = $linea->getElementsByTagName('td');

							if (isset($datos[1]) && is_numeric(substr(trim($datos[1]->textContent), 0, 1))){
								$contr = trim($datos[1]->textContent);
								$as = $datos[1]->getElementsByTagName('a');
								foreach ($as as $a){
									$ID = explode(',', str_replace(array('enviarID(', ')', "'"), array('', '', '') ,$a->getAttribute('onclick')));
									$info_contr[$contr] = array('id_contr'=>$ID[0]);
									unset($ID);
								}
								unset($contr);
							}
						}

						break;
					}
				}
				unset($dom, $datos);
				
                
                
				//Empieza a descargar las facturas
				foreach($FRAS as $num_row=>$row){
					$Session->write("porcentaje_$timestamp", $num_row/$total_fras*100);
					foreach ($row as $key=>$fra){
                        
						$fra = explode('_', $fra);
                        
						//Indivudal
						if (!isset($fra[1])){
							$curl->url("https://www.empresa.endesaclientes.com/empresa/generarFacturas.do?id=$fra[0]&amp;fromBBDD=ORACLE&amp");
							$datos = $curl->execute();
							if (!empty($datos)){file_put_contents($dir."$fra[0].pdf", $datos);} else {$error[] = $fra[0];}

						//Agrupada
						} else {
							/*
                            Intento 1: cd_cemptitu=20
                            Intento 2: cd_cemptitu=27
                            Intento 3: cd_cemptitu=70
                            */
                            for ($x=1; $x<=3; ++$x){
                                switch ($x){
                                    case 1: $cd_cemptitu = 20; break;
                                    case 2: $cd_cemptitu = 27; break;
                                    case 3: $cd_cemptitu = 70; break;
                                }
                                $url = "https://www.empresa.endesaclientes.com/empresa/obtenerFacturaPDF.do?id=$fra[0]&cd_contrext=$fra[1]&cd_creffact=AAAAAA&cd_secfactu=$fra[2]&origen=&fromBBDD=ORACLE&estado=PSEUDOFAC.&fromBBDD=ORACLE&contrato=&mercado=&cd_cliente=&provincia=&tipo=PS&importeSuperior=&anio=&destino=&numerofactura=&secOblig=&acFracc=&cd_cdistrib=&cd_cemptitu=$cd_cemptitu&cif_empresa=&to=listadoFacturas";
                                $curl->url($url);
                                $datos = $curl->execute();
                                if (!empty($datos) && substr($datos, 0, 4)=='%PDF'){break;}
                            }
                            
                            if (!empty($datos) && substr($datos, 0, 4)=='%PDF'){file_put_contents($dir."$fra[0]_$fra[1]_$fra[2].pdf", $datos);}

                            //Intento 4: cd_creffact=???
                            else {

                                //Saca el id de factura en el caso de que no esté asignado al contrato
                                if (!isset($info_contr[$fra[1]]['id_fra'])){

                                    $curl->url("https://www.empresa.endesaclientes.com/empresa/listadoFacturas.do?destino=&destino=&to=listadoFacturas&id_datcon=" . $info_contr[$fra[1]]['id_contr'] . "&cif_empresa=&pager.offset=0&maxPageItems=20&orderBy=&order=");
                                    $datos = $curl->execute();

                                    $dom = new domDocument;

                                    @$dom->loadHTML($datos);
                                    $dom->preserveWhiteSpace = false;
                                    $tables = $dom->getElementsByTagName('table');

                                    foreach ($tables as $table){
                                        if ($table->getAttribute('bgcolor') == '#FFFFFF'){
                                            $lineas = $table->getElementsByTagName('tr');

                                            foreach ($lineas as $linea){
                                                $datos = $linea->getElementsByTagName('td');

                                                if (isset($datos[1]) && is_numeric(substr(trim($datos[1]->textContent), 0, 1))){
                                                    $as = $datos[1]->getElementsByTagName('a');

                                                    foreach ($as as $a){
                                                        $href = $a->getAttribute('href');

                                                        if (substr($href, 0, 4)!='java'){
                                                            $ID = explode('&', $href);

                                                            foreach($ID as $value){

                                                                if (substr($value, 0, 11)=="cd_creffact"){
                                                                    $info_contr[$fra[1]]['id_fra'] = str_replace('cd_creffact=', '', $value);
                                                                    break(2);
                                                                }
                                                            }
                                                            unset($ID);
                                                        }
                                                    }
                                                    unset($contr);
                                                }
                                            }
                                            break;
                                        }
                                    }
                                }

                                if (isset($info_contr[$fra[1]]['id_fra'])){
                                    for ($x=1; $x<=2; ++$x){
                                        switch ($x){
                                            case 1: $cd_testfact = ''; break;
                                            case 2: $cd_testfact = 'cd_testfact=N'; break;
                                        }
                                        $curl->url("https://www.empresa.endesaclientes.com/empresa/generarFacturas.do?id=&cd_creffact=".$info_contr[$fra[1]]['id_fra']."&fromBBDD=ORACLE&cd_secfactu=$fra[2]&estado=&fromActionDo=http://empresa/listadoFacturas.do&cif_empresa=&estadoContrato=Activo&$cd_testfact");
                                        $datos = $curl->execute();
                                        if (!empty($datos) && substr($datos, 0, 4)=='%PDF'){break;}
                                    } 
                                }
                                
                                if (!empty($datos) && substr($datos, 0, 4)=='%PDF'){file_put_contents($dir."$fra[0]_$fra[1]_$fra[2].pdf", $datos);} else {$error[] = "$fra[0]_$fra[1]_$fra[2]";}
                            }
                        }
                        unset($datos);
					}
				}
				break;
				
			case 'EDP':
//EDP -------------------------------------------------------------
				//Recupera el token de ACTIR
				$curl->url("https://www.edpenergia.es/actir-wsrest/api/rest/auth/login");
				$curl->POST();
				$curl->basicAuth($usuario, $password);
				$curl->execute();
				
				
				//Recupera el listado de facturas con relativo código identificativo
				$curl->url("https://www.edpenergia.es/actir-wsrest/api/rest/bill/0/10000");
				$curl->GET();
				$datos = json_decode($curl->execute());
				
				//Crea array asociativo: num_fra=>'id_sistema-cod_fra'
				$cod_facturas = array();
				foreach($datos->listData as $num_row=>$obj){$cod_facturas[$obj->num_factura] = "$obj->id_sistema-$obj->cod_factura";}
				unset($datos);
				
				//Descarga las facturas
				foreach($FRAS as $num_row=>$row){
					$Session->write("porcentaje_$timestamp", $num_row/$total_fras*100);
					foreach ($row as $key=>$fra){
						if (array_key_exists($fra, $cod_facturas)){
							$curl->url("https://www.edpenergia.es/actir-wsrest/api/rest/bill/".$cod_facturas[$fra]."/pdf");
							$datos = $curl->execute();

							if (!empty($datos)){file_put_contents($dir."$fra.pdf", $datos);} else {$error[] = $fra;}
						} else {
							$error[] = $fra;
						}
					}
				}
				
				break;
                
//VIESGO -------------------------------------------------------------
            case 'VIESGO':
                
                if (!isset($_POST['ssid']) || empty($_POST['ssid'])){header ("Location: descargas.php");}
                
                $JSESSIONID = $_POST['ssid'];
                
                foreach ($FRAS as $num_row=>$row){
                    $fras[] = $row['NUM_FRA'];
                }
                unset($FRAS);
                
                $Conn = new Conn('mainsip', 'develop');
                $values = "'".implode("', '", $fras)."'";
                
                $FRAS = $Conn->getArray("SELECT cups, numero_factura FROM facturas WHERE numero_factura IN ($values)");
                unset($Conn, $values, $fras);
                
                $curl = new curlClass;
                
                
                foreach ($FRAS as $num_row=>$row){
                    $fra = $row['numero_factura'];
                    $cups = $row['cups'];
                    
                    $curl->url("https://grandesclientes.repsolluzygas.com/webclientesKAM/descargarFacturas?fichero=$fra&cupsFactura=$cups&tipoEnergia=ELEC");
                    $curl->httpHeaders(array("Cookie: JSESSIONID=$JSESSIONID; CookieConsent={stamp:%27J/RsRMCzDGlXJDDT/FbF8pBATsMChLp/3nJZaBltGFl0kE8c2NWaFQ==%27%2Cnecessary:true%2Cpreferences:true%2Cstatistics:true%2Cmarketing:true%2Cver:1%2Cutc:1634829847413%2Cregion:%27es%27}; recordar=on; usuario=FRANDIA"));
                    $datos = $curl->execute();
                    
                    if (!empty($datos)){file_put_contents($dir."$fra.pdf", $datos);} else {$error[] = $fra;}
                    unset($fra, $cups, $datos);
                }
                
                break;
                
//NATURGY -------------------------------------------------------------
            case 'NATURGY':
                
                $curl = new curlClass;
                $curl->url('https://areaprivadagc.naturgy.es/OV_WS/loginOV');
                $curl->POST("origenOV=1&language=es&userOV=$usuario&submitBtn=enterBtn&enterBtn=Entra&passwordOV=$password");
                $curl->follow(true);
                $curl->execute();

                $curl->url('https://areaprivadagc.naturgy.es/CanalCliente/portal/!ut/p/c1/jY5LDoIwGISP1OFvLbKEII8Gi2KqyMZ0YUgTARfG89seQHRm-WUebGDes3270b7cMtsH69kgb8RzDahm3xmR4qiSpJAxERB7fvW8K7XKs9Y0XGxAvOXylAmCwD9p3ZIpRB1FW7mLQLISWXOuUeb8R_oS3q6vB77WHzi-KAXT1TLd2XMyPdxh_ADgTCEE/dl2/d1/L0lDUWtpQ1NTUW9LVVFBISEvb0lvZ0FFQ1FRREdJUXBURE9DNEpuQSEhL1lBeEpKNDUwLTRrc3V5bHcvN18yM0ROMDBKTE1SVU4xMFFKOTVOUUFNMDBLNy93cHMuY2FuYWxjbGllbnRlLmxvZ2luLmFjdGlvbi5Mb2dpbg!!/');
                $curl->POST("wps.canalcliente.login.resumeSession=true&wps.canalcliente.login.userid=$usuario&password=$password&idFactura=null");
                $curl->follow(true);
                $curl->execute();

                $curl->url('https://areaprivadagc.naturgy.es/CanalCliente/myportal/!ut/p/c1/pY_LCsIwFES_xQ-QTG5isMuEaB_EBCrR2o10IVKwrQvx-21xbRW8szwcZi6r2Zi-ebbX5tEOfXNjFavVmYT1QOF2RmUJdKK5K2UALI38NPIy9YU1ITohVyARhNobSZD0l41fbB8obmXO-VptOEhl0rhDjtSKL_Zx-nV--yzHm8_1TxwfToP5bOgu7N7FWKHNl3qxeAEiCEXI/dl2/d1/L2dJQSEvUUt3QS9ZQnB3LzZfMjNETjAwSkxNQjZIOTBBOUExTFI0TzAwRzY!/?nID=6_23DN00JLMB6H90A9A1LR4O00D2&cID=6_23DN00JLMB6H90A9A1LR4O00D2');
                $curl->GET();
                $curl->follow(true);
                $curl->execute();
                
                foreach ($FRAS as $num_row=>$row){
                    $fra = $row['NUM_FRA'];
                    $curl->url("https://areaprivadagc.naturgy.es/CanalCliente/PA_1_2RGNJDBOUT5JE02BAV64KS20G1/FacturaServlet?numFactura=$fra&idCompany=96&isDelta=true");
                    $curl->follow(true);
                    $curl->GET();
                    $datos = $curl->execute();
                    if (!empty($datos)){file_put_contents($dir."$fra.pdf", $datos);} else {$error[] = $fra;}
                    unset($fra, $datos);
                }
                
                break;
                
//NEXUS -------------------------------------------------------------
            case 'NEXUS':
                
                if (!isset($_POST['bearer']) || empty($_POST['bearer'])){header ("Location: descargas.php");}
                
                $bearer = $_POST['bearer'];
                
                $curl = new curlClass;
                $x = 0;
                foreach ($FRAS as $num_row=>$row){
                    $listado_def[] = array('number'=>$row['NUM_FRA'], 'cups'=>$row['CUPS'], 'albaranNumber'=>'', 'nif'=>'');
                    unset($FRAS[$num_row]);

                    if (($num_row % 20) == 0){
                        $listado_def = json_encode($listado_def);

                        $curl->url('https://zonacliente.nexusenergia.com:3306/api/OpentText/GetTicketOpenTextInvoices');
                        $curl->httpHeaders(
                            array(
                                'accept: application/json, text/plain, */*',
                                'accept-encoding: gzip, deflate, br',
                                'accept-language: es-ES,es;q=0.9',
                                "authorization: Bearer $bearer",
                                'content-type: application/json',
                                'origin: https://zonacliente.nexusenergia.com',
                                'referer: https://zonacliente.nexusenergia.com/',
                                'sec-ch-ua: "Google Chrome";v="93", " Not;A Brand";v="99", "Chromium";v="93"',
                                'sec-ch-ua-mobile: ?0',
                                'sec-ch-ua-platform: "Windows"',
                                'sec-fetch-dest: empty',
                                'sec-fetch-mode: cors',
                                'sec-fetch-site: same-site',
                                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36'
                            )
                        );
                        $curl->POST('{"email":"sara.oro@gencat.cat","invoiceNumbers":'.$listado_def.'}');
                        $datos = $curl->execute();

                        $datos = base64_decode($datos);

                        file_put_contents("facturas_$x.zip", $datos);
                        $files[] = "facturas_$x.zip";
                        ++$x;

                        unset($datos, $listado_def);
                    }
                }
                
                if (isset($listado_def)){
                    $listado_def = json_encode($listado_def);

                    $curl->url('https://zonacliente.nexusenergia.com:3306/api/OpentText/GetTicketOpenTextInvoices');
                    $curl->httpHeaders(
                        array(
                            'accept: application/json, text/plain, */*',
                            'accept-encoding: gzip, deflate, br',
                            'accept-language: es-ES,es;q=0.9',
                            "authorization: Bearer $bearer",
                            'content-type: application/json',
                            'origin: https://zonacliente.nexusenergia.com',
                            'referer: https://zonacliente.nexusenergia.com/',
                            'sec-ch-ua: "Google Chrome";v="93", " Not;A Brand";v="99", "Chromium";v="93"',
                            'sec-ch-ua-mobile: ?0',
                            'sec-ch-ua-platform: "Windows"',
                            'sec-fetch-dest: empty',
                            'sec-fetch-mode: cors',
                            'sec-fetch-site: same-site',
                            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36'
                        )
                    );
                    $curl->POST('{"email":"sara.oro@gencat.cat","invoiceNumbers":'.$listado_def.'}');
                    $datos = $curl->execute();

                    $datos = base64_decode($datos);

                    file_put_contents("facturas_$x.zip", $datos);
                    $files[] = "facturas_$x.zip";
                    ++$x;

                    unset($datos, $listado_def);
                }
                
                
                $zip = new ZipArchive;
                foreach ($files as $zip_name){
                    if ($zip->open($zip_name) === FALSE){continue;}
                    $zip->extractTo('//192.168.0.250/NAS/FACTURAS NEXUS');
				    $zip->close();
                    unlink($zip_name);
                }
                
                goto RENOMBRAR;
                break;
		}
        
        
		
		unset($curl);
		$Session->close();
		
		if (isset($error)){
			$SprdSht = new SprdSht;
			$SprdSht->nuevo();
			$SprdSht->putArray($error);
			$SprdSht->directDownload('Facturas no descargadas');
			unset($SprdSht);
		}
		
		header ("Location: descargas.php"); die;
		break;
		
    case 'download_curvas':
        
        switch (true){
			case (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])):
			case (!isset($_POST['usuario']) || empty($_POST['usuario'])):
			case (!isset($_POST['desde']) || empty($_POST['desde'])):
				header ("Location: descargas.php"); die;
		}
        
		set_time_limit(0);
		
		//Obtiene el listado de CUPS y las variables
		$filenum = 0;
		foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){
				
				$SprdSht = new SprdSht;
				$SprdSht->load($file);
				$CUPS = $SprdSht->getArray(true);
				unset($SprdSht);
			}
		}
		$total_cups = count($CUPS);
		
		
		$usuario 	= $_POST['usuario'];
		$comm 		= $_POST['comm'];
		$cli 		= $_POST['cli'];
        
        $desde      = new DateClass;
        $hasta      = new DateClass;
		$desde->stringToDate($_POST['desde'], 'd/m/Y');
		if ($_POST['hasta']!=''){$hasta->stringToDate($_POST['hasta'], 'd/m/Y');}
		
        
        if ($hasta->format('Yn')<$desde->format('Yn')){
            header ("Location: descargas.php"); die;
        }
        
		$Conn = new Conn('local', 'enertrade');
		$password = $Conn->oneData("SELECT CONTRASENA FROM claves_comm_distr WHERE CLIENTE='$cli' AND COMM_DISTR='$comm' AND USUARIO='$usuario'");
		unset($Conn);
        
        //Selecciona la comercializadora
        switch ($comm){
            case 'NATURGY':
                
                //Obtiene los codigos de identificación de naturgy desde el html que se ha cargado
                if (!isset($_FILES['naturgy']['tmp_name'][0]) || empty($_FILES['naturgy']['tmp_name'][0])){break;}
                
                foreach($_FILES['naturgy']['tmp_name'] as $file){
                    if (is_uploaded_file($file)){

                        $datos = file_get_contents($file);
                        preg_match_all("/ubsJS\[(.*)\]='(.*)';/", $datos, $array);
                        preg_match_all("/ubsJSC\[(.*)\]='(.*)';/", $datos, $array2);  
                        
                        foreach ($array2[2] as $num_row=>$cups){
                            $cups = substr(trim($cups), 0, 20);
                            $codigos[$cups] = $array[2][$num_row];
                        }
                        
                        unset($array, $array2, $datos);
                    }
                }
                
                //Efectua el acceso a la web
                $curl = new curlClass;
                $curl->url('https://areaprivadagc.naturgy.es/OV_WS/loginOV');
                $curl->POST("origenOV=1&language=es&userOV=$usuario&submitBtn=enterBtn&enterBtn=Entra&passwordOV=$password");
                $curl->follow(true);
                $curl->execute();

                $curl->url('https://areaprivadagc.naturgy.es/CanalCliente/portal/!ut/p/c1/jY5NDsIgFISPxIOHxK0UbakUjIq23RgWpiGxrQvj-YUDWJ1Zfpkf0pPkKbzjEF5xnsKDtKQXN4bKAtSmkdIUoPVZFb7ZI3BMvEv8WNpaSecN8hUwdChOkjPg8E_aOuZ3XFO6FlsKTFRcmouGUuGP9DW_XV7PfKk_c_iiDRBbzeOdPEffQjwMH-dwnrQ!/dl2/d1/L0lDUWtpQ1NTUW9LVVFBISEvb0lvZ0FFQ1FRREdJUXBURE9DNEpuQSEhL1lBeEpKNDUwLTRrc3V5bHcvN18yM0ROMDBKTE0zSjkyMFFTS000NjRFMDBHNS93cHMuY2FuYWxjbGllbnRlLmxvZ2luLmFjdGlvbi5Mb2dpbg!!/');
                $curl->POST("wps.canalcliente.login.resumeSession=true&wps.canalcliente.login.userid=$usuario&password=$password&idFactura=null");
                $curl->follow(true);
                $curl->execute();

                //Home
                $curl->url('https://areaprivadagc.naturgy.es/CanalCliente/myportal/!ut/p/c1/pY_LCsIwFES_xQ-QTG5isMuEaB_EBCrR2o10IVKwrQvx-21xbRW8szwcZi6r2Zi-ebbX5tEOfXNjFavVmYT1QOF2RmUJdKK5K2UALI38NPIy9YU1ITohVyARhNobSZD0l41fbB8obmXO-VptOEhl0rhDjtSKL_Zx-nV--yzHm8_1TxwfToP5bOgu7N7FWKHNl3qxeAEiCEXI/dl2/d1/L2dJQSEvUUt3QS9ZQnB3LzZfMjNETjAwSkxNQjZIOTBBOUExTFI0TzAwRzY!/?nID=6_23DN00JLMB6H90A9A1LR4O00D2&cID=6_23DN00JLMB6H90A9A1LR4O00D2');
                $curl->GET();
                $curl->follow(true);
                $curl->execute();
                
                //Curvas
                $curl->url('https://areaprivadagc.naturgy.es/CanalCliente/myportal/!ut/p/c1/pY_LCsIwFES_xQ-QTG5isMuEaB_EBCrR2o10IVKwrQvx-21xbRW8szwcZi6r2Zi-ebbX5tEOfXNjFavVmYT1QOF2RmUJdKK5K2UALI38NPIy9YU1ITohVyARhNobSZD0l41fbB8obmXO-VptOEhl0rhDjtSKL_Zx-nV--yzHm8_1TxwfToP5bOgu7N7FWKHNl3qxeAEiCEXI/dl2/d1/L2dJQSEvUUt3QS9ZQnB3LzZfMjNETjAwSkxNQjZIOTBBOUExTFI0TzAwNDY!/?cID=6_23DN00JLMB6H90A9A1LR4O00D2&nID=6_23DN00JLMB6H90A9A1LR4O00D2');
                $curl->GET();
                $curl->follow(true);
                $curl->execute();
                
                //Empieza a descargar las curvas
                foreach ($CUPS as $num_row=>$row){
                    
                    $cups   = $row['CUPS'];
                    $codigo = (isset($codigos[$cups])) ? $codigos[$cups] : NULL;
                    
                    $desde->stringToDate($_POST['desde'], 'd/m/Y');
                    
                    if (!isset($codigo) || empty($codigo)){continue;}
                    
                    while ($desde->format('Ym')<=$hasta->format('Ym')){
                        
                        $str_date = $desde->format('d/m/Y');
                        
                        $curl->url("https://areaprivadagc.naturgy.es/CanalCliente/PA_1_2RGNJDBOUL33802JPAOGR93000/ExcelServletCurva?locale=es&isInDeltaV20=true&identificadoresSuministros=;$codigo;&fechaDesde=$str_date&tipoPeriodo=mes");
                        $curl->GET();
                        $curl->follow(true);
                        $datos = $curl->execute();
                        
                        if (isset($datos) && !empty($datos)){
                            file_put_contents($cups.'_'.$desde->format('n_Y').'.xls', $datos);
                            $files[] = $cups.'_'.$desde->format('n_Y').'.xls';
                        }
                        
                        $desde->add(0,1);
                        
                        unset($str_date, $datos);
                    }
                    
                    //Crea un zip para cada CUPS
                    if (isset($files)){
                        merge_zip("$cups.zip", $files);
                        $zips[] = "$cups.zip";
                        unset($files);
                    }
                }
                
                unset($curl);
                
                //Descarga todo
                if (isset($zips)){
                    merge_and_dwd_zip('Curvas.zip', $zips);
                    unset($zips);
                }
                break;
                
            case 'NEXUS':
                
                if (!isset($_POST['bearer']) || empty($_POST['bearer'])){
                    header ("Location: descargas.php"); die;
                }
                
                set_time_limit(0);
                
                $headers_cdc = array('Periodo', 'Fecha', 'kW_Compra', 'KVAr_C1', 'Capacitiva');
                
                $bearer = $_POST['bearer'];
                
                $str_desde = $desde->format('D M d Y');
                $str_hasta = $hasta->format('D M d Y');
                
                switch ($cli){
                    case 'NAVANTIA':                 $valor = 'Click SCREEN POOL'; break;
                    case 'GENERALITAT DE CATALUNYA': $valor = 'Pool Generalitat'; break;
                    default:                         $valor = ''; break;
                }
                
                $curl = new curlClass;
                
                foreach ($CUPS as $num_row=>$row){
                    
                    $cups   = $row['CUPS'];
                    
                    $curl->url('https://zonacliente.nexusenergia.com:3306/api/GroupedMeasures/GetConsumptionDetail');
                    $curl->POST('{"FechaDesde":"'.$str_desde.'","FechaHasta":"'.$str_hasta.'","CUPS":[{"CUPS":"'.$cups.'","InformacionCUPS":[{"Nombre":"TarifaATR","Valor":"6.1TD"},{"Nombre":"Producto","Valor":"'.$valor.'"},{"Nombre":"Zona","Valor":"Peninsula"}]}],"OrigenMedidas":"horaria","AgruparPor":["CUPS","TarifaATR","Producto","Zona"],"ClientEmail":"'.$usuario.'","Ambito":"horaria","Comparativa":"","SearchType":"multi"}');
                    
                    $curl->httpHeaders(
                        array(
                            'accept: application/json, text/plain, */*',
                            'accept-encoding: gzip, deflate, br',
                            'accept-language: es-ES,es;q=0.9',
                            "authorization: Bearer $bearer",
                            'cache-control: no-cache',
                            'content-type: application/json',
                            'origin: https://zonacliente.nexusenergia.com',
                            'pragma: no-cache',
                            'referer: https://zonacliente.nexusenergia.com/',
                            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
                            'sec-ch-ua-mobile: ?0',
                            'sec-ch-ua-platform: "Windows"',
                            'sec-fetch-dest: empty',
                            'sec-fetch-mode: cors',
                            'sec-fetch-site: same-site',
                            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.54 Safari/537.36'
                        )
                    );
                    //$curl->follow(true);
                    $curva = json_decode($curl->execute());
                    
                    if (!empty($curva)){
                        $date = new DateClass;
                        foreach ($curva->total->curva as $num_row=>$row){
                            
                            $date->stringToDate(str_replace('T', ' ', $row->fechaHora), 'Y-m-d H:i:s');
                            settype($row->ae, 'float');
                            
                            for ($x=1; $x<=4; $x++){
                                $linea = array_fill_keys($headers_cdc, '');
                                
                                $date->add(0,0,0,0,15);
                                $linea['Fecha']     = $date->format('d/m/Y H:i');
                                $linea['Periodo']   = $row->periodo;
                                $linea['kW_Compra'] = $row->ae;
                                
                                $CdC[] = $linea;
                                unset($linea);
                            }
                            unset($curva->total->curva[$num_row], $row);
                        }
                        unset($date);
                    }
                    unset($curva);
                    
                    if (isset($CdC)){
                        $fopen = fopen($cups.".csv", "a");
                        foreach ($CdC as $num_row=>$row){fputcsv($fopen, $row, ";");}
                        fclose($fopen);
                        unset($fopen, $CdC);
                        
                        $files[] = $cups.".csv";
                    }
                }//Para cada CUPS
                
                if (isset($files)){
                    merge_and_dwd_zip('CdC NEXUS.zip', $files);
                }
                
                break;
        }
        
        header ("Location: descargas.php");
        
        break;
        
    case 'download_curvas_datadis':
        
        if (!isset($_POST['desde']) || empty($_POST['desde'])){header ('Location: descargas.php'); die;}
        
        set_time_limit(0);
        $timestamp = getMicrotimeString();
            
        //Recupera las variables enviadas con POST
        $desde = new DateClass;
        $hasta = new DateClass;
        
        $desde->stringToDate($_POST['desde'], 'd/m/Y');
        
        if (isset($_POST['hasta']) && !empty($_POST['hasta'])){
            $hasta->stringToDate($_POST['hasta'], 'd/m/Y');
        }
        
        $urlDesde = urlencode($desde->format('Y/m/d'));
        $urlHasta = urlencode($hasta->format('Y/m/d'));
        
        //CON FICHERO
        if (!empty($_FILES['fichero']['tmp_name'][0])){
            foreach($_FILES['fichero']['tmp_name'] as $file){
                $SprdSht = new SprdSht;
                $SprdSht->load($file);
                $cups = $SprdSht->getArray(true);
                unset($SprdSht);
            }
            
            //Recupera los CIFs de nuestra BBDD
            $str_cups = "('".implode("', '", array_column($cups, 'CUPS'))."')";
            
            $strSQL = "SELECT
                            a.cif,
                            a.CUPS
                        FROM clientes a
                        INNER JOIN (
                            SELECT
                                CUPS,
                                MAX(fecha_alta) maxalta
                            FROM clientes
                            WHERE CUPS IN $str_cups
                            GROUP BY CUPS
                            ) b
                        ON a.CUPS=b.CUPS
                        AND a.fecha_alta=b.maxalta
                        WHERE a.CUPS IN $str_cups
                        ORDER BY a.CUPS, a.fecha_inicio";
            
            $Conn = new Conn('mainsip', 'develop');
            $cups_cifs = $Conn->getArray($strSQL, true);
            unset($Conn);
            
            //Comprueba si los CUPS están en la intranet
            foreach ($cups as $num_row=>$row_cup){
                $cup = $row_cup['CUPS'];
                if (!in_array($cup, array_column($cups_cifs, 'CUPS'))){
                    $errores[] = array('CUPS'=>$cup, 'COMENTARIO'=>'No existe este CUPS en la intranet');
                }
            }
            
            //Crea array con referencia CUPS para multiplicar/dividir
            foreach ($cups as $num_row=>$row){
                $cups[$row['CUPS']] = $row;
                unset($cups[$num_row]);
            }
            
        //CON CLIENTE
        } elseif (isset($_POST['cli'])){
            $cli = $_POST['cli'];
            
            $strSQL = "SELECT
                            a.cif,
                            a.CUPS
                        FROM clientes a
                        INNER JOIN (
                            SELECT
                                CUPS,
                                MAX(fecha_alta) maxalta
                            FROM clientes
                            WHERE Grupo='$cli'
                            GROUP BY CUPS
                            ) b
                        ON a.CUPS=b.CUPS
                        AND a.fecha_alta=b.maxalta
                        WHERE a.Grupo='$cli'
                        ORDER BY a.CUPS, a.fecha_inicio";
            
            $Conn = new Conn('mainsip', 'develop');
            $cups_cifs = $Conn->getArray($strSQL, true);
            unset($Conn);
            
        } else {
            header ('Location: descargas.php');
            die;
        }
        
        //Crea array con los CUPS divididos por CIF
        foreach ($cups_cifs as $num_row=>$row){$cifs_cups[$row['cif']][] = $row['CUPS'];}
        unset($cups_cifs);
        
        $headers_cdc = array('Periodo', 'Fecha', 'kW_Compra', 'KVAr_C1', 'Capacitiva');
        
        //Empieza a solicitar los datos de datadis
        $curl = new curlClass;
        $curl->url('https://datadis.es/nikola-auth/tokens/login');
        $username = 'B82696782';
        $pwd      = urlencode('Mmont@2018');
        $curl->POST("username=B82696782&password=$pwd");
        $token = $curl->execute();

        $headers = array("Authorization: Bearer $token");
        $curl->httpHeaders($headers);
        
        //Por cada CIF busca los datos de sus CUPS
        foreach ($cifs_cups as $cif=>$arr_cups){
            
            $curl->url("https://datadis.es/api-private/api/get-supplies?authorizedNif=$cif");
            $curl->GET();
            $supplies = json_decode($curl->execute());
            
            foreach ($supplies as $num_row=>$row){
                $cups_datadis[] = (!isset($row->cups)) ? $num_row : substr($row->cups, 0, 20);
            }
            
            $supplies = array_combine($cups_datadis, $supplies);
            
            //Para cad CUPS
            foreach ($arr_cups as $num_cup=>$cup){
                
                if (!in_array($cup, $cups_datadis)){
                    $errores[] = array('CUPS'=>$cup, 'COMENTARIO'=>'No está en DATADIS');
                    continue;
                }
                
                $distributorCode = $supplies[$cup]->distributorCode;
                $pointType = $supplies[$cup]->pointType;
                $codigo_cups = $supplies[$cup]->cups;
                //0 = Horaria
                //1 = Cuartohoraria
                for ($x=0; $x<=1; $x++){
                    $curl->url("https://datadis.es/api-private/api/get-consumption-data?cups=$codigo_cups&distributorCode=$distributorCode&startDate=$urlDesde&endDate=$urlHasta&measurementType=$x&pointType=$pointType&authorizedNif=$cif");
                    $curva = json_decode($curl->execute());
                    if (!empty($curva)){break;}
                }
                unset($distributorCode, $pointType, $codigo_cups);
                
                if (empty($curva)){
                    $errores[] = array('CUPS'=>$cup, 'COMENTARIO'=>'No hay datos para este suministro');
                    continue;
                }
                
                
                //Si hay curva
                $date = new DateClass;
                $consumo_total = array();
                $maxima = 0;
                foreach ($curva as $num_row=>$row){
                    
                    if (!isset($row->time)){continue;}
                    
                    $linea = array_fill_keys($headers_cdc, '');
                    $date->stringToDate($row->date, 'Y/m/d');
                    $linea['Fecha'] = $date->format('d/m/Y').' '.$row->time;
                    
                    //Multplica o divide el consumo según lo que sale en el fichero, si hay fichero
                    if (isset($cups)){
                        switch (true){
                            case (!empty($cups[$cup]['MULTIPLICAR']) || $cups[$cup]['MULTIPLICAR']!=0):
                                $linea['kW_Compra'] = $row->consumptionKWh*$cups[$cup]['MULTIPLICAR'];
                                break;
                            case (!empty($cups[$cup]['DIVIDIR']) || $cups[$cup]['DIVIDIR']!=0):
                                $linea['kW_Compra'] = $row->consumptionKWh/$cups[$cup]['DIVIDIR'];
                                break;
                            default:
                                $linea['kW_Compra'] = $row->consumptionKWh;
                                break;
                        }
                    } else {
                        $linea['kW_Compra'] = $row->consumptionKWh;
                    }
                    
                    $CdC[] = $linea;
                    
                    if (!isset($consumo_total[$date->format('Y')])){$consumo_total[$date->format('Y')] = 0;}
                    
                    $consumo_total[$date->format('Y')] += $linea['kW_Compra'];
                    $maxima = ($linea['kW_Compra']>$maxima) ? $linea['kW_Compra'] : $maxima;
                    
                    unset($linea);
                }
                unset($date);
                
                //Recopila los datos de fecha min, fecha max, num registros
                if (isset($CdC) && !empty($CdC)){
                    
                    $fecha_desde = new DateClass;
                    $fecha_hasta = new DateClass;
                    
                    $fecha_desde->stringToDate($CdC[0]['Fecha'], 'd/m/Y H:i');
                    $num_registros = count($CdC);
                    $fecha_hasta->stringToDate($CdC[$num_registros-1]['Fecha'], 'd/m/Y H:i');

                    $interval = date_diff($fecha_hasta->vardate, $fecha_desde->vardate);
                    $interval = ($x==1) ? $interval->format('%a')*24*4 : $interval->format('%a')*24;
                    $horaria = ($x==1) ? 'CUARTOHORARIA' : 'HORARIA';
                    
                    $dato = (!isset($curva[0]->obtainMethod)) ? '' : $curva[0]->obtainMethod;
                    
                    $linea_datos = array(
                        'CUPS'                  => $cup,
                        'DESDE'                 => $fecha_desde->format('d/m/Y H:i'),
                        'HASTA'                 => $fecha_hasta->format('d/m/Y H:i'),
                        'PORCENTAJE'            => round($num_registros/$interval, 2),
                        'HORARIA/CUARTOHORARIA' => $horaria,
                        'DATO'                  => $dato,
                        'MAXIMA'                => $maxima
                    );
                    
                    foreach ($consumo_total as $yr=>$value){$linea_datos[$yr] = $value;}
                    
                    $resultados[] = $linea_datos;
                    unset($linea_datos, $interval, $fecha_desde, $fecha_hasta, $num_registros);
                    
                    $fopen = fopen($cup."$timestamp.csv", "a");
                    foreach ($CdC as $num_row=>$row){fputcsv($fopen, $row, ";");}
                    fclose($fopen);
                    unset($fopen, $CdC, $maxima, $consumo_total);
                    
                    $files[] = $cup."$timestamp.csv";
                }
            }//Para cada CUPS
            unset($supplies, $cups_datadis);
        }//Para cada CIF
        
        
        if (isset($errores)){
            $SprdSht = new SprdSht;
            $SprdSht->nuevo();
            $SprdSht->putArray($errores, true);
            $SprdSht->save("Errores descarga DATADIS$timestamp.xlsx");
            $files[] = "Errores descarga DATADIS$timestamp.xlsx";
            unset($errores, $SprdSht);
        }
        
        if (isset($resultados)){
            $SprdSht = new SprdSht;
            $SprdSht->nuevo();
            $SprdSht->putArray($resultados, true);
            $SprdSht->save("Detalle curvas DATADIS$timestamp.xlsx");
            $files[] = "Detalle curvas DATADIS$timestamp.xlsx";
            unset($resultados, $SprdSht);
        }
        
        merge_and_dwd_zip('CdC DATADIS.zip', $files, $timestamp);
        
        break;
        
    case 'rename_fras':
RENOMBRAR:
        //EVM
        $dir = "//192.168.0.250/NAS/FACTURAS EVM/";
        $files = @scandir($dir);
				
        if (!empty($files)){
            foreach($files as $file){
                
                if ($file=='..' || $file=='.'){continue;}
                
                $new_file = substr(str_replace('.pdf', '', $file), -8).'.pdf';
                rename ($dir.$file, "//192.168.0.250/NAS/FACTURAS/$new_file");
                unset($new_file);
            }
        }
        unset($files, $dir);
        
        //TOTAL ELEIA
        $dir = "//192.168.0.250/NAS/FACTURAS TOTAL ELEIA/";
        $files = @scandir($dir);
				
        if (!empty($files)){
            foreach($files as $file){
                
                if ($file=='..' || $file=='.'){continue;}
                
                $expl_file = explode('_', $file);
                $new_file = $expl_file[1].' '.$expl_file[2].'.pdf';
                
                rename ($dir.$file, "//192.168.0.250/NAS/FACTURAS/$new_file");
                unset($new_file, $expl_file);
            }
        }
        
        //NEXUS
        $dir = "//192.168.0.250/NAS/FACTURAS NEXUS/";
        $files = @scandir($dir);
				
        if (!empty($files)){
            foreach($files as $file){
                
                if ($file=='..' || $file=='.'){continue;}
                
                $expl_file = explode('.', $file);
                $new_file = $expl_file[1].'.pdf';
                
                rename ($dir.$file, "//192.168.0.250/NAS/FACTURAS/$new_file");
                unset($new_file, $expl_file);
            }
        }
        
        header ("Location: descargas.php"); die;
        
        break;
        
	case 'get_comercializadoras':
		
		$cli = $_POST['cliente'];
		
		$strSQL = "SELECT DISTINCT COMM_DISTR FROM claves_comm_distr WHERE CLIENTE='$cli' AND IDENTIFICATIVO='C' ORDER BY COMM_DISTR";
		$Conn 	= new Conn('local', 'enertrade');
		$datos 	= $Conn->getArray($strSQL, true);
		echo json_encode($datos);
		
		break;
		
	case 'get_usuario':
		
		$cli 	= $_POST['cliente'];
		$comm 	= $_POST['comm'];
		
		$strSQL = "SELECT USUARIO FROM claves_comm_distr WHERE CLIENTE='$cli' AND COMM_DISTR='$comm' AND IDENTIFICATIVO='C' ORDER BY USUARIO";
		$Conn 	= new Conn('local', 'enertrade');
		$datos 	= $Conn->getArray($strSQL, true);
		echo json_encode($datos);
		
		break;
		
	case 'get_timestamp':
		
		session_start();
		$timestamp = $_SESSION['download_fras'];
		session_write_close();
		
		echo $timestamp;
		break;
	
	case 'get_porcentaje':
		if (isset($_POST['timestamp'])){
			$Session = new Session;
			$Session->open($_POST['timestamp']);
			$porcentaje = $Session->read('porcentaje');
			if (!$porcentaje){$Session->close($_POST['timestamp']);}
			echo $porcentaje;
		} else {
			echo false;
		}
		
		break;
		
	case 'download_gdos':
		
		if (!isset($_FILES['fichero']['tmp_name'][0]) || empty($_FILES['fichero']['tmp_name'][0])){header ("Location: info_varias.php"); die;}
		
		$timestamp = getMicrotimeString();
		
		//set_time_limit(0);
		
		//Obtiene el listado de CUPS
		$filenum = 0;
		foreach($_FILES['fichero']['tmp_name'] as $file){
			if (is_uploaded_file($file)){
				
				$SprdSht = new SprdSht;
				$SprdSht->load($file);
				$CUPS = $SprdSht->getArray(true);
				unset($SprdSht);
			}
		}
		
		$CUPS 		= array_column($CUPS, 'CUPS');
		$tipo 		= ($_POST['tipo']=='XLS') ? 1 : 0;
		$extension 	= ($_POST['tipo']=='XLS') ? '.xls' : '.pdf';
		$ano 		= $_POST['ano'];
		
		$curl = new curlClass;
		foreach ($CUPS as $num_cup=>$cup){
			usleep(200000);
			$curl->url("https://gdo.cnmc.es/CNE/informePdfPorCUPS.do?anio=$ano&tipoFiltro=1&cups=$cup&tipoArchivo=$tipo");
			$datos = $curl->execute();
			
			$filename = $cup.$timestamp.$extension;
			$zip_name = $cup.$timestamp.'.zip';
			file_put_contents($zip_name, $datos);
			
			$zip = new ZipArchive;

			if ($zip->open($zip_name) === TRUE){
				
				for ($x=0; $x<$zip->numFiles; $x++){
					$zip->extractTo('EXTRACCIONES', array($zip->statIndex($x)['name']));
					rename('EXTRACCIONES/'.$zip->statIndex($x)['name'], 'EXTRACCIONES/'.$filename);
				}
				$zip->close();
				unlink($zip_name);
			}
			unset($zip);
			
			$files[] = 'EXTRACCIONES/'.$filename;
		}
		
		merge_and_dwd_zip('GDOs.zip', $files, $timestamp);
		
		break;
}

?>