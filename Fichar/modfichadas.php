<?php

$fichero 	= basename(__FILE__);
$sql_table 	= 'fichadas';

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$Conn = new Conn('local', 'enertrade');

if (isset($_GET['id']) && !empty($_GET['id'])){
	$id 		= $_GET['id'];
	$result 	= $Conn->oneRow("SELECT * FROM fichadas WHERE id=$id");
	unset($Conn);
	
	$email 		= $result['USUARIO'];
	$in_out 	= $result['IN_OUT'];
	$hora 		= $result['HORA'];
	$mod 		= true;
} else {
	header ("Location: /Enertrade/pages/Fichar/fichadas.php");
}

if (isset($_GET['error'])){$msg = "Formato fecha invalido! (yyyy-mm-dd hh:ii:ss)";}

include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Fichar
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li class="active">Fichar</li>
      </ol>
    </section>
	
    <!-- Main content -->
    <section class="content">

      <!-- SELECT2 EXAMPLE -->
	  <form role="form" method="post" action="php_actions.php?action=mod">
      <div class="box box-default">
        <div class="box-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
				
				<div class="form-group">
					<input type="hidden" name="id" value="<?php echo $id;?>"/>
					
					<label>Usuario</label>
					<select class="form-control select2" style="width: 100%;" data-placeholder="Usuario" name="email">
						<?php
						$Lista = new Lista('mail_empleados');
						$Lista->print_list($email);
						unset($Lista);
						?>
					</select>
				 </div>
				
				<div class="form-group">
					
					
					<label>Entrada/Salida</label>
					<select class="form-control select2" style="width: 100%;" data-placeholder="Entrada/Salida" name="in_out">
						 <?php echo '<option selected="selected">'.$in_out."</option>";
						if ($in_out == "ENTRADA"){echo "<option>SALIDA</option>";}
						else {echo "<option>ENTRADA</option>";}
						?>
					</select>
				</div>
				
				<div class="form-group">
					<label>Hora</label>
					<input class="form-control" type="text" placeholder="Hora" name="hora" value="<?php echo $hora;?>"/>
				</div>
				
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