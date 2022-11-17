<?php
$fichero = str_replace(".php", "", basename(__FILE__));

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");

?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Validación &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.2tzhbl11nc9d' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Indexado</li>
		<li class="active">Validación</li>
      </ol>
    </section>
	
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-3">

          <div class="box box-primary">
            <div class="box-body">
				
                <div class="col-md-12">
                    <form role="form" method="post" action="indexado_actions.php">

                        <div class="form-group">
                            <label>Cliente</label>
                            <select class="form-control select2" style="width: 100%;" id="cliente" name="cliente">
                                <?php
                                $Lista = new Lista('clientes');
                                $Lista->print_list($cliente);
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Enumeración</label>
                            <select class="form-control select2" style="width: 100%;" id="enum" name="enum">
                                <?php
                                $Lista->change_list('prioridad');
                                $Lista->print_list($enum);
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Tarifa</label>
                            <select class="form-control select2" style="width: 100%;" id="tarifa" name="tarifa">
                                <option value="" selected="selected"></option>
                                <?php
                                $Lista->change_list('tarifas');
                                $Lista->print_list();
                                $Lista->change_list('tarifas_nuevas');
                                $Lista->print_list();
                                unset($Lista);
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Emisión desde</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right fecha" id="desde" name="desde">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Emisión hasta (no incluido)</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right fecha" id="hasta" name="hasta">
                            </div>
                        </div>
    
                        <div class="form-group">
                            <label>
                              <div><input type="checkbox" class="flat-red" name="desdeMasUno" value="desdeMasUno"> <label> Desde +1</label></div>
                              <div><input type="checkbox" class="flat-red" name="limite_fras" value="limite_fras"> <label> Limite 200 fras</label></div>
                            </label>
                        </div>
                        
                        <div class="box-footer pull-right">
                            <button class="btn btn-success" name="action" value="validacion_indexado">Validar</button>
                            <button class="btn btn-success" name="action" value="validacion_indexado_nueva">Validar Nuevo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

        <div class="col-xs-2">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="form-group">
                        <label>OM (€/kWh)</label>
                        <input type="number" step=0.0000001 class="form-control" placeholder="OM" id="om">
                    </div>

                    <div class="form-group">
                        <label>OS (€/kWh)</label>
                        <input type="number" step=0.0000001 class="form-control" placeholder="OS" id="os">
                    </div>

                    <div class="form-group">
                        <label>FNEE</label>
                        <input type="number" step=0.0000001 class="form-control" placeholder="FNEE" id="fnee">
                    </div>

                    <div class="box-footer pull-right">
                        <button class="btn btn-primary" onclick="saveValues()"><i class="fa fa-save"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    </section>
  </div>

  <?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php") ?>

<!-- page script -->
<script>
	
	function saveValues(){
		$.ajax({
			url: "indexado_actions.php?action=saveValuesValidacion",
			method: "POST",
			data: {
				om: 	$('#om').val(),
				os: 	$('#os').val(),
				fnee: 	$('#fnee').val()
				  },
			async: true
		})
	}
    
    function getValues(){
		$.ajax({
			url: "indexado_actions.php?action=getValuesValidacion",
			method: "POST",
			async: true,
			success: function (data) {
                data = data.split('|');
                $('#om').val(data[0]);
                $('#os').val(data[1]);
                $('#fnee').val(data[2]);
			}
		})
	}
	
	function confirmation() {
		if(confirm("Ejecutar esta acción?"))
		{
			return true;
		}
		return false;
	}
	
	getValues()
	
</script>
</body>
</html>