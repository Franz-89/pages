<?php
$fichero = str_replace(".php", "", basename(__FILE__));

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

//Comprueba que se hayan creado los evento de aquí a dos años y los crea
$Conn = new Conn('local', 'enertrade');
$maxima = $Conn->getArray("SELECT MAX(end) maxima FROM turnos");
$maxima = date_create_from_format('Y-m-d', $maxima[0]['maxima']);

if (date_format($maxima, 'Y') != (date('Y')+2)){
	
	//Dias de vacaciones
	function insert_date($var_fecha){
		
		if (date_format($var_fecha, 'N')==7){$var_fecha->modify('+1 day');}
		
		$fecha1 = date_format($var_fecha, 'Y-m-d');
		$var_fecha->modify('+1 day');
		$fecha2 = date_format($var_fecha, 'Y-m-d');
		
		$Conn = new Conn('local', 'enertrade');
		$strSQL = "INSERT INTO turnos (title, start, end, backGroundColor, borderColor, rendering) VALUES ('F_', '$fecha1', '$fecha2', '#FFA500', '#FFA500', 'background')";
		$Conn->Query($strSQL);
		unset($Conn);
	}
	
	$ano = date('Y')+2;
	insert_date(new DateTime("$ano-01-01"));	//01-01-20??
	insert_date(new DateTime("$ano-01-06"));	//06-01-20??
	insert_date(new DateTime("$ano-05-01"));	//01-05-20??
	insert_date(new DateTime("$ano-05-02"));	//02-05-20??
	insert_date(new DateTime("$ano-05-15"));	//15-05-20??
	insert_date(new DateTime("$ano-08-15"));	//15-08-20??
	insert_date(new DateTime("$ano-10-12"));	//12-10-20??
	insert_date(new DateTime("$ano-11-01"));	//01-11-20??
	insert_date(new DateTime("$ano-11-09"));	//09-11-20??
	insert_date(new DateTime("$ano-12-06"));	//06-12-20??
	insert_date(new DateTime("$ano-12-08"));	//08-12-20??
	insert_date(new DateTime("$ano-12-25"));	//25-12-20??
	
	//Pasqua
	function insert_pasqua($var_fecha){
		
		$fecha1 = date_format($var_fecha, 'Y-m-d');
		$var_fecha->modify('-4 day');
		$fecha2 = date_format($var_fecha, 'Y-m-d');

		$Conn = new Conn('local', 'enertrade');
		$strSQL = "INSERT INTO turnos (title, start, end, backGroundColor, borderColor, rendering) VALUES ('F_', '$fecha2', '$fecha1', '#FFA500', '#FFA500', 'background')";
		$Conn->Query($strSQL);
		unset($Conn);
}
	
	switch ($ano){
			
		case 2021: insert_pasqua(new DateTime("2021-04-04")); break;
		case 2022: insert_pasqua(new DateTime("2022-04-18")); break;
		case 2023: insert_pasqua(new DateTime("2023-04-10")); break;
		case 2024: insert_pasqua(new DateTime("2024-04-01")); break;
		case 2025: insert_pasqua(new DateTime("2025-04-21")); break;
		case 2026: insert_pasqua(new DateTime("2026-04-06")); break;
		case 2027: insert_pasqua(new DateTime("2027-03-29")); break;
		case 2028: insert_pasqua(new DateTime("2028-04-17")); break;
		case 2029: insert_pasqua(new DateTime("2029-04-02")); break;
		case 2030: insert_pasqua(new DateTime("2030-04-22")); break;
		case 2031: insert_pasqua(new DateTime("2031-04-14")); break;
		case 2032: insert_pasqua(new DateTime("2032-04-29")); break;
		case 2033: insert_pasqua(new DateTime("2033-04-18")); break;
		case 2034: insert_pasqua(new DateTime("2034-04-10")); break;
		case 2035: insert_pasqua(new DateTime("2035-03-26")); break;
		case 2036: insert_pasqua(new DateTime("2036-04-14")); break;
		case 2037: insert_pasqua(new DateTime("2037-04-06")); break;
		case 2038: insert_pasqua(new DateTime("2038-04-26")); break;
		case 2039: insert_pasqua(new DateTime("2039-04-11")); break;
		case 2040: insert_pasqua(new DateTime("2040-04-02")); break;
		case 2041: insert_pasqua(new DateTime("2041-04-22")); break;
		case 2042: insert_pasqua(new DateTime("2042-04-07")); break;
		case 2043: insert_pasqua(new DateTime("2043-03-30")); break;
		case 2044: insert_pasqua(new DateTime("2044-04-18")); break;
		case 2045: insert_pasqua(new DateTime("2045-04-10")); break;
		case 2046: insert_pasqua(new DateTime("2046-03-26")); break;
		case 2047: insert_pasqua(new DateTime("2047-04-15")); break;
		case 2048: insert_pasqua(new DateTime("2048-04-06")); break;
		case 2049: insert_pasqua(new DateTime("2049-04-19")); break;
		case 2050: insert_pasqua(new DateTime("2050-04-11")); break;
	}
	
	//Elimina los antiguos
	$ano = date('Y')-2;
	$strSQL = "DELETE FROM turnos WHERE end<='$ano-12-31'";
	mysqli_query($conn, $strSQL);
}


$fecha = new DateTime();
$fecha->modify('-1 months');
$fecha = date_format($fecha, 'Y-m-d');
$Conn->Query("DELETE FROM turnos WHERE DESDE<'$fecha'");

include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");

?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Turnos &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.6yth7bqxg58b' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Info</li>
        <li class="active">Turnos</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content container-fluid">
	
      <div class="row">
		<div class="col-md-7">
          <div class="box box-primary">
            <div class="box-body no-padding">
              <!-- THE CALENDAR -->
              <div id="calendar"></div>
            </div>
			<div class="overlay" id="loading">
				<i class="fa fa-refresh fa-spin"></i>
			</div>
          </div>
        </div>
		  
		  <div class="col-md-5">
          	<div class="box box-primary">
            	<div class="box-body">
					
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Empleado</label>
								<select class="form-control select2" style="width: 100%;" id="usuario" onchange="refetchData()">
									<option selected="selected">Todos</option>
									<?php
									$Lista = new Lista('mail_empleados');
									$Lista->print_list();
									unset($Lista);
									?>
								</select>
							</div>
						</div>
					</div>
					
				</div>
				<div class="overlay" id="loading">
					<i class="fa fa-refresh fa-spin"></i>
				</div>
          	</div>
        </div>
		  
      </div>
		

    </section>
    <!-- /.content -->
  
<?php
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php");
?>

<script>
	
	function confirmar() {
		if(confirm("Ejecutar esta acción?"))
		{
			return true;
		}
		return false;
	}
	
	function getAno(){return $('#ano').val()}
	function getUser(){return $('#usuario').val()}
	function getCalendarEvents() {return "js_actions.php?action=getEventsTurnos&usuario=" + getUser()}
	function deleteEvent(event){
		
		$('#calendar').fullCalendar('removeEvents', event.id)
	}
	function refetchData() {
		$('#calendar').fullCalendar('removeEventSources')
		$('#calendar').fullCalendar('addEventSource', getCalendarEvents())
	}
	
	/* initialize the calendar*/
    $('#calendar').fullCalendar({
      header    : {
        left  : 'prev,next today',
        center: 'title',
        right : 'prevYear,nextYear'
      },
      buttonText: {
        today: 'Hoy',
      },
		<?php
		if ($usuario=='mmontero@enertrade.es' || $usuario=='vmrodriguez@enertrade.es'){
			echo "
				eventClick: function(event, el, jsEvent, view) {
					if(confirm('Eliminar?'))
					{
						$.ajax({
							url: 'js_actions.php?action=eliminarTurnos',
							method: 'POST',
							data: {id: event.id},
							async: true,
							success: deleteEvent(event)
						})
					}
				  },
				  
				  select	: function(start, end) {
					  if (getUser()=='Todos'){}
					  else {
                            $.ajax({
                                url: 'js_actions.php?action=addEventTurnos',
                                method: 'POST',
                                data: {
                                    start: moment(start).format('YYYY-MM-DD'),
                                    end: moment(end).format('YYYY-MM-DD'),
                                    user: getUser(),
                                    extendedProps: 1
                                },
                                async: true,
                                success: setTimeout(function(){refetchData()}, 100)
                            })
					  }


				  },
			";
		}
	  
		?>
	  
	  	
		
      events    		: getCalendarEvents(),
      editable  		: false,
	  locale			: 'es', 
	  allDayDefault		: true,
	  selectable		: true
    })
	
	$('.overlay').toggle()
	
	$(document).ajaxStart(function(){
		  $(".overlay").show();
	  })
	  $(document).ajaxComplete(function(){
		  $(".overlay").hide();
	  })
	
</script>
	  
</body>
</html>