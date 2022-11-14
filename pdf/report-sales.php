<?php
	
	$peticion_ajax=true;
    $fecha_inicio=(isset($_GET['fi'])) ? $_GET['fi'] : "";
    $fecha_final=(isset($_GET['ff'])) ? $_GET['ff'] : "";
    $error_fechas="";

	/*---------- Incluyendo configuraciones ----------*/
	require_once "../config/APP.php";

    function verificar_fecha($fecha){
        $valores=explode('-',$fecha);
        if(count($valores)==3 && checkdate($valores[1], $valores[2], $valores[0])){
            return false;
        }else{
            return true;
        }
    }

    if(verificar_fecha($fecha_inicio) || verificar_fecha($fecha_final)){
        $error_fechas.="Ha introducido fecha que no son correctas. ";
    }

    if($fecha_inicio>$fecha_final){
        $error_fechas.="La fecha de inicio no puede ser mayor que la fecha final";
    }


	if($error_fechas==""){

		/*---------- Instancia al controlador venta ----------*/
	    require_once "../controladores/ventaControlador.php";
        $ins_venta = new ventaControlador();

		/*---------- Seleccion de datos de la empresa ----------*/
		$datos_empresa=$ins_venta->datos_tabla("Normal","empresa LIMIT 1","*",0);
		$datos_empresa=$datos_empresa->fetch();


		require "./code128.php";

		$pdf = new PDF_Code128('P','mm','Letter');
		$pdf->SetMargins(17,17,17);
		$pdf->AddPage();
		$pdf->Image(SERVERURL.'vistas/assets/img/logo.png',165,12,35,35,'PNG');

		$pdf->SetFont('Arial','B',16);
		$pdf->SetTextColor(32,100,210);
		$pdf->Cell(150,10,utf8_decode(strtoupper($datos_empresa['empresa_nombre'])),0,0,'L');

		$pdf->Ln(9);

		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(39,39,51);
		$pdf->Cell(150,9,utf8_decode($datos_empresa['empresa_tipo_documento'].": ".$datos_empresa['empresa_numero_documento']),0,0,'L');

		$pdf->Ln(5);

		$pdf->Cell(150,9,utf8_decode($datos_empresa['empresa_direccion']),0,0,'L');

		$pdf->Ln(5);

		$pdf->Cell(150,9,utf8_decode("Teléfono: ".$datos_empresa['empresa_telefono']),0,0,'L');

		$pdf->Ln(5);

		$pdf->Cell(150,9,utf8_decode("Email: ".$datos_empresa['empresa_email']),0,0,'L');

        $pdf->Ln(15);
        
        $pdf->MultiCell(0,9,utf8_decode(strtoupper("Reporte de ventas ".$fecha_inicio." a ".$fecha_final)),0,'C',false);

		$pdf->SetFillColor(23,83,201);
		$pdf->SetDrawColor(23,83,201);
		$pdf->SetTextColor(255,255,255);
		$pdf->Cell(45,8,utf8_decode('Ventas realizadas'),1,0,'C',true);
		$pdf->Cell(45,8,utf8_decode('Total en ventas'),1,0,'C',true);
		$pdf->Cell(46,8,utf8_decode('Costo de ventas'),1,0,'C',true);
		$pdf->Cell(45,8,utf8_decode('Ganancias'),1,0,'C',true);

		$pdf->Ln(8);

		$pdf->SetFont('Arial','',9);
		$pdf->SetTextColor(39,39,51);

		/*----------  Seleccionando datos de las ventas  ----------*/
        $check_ventas=$ins_venta->datos_tabla("Normal","venta WHERE venta_fecha BETWEEN '$fecha_inicio' AND '$fecha_final'","*",0);
        if($check_ventas->rowCount()>=1){
            $datos_ventas=$check_ventas->fetchAll();

            $ventas_totales=0;
            $total_ventas=0;
            $total_costos=0;
            $total_utilidades=0;
            foreach($datos_ventas as $ventas){
                $ventas_totales++;
                $total_ventas+=$ventas['venta_total_final'];
                $total_costos+=$ventas['venta_costo'];
                $total_utilidades+=$ventas['venta_utilidad'];
            }

            $pdf->Cell(45,7,utf8_decode($ventas_totales),'LB',0,'C');
            $pdf->Cell(45,7,utf8_decode(MONEDA_SIMBOLO.number_format($total_ventas,MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE),'LB',0,'C');
            $pdf->Cell(46,7,utf8_decode(MONEDA_SIMBOLO.number_format($total_costos,MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE),'LB',0,'C');
            $pdf->Cell(45,7,utf8_decode(MONEDA_SIMBOLO.number_format($total_utilidades,MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE),'LRB',0,'C');
        }else{
            $pdf->Cell(181,7,utf8_decode("No hay datos de ventas para mostrar"),'LBR',0,'C');
        }
		$pdf->Output("I","Reporte ventas ".$fecha_inicio." a ".$fecha_final.".pdf",true);

	}else{
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title><?php echo COMPANY; ?></title>
	<?php include '../vistas/inc/Head.php'; ?>
</head>
<body>
	<div class="full-box container-404">
		<div>
			<p class="text-center"><i class="far fa-thumbs-down fa-10x"></i></p>
			<h1 class="text-center">¡Ocurrió un error!</h1>
			<p class="lead text-center"><?php echo $error_fechas; ?></p>
		</div>
	</div>
	<?php include '../vistas/inc/Script.php'; ?>
</body>
</html>
<?php } ?>