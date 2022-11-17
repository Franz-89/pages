<?php
$fichero = str_replace(".php", "", basename(__FILE__));
$sql_table = $fichero;

include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Conversiones
        <small>Elaboración de distintos ficheros</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Operaciones</li>
        <li class="active">Conversiones</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
		   
	<div class="row">
		
		
		<div class="col-md-12">
			<div class="row">
                
				<!-- CdC -->
				<div class="col-md-3">
					<form role="form" method="post" action="conversiones_actions.php" enctype="multipart/form-data">
					  <div class="box box-primary">
						  <div class="box-header with-border">
								<h3 class="box-title">CdC</h3>
							  	<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-cdc"><i class="fa fa-info-circle"></i></button>
								  </div>
							</div>
						<div class="box-body">
						  <div class="form-group">
							  <div class="form-group">
								<label>Curvas (.xlsx, .html, .csv)</label>
								<input type="file" name ="fichero[]" multiple="multiple">
								<input type="hidden" name="MAX_FILE_SIZE" value = "100000000000000" />
								
								<p class="help-block">CdC de las comercializadoras</p>
							  </div>
							  
							<div class="box-footer">
								<button type="submit" name="action" value="cdc" class="btn btn-primary">Elaborar</button>
							</div>
						  </div>
						</div>
					  </div>
					</form>
				</div>
				
				
				<!-- CdC -->
				<div class="modal fade" id="modal-cdc">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">CdC</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Este apartado convierte las curvas de carga descargadas de las diferentes comercializadoras en un CSV para la subida a la intranet.<br><br>
							Las curvas aceptadas son las siguientes:
							<ul style="list-style-type:disc;">
								<li>ENDESA (.html)</li>
								<li>EDP (.csv)</li>
								<li>ENGIE (.csv)</li>
								<li>NATURGY (.xlsx)</li>
								<li>TOTAL (.xlsx)</li>
								<li>IBERDROLA cuartohoraria potencia (.xlsx)</li>
								<li>IBERDROLA horaria activa (.xlsx)</li>
								<li>GESTINEL (.xlsx)</li>
							</ul>
							Las curvas han de subirse sin modificarse a excepción de:
							<ul style="list-style-type:disc;">
								<li>NATURGY: como se descarga mes a mes, es necesario juntarlas en un unico fichero .xlsx, manteniendo el encabezado.</li>
								<li>ENDESA: solo hay que renombrar el fichero con el CUPS y cambiar la extensión de .xls a .html</li>
							</ul>
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
				  </div>
				</div>
				<!-- /.modal -->
				
			</div>
			<!-- .row -->
		</div>
		<!-- .col -->
	</div>
	<!-- .row -->
		
	</div>
	<!-- .content wrapper -->
		
    </section>

  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php"); ?>

</body>
</html>