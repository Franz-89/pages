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
        Elaboración FF
        <small>Elaboración ficheros de facturación</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Operaciones</li>
        <li class="active">Elaboración FF</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
		   
	<div class="row">
		
		
		<div class="col-md-12">
			<div class="row">
			    <div class="col-md-12">
                    <h3>
                        Electricidad
                    </h3>
                </div>
                
			  <div class="col-md-3">
				  <div class="box box-primary">
					<div class="box-body">
						
						<!-- ENDESA BT (.xlsx) -->
							<form role="form" method="post" action="ff_actions.php" enctype="multipart/form-data">
							  <div class="form-group">
								  <div class="form-group">
									<label>(.xlsx/.txt/.csv)</label>
									<input type="file" accept=".xlsx, .txt, .csv" name ="fichero[]" multiple="multiple">
									<input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />

									<p class="help-block">
										Fichero de facturación (Max 10Mb)<br>
										<a href="elab_ff_ejemplos/FF.zip" download>Ficheros admitidos</a>
									</p>
									<p class="help-block"></p>
								  </div>
								<div class="box-footer">
									<button type="submit" name="action" value="electricidad" class="btn btn-primary">Elaborar</button>
								</div>
							  </div>
							</form>
                        
					</div>
				</div>
			  </div>
                
                
                <!-- IB ACORDEON (.xlsx) -->
				<div class="col-md-3">
					<form role="form" method="post" action="ff_actions.php" enctype="multipart/form-data">
					  <div class="box box-primary">
						<div class="box-body">
							<div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-ibacor"><i class="fa fa-info-circle"></i></button>
							  </div>
						  <div class="form-group">
							  <div class="form-group">
								<label>Iberdrola acordeón ADIF (.xlsx)</label>
								<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]" multiple="multiple">
								<input type="hidden" name="MAX_FILE_SIZE" value="10485760" />

								<p class="help-block">
									FF (Max 10Mb)<br>
									<a href="elab_ff_ejemplos/ACORDEÓN.xlsx" download>Ejemplo</a>
								</p>
							  </div>
							<div class="box-footer">
								<button type="submit" name="action" value="ib_acordeon_xlsx_adif" class="btn btn-primary">Elaborar</button>
							</div>
						  </div>
						</div>
					  </div>
					</form>
				</div>
                
			</div>
			<!-- .row -->
            
            <div class="row">
                <div class="col-md-12">
                    <h3>
                        Gas
                    </h3>
                </div>
                <div class="col-md-3">
				    <div class="box box-primary">
                      <form role="form" method="post" action="ff_actions.php" enctype="multipart/form-data">
						<div class="box-body">
							<div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-gascor"><i class="fa fa-info-circle"></i></button>
							  </div>
						  <div class="form-group">
							  <div class="form-group">
								<label>(.xlsx)</label>
								<input type="file" accept=".xlsx" name ="fichero[]" multiple="multiple">
								<input type="hidden" name="MAX_FILE_SIZE" value="10485760" />

								<p class="help-block">
									FF (Max 10Mb)<br>
									<a href="elab_ff_ejemplos/FF GAS.zip" download>Ficheros admitidos</a>
								</p>
							  </div>
							<div class="box-footer">
								<button type="submit" name="action" value="gas" class="btn btn-primary">Elaborar</button>
							</div>
						  </div>
						</div>
					  
				      </form>
                    </div>
                </div>
                
				
                
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