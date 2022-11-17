<?php

$fichero 	= basename(__FILE__);

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$Conn = new Conn('local', 'enertrade');

if (isset($_GET['id']) && !empty($_GET['id'])){
	
	$action 	= "mod";
	
	$id 		= $_GET['id'];
	$result 	= $Conn->oneRow("SELECT * FROM datos_contadores WHERE ID=$id");
	unset($Conn);
	
	$contador			= $result['CONTADOR'];
	$tarifa				= $result['TARIFA'];
	$tarifa_nueva		= $result['TARIFA_NUEVA'];
	$cal				= $result['CALENDARIO'];
	$cal_nuevo			= $result['CALENDARIO_NUEVO'];
	$cups				= $result['CUPS'];
	$cliente 			= $result['GRUPO'];
	$cif				= $result['CIF'];
	$empresa			= $result['RAZON_SOCIAL'];
	$precio				= $result['TIPO_PRECIO'];
	$comm 				= $result['COMERCIALIZADORA'];
	$enum 				= $result['ENUMERACION'];
	$distr				= $result['DISTRIBUIDORA'];
	$tension			= $result['TENSION'];
	$fin_contrato		= $result['FIN_CONTRATO'];
	$equipo_medida		= $result['EQUIPO_MEDIDA'];
	$grupo_envio		= $result['GRUPO_ENVIO'];
	$grupo_envio_diario	= $result['GRUPO_ENVIO_DIARIO'];
	
	for ($i=1; $i<=6; $i++){
		$P 		= "P$i";
		$PC 	= "PC$i";
		$ATR 	= "ATR$i";
		$TP 	= "TP$i";
		
		$$P		= $result[$P];
		$$PC	= $result[$PC];
		$$ATR	= $result[$ATR];
		$$TP	= $result[$TP];
	}
	
} else {
	
	$action 	= "add";
	
	$contador 			= (isset($_GET['contador'])) 			? $_GET['contador'] 			: '';
	$tarifa 			= (isset($_GET['tarifa'])) 				? $_GET['tarifa'] 				: '2.0A';
	$tarifa_nueva 		= (isset($_GET['tarifa_nueva'])) 		? $_GET['tarifa_nueva'] 		: '20TD';
	$cal 				= (isset($_GET['cal'])) 				? $_GET['cal'] 					: '6_PEN';
	$cal_nuevo 			= (isset($_GET['cal_nuevo'])) 			? $_GET['cal_nuevo'] 			: 'PEN';
	$cups 				= (isset($_GET['cups'])) 				? $_GET['cups'] 				: '';
	$cliente 			= (isset($_GET['cliente'])) 			? $_GET['cliente'] 				: 'ABERTIS';
	$cif 				= (isset($_GET['cif'])) 				? $_GET['cif'] 					: '';
	$empresa 			= (isset($_GET['empresa'])) 			? $_GET['empresa'] 				: '';
	$precio 			= (isset($_GET['precio'])) 				? $_GET['precio'] 				: 'FIJO';
	$comm 				= (isset($_GET['comm'])) 				? $_GET['comm'] 				: '-';
	$enum 				= (isset($_GET['enum'])) 				? $_GET['enum'] 				: '-';
	$distr 				= (isset($_GET['distr'])) 				? $_GET['distr'] 				: '';
	$tension 			= (isset($_GET['tension'])) 			? $_GET['tension'] 				: '';
	$fin_contrato 		= (isset($_GET['fin_contrato'])) 		? $_GET['fin_contrato'] 		: '';
	$equipo_medida 		= (isset($_GET['equipo_medida'])) 		? $_GET['equipo_medida'] 		: '';
	$grupo_envio 		= (isset($_GET['grupo_envio'])) 		? $_GET['grupo_envio'] 			: '';
	$grupo_envio_diario = (isset($_GET['grupo_envio_diario'])) 	? $_GET['grupo_envio_diario'] 	: '';
	
	
	
	for ($i=1; $i<=6; $i++){
		$P 		= "P$i";
		$PC 	= "PC$i";
		$ATR 	= "ATR$i";
		$TP 	= "TP$i";
		
		$$P 	= (isset($_GET[$P])) 	? $_GET[$P] 	: '';
		$$PC 	= (isset($_GET[$PC])) 	? $_GET[$PC] 	: '';
		$$ATR 	= (isset($_GET[$ATR])) 	? $_GET[$ATR] 	: '';
		$$TP 	= (isset($_GET[$TP])) 	? $_GET[$TP] 	: '';
	}
	
}

include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Datos contadores
		 <small><strong><font color="red">
			Contador, Tarifa, Calendario y Cliente no pueden ser vacíos!
		</font></strong></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Telemedida</li>
		<li class="active">Datos contadores</li>
      </ol>
    </section>
	
    <!-- Main content -->
    <section class="content">

      <!-- SELECT2 EXAMPLE -->
	<div class="row">
      <div class="col-md-12">
		  <form role="form" method="post" action="<?php echo "acciones.php?table=datos_contadores&action=$action";?>">
		  <div class="box box-default">
			<div class="box-body">
				<div class="form-group">
          		<div class="row">
              		<div class="col-md-3">
						<div class="form-group">
							<label>Contador</label>
							<input class="form-control" type="text" placeholder="Contador" name="contador" value="<?php echo $contador;?>"/>
						</div>
					</div>
					
					<div class="col-md-1">
						<div class="form-group">
							<label>Tarifa</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Tarifa" name="tarifa">
								<?php
								$Lista = new Lista('tarifas');
								$Lista->print_list($tarifa);
								?>
							</select>
						 </div>
                        <div class="form-group">
							<label>Nueva tarifa</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Tarifa" name="tarifa_nueva">
								<?php
								$Lista->change_list('tarifas_nuevas');
								$Lista->print_list($tarifa_nueva);
								?>
							</select>
						 </div>
					</div>
					
					<div class="col-md-1">
						<div class="form-group">
							<label>Calendario</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Calendario" name="cal">
								<?php
								$Lista->change_list('calendarios');
								$Lista->print_list($cal);
								?>
							</select>
						 </div>
                        <div class="form-group">
							<label>Calendario nuevo</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Calendario" name="cal_nuevo">
								<?php
								$Lista->change_list('calendarios_nuevos');
								$Lista->print_list($cal_nuevo);
								?>
							</select>
						 </div>
					</div>
					
					<div class="col-md-3">
						<div class="form-group">
							<label>CUPS</label>
							<input class="form-control" type="text" placeholder="CUPS" name="cups" value="<?php echo $cups;?>"/>
						</div>
					</div>
					
					<div class="col-md-4">
						<div class="form-group">
							<label>Cliente</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cliente">
								<?php
								$Lista->change_list('clientes');
								$Lista->print_list($cliente);
								?>
							</select>
						 </div>
					</div>
				</div>
				
					
				<div class="row">
					<div class="col-md-2">
						<div class="form-group">
							<label>CIF</label>
							<input class="form-control" type="text" placeholder="CIF" name="cif" value="<?php echo $cif;?>"/>
						</div>
					</div>
					
					<div class="col-md-3">
						<div class="form-group">
							<label>Razón social</label>
							<input class="form-control" type="text" placeholder="Razón social" name="empresa" value="<?php echo $empresa;?>"/>
						</div>
					</div>
                    
                    <div class="col-md-1">
						<div class="form-group">
							<label>Enum</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Enumeración" name="enum">
								<option>-</option> 
								<?php
								$Lista->change_list('prioridad');
								$Lista->print_list($enum);
								?>
							</select>
						 </div>
					</div>
					
					<div class="col-md-1">
						<div class="form-group">
							<label>Tipo precio</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Tipo precio" name="precio">
								 <?php
								 if ($precio!='FIJO'){
									 echo '<option>FIJO</option>
									 		<option selected="selected">INDEXADO</option>';
								 } else {
									 echo '<option selected="selected">FIJO</option>
									 		<option>INDEXADO</option>';
								 }
								 
								 ?>
							</select>
						 </div>
					</div>
					
					<div class="col-md-2">
						<div class="form-group">
							<label>Comercializadora</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Comercializadora" name="comm">
								<option>-</option> 
								<?php
								$Lista->change_list('comm_elec_mainsip');
								$Lista->print_list($comm);
								?>
							</select>
						 </div>
					</div>
					
					<div class="col-md-3">
						<div class="form-group">
							<label>Distribuidora</label>
							<input class="form-control" type="text" placeholder="Distribuidora" name="distr" value="<?php echo $distr;?>"/>
						</div>
					</div>
				</div>
				
				
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label>Tensión</label>
							<input class="form-control" type="text" placeholder="Tensión" name="tension" value="<?php echo $tension;?>"/>
						</div>
					</div>
					
					<div class="col-md-3">
						<div class="form-group">
							<label>Fin contrato</label>
							<input class="form-control" type="text" placeholder="Fin contrato" name="fin_contrato" value="<?php echo $fin_contrato;?>"/>
						</div>
					</div>
					
					<div class="col-md-2">
						<div class="form-group">
							<label>Equipo de medida</label>
							<input class="form-control" type="number" step=0.001 placeholder="Equipo de medida" name="equipo_medida" value="<?php echo $equipo_medida;?>"/>
						</div>
					</div>
					
					<div class="col-md-2">
						<div class="form-group">
							<label>Grupo de envio</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Grupo de envío" name="grupo_envio">
								 <option selected="selected">Ninguno</option>;
									<?php
									$Lista->change_list('envios_gestinel');
									$Lista->print_list($grupo_envio);
									?>
							</select>
						 </div>
					</div>
					
					<div class="col-md-2">
						<div class="form-group">
							<label>Grupo de envio diario</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Grupo de envío" name="grupo_envio_diario">
								 <option selected="selected">Ninguno</option>;
									<?php
									$Lista->change_list('envios_gestinel_diarios');
									$Lista->print_list($grupo_envio_diario);
									unset($Lista);
									?>
							</select>
						 </div>
					</div>
					
				</div>
				
				
				<div class="row">
					<div class="col-md-2">
						<div class="form-group">
							<label>P1</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="P1" name="P1" value="<?php echo $P1;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>P2</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="P2" name="P2" value="<?php echo $P2;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>P3</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="P3" name="P3" value="<?php echo $P3;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>P4</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="P4" name="P4" value="<?php echo $P4;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>P5</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="P5" name="P5" value="<?php echo $P5;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>P6</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="P6" name="P6" value="<?php echo $P6;?>"/>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-2">
						<div class="form-group">
							<label>PC1</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="PC1" name="PC1" value="<?php echo $PC1;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>PC2</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="PC2" name="PC2" value="<?php echo $PC2;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>PC3</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="PC3" name="PC3" value="<?php echo $PC3;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>PC4</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="PC4" name="PC4" value="<?php echo $PC4;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>PC5</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="PC5" name="PC5" value="<?php echo $PC5;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>PC6</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="PC6" name="PC6" value="<?php echo $PC6;?>"/>
						</div>
					</div>
				</div>
				
				
				<div class="row">
					<div class="col-md-2">
						<div class="form-group">
							<label>ATR1</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="ATR1" name="ATR1" value="<?php echo $ATR1;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>ATR2</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="ATR2" name="ATR2" value="<?php echo $ATR2;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>ATR3</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="ATR3" name="ATR3" value="<?php echo $ATR3;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>ATR4</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="ATR4" name="ATR4" value="<?php echo $ATR4;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>ATR5</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="ATR5" name="ATR5" value="<?php echo $ATR5;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>ATR6</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="ATR6" name="ATR6" value="<?php echo $ATR6;?>"/>
						</div>
					</div>
				</div>
				
				
				<div class="row">
					<div class="col-md-2">
						<div class="form-group">
							<label>TP1</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="TP1" name="TP1" value="<?php echo $TP1;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>TP2</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="TP2" name="TP2" value="<?php echo $TP2;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>TP3</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="TP3" name="TP3" value="<?php echo $TP3;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>TP4</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="TP4" name="TP4" value="<?php echo $TP4;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>TP5</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="TP5" name="TP5" value="<?php echo $TP5;?>"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>TP6</label>
							<input class="form-control" type="number" step=0.000000001 placeholder="TP6" name="TP6" value="<?php echo $TP6;?>"/>
						</div>
					</div>
				</div>
				
				<input type="hidden" name="id" value="<?php echo $id;?>"/>
				
				<div class="box-footer">
					<button type="submit" class="btn btn-primary">Guardar</button>
				</div>
				
              </div>
              <!-- /.form-group -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
	  </form>
	<!-- /form -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php") ?>


<!-- page script -->
<script>

	function confirmation() {
    if(confirm("Ejecutar esta acción?"))
    {
        return true;
    }
    return false;
	}
</script>
</body>
</html>