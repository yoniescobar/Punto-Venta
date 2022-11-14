<?php

	if($peticion_ajax){
		require_once "../modelos/mainModel.php";
	}else{
		require_once "./modelos/mainModel.php";
	}

	class kardexControlador extends mainModel{

		/*---------- Controlador paginador movimiento ----------*/
		public function paginador_kardex_controlador($pagina,$registros,$url,$fecha_mes,$fecha_year){

			$pagina=mainModel::limpiar_cadena($pagina);
			$registros=mainModel::limpiar_cadena($registros);

			$url=mainModel::limpiar_cadena($url);
			$tipo=$url;

			if($tipo=="kardex-product"){
				$url=SERVERURL.$url."/".$fecha_mes."/";
			}else{
				$url=SERVERURL.$url."/";
			}
			
			
			$fecha_mes=mainModel::limpiar_cadena($fecha_mes);
			$fecha_year=mainModel::limpiar_cadena($fecha_year);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
            $inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;
            
            if($tipo=="kardex-search"){
				if(($fecha_mes<1 && $fecha_mes>12) || (strlen($fecha_year)<4 || strlen($fecha_year)>4)){
					return '
						<div class="alert alert-danger text-center" role="alert">
							<p><i class="fas fa-exclamation-triangle fa-5x"></i></p>
							<h4 class="alert-heading">¡Ocurrió un error inesperado!</h4>
							<p class="mb-0">Lo sentimos, no podemos realizar la búsqueda ya que al parecer a ingresado una fecha incorrecta.</p>
						</div>
					';
					exit();
				}
			}
			
			if($tipo=="kardex-product"){
				$id=mainModel::decryption($fecha_mes);
			}
            
            $campos_tablas="kardex.kardex_id,kardex.kardex_codigo,kardex.kardex_mes,kardex.kardex_year,kardex.kardex_entrada_unidad,kardex.kardex_entrada_costo_total,kardex.kardex_salida_unidad,kardex.kardex_salida_costo_total,kardex.kardex_existencia_inicial,kardex.kardex_existencia_unidad,kardex.kardex_existencia_costo_total,kardex.producto_id,producto.producto_id,producto.producto_codigo,producto.producto_nombre,producto.producto_stock_total";

			if($tipo=="kardex-search" && $fecha_mes!="" && $fecha_year!=""){
				$consulta="SELECT SQL_CALC_FOUND_ROWS $campos_tablas FROM kardex INNER JOIN producto ON kardex.producto_id=producto.producto_id WHERE kardex.kardex_mes='$fecha_mes' AND kardex.	kardex_year='$fecha_year' ORDER BY kardex.kardex_id DESC LIMIT $inicio,$registros";
			}elseif($tipo=="kardex-product"){
				$consulta="SELECT SQL_CALC_FOUND_ROWS $campos_tablas FROM kardex INNER JOIN producto ON kardex.producto_id=producto.producto_id WHERE kardex.producto_id='$id'  ORDER BY kardex.kardex_id DESC LIMIT $inicio,$registros";
			}else{
				$consulta="SELECT SQL_CALC_FOUND_ROWS $campos_tablas FROM kardex INNER JOIN producto ON kardex.producto_id=producto.producto_id ORDER BY kardex.kardex_id DESC LIMIT $inicio,$registros";
			}

			$conexion = mainModel::conectar();

			$datos = $conexion->query($consulta);

			$datos = $datos->fetchAll();

			$total = $conexion->query("SELECT FOUND_ROWS()");
			$total = (int) $total->fetchColumn();

			$Npaginas =ceil($total/$registros);

			### Cuerpo de la tabla ###
			$tabla.='
				<div class="table-responsive">
				<table class="table table-dark table-sm">
					<thead>
						<tr class="text-center roboto-medium">
                            <th>#</th>
                            <th>FECHA</th>
							<th>PRODUCTO</th>
							<th>U. ENTRADA</th>
							<th>C.U. ENTRADA</th>
							<th>U. SALIDA</th>
                            <th>C.U. SALIDA</th>
                            <th>INVENTARIO INI.</th>
							<th>INVENTARIO AC.</th>
							<th>C. INVENTARIO</th>
							<th>DET.</th>
                        </tr>
					</thead>
					<tbody>
			';

			if($total>=1 && $pagina<=$Npaginas){
				$contador=$inicio+1;
				$pag_inicio=$inicio+1;
				foreach($datos as $rows){
					$tabla.='
						<tr class="text-center" >
                            <td>'.$contador.'</td>
                            <td>'.mainModel::obtener_nombre_mes($rows['kardex_mes'])." de ".$rows['kardex_year'].'</td>
							<td>'.$rows['producto_nombre'].'</td>
							<td>'.$rows['kardex_entrada_unidad'].'</td>
							<td>'.MONEDA_SIMBOLO.number_format($rows['kardex_entrada_costo_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
							<td>'.$rows['kardex_salida_unidad'].'</td>
							<td>'.MONEDA_SIMBOLO.number_format($rows['kardex_salida_costo_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
							<td>'.$rows['kardex_existencia_inicial'].'</td>
							<td>'.$rows['kardex_existencia_unidad'].'</td>
							<td>'.MONEDA_SIMBOLO.number_format($rows['kardex_existencia_costo_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
							<td>
								<a class="btn btn-primary" href="'.SERVERURL.'kardex-detail/'.mainModel::encryption($rows['kardex_codigo']).'/" data-toggle="popover" data-trigger="hover" title="Detalles de kardex" data-content="'.$rows['producto_nombre'].' ('.mainModel::obtener_nombre_mes($rows['kardex_mes'])." de ".$rows['kardex_year'].')">
									<i class="fas fa-pallet fa-fw"></i>
								</a>
							</td>
                        </tr>
                    ';
                    $contador++;
				}
				$pag_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='
						<tr class="text-center" >
							<td colspan="11">
								<a href="'.$url.'" class="btn btn-raised btn-primary btn-sm">
									Haga clic acá para recargar el listado
								</a>
							</td>
						</tr>
					';
				}else{
					$tabla.='
						<tr class="text-center" >
							<td colspan="11">
								No hay registros en el sistema
							</td>
						</tr>
					';
				}
			}

			$tabla.='</tbody></table></div>';

			if($total>0 && $pagina<=$Npaginas){
				$tabla.='<p class="text-right">Mostrando kardex <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';
			}

			### Paginacion ###
			if($total>=1 && $pagina<=$Npaginas){
				$tabla.=mainModel::paginador_tablas($pagina,$Npaginas,$url,7);
			}

			return $tabla;
		} /*-- Fin controlador --*/


		/*---------- Buscar kardex producto ----------*/
		public function buscar_kardex_producto_controlador(){

			/*== Recuperando codigo del producto ==*/
			$codigo=mainModel::limpiar_cadena($_POST['producto_codigo_kardex']);
			$modulo=mainModel::limpiar_cadena($_POST['modulo_url']);

			if($modulo==""){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No podemos continuar con la búsqueda debido a un error de configuración.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			/*== Comprobando producto en la DB ==*/
            $check_producto=mainModel::ejecutar_consulta_simple("SELECT * FROM producto WHERE producto_codigo='$codigo'");
            if($check_producto->rowCount()<=0){
            	$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos encontrado ningún producto registrado en el sistema con código de barras: ".$codigo,
					"Tipo"=>"error"
				];
            }else{
				$campos=$check_producto->fetch();
				$id=mainModel::encryption($campos['producto_id']);

				$alerta=[
					"Alerta"=>"redireccionar",
					"URL"=>SERVERURL.$modulo."/".$id."/"
				];
				
			}

			$check_producto->closeCursor();
			$check_producto=mainModel::desconectar($check_producto);

			echo json_encode($alerta);
		} /*-- Fin controlador --*/

    }