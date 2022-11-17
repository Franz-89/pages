<?php
$fichero 	= str_replace(".php", "", basename(__FILE__));

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Claves clientes &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.exy8dfd9kyb7' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
        
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Info</li>
		<li class="active">Claves clientes</li>
      </ol>
    </section>
	
    <!-- Main content -->
    <section class="content">
      <div class="row">
          <div class="col-md-12">
              <div class="box box-primary">
                <!-- /.box-header -->
                <div class="box-body">
                  <div class="col-md-12">
                      
                      <form onsubmit="addClaveCommDistr();return false">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Cliente</label>
                                <select class="form-control select2" style="width: 100%;" id="cli" required>
                                    <?php
                                    $Lista = new Lista('clientes');
                                    $Lista->print_list();
                                    ?>
                                </select>
                             </div>
                         </div>
                        <div class="col-md-2">
                            <label>Comercializadora/Distribuidora</label>
                            <select class="form-control select2" style="width: 100%;" id="add_comm_distr">
                                <?php
                                $Lista->change_list('comercializadoras');
                                $Lista->print_list();
                                ?>
                            </select>
                         </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Usuario</label>
                                <input type="text" class="form-control" placeholder="Usuario..." id="usuario" required>
                             </div>
                         </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Contraseña</label>
                                 <input type="text" class="form-control" placeholder="Contraseña..." id="contrasena" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Comentarios</label>
                                <input type="text" class="form-control" id="comentarios" placeholder="Comentarios...">
                             </div>
                         </div>
                        <div class="col-md-2">
                            <label>Comercializadora/Distribuidora</label>
                            <select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" id="C_D">
                                <option selected="selected" value="C">COMERCIALIZADORA</option>
                                <option value="D">DISTRIBUIDORA</option>
                            </select>
                         </div>

                        <div class="col-md-1">
                            <label>&nbsp;</label>
                            <div class="form-group">
                                <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                      </form>
                </div>
                
                    
                <div class="col-xs-12">
                    <div class="col-md-3">
                        <h2>Claves</h2>
                        <div class="form-group">
                            <select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" id="comm_distr" onchange="filterDataType($(this).val())">
                                <option selected="selected" value="C">COMERCIALIZADORA</option>
                                <option value="D">DISTRIBUIDORA</option>
                            </select>
                         </div>
                    </div>
                </div>
                
                <div class="col-xs-12">
                    <table id="claves" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>CLIENTE</th>
                                <th>COMERCIALIZADORA</th>
                                <th>USUARIO</th>
                                <th>CONTRASEÑA</th>
                                <th>COMENTARIOS</th>
                                <th>IDENTIFICATIVO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
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
	
    function addClaveCommDistr(){
          $.ajax({
                url: "js_actions.php?action=addClaveCommDistr",
                method: "POST",
                data: {
                    cli: $('#cli').val(),
                    comm_distr: $('#add_comm_distr').val(),
                    usu: $('#usuario').val(),
                    pwd: $('#contrasena').val(),
                    comentarios: $('#comentarios').val(),
                    C_D: $('#C_D').val()
                },
                async: false,
                success: function () {setTimeout(reloadClaves(), 500)}
            })
	  }
    
	function delClaveCommDistr(valor){
		  if (confirmation()){
			  $.ajax({
					url: "js_actions.php?action=delClaveCommDistr",
					method: "POST",
					data: {id: valor},
					async: false,
					success: function () {setTimeout(reloadClaves(), 500)}
				})
		  }
	  }
	
	function reloadClaves() {$('#claves').DataTable().ajax.reload();}
	
	function filterDataType(dato){$('#claves').DataTable().column(6).search(dato).draw()}
	
  $(function () {
	  
	  $('#claves').DataTable({
		  paging		: true,
		  searching		: true,
		  serverSide	: false,
		  processing	: true,
		  language		: {
			  loadingRecords : '&nbsp;',
			  processing 	 : 'Procesando...'
		  },
		  lengthChange	: true,
		  statesave		: true,
		  ordering    	: true,
		  info        	: true,
		  autoWidth   	: true,
		  dom			: '<"top"f>rt<"bottom"ilp><"clear">',
		  order			: [[ 1, "asc" ]],
		  ajax			: {
			  url 		: "js_actions.php?action=getClavesCommDistr",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0, 6 ],
                visible		: false,
			  	searchable	: true
            }]
		}).column(6).search($('#comm_distr').val()).draw()
  })
</script>
</body>
</html>