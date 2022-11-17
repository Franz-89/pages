<?php
$fichero = str_replace(".php", "", basename(__FILE__));
$sql_table = $fichero;

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Informes
        <small>Informes varios</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Operaciones</li>
        <li class="active">Informes</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
		   
	<div class="row">
		
		
		<div class="col-md-12">
			<div class="row">
				
				<!-- INFORME RECLAMACIONES -->
				<div class="col-md-3">
					<form role="form" method="post" action="informes_actions.php">
                        <div class="box box-primary">
                            <div class="box-body">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Reclamaciones</h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-reclamaciones"><i class="fa fa-info-circle"></i></button>
                                      </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Cliente</label>

                                        <select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli">
                                            <?php
                                            $Lista = new Lista('clientes');
                                            $Lista->print_list('');
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Mes</label>

                                        <select class="form-control select2" style="width: 100%;" data-placeholder="Mes" name="mes">
                                            <?php
                                            $Lista->change_list('mes');
                                            $Lista->print_list(date('01/m/Y'));
                                            ?>
                                        </select>
                                    </div>
                                    <div class="box-footer">
                                        <button type="submit" name="action" value="reclamaciones" class="btn btn-success"><i class="fa fa-download"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
					</form>
                </div>
				
				<!-- INFO -->
				<div class="modal fade" id="modal-reclamacioens">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">RECLAMACIONES</h4>
					  </div>
					  <div class="modal-body">
						<p>
							
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
					<!-- /.modal-content -->
				  </div>
				  <!-- /.modal-dialog -->
				</div>
				<!-- /.modal -->
                
                
                
                <!-- INFORME MENSUAL -->
				<div class="col-md-6">
					<form role="form" method="post" action="informes_actions.php">
                        <div class="box box-primary">
                            <div class="box-body">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Informe mensual</h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-mensual"><i class="fa fa-info-circle"></i></button>
                                      </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cliente</label>

                                        <select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli" id="cli" onchange="getFolderMensual()">
                                            <?php
                                            $Lista->change_list('clientes');
                                            $Lista->print_list('');
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Año (default = en curso)</label>

                                        <select class="form-control select2" style="width: 100%;" data-placeholder="Año" name="ano">
                                            <?php
                                            $Lista->change_list('ano');
                                            $Lista->print_list(date('Y'));
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <label>Dirección del fichero:</label>
                                    <div class="input-group margin">
                                        

                                        <input type="text" class="form-control" id="direccion_informe_mensual">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-info btn-flat" onclick="copyToClipboard()"><i class="fa fa-copy"></i></button>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                      <label>Dato adicionales</label>
                                      <select multiple class="form-control" size=10 name="columnas[]" id="columnas_informe">
                                          <?php
                                          $Lista = new Lista('datos_notelemedidas');
                                          $Lista->print_list();
                                          ?>
                                      </select>
                                    </div>
                                </div>
                                
                                    <div class="box-footer">
                                        <button type="submit" name="action" value="mensual" class="btn btn-primary"><i class="fa fa-refresh"></i></button>
                                    </div>
                            </div>
                        </div>
					</form>
                </div>
                
                
                
                
                
                
                
                
                
                
            </div>
            <!-- .row -->
        </div>
        <!-- .col -->
		
	</div>
	<!-- .content wrapper -->
		
    </section>

  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php"); ?>

      <script>
          
        function getFolderMensual(){

            $.ajax({
                url: "informes_actions.php",
                method: "POST",
                data: {
                    action: "getFolderMensual",
                    cli: $('#cli').val()
                },
                async: false,
                success: function (data) {
                    $('#direccion_informe_mensual').val(data);
                }
            })
        }
          
          function copyToClipboard(){
              $('#direccion_informe_mensual').select();
              document.execCommand("copy");
          }
          
      </script>
      
</body>
</html>