<?php

	if($peticion_ajax){
		require_once "../modelos/mainModel.php";
	}else{
		require_once "./modelos/mainModel.php";
	}

	class ventaControlador extends mainModel{

        /*---------- Controlador agregar producto a venta ----------*/
        public function agregar_producto_carrito_controlador(){
 
            if($_SESSION['lector_codigo_svi']=="Barras"){
                $campo_tabla="producto_codigo";
                $txt_codigo="de barras";
            }else{
                $campo_tabla="producto_sku";
                $txt_codigo="SKU";
            }

            if($_SESSION['venta_tipo']=="normal"){
                $campo_precio="producto_precio_venta";
                $url_venta=SERVERURL."sale-new/";
            }else{
                $campo_precio="producto_precio_mayoreo";
                $url_venta=SERVERURL."sale-new/wholesale/";
            }

            /*== Recuperando codigo del producto ==*/
            $codigo=mainModel::limpiar_cadena($_POST['producto_codigo_add']);

            if($codigo==""){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"Debes de introducir el código $txt_codigo del producto.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Verificando integridad de los datos ==*/
            if(mainModel::verificar_datos("[a-zA-Z0-9- ]{1,70}",$codigo)){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El código $txt_codigo no coincide con el formato solicitado",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando producto en la DB ==*/
            $check_producto=mainModel::ejecutar_consulta_simple("SELECT * FROM producto WHERE $campo_tabla='$codigo'");
            if($check_producto->rowCount()<=0){
                $alerta=[
                    "Alerta"=>"venta",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos encontrado el producto con código $txt_codigo : '$codigo'.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }else{
                $campos=$check_producto->fetch();
            }
            $check_producto->closeCursor();
			$check_producto=mainModel::desconectar($check_producto);

            /*== Obteniendo datos de la empresa ==*/
            $check_empresa=mainModel::ejecutar_consulta_simple("SELECT * FROM empresa LIMIT 1");
            if($check_empresa->rowCount()<1){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido obtener algunos datos de los impuestos para agregar el producto.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }else{
                $datos_empresa=$check_empresa->fetch();
            }
            $check_empresa->closeCursor();
			$check_empresa=mainModel::desconectar($check_empresa);

            if($datos_empresa['empresa_impuesto_nombre']!=$_SESSION['venta_impuesto_nombre'] || $datos_empresa['empresa_impuesto_porcentaje']!=$_SESSION['venta_impuesto_porcentaje']){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"Hemos detectado un cambio en los datos de la empresa e impuestos, por favor recargue la página e intente nuevamente o verifique los datos de la empresa.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Codigo de producto ==*/
            $codigo=$campos['producto_codigo'];

            /*== Garantia de producto ==*/
            if($campos['producto_garantia_unidad']=="0" || $campos['producto_garantia_tiempo']=="N/A"){
                $producto_garantia="N/A";
            }else{
                $producto_garantia=$campos['producto_garantia_unidad']." ".$campos['producto_garantia_tiempo'];
            }



            if(empty($_SESSION['datos_producto_venta'][$codigo])){

                $detalle_cantidad=1;

                $stock_total=$campos['producto_stock_total']-$detalle_cantidad;

                if($stock_total<0){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"Lo sentimos, no hay existencias disponibles del producto seleccionado.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                $detalle_descuento_total=$campos[$campo_precio]*($campos['producto_descuento']/100);
                $detalle_descuento_total=number_format($detalle_descuento_total,MONEDA_DECIMALES,'.','');

                $precio_con_descuento=$campos[$campo_precio]-$detalle_descuento_total;
                $precio_con_descuento=number_format($precio_con_descuento,MONEDA_DECIMALES,'.','');

                $detalle_total=$detalle_cantidad*$precio_con_descuento;
                $detalle_total=number_format($detalle_total,MONEDA_DECIMALES,'.','');

                $detalle_subtotal=$detalle_total/(($datos_empresa['empresa_impuesto_porcentaje']/100)+1);
                $detalle_subtotal=number_format($detalle_subtotal,MONEDA_DECIMALES,'.','');

                $detalle_impuestos=$detalle_total-$detalle_subtotal;
                $detalle_impuestos=number_format($detalle_impuestos,MONEDA_DECIMALES,'.','');

                $detalle_costos=$campos['producto_precio_compra']*$detalle_cantidad;
                $detalle_costos=number_format($detalle_costos,MONEDA_DECIMALES,'.','');

                $detalle_utilidad=$detalle_total-$detalle_costos;
                $detalle_utilidad=number_format($detalle_utilidad,MONEDA_DECIMALES,'.','');

                $_SESSION['datos_producto_venta'][$codigo]=[
                    "tipo_precio"=>$_SESSION['venta_tipo'],
                    "producto_id"=>$campos['producto_id'],
					"producto_codigo"=>$campos['producto_codigo'],
					"producto_sku"=>$campos['producto_sku'],
					"producto_stock_total"=>$stock_total,
					"producto_stock_total_old"=>$campos['producto_stock_total'],
					"producto_stock_vendido"=>$campos['producto_stock_vendido'],
                    "producto_stock_vendido_old"=>$campos['producto_stock_vendido'],
                    "producto_garantia"=>$producto_garantia,
                    "venta_detalle_precio_compra"=>$campos['producto_precio_compra'],
                    "venta_detalle_precio_regular"=>$campos[$campo_precio],
                    "venta_detalle_precio_venta"=>$precio_con_descuento,
                    "venta_detalle_cantidad"=>1,
                    "venta_detalle_subtotal"=>$detalle_subtotal,
                    "venta_detalle_impuestos"=>$detalle_impuestos,
                    "venta_detalle_descuento_porcentaje"=>$campos['producto_descuento'],
                    "venta_detalle_descuento_total"=>$detalle_descuento_total,
                    "venta_detalle_total"=>$detalle_total,
                    "venta_detalle_costos"=>$detalle_costos,
                    "venta_detalle_utilidad"=>$detalle_utilidad,
                    "venta_detalle_descripcion"=>$campos['producto_nombre']
                ];
                
                $_SESSION['alerta_producto_agregado']="Se agrego <strong>".$campos['producto_nombre']."</strong> a la venta";
            }else{
                $detalle_cantidad=($_SESSION['datos_producto_venta'][$codigo]['venta_detalle_cantidad'])+1;

                $stock_total=$campos['producto_stock_total']-$detalle_cantidad;

                if($stock_total<0){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"Lo sentimos, no hay existencias disponibles del producto seleccionado.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                $detalle_descuento_total=$campos[$campo_precio]*($campos['producto_descuento']/100);
                $detalle_descuento_total=number_format($detalle_descuento_total,MONEDA_DECIMALES,'.','');

                $precio_con_descuento=$campos[$campo_precio]-$detalle_descuento_total;
                $precio_con_descuento=number_format($precio_con_descuento,MONEDA_DECIMALES,'.','');

                $detalle_total=$detalle_cantidad*$precio_con_descuento;
                $detalle_total=number_format($detalle_total,MONEDA_DECIMALES,'.','');

                $detalle_subtotal=$detalle_total/(($datos_empresa['empresa_impuesto_porcentaje']/100)+1);
                $detalle_subtotal=number_format($detalle_subtotal,MONEDA_DECIMALES,'.','');

                $detalle_impuestos=$detalle_total-$detalle_subtotal;
                $detalle_impuestos=number_format($detalle_impuestos,MONEDA_DECIMALES,'.','');

                $detalle_costos=$campos['producto_precio_compra']*$detalle_cantidad;
                $detalle_costos=number_format($detalle_costos,MONEDA_DECIMALES,'.','');

                $detalle_utilidad=$detalle_total-$detalle_costos;
                $detalle_utilidad=number_format($detalle_utilidad,MONEDA_DECIMALES,'.','');

                $_SESSION['datos_producto_venta'][$codigo]=[
                    "tipo_precio"=>$_SESSION['venta_tipo'],
                    "producto_id"=>$campos['producto_id'],
					"producto_codigo"=>$campos['producto_codigo'],
					"producto_sku"=>$campos['producto_sku'],
					"producto_stock_total"=>$stock_total,
					"producto_stock_total_old"=>$campos['producto_stock_total'],
					"producto_stock_vendido"=>$campos['producto_stock_vendido'],
                    "producto_stock_vendido_old"=>$campos['producto_stock_vendido'],
                    "producto_garantia"=>$producto_garantia,
                    "venta_detalle_precio_compra"=>$campos['producto_precio_compra'],
                    "venta_detalle_precio_regular"=>$campos[$campo_precio],
                    "venta_detalle_precio_venta"=>$precio_con_descuento,
                    "venta_detalle_cantidad"=>$detalle_cantidad,
                    "venta_detalle_subtotal"=>$detalle_subtotal,
                    "venta_detalle_impuestos"=>$detalle_impuestos,
                    "venta_detalle_descuento_porcentaje"=>$campos['producto_descuento'],
                    "venta_detalle_descuento_total"=>$detalle_descuento_total,
                    "venta_detalle_total"=>$detalle_total,
                    "venta_detalle_costos"=>$detalle_costos,
                    "venta_detalle_utilidad"=>$detalle_utilidad,
                    "venta_detalle_descripcion"=>$campos['producto_nombre']
                ];

                $_SESSION['alerta_producto_agregado']="Se agrego +1 <strong>".$campos['producto_nombre']."</strong> a la venta. Total en carrito: <strong>$detalle_cantidad</strong>";
            }

            $alerta=[
                "Alerta"=>"redireccionar",
                "URL"=>$url_venta
            ];

            echo json_encode($alerta);
        } /*-- Fin controlador --*/


        /*---------- Controlador eliminar producto a venta ----------*/
        public function eliminar_producto_carrito_controlador(){        

            /*== Recuperando codigo del producto ==*/
            $codigo=mainModel::limpiar_cadena($_POST['producto_codigo_del']);

            unset($_SESSION['datos_producto_venta'][$codigo]);

            if(empty($_SESSION['datos_producto_venta'][$codigo])){
				$alerta=[
					"Alerta"=>"recargar",
					"Titulo"=>"¡Producto removido!",
					"Texto"=>"El producto se ha removido de la venta.",
					"Tipo"=>"success"
				];
			}else{
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido remover el producto, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
            }
            echo json_encode($alerta);
        } /*-- Fin controlador --*/


        /*---------- Controlador actualizar producto a venta ----------*/
        public function actualizar_producto_carrito_controlador(){    

            if($_SESSION['venta_tipo']=="normal"){
                $campo_precio="producto_precio_venta";
                $url_venta=SERVERURL."sale-new/";
            }else{
                $campo_precio="producto_precio_mayoreo";
                $url_venta=SERVERURL."sale-new/wholesale/";
            }

            /*== Recuperando codigo & cantidad del producto ==*/
            $codigo=mainModel::limpiar_cadena($_POST['producto_codigo_up']);
            $cantidad=mainModel::limpiar_cadena($_POST['producto_cantidad_up']);

            /*== comprobando campos vacios ==*/
            if($codigo=="" || $cantidad==""){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No podemos actualizar la cantidad de productos debido a que faltan algunos parámetros de configuración.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== comprobando cantidad de productos ==*/
            if($cantidad<=0){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"Debes de introducir una cantidad mayor a 0.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando producto en la DB ==*/
            $check_producto=mainModel::ejecutar_consulta_simple("SELECT * FROM producto WHERE producto_codigo='$codigo'");
            if($check_producto->rowCount()<=0){
                $alerta=[
                    "Alerta"=>"venta",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos encontrado el producto con código de barras : '$codigo'.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }else{
                $campos=$check_producto->fetch();
            }
            $check_producto->closeCursor();
			$check_producto=mainModel::desconectar($check_producto);

            /*== Obteniendo datos de la empresa ==*/
            $check_empresa=mainModel::ejecutar_consulta_simple("SELECT * FROM empresa LIMIT 1");
            if($check_empresa->rowCount()<1){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos podido obtener algunos datos de los impuestos para agregar el producto.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }else{
                $datos_empresa=$check_empresa->fetch();
            }
            $check_empresa->closeCursor();
			$check_empresa=mainModel::desconectar($check_empresa);

            if($datos_empresa['empresa_impuesto_nombre']!=$_SESSION['venta_impuesto_nombre'] || $datos_empresa['empresa_impuesto_porcentaje']!=$_SESSION['venta_impuesto_porcentaje']){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"Hemos detectado un cambio en los datos de la empresa e impuestos, por favor recargue la página e intente nuevamente o verifique los datos de la empresa.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== comprobando producto en carrito ==*/
            if(!empty($_SESSION['datos_producto_venta'][$codigo])){

                if($_SESSION['datos_producto_venta'][$codigo]["venta_detalle_cantidad"]==$cantidad){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"No has modificado la cantidad de productos",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                if($cantidad>$_SESSION['datos_producto_venta'][$codigo]["venta_detalle_cantidad"]){
                    $diferencia_productos="agrego +".($cantidad-$_SESSION['datos_producto_venta'][$codigo]["venta_detalle_cantidad"]);
                }else{
                    $diferencia_productos="quito -".($_SESSION['datos_producto_venta'][$codigo]["venta_detalle_cantidad"]-$cantidad);
                }
                

                $detalle_cantidad=$cantidad;

                $stock_total=$campos['producto_stock_total']-$detalle_cantidad;

                if($stock_total<0){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"Lo sentimos, no hay existencias suficientes del producto seleccionado. Existencias disponibles: ".($stock_total+$detalle_cantidad)."",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                $detalle_descuento_total=$campos[$campo_precio]*($campos['producto_descuento']/100);
                $detalle_descuento_total=number_format($detalle_descuento_total,MONEDA_DECIMALES,'.','');

                $precio_con_descuento=$campos[$campo_precio]-$detalle_descuento_total;
                $precio_con_descuento=number_format($precio_con_descuento,MONEDA_DECIMALES,'.','');

                $detalle_total=$detalle_cantidad*$precio_con_descuento;
                $detalle_total=number_format($detalle_total,MONEDA_DECIMALES,'.','');

                $detalle_subtotal=$detalle_total/(($datos_empresa['empresa_impuesto_porcentaje']/100)+1);
                $detalle_subtotal=number_format($detalle_subtotal,MONEDA_DECIMALES,'.','');

                $detalle_impuestos=$detalle_total-$detalle_subtotal;
                $detalle_impuestos=number_format($detalle_impuestos,MONEDA_DECIMALES,'.','');

                $detalle_costos=$campos['producto_precio_compra']*$detalle_cantidad;
                $detalle_costos=number_format($detalle_costos,MONEDA_DECIMALES,'.','');

                $detalle_utilidad=$detalle_total-$detalle_costos;
                $detalle_utilidad=number_format($detalle_utilidad,MONEDA_DECIMALES,'.','');

                /*== Garantia de producto ==*/
                if($campos['producto_garantia_unidad']=="0" || $campos['producto_garantia_tiempo']=="N/A"){
                    $producto_garantia="N/A";
                }else{
                    $producto_garantia=$campos['producto_garantia_unidad']." ".$campos['producto_garantia_tiempo'];
                }

                $_SESSION['datos_producto_venta'][$codigo]=[
                    "tipo_precio"=>$_SESSION['venta_tipo'],
                    "producto_id"=>$campos['producto_id'],
					"producto_codigo"=>$campos['producto_codigo'],
					"producto_sku"=>$campos['producto_sku'],
					"producto_stock_total"=>$stock_total,
					"producto_stock_total_old"=>$campos['producto_stock_total'],
					"producto_stock_vendido"=>$campos['producto_stock_vendido'],
                    "producto_stock_vendido_old"=>$campos['producto_stock_vendido'],
                    "producto_garantia"=>$producto_garantia,
                    "venta_detalle_precio_compra"=>$campos['producto_precio_compra'],
                    "venta_detalle_precio_regular"=>$campos[$campo_precio],
                    "venta_detalle_precio_venta"=>$precio_con_descuento,
                    "venta_detalle_cantidad"=>$detalle_cantidad,
                    "venta_detalle_subtotal"=>$detalle_subtotal,
                    "venta_detalle_impuestos"=>$detalle_impuestos,
                    "venta_detalle_descuento_porcentaje"=>$campos['producto_descuento'],
                    "venta_detalle_descuento_total"=>$detalle_descuento_total,
                    "venta_detalle_total"=>$detalle_total,
                    "venta_detalle_costos"=>$detalle_costos,
                    "venta_detalle_utilidad"=>$detalle_utilidad,
                    "venta_detalle_descripcion"=>$campos['producto_nombre']
                ];

                $_SESSION['alerta_producto_agregado']="Se $diferencia_productos <strong>".$campos['producto_nombre']."</strong> a la venta. Total en carrito <strong>$detalle_cantidad</strong>";

                $alerta=[
                    "Alerta"=>"redireccionar",
                    "URL"=>$url_venta
                ];
    
                echo json_encode($alerta);
                exit(); 
            }else{
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos encontrado el producto que desea actualizar en el carrito.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }
        } /*-- Fin controlador --*/


        /*---------- Controlador buscar cliente ----------*/
        public function buscar_cliente_venta_controlador(){

            /*== Recuperando termino de busqueda ==*/
			$cliente=mainModel::limpiar_cadena($_POST['buscar_cliente']);

			/*== Comprobando que no este vacio el campo ==*/
			if($cliente==""){
				return '<div class="alert alert-warning" role="alert">
					<p class="text-center mb-0">
						<i class="fas fa-exclamation-triangle fa-2x"></i><br>
						Debes de introducir el Numero de documento, Nombre, Apellido o Teléfono del cliente
					</p>
				</div>';
				exit();
            }
            
            /*== Seleccionando clientes en la DB ==*/
            $datos_cliente=mainModel::ejecutar_consulta_simple("SELECT * FROM cliente WHERE (cliente_id!='1') AND (cliente_numero_documento LIKE '%$cliente%' OR cliente_nombre LIKE '%$cliente%' OR cliente_apellido LIKE '%$cliente%' OR cliente_telefono LIKE '%$cliente%') ORDER BY cliente_nombre ASC");
            
            if($datos_cliente->rowCount()>=1){

				$datos_cliente=$datos_cliente->fetchAll();

				$tabla='<div class="table-responsive" ><table class="table table-hover table-bordered table-sm"><tbody>';

				foreach($datos_cliente as $rows){
					$tabla.='
					<tr class="text-center">
                        <td>'.$rows['cliente_nombre'].' '.$rows['cliente_apellido'].' ('.$rows['cliente_tipo_documento'].': '.$rows['cliente_numero_documento'].')</td>
                        <td>
                            <button type="button" class="btn btn-primary" onclick="agregar_cliente('.$rows['cliente_id'].')"><i class="fas fa-user-plus"></i></button>
                        </td>
                    </tr>
                    ';
				}

				$tabla.='</tbody></table></div>';
				return $tabla;
			}else{
				return '<div class="alert alert-warning" role="alert">
					<p class="text-center mb-0">
						<i class="fas fa-exclamation-triangle fa-2x"></i><br>
						No hemos encontrado ningún cliente en el sistema que coincida con <strong>“'.$cliente.'”</strong>
					</p>
				</div>';
				exit();
			}
        } /*-- Fin controlador --*/


        /*---------- Controlador agregar cliente ----------*/
        public function agregar_cliente_venta_controlador(){

            /*== Recuperando id del cliente ==*/
			$id=mainModel::limpiar_cadena($_POST['cliente_id_add']);

			/*== Comprobando cliente en la DB ==*/
			$check_cliente=mainModel::ejecutar_consulta_simple("SELECT * FROM cliente WHERE cliente_id='$id'");
			if($check_cliente->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido agregar el cliente debido a un error, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}else{
				$campos=$check_cliente->fetch();
            }
            $check_cliente->closeCursor();
			$check_cliente=mainModel::desconectar($check_cliente);	

			if($_SESSION['datos_cliente_venta']['cliente_id']==1){
                $_SESSION['datos_cliente_venta']=[
                    "cliente_id"=>$campos['cliente_id'],
                    "cliente_tipo_documento"=>$campos['cliente_tipo_documento'],
                    "cliente_numero_documento"=>$campos['cliente_numero_documento'],
                    "cliente_nombre"=>$campos['cliente_nombre'],
                    "cliente_apellido"=>$campos['cliente_apellido']
                ];

				$alerta=[
					"Alerta"=>"recargar",
					"Titulo"=>"¡Cliente agregado!",
					"Texto"=>"El cliente se agregó para realizar una venta.",
					"Tipo"=>"success"
				];
			}else{
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido agregar el cliente debido a un error, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
				
            }
            echo json_encode($alerta); 
        } /*-- Fin controlador --*/


        /*---------- Controlador eliminar cliente ----------*/
        public function eliminar_cliente_venta_controlador(){

			unset($_SESSION['datos_cliente_venta']);

			if(empty($_SESSION['datos_cliente_venta'])){
				$alerta=[
					"Alerta"=>"recargar",
					"Titulo"=>"¡Cliente removido!",
					"Texto"=>"Los datos del cliente se han removido.",
					"Tipo"=>"success"
				];
			}else{
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido remover el cliente, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
			}
			echo json_encode($alerta);
        } /*-- Fin controlador --*/


        /*---------- Controlador buscar codigo de producto ----------*/
        public function buscar_codigo_venta_controlador(){

            /*== Recuperando codigo de busqueda ==*/
			$producto=mainModel::limpiar_cadena($_POST['buscar_codigo']);

			/*== Comprobando que no este vacio el campo ==*/
			if($producto==""){
				return '<div class="alert alert-warning" role="alert">
					<p class="text-center mb-0">
						<i class="fas fa-exclamation-triangle fa-2x"></i><br>
						Debes de introducir el Nombre, Marca o Modelo del producto
					</p>
				</div>';
				exit();
            }

            /*== Seleccionando productos en la DB ==*/
            $datos_productos=mainModel::ejecutar_consulta_simple("SELECT * FROM producto WHERE (producto_nombre LIKE '%$producto%' OR producto_marca LIKE '%$producto%' OR producto_modelo LIKE '%$producto%') ORDER BY producto_nombre ASC");
            
            if($datos_productos->rowCount()>=1){

                if($_SESSION['lector_codigo_svi']=="Barras"){
                    $campo_codigo="producto_codigo";
                }else{
                    $campo_codigo="producto_sku";
                }

				$datos_productos=$datos_productos->fetchAll();

				$tabla='<div class="table-responsive" ><table class="table table-hover table-bordered table-sm"><tbody>';

				foreach($datos_productos as $rows){
					$tabla.='
					<tr class="text-center">
                        <td>'.$rows['producto_nombre'].'</td>
                        <td>
                            <button type="button" class="btn btn-primary" onclick="agregar_codigo(\''.$rows[$campo_codigo].'\')"><i class="fas fa-plus-circle"></i></button>
                        </td>
                    </tr>
                    ';
				}

				$tabla.='</tbody></table></div>';
				return $tabla;
			}else{
				return '<div class="alert alert-warning" role="alert">
					<p class="text-center mb-0">
						<i class="fas fa-exclamation-triangle fa-2x"></i><br>
						No hemos encontrado ningún producto en el sistema que coincida con <strong>“'.$producto.'”</strong>
					</p>
				</div>';
				exit();
			}
        } /*-- Fin controlador --*/


        /*---------- Controlador aplicar descuento a venta ----------*/
        public function aplicar_descuento_venta_controlador(){    

            /*== Recuperando descuento ==*/
            $descuento=mainModel::limpiar_cadena($_POST['venta_descuento_add']);
            
            /*== Comprobando que no este vacio el campo y que sea mayor a 0 ==*/
			if($descuento=="" || $descuento<=0){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"Debe de ingresar una cantidad mayor a 0.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Comprobando formato de descuento ==*/
            if(mainModel::verificar_datos("[0-9]{1,2}",$descuento)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El descuento no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Comprobando que se hayan agregado productos y que la venta sea mayor a 0 ==*/
            if($_SESSION['venta_total']<=0 || (!isset($_SESSION['datos_producto_venta']) && count($_SESSION['datos_producto_venta'])<=0)){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No podemos aplicar el descuento ya que no ha agregado productos a esta venta.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            $_SESSION['venta_descuento']=$descuento;

            if($_SESSION['venta_descuento']==$descuento){
                $alerta=[
					"Alerta"=>"recargar",
					"Titulo"=>"¡Descuento aplicado!",
					"Texto"=>"El descuento ha sido aplicado con éxito en la venta.",
					"Tipo"=>"success"
				];
            }else{
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No podemos aplicar el descuento debido a un error, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
            }
            echo json_encode($alerta);
        } /*-- Fin controlador --*/


        /*---------- Controlador remover descuento a venta ----------*/
        public function remover_descuento_venta_controlador(){

            /*== Recuperando descuento ==*/
            $descuento=mainModel::limpiar_cadena($_POST['venta_descuento_del']);

            if($descuento!=$_SESSION['venta_descuento']){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No podemos remover el descuento debido a un error, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            $_SESSION['venta_descuento']=0;

            if($_SESSION['venta_descuento']==0){
                $alerta=[
					"Alerta"=>"recargar",
					"Titulo"=>"¡Descuento removido!",
					"Texto"=>"El descuento ha sido removido con éxito de la venta.",
					"Tipo"=>"success"
				];
            }else{
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No podemos remover el descuento debido a un error, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
            }
            echo json_encode($alerta);
        } /*-- Fin controlador --*/


        /*---------- Controlador registrar venta ----------*/
        public function registrar_venta_controlador(){    

            $venta_tipo=mainModel::limpiar_cadena($_POST['venta_tipo_venta_reg']);
            $venta_pagado=mainModel::limpiar_cadena($_POST['venta_abono_reg']);

            /*== Comprobando integridad de los datos ==*/
            if(mainModel::verificar_datos("[0-9.]{1,25}",$venta_pagado)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El total pagado por el cliente no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            if($venta_tipo!="Contado" && $venta_tipo!="Credito"){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El tipo de pago no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            if($_SESSION['venta_subtotal']<=0 || $_SESSION['venta_impuestos']<=0 || $_SESSION['venta_total']<=0 || $_SESSION['venta_costos']<=0 || (!isset($_SESSION['datos_producto_venta']) && count($_SESSION['datos_producto_venta'])<=0)){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No ha agregado productos a esta venta o no ha agregado los impuestos en los datos de la empresa",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            if(!isset($_SESSION['datos_cliente_venta'])){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No ha seleccionado ningún cliente para realizar esta venta",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            
            /*== Comprobando cliente en la DB ==*/
			$check_cliente=mainModel::ejecutar_consulta_simple("SELECT cliente_id FROM cliente WHERE cliente_id='".$_SESSION['datos_cliente_venta']['cliente_id']."'");
			if($check_cliente->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos encontrado el cliente registrado en el sistema.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }
            $check_cliente->closeCursor();
			$check_cliente=mainModel::desconectar($check_cliente);


            /*== Comprobando caja en la DB ==*/
            $check_caja=mainModel::ejecutar_consulta_simple("SELECT * FROM caja WHERE caja_id='".$_SESSION['caja_svi']."' AND caja_estado='Habilitada'");
			if($check_caja->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"La caja se encuentra deshabilitada o no está registrada en el sistema.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }else{
                $datos_caja=$check_caja->fetch();
            }
            $check_caja->closeCursor();
			$check_caja=mainModel::desconectar($check_caja);

            /*== Comprobando que la venta al credito sea para un cliente ==*/
            if($venta_tipo=="Credito"){
                if($_SESSION['datos_cliente_venta']['cliente_id']==1){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"Para realizar una venta al crédito debe de seleccionar un cliente.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
            }
            

            /*== Formateando variables ==*/
            $venta_impuesto_nombre=$_SESSION['venta_impuesto_nombre'];
            $venta_impuesto_porcentaje=$_SESSION['venta_impuesto_porcentaje'];
            $venta_pagado=number_format($venta_pagado,MONEDA_DECIMALES,'.','');
            $venta_total=number_format($_SESSION['venta_total'],MONEDA_DECIMALES,'.','');
            $venta_costo=number_format($_SESSION['venta_costos'],MONEDA_DECIMALES,'.','');
            $venta_fecha=date("Y-m-d");
            $venta_hora=date("h:i a");

            $venta_descuento_porcentaje=$_SESSION['venta_descuento'];

            $venta_descuento_total=$venta_total*($venta_descuento_porcentaje/100);
            $venta_descuento_total=number_format($venta_descuento_total,MONEDA_DECIMALES,'.','');

            $venta_total_final=$venta_total-$venta_descuento_total;
            $venta_total_final=number_format($venta_total_final,MONEDA_DECIMALES,'.','');

            $venta_subtotal=$venta_total_final/(($venta_impuesto_porcentaje/100)+1);
            $venta_subtotal=number_format($venta_subtotal,MONEDA_DECIMALES,'.','');

            $venta_impuestos=$venta_total_final-$venta_subtotal;
            $venta_impuestos=number_format($venta_impuestos,MONEDA_DECIMALES,'.','');

            $venta_utilidad=$venta_total_final-$venta_costo;
            $venta_utilidad=number_format($venta_utilidad,MONEDA_DECIMALES,'.','');

            /*== Calculando el cambio ==*/
            if($venta_tipo=="Contado"){

                if($venta_pagado<$venta_total_final){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"Esta es una venta al contado, el total a pagar por el cliente no puede ser menor al total a pagar.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                $venta_cambio=$venta_pagado-$venta_total_final;
                $venta_cambio=number_format($venta_cambio,MONEDA_DECIMALES,'.','');

                $venta_estado="Cancelado";

            }else{
                if($venta_pagado<$venta_total_final){
                    $venta_estado="Pendiente";
                    $venta_cambio=0.00;
                    $venta_cambio=number_format($venta_cambio,MONEDA_DECIMALES,'.','');
                }else{
                    $venta_estado="Cancelado";
                    $venta_cambio=$venta_pagado-$venta_total_final;
                    $venta_cambio=number_format($venta_cambio,MONEDA_DECIMALES,'.','');
                }
            }

            /*== Calculando total en caja ==*/
            $movimiento_cantidad=$venta_pagado-$venta_cambio;
            $movimiento_cantidad=number_format($movimiento_cantidad,MONEDA_DECIMALES,'.','');

            $total_caja=$datos_caja['caja_efectivo']+$movimiento_cantidad;
            $total_caja=number_format($total_caja,MONEDA_DECIMALES,'.','');


            /*== Actualizando productos ==*/
            $errores_productos=0;
			foreach($_SESSION['datos_producto_venta'] as $productos){

                /*== Obteniendo datos del producto ==*/
                $check_producto=mainModel::ejecutar_consulta_simple("SELECT * FROM producto WHERE producto_id='".$productos['producto_id']."' AND producto_codigo='".$productos['producto_codigo']."'");
                if($check_producto->rowCount()<1){
                    $errores_productos=1;
                    break;
                }else{
                    $datos_producto=$check_producto->fetch();
                }
                $check_producto->closeCursor();
                $check_producto=mainModel::desconectar($check_producto);

                /*== Respaldando datos de BD para poder restaurar en caso de errores ==*/
                $_SESSION['datos_producto_venta'][$productos['producto_codigo']]['producto_stock_total']=$datos_producto['producto_stock_total']-$_SESSION['datos_producto_venta'][$productos['producto_codigo']]['venta_detalle_cantidad'];

                $_SESSION['datos_producto_venta'][$productos['producto_codigo']]['producto_stock_total_old']=$datos_producto['producto_stock_total'];

                $_SESSION['datos_producto_venta'][$productos['producto_codigo']]['producto_stock_vendido']=$datos_producto['producto_stock_vendido']+$_SESSION['datos_producto_venta'][$productos['producto_codigo']]['venta_detalle_cantidad'];

                $_SESSION['datos_producto_venta'][$productos['producto_codigo']]['producto_stock_vendido_old']=$datos_producto['producto_stock_vendido'];

                /*== Preparando datos para enviarlos al modelo ==*/
                $datos_producto_up=[
                    "producto_stock_total"=>[
                        "campo_marcador"=>":Stock",
                        "campo_valor"=>$_SESSION['datos_producto_venta'][$productos['producto_codigo']]['producto_stock_total']
                    ],
                    "producto_stock_vendido"=>[
                        "campo_marcador"=>":StockVendido",
                        "campo_valor"=>$_SESSION['datos_producto_venta'][$productos['producto_codigo']]['producto_stock_vendido']
                    ]
                ];

                $condicion=[
                    "condicion_campo"=>"producto_id",
                    "condicion_marcador"=>":ID",
                    "condicion_valor"=>$productos['producto_id']
                ];

                /*== Actualizando producto ==*/
                if(!mainModel::actualizar_datos("producto",$datos_producto_up,$condicion)){
                    $errores_productos=1;
                    break;
                }
            }

            /*== Reestableciendo DB debido a errores ==*/
            if($errores_productos==1){

                foreach($_SESSION['datos_producto_venta'] as $producto){

                    $datos_producto_rs=[
                        "producto_stock_total"=>[
                            "campo_marcador"=>":Stock",
                            "campo_valor"=>$producto['producto_stock_total_old']
                        ],
                        "producto_stock_vendido"=>[
                            "campo_marcador"=>":StockVendido",
                            "campo_valor"=>$producto['producto_stock_vendido_old']
                        ]
                    ];

                    $condicion=[
                        "condicion_campo"=>"producto_id",
                        "condicion_marcador"=>":ID",
                        "condicion_valor"=>$producto['producto_id']
                    ];

                    mainModel::actualizar_datos("producto",$datos_producto_rs,$condicion);

                }

                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos podido actualizar los productos en el sistema.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== generando codigo de venta ==*/
            $correlativo=mainModel::ejecutar_consulta_simple("SELECT venta_id FROM venta");
			$correlativo=($correlativo->rowCount())+1;
            $codigo_venta=mainModel::generar_codigo_aleatorio(10,$correlativo);

            /*== Preparando datos para enviarlos al modelo ==*/
			$datos_venta_reg=[
                "venta_codigo"=>[
					"campo_marcador"=>":Codigo",
					"campo_valor"=>$codigo_venta
                ],
                "venta_tipo"=>[
					"campo_marcador"=>":Tipo",
					"campo_valor"=>$venta_tipo
                ],
                "venta_fecha"=>[
					"campo_marcador"=>":Fecha",
					"campo_valor"=>$venta_fecha
                ],
                "venta_hora"=>[
					"campo_marcador"=>":Hora",
					"campo_valor"=>$venta_hora
                ],
                "venta_impuesto_nombre"=>[
					"campo_marcador"=>":ImpuestoNombre",
					"campo_valor"=>$venta_impuesto_nombre
                ],
                "venta_impuesto_porcentaje"=>[
					"campo_marcador"=>":ImpuestoPorcentaje",
					"campo_valor"=>$venta_impuesto_porcentaje
                ],
                "venta_subtotal"=>[
					"campo_marcador"=>":Subtotal",
					"campo_valor"=>$venta_subtotal
                ],
                "venta_impuestos"=>[
					"campo_marcador"=>":Impuestos",
					"campo_valor"=>$venta_impuestos
                ],
                "venta_total"=>[
					"campo_marcador"=>":Total",
					"campo_valor"=>$venta_total
                ],
                "venta_descuento_porcentaje"=>[
					"campo_marcador"=>":DescPorcentaje",
					"campo_valor"=>$venta_descuento_porcentaje
                ],
                "venta_descuento_total"=>[
					"campo_marcador"=>":DescTotal",
					"campo_valor"=>$venta_descuento_total
                ],
                "venta_total_final"=>[
					"campo_marcador"=>":TotalFinal",
					"campo_valor"=>$venta_total_final
                ],
                "venta_pagado"=>[
					"campo_marcador"=>":Pagado",
					"campo_valor"=>$venta_pagado
                ],
                "venta_costo"=>[
					"campo_marcador"=>":Costos",
					"campo_valor"=>$venta_costo
                ],
                "venta_utilidad"=>[
					"campo_marcador"=>":Utilidad",
					"campo_valor"=>$venta_utilidad
                ],
                "venta_cambio"=>[
					"campo_marcador"=>":Cambio",
					"campo_valor"=>$venta_cambio
                ],
                "venta_estado"=>[
					"campo_marcador"=>":Estado",
					"campo_valor"=>$venta_estado
                ],
                "usuario_id"=>[
					"campo_marcador"=>":Usuario",
					"campo_valor"=>$_SESSION['id_svi']
                ],
                "cliente_id"=>[
					"campo_marcador"=>":Cliente",
					"campo_valor"=>$_SESSION['datos_cliente_venta']['cliente_id']
                ],
                "caja_id"=>[
					"campo_marcador"=>":Caja",
					"campo_valor"=>$_SESSION['caja_svi']
                ]
            ];

            /*== Agregando venta ==*/
            $agregar_venta=mainModel::guardar_datos("venta",$datos_venta_reg);

            if($agregar_venta->rowCount()!=1){
                foreach($_SESSION['datos_producto_venta'] as $producto){

                    $datos_producto_rs=[
                        "producto_stock_total"=>[
                            "campo_marcador"=>":Stock",
                            "campo_valor"=>$producto['producto_stock_total_old']
                        ],
                        "producto_stock_vendido"=>[
                            "campo_marcador"=>":StockVendido",
                            "campo_valor"=>$producto['producto_stock_vendido_old']
                        ]
                    ];

                    $condicion=[
                        "condicion_campo"=>"producto_id",
                        "condicion_marcador"=>":ID",
                        "condicion_valor"=>$producto['producto_id']
                    ];

                    mainModel::actualizar_datos("producto",$datos_producto_rs,$condicion);

                }

                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos podido registrar la venta, por favor intente nuevamente. Código de error: 001",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }
            $agregar_venta->closeCursor();
			$agregar_venta=mainModel::desconectar($agregar_venta);

            /*== Agregando detalles de la venta ==*/
            $errores_venta_detalle=0;
            foreach($_SESSION['datos_producto_venta'] as $venta_detalle){

                $detalle_descuento_producto=$venta_detalle['venta_detalle_precio_venta']*($venta_descuento_porcentaje/100);
                $detalle_descuento_producto=number_format($detalle_descuento_producto,MONEDA_DECIMALES,'.','');

                $venta_detalle_precio_venta=$venta_detalle['venta_detalle_precio_venta']-$detalle_descuento_producto;
                $venta_detalle_precio_venta=number_format($venta_detalle_precio_venta,MONEDA_DECIMALES,'.','');

                $venta_detalle_total=$venta_detalle['venta_detalle_cantidad']*$venta_detalle_precio_venta;
                $venta_detalle_total=number_format($venta_detalle_total,MONEDA_DECIMALES,'.','');

                $venta_detalle_subtotal=$venta_detalle_total/(($venta_impuesto_porcentaje/100)+1);
                $venta_detalle_subtotal=number_format($venta_detalle_subtotal,MONEDA_DECIMALES,'.','');

                $venta_detalle_impuestos=$venta_detalle_total-$venta_detalle_subtotal;
                $venta_detalle_impuestos=number_format($venta_detalle_impuestos,MONEDA_DECIMALES,'.','');

                $venta_detalle_utilidad=$venta_detalle_total-$venta_detalle['venta_detalle_costos'];
                $venta_detalle_utilidad=number_format($venta_detalle_utilidad,MONEDA_DECIMALES,'.','');

                /*== Preparando datos para enviarlos al modelo ==*/
                $datos_venta_detalle_reg=[
                    "venta_detalle_cantidad"=>[
                        "campo_marcador"=>":Cantidad",
                        "campo_valor"=>$venta_detalle['venta_detalle_cantidad']
                    ],
                    "venta_detalle_precio_compra"=>[
                        "campo_marcador"=>":PrecioCompra",
                        "campo_valor"=>$venta_detalle['venta_detalle_precio_compra']
                    ],
                    "venta_detalle_precio_regular"=>[
                        "campo_marcador"=>":PrecioRegular",
                        "campo_valor"=>$venta_detalle['venta_detalle_precio_regular']
                    ],
                    "venta_detalle_precio_venta"=>[
                        "campo_marcador"=>":PrecioVenta",
                        "campo_valor"=>$venta_detalle_precio_venta
                    ],
                    "venta_detalle_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$venta_detalle_subtotal
                    ],
                    "venta_detalle_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$venta_detalle_impuestos
                    ],
                    "venta_detalle_descuento_porcentaje"=>[
                        "campo_marcador"=>":DescPorcentaje",
                        "campo_valor"=>$venta_descuento_porcentaje
                    ],
                    "venta_detalle_descuento_total"=>[
                        "campo_marcador"=>":DescTotal",
                        "campo_valor"=>$detalle_descuento_producto
                    ],
                    "venta_detalle_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$venta_detalle_total
                    ],
                    "venta_detalle_costo"=>[
                        "campo_marcador"=>":Costo",
                        "campo_valor"=>$venta_detalle['venta_detalle_costos']
                    ],
                    "venta_detalle_utilidad"=>[
                        "campo_marcador"=>":Utilidad",
                        "campo_valor"=>$venta_detalle_utilidad
                    ],
                    "venta_detalle_descripcion"=>[
                        "campo_marcador"=>":Descripcion",
                        "campo_valor"=>$venta_detalle['venta_detalle_descripcion']
                    ],
                    "venta_detalle_garantia"=>[
                        "campo_marcador"=>":Garantia",
                        "campo_valor"=>$venta_detalle['producto_garantia']
                    ],
                    "venta_codigo"=>[
                        "campo_marcador"=>":VentaCodigo",
                        "campo_valor"=>$codigo_venta
                    ],
                    "producto_id"=>[
                        "campo_marcador"=>":Producto",
                        "campo_valor"=>$venta_detalle['producto_id']
                    ]
                ];

                $agregar_detalle_venta=mainModel::guardar_datos("venta_detalle",$datos_venta_detalle_reg);

                if($agregar_detalle_venta->rowCount()!=1){
                    $errores_venta_detalle=1;
                    break;
                }
                $agregar_detalle_venta->closeCursor();
			    $agregar_detalle_venta=mainModel::desconectar($agregar_detalle_venta);
            }

            /*== Reestableciendo DB debido a errores ==*/
            if($errores_venta_detalle==1){

                mainModel::eliminar_registro("venta_detalle","venta_codigo",$codigo_venta);
                mainModel::eliminar_registro("venta","venta_codigo",$codigo_venta);

                foreach($_SESSION['datos_producto_venta'] as $producto){

                    $datos_producto_rs=[
                        "producto_stock_total"=>[
                            "campo_marcador"=>":Stock",
                            "campo_valor"=>$producto['producto_stock_total_old']
                        ],
                        "producto_stock_vendido"=>[
                            "campo_marcador"=>":StockVendido",
                            "campo_valor"=>$producto['producto_stock_vendido_old']
                        ]
                    ];

                    $condicion=[
                        "condicion_campo"=>"producto_id",
                        "condicion_marcador"=>":ID",
                        "condicion_valor"=>$producto['producto_id']
                    ];

                    mainModel::actualizar_datos("producto",$datos_producto_rs,$condicion);

                }

                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos podido registrar la venta, por favor intente nuevamente. Código de error: 002",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Actualizando efectivo en caja ==*/
            $datos_caja_up=[
                "caja_efectivo"=>[
                    "campo_marcador"=>":Efectivo",
                    "campo_valor"=>$total_caja
                ]
            ];

            $condicion_caja=[
                "condicion_campo"=>"caja_id",
                "condicion_marcador"=>":ID",
                "condicion_valor"=>$_SESSION['caja_svi']
            ];

            if(!mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja)){

                mainModel::eliminar_registro("venta_detalle","venta_codigo",$codigo_venta);
                mainModel::eliminar_registro("venta","venta_codigo",$codigo_venta);

                foreach($_SESSION['datos_producto_venta'] as $producto){

                    $datos_producto_rs=[
                        "producto_stock_total"=>[
                            "campo_marcador"=>":Stock",
                            "campo_valor"=>$producto['producto_stock_total_old']
                        ],
                        "producto_stock_vendido"=>[
                            "campo_marcador"=>":StockVendido",
                            "campo_valor"=>$producto['producto_stock_vendido_old']
                        ]
                    ];

                    $condicion=[
                        "condicion_campo"=>"producto_id",
                        "condicion_marcador"=>":ID",
                        "condicion_valor"=>$producto['producto_id']
                    ];

                    mainModel::actualizar_datos("producto",$datos_producto_rs,$condicion);

                }

                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos podido registrar la venta, por favor intente nuevamente. Código de error: 003",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();

            }

            /*== Agregando movimiento de caja ==*/
            $correlativo=mainModel::ejecutar_consulta_simple("SELECT movimiento_id FROM movimiento");
			$correlativo=($correlativo->rowCount())+1;

            $codigo_movimiento=mainModel::generar_codigo_aleatorio(8,$correlativo);
            
            /*== Preparando datos para enviarlos al modelo ==*/
            $datos_movimiento=[
                "movimiento_codigo"=>[
                    "campo_marcador"=>":Codigo",
                    "campo_valor"=>$codigo_movimiento
                ],
                "movimiento_fecha"=>[
                    "campo_marcador"=>":Fecha",
                    "campo_valor"=>$venta_fecha
                ],
                "movimiento_hora"=>[
                    "campo_marcador"=>":Hora",
                    "campo_valor"=>$venta_hora
                ],
                "movimiento_tipo"=>[
                    "campo_marcador"=>":Tipo",
                    "campo_valor"=>"Entrada de efectivo"
                ],
                "movimiento_motivo"=>[
                    "campo_marcador"=>":Motivo",
                    "campo_valor"=>"Venta de productos"
                ],
                "movimiento_saldo_anterior"=>[
                    "campo_marcador"=>":Anterior",
                    "campo_valor"=>$datos_caja['caja_efectivo']
                ],
                "movimiento_cantidad"=>[
                    "campo_marcador"=>":Cantidad",
                    "campo_valor"=>$movimiento_cantidad
                ],
                "movimiento_saldo_actual"=>[
                    "campo_marcador"=>":Actual",
                    "campo_valor"=>$total_caja
                ],
                "usuario_id"=>[
                    "campo_marcador"=>":Usuario",
                    "campo_valor"=>$_SESSION['id_svi']
                ],
                "caja_id"=>[
                    "campo_marcador"=>":Caja",
                    "campo_valor"=>$_SESSION['caja_svi']
                ]
            ];

            $agregar_movimiento=mainModel::guardar_datos("movimiento",$datos_movimiento);

            if($agregar_movimiento->rowCount()<1){
                mainModel::eliminar_registro("venta_detalle","venta_codigo",$codigo_venta);
                mainModel::eliminar_registro("venta","venta_codigo",$codigo_venta);

                /*== Actualizando efectivo en caja ==*/
                $datos_caja_up=[
                    "caja_efectivo"=>[
                        "campo_marcador"=>":Efectivo",
                        "campo_valor"=>$datos_caja['caja_efectivo']
                    ]
                ];
                $condicion_caja=[
                    "condicion_campo"=>"caja_id",
                    "condicion_marcador"=>":ID",
                    "condicion_valor"=>$_SESSION['caja_svi']
                ];
                mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

                foreach($_SESSION['datos_producto_venta'] as $producto){

                    $datos_producto_rs=[
                        "producto_stock_total"=>[
                            "campo_marcador"=>":Stock",
                            "campo_valor"=>$producto['producto_stock_total_old']
                        ],
                        "producto_stock_vendido"=>[
                            "campo_marcador"=>":StockVendido",
                            "campo_valor"=>$producto['producto_stock_vendido_old']
                        ]
                    ];

                    $condicion=[
                        "condicion_campo"=>"producto_id",
                        "condicion_marcador"=>":ID",
                        "condicion_valor"=>$producto['producto_id']
                    ];

                    mainModel::actualizar_datos("producto",$datos_producto_rs,$condicion);

                }

                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos podido registrar la venta, por favor intente nuevamente. Código de error: 004",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }
            $agregar_movimiento->closeCursor();
			$agregar_movimiento=mainModel::desconectar($agregar_movimiento);

            /*== Comprobando monto pagado para guardar en tabla pago ==*/
            if($venta_pagado>0){

                /*== Preparando datos para enviarlos al modelo ==*/
                $datos_pago=[
                    "pago_fecha"=>[
                        "campo_marcador"=>":Fecha",
                        "campo_valor"=>$venta_fecha
                    ],
                    "pago_monto"=>[
                        "campo_marcador"=>":Monto",
                        "campo_valor"=>$movimiento_cantidad
                    ],
                    "venta_codigo"=>[
                        "campo_marcador"=>":Codigo",
                        "campo_valor"=>$codigo_venta
                    ],
                    "usuario_id"=>[
                        "campo_marcador"=>":Usuario",
                        "campo_valor"=>$_SESSION['id_svi']
                    ],
                    "caja_id"=>[
                        "campo_marcador"=>":Caja",
                        "campo_valor"=>$_SESSION['caja_svi']
                    ]
                ];

                $agregar_pago=mainModel::guardar_datos("pago",$datos_pago);

                /*== Reestableciendo DB debido a errores ==*/
                if($agregar_pago->rowCount()<1){
                    mainModel::eliminar_registro("venta_detalle","venta_codigo",$codigo_venta);
                    mainModel::eliminar_registro("venta","venta_codigo",$codigo_venta);
                    mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);
    
                    /*== Actualizando efectivo en caja ==*/
                    $datos_caja_up=[
                        "caja_efectivo"=>[
                            "campo_marcador"=>":Efectivo",
                            "campo_valor"=>$datos_caja['caja_efectivo']
                        ]
                    ];
                    $condicion_caja=[
                        "condicion_campo"=>"caja_id",
                        "condicion_marcador"=>":ID",
                        "condicion_valor"=>$_SESSION['caja_svi']
                    ];
                    mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);
    
                    foreach($_SESSION['datos_producto_venta'] as $producto){
    
                        $datos_producto_rs=[
                            "producto_stock_total"=>[
                                "campo_marcador"=>":Stock",
                                "campo_valor"=>$producto['producto_stock_total_old']
                            ],
                            "producto_stock_vendido"=>[
                                "campo_marcador"=>":StockVendido",
                                "campo_valor"=>$producto['producto_stock_vendido_old']
                            ]
                        ];
    
                        $condicion=[
                            "condicion_campo"=>"producto_id",
                            "condicion_marcador"=>":ID",
                            "condicion_valor"=>$producto['producto_id']
                        ];
    
                        mainModel::actualizar_datos("producto",$datos_producto_rs,$condicion);
                    }
    
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"No hemos podido registrar la venta, por favor intente nuevamente. Código de error: 005",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
                $agregar_pago->closeCursor();
			    $agregar_pago=mainModel::desconectar($agregar_pago);
            }
            
            /*== Agregando kardex y detalle kardex ==*/
            $year=date("Y");
            $mes=date("m");
            $fecha=date("Y-m-d");
            $errores_kardex=0;
            $array_kardex=[];
            foreach($_SESSION['datos_producto_venta'] as $kardex){
                /*== Obteniendo datos de kardex ==*/
                $check_kardex=mainModel::ejecutar_consulta_simple("SELECT * FROM kardex WHERE kardex_mes='$mes' AND kardex_year='$year' AND producto_id='".$kardex['producto_id']."'");
                if($check_kardex->rowCount()<1){
                    $operacion="Guardar";

                    $correlativo=mainModel::ejecutar_consulta_simple("SELECT kardex_id FROM kardex");
                    $correlativo=($correlativo->rowCount())+1;

                    $kardex_codigo=mainModel::generar_codigo_aleatorio(10,$correlativo);
                    $kardex_mes=$mes;
                    $kardex_year=$year;

                    $array_kardex[$kardex_codigo]=[
                        "tipo_operacion"=>$operacion,
                        "kardex_codigo"=>$kardex_codigo,
                        "total_unidades"=>$kardex['venta_detalle_cantidad'],
                        "kardex_salida_unidad"=>$kardex['venta_detalle_cantidad'],
                        "kardex_salida_costo_total"=>$kardex['venta_detalle_total'],
                        "kardex_existencia_unidad"=>$kardex['producto_stock_total'],
                        "kardex_existencia_costo_total"=>$kardex['venta_detalle_total']
                    ];

                    $kardex_entrada_unidad=0;
                    $kardex_entrada_costo_total=0.00;
                    $kardex_entrada_costo_total=number_format($kardex_entrada_costo_total,MONEDA_DECIMALES,'.','');

                    $kardex_salida_unidad=$kardex['venta_detalle_cantidad'];
                    $kardex_salida_costo_total=$kardex['venta_detalle_total']-($kardex['venta_detalle_total']*($venta_descuento_porcentaje/100));

                    $check_kardex_anterior=mainModel::ejecutar_consulta_simple("SELECT * FROM kardex WHERE producto_id='".$kardex['producto_id']."' ORDER BY kardex_id DESC LIMIT 1");
                    if($check_kardex_anterior->rowCount()==1){
                        $datos_ka=$check_kardex_anterior->fetch();
                        $kardex_existencia_costo_total_old=$datos_ka['kardex_existencia_costo_total'];
                    }else{
                        $kardex_existencia_costo_total_old=$kardex['producto_stock_total_old']*$kardex['venta_detalle_precio_compra'];
                    }
                    $check_kardex_anterior->closeCursor();
                    $check_kardex_anterior=mainModel::desconectar($check_kardex_anterior);

                    $kardex_existencia_inicial=$kardex['producto_stock_total_old'];
                    $kardex_existencia_unidad=$kardex['producto_stock_total_old']-$kardex['venta_detalle_cantidad'];
                    $kardex_existencia_costo_total=$kardex_existencia_costo_total_old-$kardex['venta_detalle_costos']; 
                    $kardex_existencia_costo_total=number_format($kardex_existencia_costo_total,MONEDA_DECIMALES,'.','');
                }else{
                    $operacion="Actualizar";

                    $datos_kardex=$check_kardex->fetch();
				
                    $kardex_codigo=$datos_kardex['kardex_codigo'];
                    $kardex_mes=$datos_kardex['kardex_mes'];
                    $kardex_year=$datos_kardex['kardex_year'];

                    $array_kardex[$kardex_codigo]=[
                        "tipo_operacion"=>$operacion,
                        "kardex_codigo"=>$kardex_codigo,
                        "total_unidades"=>$kardex['venta_detalle_cantidad'],
                        "kardex_salida_unidad"=>$datos_kardex['kardex_salida_unidad'],
                        "kardex_salida_costo_total"=>$datos_kardex['kardex_salida_costo_total'],
                        "kardex_existencia_unidad"=>$datos_kardex['kardex_existencia_unidad'],
                        "kardex_existencia_costo_total"=>$datos_kardex['kardex_existencia_costo_total']
                    ];

                    $kardex_entrada_unidad=$datos_kardex['kardex_entrada_unidad'];
                    $kardex_entrada_costo_total=$datos_kardex['kardex_entrada_costo_total'];
                    

                    $kardex_salida_unidad=$datos_kardex['kardex_salida_unidad']+$kardex['venta_detalle_cantidad'];
                    $kardex_salida_costo_total=$datos_kardex['kardex_salida_costo_total']+($kardex['venta_detalle_total']-($kardex['venta_detalle_total']*($venta_descuento_porcentaje/100)));
                    $kardex_salida_costo_total=number_format($kardex_salida_costo_total,MONEDA_DECIMALES,'.','');

                    $kardex_existencia_inicial=$datos_kardex['kardex_existencia_inicial'];
                    $kardex_existencia_unidad=$datos_kardex['kardex_existencia_unidad']-$kardex['venta_detalle_cantidad'];
                    $kardex_existencia_costo_total=$datos_kardex['kardex_existencia_costo_total']-$kardex['venta_detalle_costos'];
                    $kardex_existencia_costo_total=number_format($kardex_existencia_costo_total,MONEDA_DECIMALES,'.','');  
                }
                $check_kardex->closeCursor();
			    $check_kardex=mainModel::desconectar($check_kardex);

                /*== Preparando datos para enviarlos al modelo ==*/
                $datos_kardex_reg=[
                    "kardex_codigo"=>[
                        "campo_marcador"=>":Codigo",
                        "campo_valor"=>$kardex_codigo
                    ],
                    "kardex_mes"=>[
                        "campo_marcador"=>":Mes",
                        "campo_valor"=>$kardex_mes
                    ],
                    "kardex_year"=>[
                        "campo_marcador"=>":Year",
                        "campo_valor"=>$kardex_year
                    ],
                    "kardex_entrada_unidad"=>[
                        "campo_marcador"=>":EntradaU",
                        "campo_valor"=>$kardex_entrada_unidad
                    ],
                    "kardex_entrada_costo_total"=>[
                        "campo_marcador"=>":EntradaCT",
                        "campo_valor"=>$kardex_entrada_costo_total
                    ],
                    "kardex_salida_unidad"=>[
                        "campo_marcador"=>":SalidaU",
                        "campo_valor"=>$kardex_salida_unidad
                    ],
                    "kardex_salida_costo_total"=>[
                        "campo_marcador"=>":SalidaCT",
                        "campo_valor"=>$kardex_salida_costo_total
                    ],
                    "kardex_existencia_inicial"=>[
                        "campo_marcador"=>":ExistenciaI",
                        "campo_valor"=>$kardex_existencia_inicial
                    ],
                    "kardex_existencia_unidad"=>[
                        "campo_marcador"=>":ExistenciaU",
                        "campo_valor"=>$kardex_existencia_unidad
                    ],
                    "kardex_existencia_costo_total"=>[
                        "campo_marcador"=>":ExistenciaCT",
                        "campo_valor"=>$kardex_existencia_costo_total
                    ],
                    "producto_id"=>[
                        "campo_marcador"=>":Producto",
                        "campo_valor"=>$kardex['producto_id']
                    ]
                ];
                
                /*== Detectando la operacion a realizar ==*/
                if($operacion=="Guardar"){
                    $agregar_kardex=mainModel::guardar_datos("kardex",$datos_kardex_reg);
                    if($agregar_kardex->rowCount()!=1){
                        $errores_kardex=1;
                        break;
                    }
                    $agregar_kardex->closeCursor();
			        $agregar_kardex=mainModel::desconectar($agregar_kardex);
                }else{
                    $condicion_kardex=[
                        "condicion_campo"=>"kardex_id",
                        "condicion_marcador"=>":ID",
                        "condicion_valor"=>$datos_kardex['kardex_id']
                    ];

                    if(!mainModel::actualizar_datos("kardex",$datos_kardex_reg,$condicion_kardex)){
                        $errores_kardex=1;
                        break;
                    }
                }

                /*== Preparando datos para enviarlos al modelo ==*/
                $kardex_detalle_costo_total=$kardex['venta_detalle_total']-($kardex['venta_detalle_total']*($venta_descuento_porcentaje/100));
                $kardex_detalle_costo_total=number_format($kardex_detalle_costo_total,MONEDA_DECIMALES,'.','');

                $kardex_detalle_costo_unidad=$kardex['venta_detalle_precio_venta']-($kardex['venta_detalle_precio_venta']*($venta_descuento_porcentaje/100));
                $kardex_detalle_costo_unidad=number_format($kardex_detalle_costo_unidad,MONEDA_DECIMALES,'.','');

                $datos_kardex_detalle=[
                    "kardex_detalle_fecha"=>[
                        "campo_marcador"=>":Fecha",
                        "campo_valor"=>$fecha
                    ],
                    "kardex_detalle_tipo"=>[
                        "campo_marcador"=>":Tipo",
                        "campo_valor"=>"Salida"
                    ],
                    "kardex_detalle_descripcion"=>[
                        "campo_marcador"=>":Descripcion",
                        "campo_valor"=>"Venta de producto"
                    ],
                    "kardex_detalle_unidad"=>[
                        "campo_marcador"=>":Unidad",
                        "campo_valor"=>$kardex['venta_detalle_cantidad']
                    ],
                    "kardex_detalle_costo_unidad"=>[
                        "campo_marcador"=>":Costo",
                        "campo_valor"=>$kardex_detalle_costo_unidad
                    ],
                    "kardex_detalle_costo_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$kardex_detalle_costo_total
                    ],
                    "kardex_codigo"=>[
                        "campo_marcador"=>":Codigo",
                        "campo_valor"=>$kardex_codigo
                    ],
                    "producto_id"=>[
                        "campo_marcador"=>":Producto",
                        "campo_valor"=>$kardex['producto_id']
                    ],
                    "usuario_id"=>[
                        "campo_marcador"=>":Usuario",
                        "campo_valor"=>$_SESSION['id_svi']
                    ]
                ];

                $agregar_kardex_detalle=mainModel::guardar_datos("kardex_detalle",$datos_kardex_detalle);
                if($agregar_kardex_detalle->rowCount()!=1){
                    $errores_kardex=1;
                    break;
                }
                $agregar_kardex_detalle->closeCursor();
			    $agregar_kardex_detalle=mainModel::desconectar($agregar_kardex_detalle);
            }

            /*== Reestableciendo DB debido a errores ==*/
            if($errores_kardex==1){

                mainModel::eliminar_registro("venta_detalle","venta_codigo",$codigo_venta);
                mainModel::eliminar_registro("venta","venta_codigo",$codigo_venta);
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

                $check_pago=mainModel::ejecutar_consulta_simple("SELECT pago_id FROM pago WHERE venta_codigo='$codigo_venta'");
                if($check_pago->rowCount()>=1){
                    mainModel::eliminar_registro("pago","venta_codigo",$codigo_venta);
                }
                $check_pago->closeCursor();
			    $check_pago=mainModel::desconectar($check_pago);

                /*== Actualizando efectivo en caja ==*/
                $datos_caja_up=[
                    "caja_efectivo"=>[
                        "campo_marcador"=>":Efectivo",
                        "campo_valor"=>$datos_caja['caja_efectivo']
                    ]
                ];
                $condicion_caja=[
                    "condicion_campo"=>"caja_id",
                    "condicion_marcador"=>":ID",
                    "condicion_valor"=>$_SESSION['caja_svi']
                ];
                mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

                foreach($array_kardex as $kardexE){
                    $check_detalle_kardex=mainModel::ejecutar_consulta_simple("SELECT kardex_detalle_id FROM kardex_detalle WHERE kardex_detalle_fecha='$fecha' AND kardex_codigo='".$kardexE['kardex_codigo']."' AND kardex_detalle_unidad='".$kardexE['total_unidades']."'");

                    if($check_detalle_kardex->rowCount()==1){
                        mainModel::eliminar_registro("kardex_detalle","kardex_codigo",$kardexE['kardex_codigo']);
                    }
                    $check_detalle_kardex->closeCursor();
			        $check_detalle_kardex=mainModel::desconectar($check_detalle_kardex);

                    if($kardexE['tipo_operacion']=="Actualizar"){
                        $datos_kardex_rs=[
                            "kardex_salida_unidad"=>[
                                "campo_marcador"=>":KardexSalU",
                                "campo_valor"=>$kardexE['kardex_salida_unidad']
                            ],
                            "kardex_salida_costo_total"=>[
                                "campo_marcador"=>":KardexSalCT",
                                "campo_valor"=>$kardexE['kardex_salida_costo_total']
                            ],
                            "kardex_existencia_unidad"=>[
                                "campo_marcador"=>":KardexExU",
                                "campo_valor"=>$kardexE['kardex_existencia_unidad']
                            ],
                            "kardex_existencia_costo_total"=>[
                                "campo_marcador"=>":KardexExCT",
                                "campo_valor"=>$kardexE['kardex_existencia_costo_total']
                            ]
                        ];
    
                        $condicion=[
                            "condicion_campo"=>"kardex_codigo",
                            "condicion_marcador"=>":Codigo",
                            "condicion_valor"=>$kardexE['kardex_codigo']
                        ];

                        mainModel::actualizar_datos("kardex",$datos_kardex_rs,$condicion);

                    }elseif($kardexE['tipo_operacion']=="Guardar"){

                        $check_kardex_del=mainModel::ejecutar_consulta_simple("SELECT kardex_id FROM kardex WHERE kardex_codigo='".$kardexE['kardex_codigo']."'");

                        if($check_kardex_del->rowCount()==1){
                            mainModel::eliminar_registro("kardex","kardex_codigo",$kardexE['kardex_codigo']);
                        }
                        $check_kardex_del->closeCursor();
			            $check_kardex_del=mainModel::desconectar($check_kardex_del);
                    }
                }

                foreach($_SESSION['datos_producto_venta'] as $producto){

                    $datos_producto_rs=[
                        "producto_stock_total"=>[
                            "campo_marcador"=>":Stock",
                            "campo_valor"=>$producto['producto_stock_total_old']
                        ],
                        "producto_stock_vendido"=>[
                            "campo_marcador"=>":StockVendido",
                            "campo_valor"=>$producto['producto_stock_vendido_old']
                        ]
                    ];

                    $condicion=[
                        "condicion_campo"=>"producto_id",
                        "condicion_marcador"=>":ID",
                        "condicion_valor"=>$producto['producto_id']
                    ];

                    mainModel::actualizar_datos("producto",$datos_producto_rs,$condicion);

                }

                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos podido registrar la venta, por favor intente nuevamente. Código de error: 006",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Vaciando variables de sesion ==*/
            unset($_SESSION['venta_total']);
            unset($_SESSION['venta_impuestos']);
            unset($_SESSION['venta_subtotal']);
            unset($_SESSION['venta_costos']);
            unset($_SESSION['datos_cliente_venta']);
            unset($_SESSION['datos_producto_venta']);
            unset($_SESSION['venta_descuento']);

            $_SESSION['venta_codigo_factura']=$codigo_venta;

            $alerta=[
                "Alerta"=>"recargar",
                "Titulo"=>"¡Venta registrada!",
                "Texto"=>"La venta se registró con éxito en el sistema",
                "Tipo"=>"success"
            ];
            echo json_encode($alerta);
        } /*-- Fin controlador --*/


        /*---------- Controlador paginador ventas ----------*/
		public function paginador_venta_controlador($pagina,$registros,$url,$fecha_inicio,$fecha_final){

			$pagina=mainModel::limpiar_cadena($pagina);
			$registros=mainModel::limpiar_cadena($registros);

            $url=mainModel::limpiar_cadena($url);
            $tipo=$url;
            
			$url=SERVERURL.$url."/";
            
			$fecha_inicio=mainModel::limpiar_cadena($fecha_inicio);
			$fecha_final=mainModel::limpiar_cadena($fecha_final);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
            $inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;
            
            if($tipo=="sale-search-date"){
				if(mainModel::verificar_fecha($fecha_inicio) || mainModel::verificar_fecha($fecha_final)){
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
            
            $campos_tablas="venta.venta_id,venta.venta_codigo,venta.venta_tipo,venta.venta_fecha,venta.venta_hora,venta.venta_total_final,venta.venta_estado,venta.usuario_id,venta.cliente_id,venta.caja_id,usuario.usuario_id,usuario.usuario_nombre,usuario.usuario_apellido,cliente.cliente_id,cliente.cliente_nombre,cliente.cliente_apellido";

			if($tipo=="sale-search-date" && $fecha_inicio!="" && $fecha_final!=""){
				$consulta="SELECT SQL_CALC_FOUND_ROWS $campos_tablas FROM venta INNER JOIN cliente ON venta.cliente_id=cliente.cliente_id INNER JOIN usuario ON venta.usuario_id=usuario.usuario_id WHERE (venta.venta_fecha BETWEEN '$fecha_inicio' AND '$fecha_final') ORDER BY venta.venta_id DESC LIMIT $inicio,$registros";
			}elseif($tipo=="sale-search-code" && $fecha_inicio!=""){
                $consulta="SELECT SQL_CALC_FOUND_ROWS $campos_tablas FROM venta INNER JOIN cliente ON venta.cliente_id=cliente.cliente_id INNER JOIN usuario ON venta.usuario_id=usuario.usuario_id WHERE (venta.venta_codigo='$fecha_inicio') ORDER BY venta.venta_id DESC LIMIT $inicio,$registros";
            }elseif($tipo=="sale-pending"){
                $consulta="SELECT SQL_CALC_FOUND_ROWS $campos_tablas FROM venta INNER JOIN cliente ON venta.cliente_id=cliente.cliente_id INNER JOIN usuario ON venta.usuario_id=usuario.usuario_id WHERE (venta.venta_estado='Pendiente') ORDER BY venta.venta_id DESC LIMIT $inicio,$registros";
            }else{
				$consulta="SELECT SQL_CALC_FOUND_ROWS $campos_tablas FROM venta INNER JOIN cliente ON venta.cliente_id=cliente.cliente_id INNER JOIN usuario ON venta.usuario_id=usuario.usuario_id ORDER BY venta.venta_id DESC LIMIT $inicio,$registros";
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
                            <th>NRO.</th>
                            <th>CODIGO</th>
                            <th>FECHA</th>
                            <th>CLIENTE</th>
							<th>VENDEDOR</th>
                            <th>TOTAL</th>
                            <th>ESTADO</th>
                            <th><i class="fas fa-tools"></i>&nbsp; OPCIONES</th>
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
                            <td>'.$rows['venta_id'].'</td>
                            <td>'.$rows['venta_codigo'].'</td>
                            <td>'.date("d-m-Y", strtotime($rows['venta_fecha'])).' '.$rows['venta_hora'].'</td>
                            <td>'.$rows['cliente_nombre'].' '.$rows['cliente_apellido'].'</td>
                            <td>'.$rows['usuario_nombre'].' '.$rows['usuario_apellido'].'</td>
                            <td>'.MONEDA_SIMBOLO.number_format($rows['venta_total_final'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                    ';
                            if($rows['venta_estado']=="Cancelado"){
                                $tabla.='<td><span class="badge badge-secondary">'.$rows['venta_estado'].'</span></td>';
                            }else{
                                $tabla.='<td><span class="badge badge-warning">'.$rows['venta_estado'].'</span></td>';
                            }
                    $tabla.='
                            <td>
                                <div class="btn-group" role="group" aria-label="Options" style="margin: 0;" >
                                    <a class="btn btn-primary btn-sale-options" href="'.SERVERURL.'sale-detail/'.$rows['venta_codigo'].'/" data-toggle="popover" data-trigger="hover" title="Detalle venta Nro. '.$rows['venta_id'].'" data-content="Detalles, pagos & devoluciones de venta Nro.'.$rows['venta_id'].' - código: '.$rows['venta_codigo'].'">
                                        <i class="fas fa-cart-plus fa-fw"></i>
                                    </a>
                                    <button type="button" class="btn btn-info btn-sale-options" onclick="print_invoice(\''.SERVERURL.'pdf/invoice.php?code='.$rows['venta_codigo'].'\')" data-toggle="popover" data-trigger="hover" title="Imprimir factura Nro. '.$rows['venta_id'].'" data-content="CÓDIGO: '.$rows['venta_codigo'].'">
                                        <i class="fas fa-file-invoice-dollar fa-fw"></i>
                                    </button>
                                    <button type="button" class="btn btn-info btn-sale-options" onclick="print_ticket(\''.SERVERURL.'pdf/ticket_'.THERMAL_PRINT_SIZE.'mm.php?code='.$rows['venta_codigo'].'\')" data-toggle="popover" data-trigger="hover" title="Imprimir ticket Nro. '.$rows['venta_id'].'" data-content="CÓDIGO: '.$rows['venta_codigo'].'">
                                        <i class="fas fa-receipt fa-fw"></i>
                                    </button>';
                                    if($_SESSION['cargo_svi']=="Administrador"){
                                        $tabla.='<form class="FormularioAjax" action="'.SERVERURL.'ajax/ventaAjax.php" method="POST" data-form="delete" enctype="multipart/form-data" autocomplete="off" >
                                            <input type="hidden" name="venta_codigo_del" value="'.mainModel::encryption($rows['venta_codigo']).'">
                                            <input type="hidden" name="modulo_venta" value="eliminar_venta">
                                            <button type="submit" class="btn btn-warning btn-sale-options" data-toggle="popover" data-trigger="hover" title="Eliminar venta Nro. '.$rows['venta_id'].'" data-content="CÓDIGO: '.$rows['venta_codigo'].'">
                                                    <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>';
                                    }
                        $tabla.='</div>
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
				$tabla.='<p class="text-right">Mostrando ventas <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';
			}

			### Paginacion ###
			if($total>=1 && $pagina<=$Npaginas){
				$tabla.=mainModel::paginador_tablas($pagina,$Npaginas,$url,7);
			}

			return $tabla;
        } /*-- Fin controlador --*/
        

        /*---------- Controlador agregar pagos de ventas ----------*/
        public function agregar_pago_venta_controlador(){
            
            /*== Recuperando el codigo de la venta y monto ==*/
            $venta_codigo=mainModel::limpiar_cadena($_POST['pago_codigo_reg']);
            $pago_monto=mainModel::limpiar_cadena($_POST['pago_monto_reg']);

            /*== Comprobando venta ==*/
			$check_venta=mainModel::ejecutar_consulta_simple("SELECT * FROM venta WHERE venta_codigo='$venta_codigo' AND venta_estado='Pendiente' AND venta_tipo='Credito'");
			if($check_venta->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos encontrado en la base de datos la venta seleccionada para realizar el pago. También es posible que la venta ya haya sido cancelada o no es una venta al crédito por lo tanto no podemos agregar pagos",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }else{
                $datos_venta=$check_venta->fetch();
            }
            $check_venta->closeCursor();
            $check_venta=mainModel::desconectar($check_venta);
            
            /*== Comprobando pago ==*/
            if($pago_monto=="" || $pago_monto<=0){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"Debes de introducir una cantidad (monto) que sea mayor a 0 para poder realizar el pago.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Comprobando integridad de los datos ==*/
            if(mainModel::verificar_datos("[0-9.]{1,25}",$pago_monto)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El monto no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Comprobando caja en la DB ==*/
            $check_caja=mainModel::ejecutar_consulta_simple("SELECT * FROM caja WHERE caja_id='".$_SESSION['caja_svi']."' AND caja_estado='Habilitada'");
			if($check_caja->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"La caja se encuentra deshabilitada o no está registrada en el sistema.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }else{
                $datos_caja=$check_caja->fetch();
            }
            $check_caja->closeCursor();
            $check_caja=mainModel::desconectar($check_caja);

            /*== Calculando total pendiente ==*/
            $venta_pendiente=$datos_venta['venta_total_final']-$datos_venta['venta_pagado'];
            $venta_pendiente=number_format($venta_pendiente,MONEDA_DECIMALES,'.','');

            /*== Calculando el cambio ==*/
            if($pago_monto<$venta_pendiente){
                $venta_estado="Pendiente";
                $venta_cambio=0.00;
                $venta_cambio=number_format($venta_cambio,MONEDA_DECIMALES,'.','');
            }else{
                $venta_estado="Cancelado";
                $venta_cambio=$pago_monto-$venta_pendiente;
                $venta_cambio=number_format($venta_cambio,MONEDA_DECIMALES,'.','');
            }

            /*== Calculando total en caja ==*/
            $movimiento_cantidad=$pago_monto-$venta_cambio;
            $movimiento_cantidad=number_format($movimiento_cantidad,MONEDA_DECIMALES,'.','');

            $total_caja=$datos_caja['caja_efectivo']+$movimiento_cantidad;
            $total_caja=number_format($total_caja,MONEDA_DECIMALES,'.','');

            /*== Calculando total pagado de la venta ==*/
            $venta_pagado=($pago_monto+$datos_venta['venta_pagado'])-$venta_cambio;
            $venta_pagado=number_format($venta_pagado,MONEDA_DECIMALES,'.','');

            /*== Generando fecha y hora ==*/
            $pago_fecha=date("Y-m-d");
            $pago_hora=date("h:i a");

            /*== Preparando datos para enviarlos al modelo ==*/
            $datos_pago=[
                "pago_fecha"=>[
                    "campo_marcador"=>":Fecha",
                    "campo_valor"=>$pago_fecha
                ],
                "pago_monto"=>[
                    "campo_marcador"=>":Monto",
                    "campo_valor"=>$movimiento_cantidad
                ],
                "venta_codigo"=>[
                    "campo_marcador"=>":Codigo",
                    "campo_valor"=>$venta_codigo
                ],
                "usuario_id"=>[
                    "campo_marcador"=>":Usuario",
                    "campo_valor"=>$_SESSION['id_svi']
                ],
                "caja_id"=>[
                    "campo_marcador"=>":Caja",
                    "campo_valor"=>$_SESSION['caja_svi']
                ]
            ];

            $agregar_pago=mainModel::guardar_datos("pago",$datos_pago);

            if($agregar_pago->rowCount()<1){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido agregar el pago, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }
            $agregar_pago->closeCursor();
            $agregar_pago=mainModel::desconectar($agregar_pago);

            /*== Preparando datos para enviarlos al modelo ==*/
            $datos_venta=[
                "venta_pagado"=>[
                    "campo_marcador"=>":Pagado",
                    "campo_valor"=>$venta_pagado
                ],
                "venta_estado"=>[
                    "campo_marcador"=>":Estado",
                    "campo_valor"=>$venta_estado
                ]
            ];

            $condicion=[
                "condicion_campo"=>"venta_codigo",
                "condicion_marcador"=>":Codigo",
                "condicion_valor"=>$venta_codigo
            ];

            /*== Reestableciendo DB debido a errores ==*/
            if(!mainModel::actualizar_datos("venta",$datos_venta,$condicion)){
                
                /*== Eliminando pago ==*/
                $check_pago=mainModel::ejecutar_consulta_simple("SELECT pago_id FROM pago WHERE pago_fecha='$pago_fecha' AND venta_codigo='$venta_codigo' AND usuario_id='".$_SESSION['id_svi']."' ORDER BY pago_id DESC LIMIT 1");
                $datos_pago=$check_pago->fetch();

                mainModel::eliminar_registro("pago","pago_id",$datos_pago['pago_id']);

                $check_pago->closeCursor();
                $check_pago=mainModel::desconectar($check_pago);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido actualizar algunos datos de la venta para poder agregar el pago.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Agregando movimiento de caja ==*/
            $correlativo=mainModel::ejecutar_consulta_simple("SELECT movimiento_id FROM movimiento");
			$correlativo=($correlativo->rowCount())+1;

            $codigo_movimiento=mainModel::generar_codigo_aleatorio(8,$correlativo);

            /*== Preparando datos para enviarlos al modelo ==*/
            $datos_movimiento=[
                "movimiento_codigo"=>[
                    "campo_marcador"=>":Codigo",
                    "campo_valor"=>$codigo_movimiento
                ],
                "movimiento_fecha"=>[
                    "campo_marcador"=>":Fecha",
                    "campo_valor"=>$pago_fecha
                ],
                "movimiento_hora"=>[
                    "campo_marcador"=>":Hora",
                    "campo_valor"=>$pago_hora
                ],
                "movimiento_tipo"=>[
                    "campo_marcador"=>":Tipo",
                    "campo_valor"=>"Entrada de efectivo"
                ],
                "movimiento_motivo"=>[
                    "campo_marcador"=>":Motivo",
                    "campo_valor"=>"Pago de venta al crédito"
                ],
                "movimiento_saldo_anterior"=>[
                    "campo_marcador"=>":Anterior",
                    "campo_valor"=>$datos_caja['caja_efectivo']
                ],
                "movimiento_cantidad"=>[
                    "campo_marcador"=>":Cantidad",
                    "campo_valor"=>$movimiento_cantidad
                ],
                "movimiento_saldo_actual"=>[
                    "campo_marcador"=>":Actual",
                    "campo_valor"=>$total_caja
                ],
                "usuario_id"=>[
                    "campo_marcador"=>":Usuario",
                    "campo_valor"=>$_SESSION['id_svi']
                ],
                "caja_id"=>[
                    "campo_marcador"=>":Caja",
                    "campo_valor"=>$_SESSION['caja_svi']
                ]
            ];

            $agregar_movimiento=mainModel::guardar_datos("movimiento",$datos_movimiento);

            /*== Reestableciendo DB debido a errores ==*/
            if($agregar_movimiento->rowCount()<1){

                /*== Actualizando venta ==*/
                $datos_venta=[
                    "venta_pagado"=>[
                        "campo_marcador"=>":Pagado",
                        "campo_valor"=>$datos_venta['venta_pagado']
                    ],
                    "venta_estado"=>[
                        "campo_marcador"=>":Estado",
                        "campo_valor"=>$datos_venta['venta_estado']
                    ]
                ];

                $condicion=[
                    "condicion_campo"=>"venta_codigo",
                    "condicion_marcador"=>":Codigo",
                    "condicion_valor"=>$venta_codigo
                ];

                mainModel::actualizar_datos("venta",$datos_venta,$condicion);

                /*== Eliminando pago ==*/
                $check_pago=mainModel::ejecutar_consulta_simple("SELECT pago_id FROM pago WHERE pago_fecha='$pago_fecha' AND venta_codigo='$venta_codigo' AND usuario_id='".$_SESSION['id_svi']."' ORDER BY pago_id DESC LIMIT 1");
                $datos_pago=$check_pago->fetch();

                mainModel::eliminar_registro("pago","pago_id",$datos_pago['pago_id']);

                $check_pago->closeCursor();
                $check_pago=mainModel::desconectar($check_pago);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido actualizar algunos datos de la caja para poder agregar el pago.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
                exit();
            }
            $agregar_movimiento->closeCursor();
            $agregar_movimiento=mainModel::desconectar($agregar_movimiento);

            /*== Actualizando efectivo en caja ==*/
            $datos_caja_up=[
                "caja_efectivo"=>[
                    "campo_marcador"=>":Efectivo",
                    "campo_valor"=>$total_caja
                ]
            ];

            $condicion_caja=[
                "condicion_campo"=>"caja_id",
                "condicion_marcador"=>":ID",
                "condicion_valor"=>$_SESSION['caja_svi']
            ];

            /*== Reestableciendo DB debido a errores ==*/
            if(!mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja)){
                
                /*== Eliminando movimiento ==*/
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

                /*== Actualizando venta ==*/
                $datos_venta=[
                    "venta_pagado"=>[
                        "campo_marcador"=>":Pagado",
                        "campo_valor"=>$datos_venta['venta_pagado']
                    ],
                    "venta_estado"=>[
                        "campo_marcador"=>":Estado",
                        "campo_valor"=>$datos_venta['venta_estado']
                    ]
                ];

                $condicion=[
                    "condicion_campo"=>"venta_codigo",
                    "condicion_marcador"=>":Codigo",
                    "condicion_valor"=>$venta_codigo
                ];

                mainModel::actualizar_datos("venta",$datos_venta,$condicion);

                /*== Eliminando pago ==*/
                $check_pago=mainModel::ejecutar_consulta_simple("SELECT pago_id FROM pago WHERE pago_fecha='$pago_fecha' AND venta_codigo='$venta_codigo' AND usuario_id='".$_SESSION['id_svi']."' ORDER BY pago_id DESC LIMIT 1");
                $datos_pago=$check_pago->fetch();

                mainModel::eliminar_registro("pago","pago_id",$datos_pago['pago_id']);

                $check_pago->closeCursor();
                $check_pago=mainModel::desconectar($check_pago);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido actualizar el efectivo de la caja para poder agregar el pago.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
                exit();
            }

            $alerta=[
                "Alerta"=>"recargar",
                "Titulo"=>"¡Pago agregado!",
                "Texto"=>"El pago de la venta ha sido registrado exitosamente",
                "Tipo"=>"success"
            ];
            echo json_encode($alerta);
        } /*-- Fin controlador --*/

        /*---------- Controlador eliminar venta ----------*/
        public function eliminar_venta_controlador(){

            /*== Recuperando codigo de venta ==*/
			$codigo=mainModel::decryption($_POST['venta_codigo_del']);
            $codigo=mainModel::limpiar_cadena($codigo);
            
            /*== Comprobando venta en la BD ==*/
			$check_venta=mainModel::ejecutar_consulta_simple("SELECT venta_id FROM venta WHERE venta_codigo='$codigo'");
			if($check_venta->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"La venta que intenta eliminar no existe en el sistema.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}
			$check_venta->closeCursor();
            $check_venta=mainModel::desconectar($check_venta);
            
            /*== Comprobando detalle de venta ==*/
			$check_venta_detalle=mainModel::ejecutar_consulta_simple("SELECT venta_detalle_id FROM venta_detalle WHERE venta_codigo='$codigo' LIMIT 1");
			if($check_venta_detalle->rowCount()>0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No podemos eliminar la venta ya que existen productos asociados, para eliminar esta venta debe de hacer la devolución de todos los productos asociados e intentar nuevamente.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}
			$check_venta_detalle->closeCursor();
            $check_venta_detalle=mainModel::desconectar($check_venta_detalle);

            /*== Comprobando privilegios ==*/
			if($_SESSION['cargo_svi']!="Administrador"){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No tienes los permisos necesarios para realizar esta operación en el sistema.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Eliminado pagos ==*/
            $eliminar_pago=mainModel::eliminar_registro("pago","venta_codigo",$codigo);
            if($eliminar_pago->rowCount()<=0){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido eliminar los pagos asociados a esta venta, no podemos continuar. Por favor intente nuevamente..",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }
            $eliminar_pago->closeCursor();
			$eliminar_pago=mainModel::desconectar($eliminar_pago);
            
            /*== Eliminado venta ==*/
            $eliminar_venta=mainModel::eliminar_registro("venta","venta_codigo",$codigo);

			if($eliminar_venta->rowCount()==1){
				$alerta=[
					"Alerta"=>"recargar",
					"Titulo"=>"¡Venta eliminada!",
					"Texto"=>"La venta ha sido eliminada del sistema exitosamente.",
					"Tipo"=>"success"
				];
			}else{
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido eliminar la venta del sistema, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
			}

			$eliminar_venta->closeCursor();
			$eliminar_venta=mainModel::desconectar($eliminar_venta);

			echo json_encode($alerta);
        } /*-- Fin controlador --*/
    }