<?php
$fichero = str_replace(".php", "", basename(__FILE__));
$sql_table = $fichero;

if (isset($_GET['comm']))	{$comm 		= $_GET['comm'];} 		else {$comm 	= "IBERDROLA";}
if (isset($_GET['tarifa']))	{$tarifa 	= $_GET['tarifa'];} 	else {$tarifa 	= "";}

if (isset($_GET['mas_ie']) 		&& !empty($_GET['mas_ie']))			{$mas_ie 	= "checked";} 			else {$mas_ie 		= "";}
if (isset($_GET['menos_ie']) 	&& !empty($_GET['menos_ie']))		{$menos_ie 	= "checked";} 			else {$menos_ie 	= "";}
if (isset($_GET['ICP']) 		&& !empty($_GET['ICP']))			{$ICP = $_GET['ICP'];} 				else {$ICP 			= "";}
if (isset($_GET['descuento']) 	&& !empty($_GET['descuento']))		{$descuento = $_GET['descuento'];} 	else {$descuento 	= "";}

for ($i=1;$i<=6;$i++){
	$precio_p = "precio_p$i";
	$interr_p = "interr_p$i";
	if (isset($_GET[$precio_p]))	{$$precio_p = $_GET[$precio_p];} 	else {$$precio_p 	= "";}
	if (isset($_GET[$interr_p]))	{$$interr_p = $_GET[$interr_p];} 	else {$interr_p 	= "";}
	
}

if (isset($_GET['periodo']))	{$periodo 	= $_GET['periodo'];}	else {
	if (date('n')>=6){$periodo = "JUNIO ".date('Y');} else {$periodo = "ENERO ".date('Y');}
}

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Interrumpibilidad &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.8kq7d94rqg5v' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Ayuda</li>
		<li>Calculos</li>
        <li class="active">Interrumpibilidad</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
		   
	<div class="row">
		
		
		<div class="col-md-6">
			
			<!-- PERIODO Y COMERCIALIZADORA -->
		<form role="form" method="POST" action="calculo_interr.php" name="interr_form" enctype="multipart/form-data">
		  <div class="box">
            <div class="box-body">
              <div class="form-group">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Periodo</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Periodo" name="periodo" onchange="javascript:this.form.submit()">
								<?php
								$Lista = new Lista('periodos_interr');
								$Lista->print_list($periodo);
								?>
							</select>
						 </div>
					</div>
						
					<div class="col-md-6">
						<div class="form-group">
							<label>Comercializadora</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Comercializadora" name="comm" onchange="javascript:this.form.submit()">
								<?php
								$Lista->change_list('comercializadoras');
								$Lista->print_list($comm);
								?>
							</select>
						</div>
					</div>
				</div>
				
				  
				  
				  <div class="row">
						<div class="col-md-2">
							<div class="form-group">
								<label>Tarifa</label>
								<select class="form-control select2" style="width: 100%;" data-placeholder="Tarifa" name="tarifa">
									<?php
									$Lista->change_list('tarifas');
									$Lista->print_list($tarifa);
									unset($Lista);
									?>
								</select>
							</div>
						</div>
					</div>
					
					<!-- PERIODOS -->
					<div class="row">
						<div class="col-md-2">
							<div class="form-group">
							  <label>P1</label>
							  <input type="number" step=0.000000001 class="form-control" placeholder="P1" name="precio_p1" value="<?php echo $precio_p1 ?>">
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
							  <label>P2</label>
							  <input type="number" step=0.000000001 class="form-control" placeholder="P2" name="precio_p2" value="<?php echo $precio_p2 ?>">
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
							  <label>P3</label>
							  <input type="number" step=0.000000001 class="form-control" placeholder="P3" name="precio_p3" value="<?php echo $precio_p3 ?>">
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
							  <label>P4</label>
							  <input type="number" step=0.000000001 class="form-control" placeholder="P4" name="precio_p4" value="<?php echo $precio_p4 ?>">
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
							  <label>P5</label>
							  <input type="number" step=0.000000001 class="form-control" placeholder="P5" name="precio_p5" value="<?php echo $precio_p5 ?>">
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
							  <label>P6</label>
							  <input type="number" step=0.000000001 class="form-control" placeholder="P6" name="precio_p6" value="<?php echo $precio_p6 ?>">
							</div>
						</div>
					</div>
					
					<!-- IPC/DESCUENTO -->
					<div class="row">
						<div class="col-md-2">
							<div class="form-group">
								<label>IPC (<a href="https://comunidadhorizontal.com/utiles/ipc-interanual-definicion-valor/" target="_blank">consultar</a>)</label>
								<input type="number" step=0.001 class="form-control" placeholder="Si necesario..." name="ICP" value="<?php echo $ICP ?>">
							</div>
						</div>

						<div class="col-md-2">
							<div class="form-group">
								<label>Descuento</label>
								<input type="number" class="form-control" min="0" max="1" step="0.01" placeholder="Entre 0 y 1" name="descuento" value="<?php echo $descuento ?>">
							</div>
						</div>
						
						<div class="col-md-2">
							<div class="form-group">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="mas_ie" <?php echo $mas_ie ?>> Añadir IE
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" name="menos_ie" <?php echo $menos_ie ?>> Quitar IE
									</label>
								</div>
							</div>
						</div>
					</div>
				  	
				  <div class="row">
					<div class="box-header with-border">
						<h3 class="box-title">Resultado</h3>
					</div>
					<div class="col-md-2">
						<div class="form-group">
						  <label>P1</label>
						  <input type="number" step=0.000000001 class="form-control" disabled="disabled" placeholder="P1" name="interr_p1" value="<?php echo $interr_p1 ?>">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
						  <label>P2</label>
						  <input type="number" step=0.000000001 class="form-control" disabled="disabled" placeholder="P2" name="interr_p2"  value="<?php echo $interr_p2 ?>">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
						  <label>P3</label>
						  <input type="number" step=0.000000001 class="form-control" disabled="disabled" placeholder="P3" name="interr_p3"  value="<?php echo $interr_p3 ?>">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
						  <label>P4</label>
						  <input type="number" step=0.000000001 class="form-control" disabled="disabled" placeholder="P4" name="interr_p4"  value="<?php echo $interr_p4 ?>">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
						  <label>P5</label>
						  <input type="number" step=0.000000001 class="form-control" disabled="disabled" placeholder="P5" name="interr_p5"  value="<?php echo $interr_p5 ?>">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
						  <label>P6</label>
						  <input type="number" step=0.000000001 class="form-control" disabled="disabled" placeholder="P6" name="interr_p6"  value="<?php echo $interr_p6 ?>">
						</div>
					</div>
				 </div>
                  
                  <div class="form-group">
                    <label>Interrumpibilidad (.xlsx)</label>
                    <input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero">
                    <input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />

                    <p class="help-block">
                        Interrumpibilidad<br>
                        <a href="plantillas/Carga interrumpibilidad.xlsx" download>Plantilla de subida</a>
                    </p>
                    <p class="help-block"></p>
                  </div>
                  
				<div class="box-footer">
					<button class="btn btn-primary" name="action" value="calcular">Calcular</button>
					<button class="btn btn-success" name="action" value="descargar"><i class="fa fa-download"></i> Ordenado por tarifa</button>
					<button class="btn btn-success" name="action" value="cargar"><i class="fa fa-upload"></i> Carga</button>
				</div>
              </div>
			</div>
		  </div>
		</form>
	</div>
		
		<!-- TABLA -->
		<div class="col-md-6">
		  <div class="box">
			<div class="box-body no-padding">
			  <?php
				$strSQL = "SELECT * FROM $sql_table WHERE COMERCIALIZADORA='$comm' AND FECHA='$periodo'";
				include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/simple_table.php");
				?>
			</div>
		  </div>
		</div>
	</div>
		
		</div>
		
    </section>

  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php"); ?>

</body>
</html>