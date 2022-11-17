<?php
$fichero = str_replace(".php", "", basename(__FILE__));

session_start();
if (isset($_SESSION['email'])){$usuario = $_SESSION['email'];} else {header ("Location: /Enertrade/index.php");}
session_write_close();

if ($usuario != "fannunziato@enertrade.es"){header ("Location: /Enertrade/pages/Home.php"); die;}

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Plantillas
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li class="active">Home</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content container-fluid">
		<div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <table id="plantillas" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>FICHERO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-body">
                        
                        <p>
                            <ul>
                                <li>Insertar los encabezados separados por "#"</li>
                                <li>Si se inserta más de un encabezado en el numero de factura va a concatenar los valores</li>
                                <li>Si en el numero de factura hay 2 encabezados, añadirá automaticamente el secuencial al final</li>
                                <li>Si no se especifica el total de activa o reactiva sumanará automaticamente de P1 a P6</li>
                                <li>Si no se especifica la BIE sumará automaticamente los conceptos relativos</li>
                                <li>Si no se especifica la BI restará automaticamente el IVA del total</li>
                                <li>Se pueden añadir las constantes "const#" como si fueran encabezados</li>
                                <li>Las constantes (const#) tienen que ser celdas (ej. "A8")</li>
                                <li>Si se inserta más de una celda en las contantes cogerá el valor de la primera que no salga vacía</li>
                            </ul>
                        </p>
                        
                        <span class="input-group-btn">
                            <button type="button" name="action" value="checkInfo" class="btn btn-info btn-flat" onclick="checkInfo()"><i class="fa fa-refresh"></i></button>
                        </span>
                        
                        <form role="form" method="post" action="js_actions.php" enctype="multipart/form-data">
                            
                            <?php
                            
                            $Conn = new Conn('local', 'enertrade');
                            $headers = $Conn->getHeaders('plantillas_conversion_ff');
                            unset($Conn);
                            
                            
                            foreach ($headers as $key=>$value){
                                
                                switch ($value){
                                    case 'ID':      $required = 'required';             break;
                                    case 'fichero': $required = 'disabled="disabled"';  break;
                                    default:        $required = '';                     break;
                                }
                                
                                echo '<div class="form-group">
                                        <label>'.$value.'</label>
                                        <input type="text" class="form-control" id="'.$value.'" name="'.$value.'" '.$required.'>
                                    </div>';
                            }
                            ?>
                            
                            <div class="form-group">
                                <label>FF</label>
                                <input type="file" name ="fichero[]" required>
                                <input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />
                            </div>
                            
                            <div class="form-group">
                                <label>  </label>
                                <span class="input-group-btn">
                                    <button type="submit" name="action" value="add_update_plantilla_elaboracion_ff" class="btn btn-success btn-flat"><i class="fa fa-plus"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
		</div>

    </section>
    <!-- /.content -->
  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php") ?>

<script>
	
    function confirmation() {
        if(confirm("Ejecutar esta acción?")){
            return true;
        }
        return false;
    }
    
    function checkInfo(){
        $.ajax({
            url: "js_actions.php?action=checkInfoPlantillaFF" ,
            method: 'POST',
            data: {
                id: $('#ID').val()
            },
            async: true,
            success: function (data) {
                if (data!=''){
                    var obj = JSON.parse(data);
                    console.log(obj)
                    Object.keys(obj).forEach(key => {
                      $("#"+key).val(obj[key]);
                    });
                }
            }
        })
    }
    
	function delPlantilla(valor){
        if (confirmation()){
            $.ajax({
                url: "js_actions.php?action=delPlantilla" ,
                method: 'POST',
                data: {
                    id: valor
                },
                async: true,
                success: function (data) {
                    setTimeout(reloadPlantillas(), 500)
                }
            })
        }
	}
    
    function reloadPlantillas() {
        $('#plantillas').DataTable().ajax.reload();
    }
	  
  $('#plantillas').DataTable({
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
	  order			: [[ 0, "asc" ]],

	  ajax			: {
		  url 		: "js_actions.php?action=getPlantillasConversionFf",
		  dataSrc	: ''
	  }
	})
	
</script>
	  
</body>
</html>