<?php

	if($peticion_ajax){
		require_once "../modelos/mainModel.php";
	}else{
		require_once "./modelos/mainModel.php";
	}

	class devolucionControlador extends mainModel{

        /*---------- Controlador devolucion de venta ----------*/
        public function devolucion_venta_controlador(){

            /*== Recuperando el codigo de la venta ==*/
            $venta_codigo=mainModel::limpiar_cadena($_POST['codigo_venta']);

            /*== Comprobando venta ==*/
			$check_venta=mainModel::ejecutar_consulta_simple("SELECT * FROM venta WHERE venta_codigo='$venta_codigo'");
			if($check_venta->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos encontrado la venta en el sistema para realizar la devolución.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }else{
                $datos_venta=$check_venta->fetch();
            }
            $check_venta->closeCursor();
            $check_venta=mainModel::desconectar($check_venta);

            /*== Recuperando id de producto y cantidad ==*/
            $producto_id=mainModel::limpiar_cadena($_POST['id_producto']);
            $devolucion_cantidad=mainModel::limpiar_cadena($_POST['devolucion_cantidad']);

            /*== Comprobando venta detalle ==*/
			$check_venta_detalle=mainModel::ejecutar_consulta_simple("SELECT * FROM venta_detalle WHERE venta_codigo='$venta_codigo' AND producto_id='$producto_id'");
			if($check_venta_detalle->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos encontrado los detalles de la venta en el sistema para realizar la devolución.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }else{
                $datos_venta_detalle=$check_venta_detalle->fetch();
            }
            $check_venta_detalle->closeCursor();
            $check_venta_detalle=mainModel::desconectar($check_venta_detalle);

            /*== Comprobando campos vacios ==*/
            if($devolucion_cantidad=="" || $devolucion_cantidad<=0){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"Debe de introducir una cantidad a devolver mayor a 0.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Comprobando integridad de los datos ==*/
            if(mainModel::verificar_datos("[0-9]{1,9}",$devolucion_cantidad)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"La cantidad a devolver no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Comprobando que la cantidad sea menor o igual a la de detalle ==*/
            if($devolucion_cantidad>$datos_venta_detalle['venta_detalle_cantidad']){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"La cantidad que desea devolver es mayor a la registrada en la venta.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Comprobando producto ==*/
			$check_producto=mainModel::ejecutar_consulta_simple("SELECT * FROM producto WHERE producto_id='$producto_id'");
			if($check_producto->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos encontrado el producto en el sistema para realizar la devolución.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }else{
                $datos_producto=$check_producto->fetch();
            }
            $check_producto->closeCursor();
            $check_producto=mainModel::desconectar($check_producto);

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
			
			/*== Generando datos de devolucion ==*/
            $devolucion_fecha=date("Y-m-d");
			$devolucion_hora=date("h:i a");
			
			$correlativo=mainModel::ejecutar_consulta_simple("SELECT devolucion_id FROM devolucion");
			$correlativo=($correlativo->rowCount())+1;

			$devolucion_codigo=mainModel::generar_codigo_aleatorio(8,$correlativo);
			
			$devolucion_tipo="Devolución de venta";

			$devolucion_total=$devolucion_cantidad*$datos_venta_detalle['venta_detalle_precio_venta'];
			$devolucion_total=number_format($devolucion_total,MONEDA_DECIMALES,'.','');
			
			/*== Preparando datos para agregar devolucion ==*/
            $datos_devolucion=[
                "devolucion_codigo"=>[
                    "campo_marcador"=>":Codigo",
                    "campo_valor"=>$devolucion_codigo
                ],
                "devolucion_fecha"=>[
                    "campo_marcador"=>":Fecha",
                    "campo_valor"=>$devolucion_fecha
                ],
                "devolucion_hora"=>[
                    "campo_marcador"=>":Hora",
                    "campo_valor"=>$devolucion_hora
                ],
                "devolucion_tipo"=>[
                    "campo_marcador"=>":Tipo",
                    "campo_valor"=>$devolucion_tipo
                ],
                "devolucion_descripcion"=>[
                    "campo_marcador"=>":Descripcion",
                    "campo_valor"=>$datos_venta_detalle['venta_detalle_descripcion']
                ],
                "devolucion_cantidad"=>[
                    "campo_marcador"=>":Cantidad",
                    "campo_valor"=>$devolucion_cantidad
                ],
                "devolucion_precio"=>[
                    "campo_marcador"=>":Precio",
                    "campo_valor"=>$datos_venta_detalle['venta_detalle_precio_venta']
                ],
                "devolucion_total"=>[
                    "campo_marcador"=>":Total",
                    "campo_valor"=>$devolucion_total
                ],
                "compra_venta_codigo"=>[
                    "campo_marcador"=>":Venta",
                    "campo_valor"=>$venta_codigo
                ],
                "producto_id"=>[
                    "campo_marcador"=>":Producto",
                    "campo_valor"=>$producto_id
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
			
			$agregar_devolucion=mainModel::guardar_datos("devolucion",$datos_devolucion);

			if($agregar_devolucion->rowCount()<1){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 001",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }
            $agregar_devolucion->closeCursor();
			$agregar_devolucion=mainModel::desconectar($agregar_devolucion);

			/*== Generando datos de movimiento ==*/
			$correlativo=mainModel::ejecutar_consulta_simple("SELECT movimiento_id FROM movimiento");
			$correlativo=($correlativo->rowCount())+1;

			$codigo_movimiento=mainModel::generar_codigo_aleatorio(8,$correlativo);
			
			$total_caja=$datos_caja['caja_efectivo']-$devolucion_total;
			$total_caja=number_format($total_caja,MONEDA_DECIMALES,'.','');

			/*== Preparando datos para agregar movimiento ==*/
			$datos_movimiento=[
                "movimiento_codigo"=>[
                    "campo_marcador"=>":Codigo",
                    "campo_valor"=>$codigo_movimiento
                ],
                "movimiento_fecha"=>[
                    "campo_marcador"=>":Fecha",
                    "campo_valor"=>$devolucion_fecha
                ],
                "movimiento_hora"=>[
                    "campo_marcador"=>":Hora",
                    "campo_valor"=>$devolucion_hora
                ],
                "movimiento_tipo"=>[
                    "campo_marcador"=>":Tipo",
                    "campo_valor"=>"Retiro de efectivo"
                ],
                "movimiento_motivo"=>[
                    "campo_marcador"=>":Motivo",
                    "campo_valor"=>"Devolución de venta de producto"
                ],
                "movimiento_saldo_anterior"=>[
                    "campo_marcador"=>":Anterior",
                    "campo_valor"=>$datos_caja['caja_efectivo']
                ],
                "movimiento_cantidad"=>[
                    "campo_marcador"=>":Cantidad",
                    "campo_valor"=>$devolucion_total
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

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 002",
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

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 003",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			/*== Actualizando stock de producto ==*/
			$stock_total=$devolucion_cantidad+$datos_producto['producto_stock_total'];
			$stock_vendido=$datos_producto['producto_stock_vendido']-$devolucion_cantidad;

            $datos_producto_up=[
                "producto_stock_total"=>[
                    "campo_marcador"=>":Stock",
                    "campo_valor"=>$stock_total
				],
				"producto_stock_vendido"=>[
                    "campo_marcador"=>":Vendido",
                    "campo_valor"=>$stock_vendido
				]
            ];

            $condicion_producto=[
                "condicion_campo"=>"producto_id",
                "condicion_marcador"=>":ID",
                "condicion_valor"=>$producto_id
			];
			
			/*== Reestableciendo DB debido a errores ==*/
            if(!mainModel::actualizar_datos("producto",$datos_producto_up,$condicion_producto)){

				/*== Actualizando efectivo en caja ==*/
				$datos_caja_up=[
					"caja_efectivo"=>[
						"campo_marcador"=>":Efectivo",
						"campo_valor"=>$datos_caja['caja_efectivo']
					]
                ];
				mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

				/*== Eliminando movimiento ==*/
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 004",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			/*== Actualizando datos de venta ==*/
			$venta_total=$datos_venta['venta_total']-($datos_venta_detalle['venta_detalle_precio_regular']*$devolucion_cantidad);
            $venta_total=number_format($venta_total,MONEDA_DECIMALES,'.','');

            $venta_descuento_total=$venta_total*($datos_venta['venta_descuento_porcentaje']/100);
            $venta_descuento_total=number_format($venta_descuento_total,MONEDA_DECIMALES,'.','');
            
            $venta_total_final=$datos_venta['venta_total_final']-$devolucion_total;
            $venta_total_final=number_format($venta_total_final,MONEDA_DECIMALES,'.','');
            
            $venta_subtotal=$venta_total_final/(($datos_venta['venta_impuesto_porcentaje']/100)+1);
            $venta_subtotal=number_format($venta_subtotal,MONEDA_DECIMALES,'.','');

            $venta_impuestos=$venta_total_final-$venta_subtotal;
            $venta_impuestos=number_format($venta_impuestos,MONEDA_DECIMALES,'.','');

            $venta_costo=$datos_venta['venta_costo']-($datos_venta_detalle['venta_detalle_precio_compra']*$devolucion_cantidad);
            $venta_costo=number_format($venta_costo,MONEDA_DECIMALES,'.','');

            $venta_utilidad=$venta_total_final-$venta_costo;
            $venta_utilidad=number_format($venta_utilidad,MONEDA_DECIMALES,'.','');

            $venta_pagado=$datos_venta['venta_pagado']-$devolucion_total;
            $venta_pagado=number_format($venta_pagado,MONEDA_DECIMALES,'.','');

            $datos_venta_up=[
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
                ]
            ];

            $condicion_venta=[
                "condicion_campo"=>"venta_codigo",
                "condicion_marcador"=>":Codigo",
                "condicion_valor"=>$venta_codigo
			];

            /*== Reestableciendo DB debido a errores ==*/
            if(!mainModel::actualizar_datos("venta",$datos_venta_up,$condicion_venta)){

                /*== Actualizando stock de producto ==*/
                $datos_producto_up=[
                    "producto_stock_total"=>[
                        "campo_marcador"=>":Stock",
                        "campo_valor"=>$datos_producto['producto_stock_total']
                    ],
                    "producto_stock_vendido"=>[
                        "campo_marcador"=>":Vendido",
                        "campo_valor"=>$datos_producto['producto_stock_vendido']
                    ]
                ];
                mainModel::actualizar_datos("producto",$datos_producto_up,$condicion_producto);

                /*== Actualizando efectivo en caja ==*/
				$datos_caja_up=[
					"caja_efectivo"=>[
						"campo_marcador"=>":Efectivo",
						"campo_valor"=>$datos_caja['caja_efectivo']
					]
                ];
				mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

				/*== Eliminando movimiento ==*/
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 005",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Actualizando datos de detalle venta ==*/
            $errores_venta_detalle=0;
            if($devolucion_cantidad==$datos_venta_detalle['venta_detalle_cantidad']){
                $operacion_venta_detalle="Eliminar";

                $eliminar_detalle_venta=mainModel::eliminar_registro("venta_detalle","venta_detalle_id",$datos_venta_detalle['venta_detalle_id']);
                if($eliminar_detalle_venta->rowCount()<=0){
                    $errores_venta_detalle=1;
                }
                $eliminar_detalle_venta->closeCursor();
			    $eliminar_detalle_venta=mainModel::desconectar($eliminar_detalle_venta);
            }else{
                $operacion_venta_detalle="Actualizar";

                $venta_detalle_cantidad=$datos_venta_detalle['venta_detalle_cantidad']-$devolucion_cantidad;

                $venta_detalle_total=$venta_detalle_cantidad*$datos_venta_detalle['venta_detalle_precio_venta'];
                $venta_detalle_total=number_format($venta_detalle_total,MONEDA_DECIMALES,'.','');

                $venta_detalle_subtotal=$venta_detalle_total/(($datos_venta['venta_impuesto_porcentaje']/100)+1);
                $venta_detalle_subtotal=number_format($venta_detalle_subtotal,MONEDA_DECIMALES,'.','');

                $venta_detalle_impuestos=$venta_detalle_total-$venta_detalle_subtotal;
                $venta_detalle_impuestos=number_format($venta_detalle_impuestos,MONEDA_DECIMALES,'.','');

                $venta_detalle_costo=$venta_detalle_cantidad*$datos_venta_detalle['venta_detalle_precio_compra'];
                $venta_detalle_costo=number_format($venta_detalle_costo,MONEDA_DECIMALES,'.','');

                $venta_detalle_utilidad=$venta_detalle_total-$venta_detalle_costo;
                $venta_detalle_utilidad=number_format($venta_detalle_utilidad,MONEDA_DECIMALES,'.','');

                $venta_detalle_descuento_total=$datos_venta_detalle['venta_detalle_descuento_total']/$datos_venta_detalle['venta_detalle_cantidad'];
                $venta_detalle_descuento_total=$venta_detalle_descuento_total*$venta_detalle_cantidad;
                $venta_detalle_descuento_total=number_format($venta_detalle_descuento_total,MONEDA_DECIMALES,'.','');

                $datos_detalle_venta_up=[
                    "venta_detalle_cantidad"=>[
                        "campo_marcador"=>":Cantidad",
                        "campo_valor"=>$venta_detalle_cantidad
                    ],
                    "venta_detalle_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$venta_detalle_subtotal
                    ],
                    "venta_detalle_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$venta_detalle_impuestos
                    ],
                    "venta_detalle_descuento_total"=>[
                        "campo_marcador"=>":DescTotal",
                        "campo_valor"=>$venta_detalle_descuento_total
                    ],
                    "venta_detalle_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$venta_detalle_total
                    ],
                    "venta_detalle_costo"=>[
                        "campo_marcador"=>":Costo",
                        "campo_valor"=>$venta_detalle_costo
                    ],
                    "venta_detalle_utilidad"=>[
                        "campo_marcador"=>":Utilidad",
                        "campo_valor"=>$venta_detalle_utilidad
                    ]
                ];

                $condicion_venta_detalle=[
                    "condicion_campo"=>"venta_detalle_id",
                    "condicion_marcador"=>":Codigo",
                    "condicion_valor"=>$datos_venta_detalle['venta_detalle_id']
                ];
                
                if(!mainModel::actualizar_datos("venta_detalle",$datos_detalle_venta_up,$condicion_venta_detalle)){
                    $errores_venta_detalle=1;
                }
            }

            /*== Reestableciendo DB debido a errores ==*/
            if($errores_venta_detalle==1){

                /*== Actualizando datos de la venta ==*/
                $datos_venta_up=[
                    "venta_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$datos_venta['venta_subtotal']
                    ],
                    "venta_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$datos_venta['venta_impuestos']
                    ],
                    "venta_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$datos_venta['venta_total']
                    ],
                    "venta_descuento_total"=>[
                        "campo_marcador"=>":DescTotal",
                        "campo_valor"=>$datos_venta['venta_descuento_total']
                    ],
                    "venta_total_final"=>[
                        "campo_marcador"=>":TotalFinal",
                        "campo_valor"=>$datos_venta['venta_total_final']
                    ],
                    "venta_pagado"=>[
                        "campo_marcador"=>":Pagado",
                        "campo_valor"=>$datos_venta['venta_pagado']
                    ],
                    "venta_costo"=>[
                        "campo_marcador"=>":Costos",
                        "campo_valor"=>$datos_venta['venta_costo']
                    ],
                    "venta_utilidad"=>[
                        "campo_marcador"=>":Utilidad",
                        "campo_valor"=>$datos_venta['venta_utilidad']
                    ]
                ];
                mainModel::actualizar_datos("venta",$datos_venta_up,$condicion_venta);

                /*== Actualizando stock de producto ==*/
                $datos_producto_up=[
                    "producto_stock_total"=>[
                        "campo_marcador"=>":Stock",
                        "campo_valor"=>$datos_producto['producto_stock_total']
                    ],
                    "producto_stock_vendido"=>[
                        "campo_marcador"=>":Vendido",
                        "campo_valor"=>$datos_producto['producto_stock_vendido']
                    ]
                ];
                mainModel::actualizar_datos("producto",$datos_producto_up,$condicion_producto);

                /*== Actualizando efectivo en caja ==*/
				$datos_caja_up=[
					"caja_efectivo"=>[
						"campo_marcador"=>":Efectivo",
						"campo_valor"=>$datos_caja['caja_efectivo']
					]
                ];
				mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

				/*== Eliminando movimiento ==*/
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 006",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Agregando kardex y detalle kardex ==*/
            $year=date("Y");
            $mes=date("m");
            $fecha=date("Y-m-d");
            $errores_kardex=0;

            /*== Obteniendo datos de kardex ==*/
            $check_kardex=mainModel::ejecutar_consulta_simple("SELECT * FROM kardex WHERE kardex_mes='$mes' AND kardex_year='$year' AND producto_id='$producto_id'");
            if($check_kardex->rowCount()<1){
                $operacion_kardex="Guardar";

                $correlativo=mainModel::ejecutar_consulta_simple("SELECT kardex_id FROM kardex");
                $correlativo=($correlativo->rowCount())+1;

                $kardex_codigo=mainModel::generar_codigo_aleatorio(10,$correlativo);
                $kardex_mes=$mes;
                $kardex_year=$year;

                $kardex_entrada_unidad=$devolucion_cantidad;
                $kardex_entrada_costo_total=$devolucion_cantidad*$datos_venta_detalle['venta_detalle_precio_venta'];
                $kardex_entrada_costo_total=number_format($kardex_entrada_costo_total,MONEDA_DECIMALES,'.','');

                $kardex_salida_unidad=0;
                $kardex_salida_costo_total=0.00;

                $check_kardex_anterior=mainModel::ejecutar_consulta_simple("SELECT * FROM kardex WHERE producto_id='$producto_id' ORDER BY kardex_id DESC LIMIT 1");
                if($check_kardex_anterior->rowCount()==1){
                    $datos_ka=$check_kardex_anterior->fetch();
                    $kardex_existencia_costo_total_old=$datos_ka['kardex_existencia_costo_total'];
                }else{
                    $kardex_existencia_costo_total_old=$datos_producto['producto_stock_total']*$datos_producto['producto_precio_compra'];
                }
                $check_kardex_anterior->closeCursor();
                $check_kardex_anterior=mainModel::desconectar($check_kardex_anterior);

                $kardex_existencia_inicial=$datos_producto['producto_stock_total'];
                $kardex_existencia_unidad=$datos_producto['producto_stock_total']+$devolucion_cantidad;
                $kardex_existencia_costo_total=$kardex_existencia_costo_total_old+($devolucion_cantidad*$datos_venta_detalle['venta_detalle_precio_compra']); 
                $kardex_existencia_costo_total=number_format($kardex_existencia_costo_total,MONEDA_DECIMALES,'.','');

                $array_kardex=[
                    "kardex_codigo"=>$kardex_codigo,
                    "total_unidades"=>$devolucion_cantidad,
                    "kardex_entrada_unidad"=>$devolucion_cantidad,
                    "kardex_entrada_costo_total"=>$kardex_entrada_costo_total,
                    "kardex_existencia_unidad"=>$kardex_existencia_unidad,
                    "kardex_existencia_costo_total"=>$kardex_existencia_costo_total
                ];

            }else{
                $operacion_kardex="Actualizar";

                $datos_kardex=$check_kardex->fetch();
				
                $kardex_codigo=$datos_kardex['kardex_codigo'];
                $kardex_mes=$datos_kardex['kardex_mes'];
                $kardex_year=$datos_kardex['kardex_year'];

                $kardex_entrada_unidad=$datos_kardex['kardex_entrada_unidad']+$devolucion_cantidad;
                $kardex_entrada_costo_total=$datos_kardex['kardex_entrada_costo_total']+($devolucion_cantidad*$datos_venta_detalle['venta_detalle_precio_venta']);
                $kardex_entrada_costo_total=number_format($kardex_entrada_costo_total,MONEDA_DECIMALES,'.','');
                

                $kardex_salida_unidad=$datos_kardex['kardex_salida_unidad'];
                $kardex_salida_costo_total=$datos_kardex['kardex_salida_costo_total'];

                $kardex_existencia_inicial=$datos_kardex['kardex_existencia_inicial'];
                $kardex_existencia_unidad=$datos_kardex['kardex_existencia_unidad']+$devolucion_cantidad;
                $kardex_existencia_costo_total=$datos_kardex['kardex_existencia_costo_total']+($devolucion_cantidad*$datos_venta_detalle['venta_detalle_precio_compra']);
                $kardex_existencia_costo_total=number_format($kardex_existencia_costo_total,MONEDA_DECIMALES,'.','');

                $array_kardex=[
                    "kardex_codigo"=>$datos_kardex['kardex_codigo'],
                    "total_unidades"=>$devolucion_cantidad,
                    "kardex_entrada_unidad"=>$datos_kardex['kardex_entrada_unidad'],
                    "kardex_entrada_costo_total"=>$datos_kardex['kardex_entrada_costo_total'],
                    "kardex_existencia_unidad"=>$datos_kardex['kardex_existencia_unidad'],
                    "kardex_existencia_costo_total"=>$datos_kardex['kardex_existencia_costo_total']
                ];
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
                    "campo_valor"=>$producto_id
                ]
            ];

            /*== Detectando la operacion a realizar ==*/
            if($operacion_kardex=="Guardar"){
                $agregar_kardex=mainModel::guardar_datos("kardex",$datos_kardex_reg);
                if($agregar_kardex->rowCount()!=1){
                    $errores_kardex=1;
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
                }
            }

            /*== Reestableciendo DB debido a errores ==*/
            if($errores_kardex==1){

                /*== Actualizando datos de detalle de venta ==*/
                $datos_detalle_venta_up=[
                    "venta_detalle_cantidad"=>[
                        "campo_marcador"=>":Cantidad",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_cantidad']
                    ],
                    "venta_detalle_precio_compra"=>[
                        "campo_marcador"=>":PrecioCompra",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_precio_compra']
                    ],
                    "venta_detalle_precio_regular"=>[
                        "campo_marcador"=>":PrecioRegular",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_precio_regular']
                    ],
                    "venta_detalle_precio_venta"=>[
                        "campo_marcador"=>":PrecioVenta",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_precio_venta']
                    ],
                    "venta_detalle_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_subtotal']
                    ],
                    "venta_detalle_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_impuestos']
                    ],
                    "venta_detalle_descuento_porcentaje"=>[
                        "campo_marcador"=>":DescPorcentaje",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_descuento_porcentaje']
                    ],
                    "venta_detalle_descuento_total"=>[
                        "campo_marcador"=>":DescTotal",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_descuento_total']
                    ],
                    "venta_detalle_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_total']
                    ],
                    "venta_detalle_costo"=>[
                        "campo_marcador"=>":Costo",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_costo']
                    ],
                    "venta_detalle_utilidad"=>[
                        "campo_marcador"=>":Utilidad",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_utilidad']
                    ],
                    "venta_detalle_descripcion"=>[
                        "campo_marcador"=>":Descripcion",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_descripcion']
                    ],
                    "venta_detalle_garantia"=>[
                        "campo_marcador"=>":Garantia",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_garantia']
                    ],
                    "venta_codigo"=>[
                        "campo_marcador"=>":VentaCodigo",
                        "campo_valor"=>$datos_venta_detalle['venta_codigo']
                    ],
                    "producto_id"=>[
                        "campo_marcador"=>":Producto",
                        "campo_valor"=>$datos_venta_detalle['producto_id']
                    ]
                ];
                if($operacion_venta_detalle=="Actualizar"){
                    $condicion_venta_detalle=[
                        "condicion_campo"=>"venta_detalle_id",
                        "condicion_marcador"=>":Codigo",
                        "condicion_valor"=>$datos_venta_detalle['venta_detalle_id']
                    ];
                    mainModel::actualizar_datos("venta_detalle",$datos_detalle_venta_up,$condicion_venta_detalle);
                }else{
                    mainModel::guardar_datos("venta_detalle",$datos_detalle_venta_up);
                }
                

                /*== Actualizando datos de la venta ==*/
                $datos_venta_up=[
                    "venta_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$datos_venta['venta_subtotal']
                    ],
                    "venta_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$datos_venta['venta_impuestos']
                    ],
                    "venta_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$datos_venta['venta_total']
                    ],
                    "venta_descuento_total"=>[
                        "campo_marcador"=>":DescTotal",
                        "campo_valor"=>$datos_venta['venta_descuento_total']
                    ],
                    "venta_total_final"=>[
                        "campo_marcador"=>":TotalFinal",
                        "campo_valor"=>$datos_venta['venta_total_final']
                    ],
                    "venta_pagado"=>[
                        "campo_marcador"=>":Pagado",
                        "campo_valor"=>$datos_venta['venta_pagado']
                    ],
                    "venta_costo"=>[
                        "campo_marcador"=>":Costos",
                        "campo_valor"=>$datos_venta['venta_costo']
                    ],
                    "venta_utilidad"=>[
                        "campo_marcador"=>":Utilidad",
                        "campo_valor"=>$datos_venta['venta_utilidad']
                    ]
                ];
                mainModel::actualizar_datos("venta",$datos_venta_up,$condicion_venta);

                /*== Actualizando stock de producto ==*/
                $datos_producto_up=[
                    "producto_stock_total"=>[
                        "campo_marcador"=>":Stock",
                        "campo_valor"=>$datos_producto['producto_stock_total']
                    ],
                    "producto_stock_vendido"=>[
                        "campo_marcador"=>":Vendido",
                        "campo_valor"=>$datos_producto['producto_stock_vendido']
                    ]
                ];
                mainModel::actualizar_datos("producto",$datos_producto_up,$condicion_producto);

                /*== Actualizando efectivo en caja ==*/
				$datos_caja_up=[
					"caja_efectivo"=>[
						"campo_marcador"=>":Efectivo",
						"campo_valor"=>$datos_caja['caja_efectivo']
					]
                ];
				mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

				/*== Eliminando movimiento ==*/
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 007",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Preparando datos para enviarlos al modelo ==*/
            $datos_kardex_detalle=[
                "kardex_detalle_fecha"=>[
                    "campo_marcador"=>":Fecha",
                    "campo_valor"=>$fecha
                ],
                "kardex_detalle_tipo"=>[
                    "campo_marcador"=>":Tipo",
                    "campo_valor"=>"Entrada"
                ],
                "kardex_detalle_descripcion"=>[
                    "campo_marcador"=>":Descripcion",
                    "campo_valor"=>"Devolución de producto de cliente"
                ],
                "kardex_detalle_unidad"=>[
                    "campo_marcador"=>":Unidad",
                    "campo_valor"=>$devolucion_cantidad
                ],
                "kardex_detalle_costo_unidad"=>[
                    "campo_marcador"=>":Costo",
                    "campo_valor"=>$datos_venta_detalle['venta_detalle_precio_venta']
                ],
                "kardex_detalle_costo_total"=>[
                    "campo_marcador"=>":Total",
                    "campo_valor"=>$devolucion_total
                ],
                "kardex_codigo"=>[
                    "campo_marcador"=>":Codigo",
                    "campo_valor"=>$kardex_codigo
                ],
                "producto_id"=>[
                    "campo_marcador"=>":Producto",
                    "campo_valor"=>$producto_id
                ],
                "usuario_id"=>[
                    "campo_marcador"=>":Usuario",
                    "campo_valor"=>$_SESSION['id_svi']
                ]
            ];

            $agregar_kardex_detalle=mainModel::guardar_datos("kardex_detalle",$datos_kardex_detalle);

            /*== Reestableciendo DB debido a errores ==*/
            if($agregar_kardex_detalle->rowCount()!=1){

                /*== Actualizando kardex ==*/
                if($operacion_kardex=="Actualizar"){

                    $datos_kardex_rs=[
                        "kardex_entrada_unidad"=>[
                            "campo_marcador"=>":KardexEnU",
                            "campo_valor"=>$array_kardex['kardex_entrada_unidad']
                        ],
                        "kardex_entrada_costo_total"=>[
                            "campo_marcador"=>":KardexEnCT",
                            "campo_valor"=>$array_kardex['kardex_entrada_costo_total']
                        ],
                        "kardex_existencia_unidad"=>[
                            "campo_marcador"=>":KardexExU",
                            "campo_valor"=>$array_kardex['kardex_existencia_unidad']
                        ],
                        "kardex_existencia_costo_total"=>[
                            "campo_marcador"=>":KardexExCT",
                            "campo_valor"=>$array_kardex['kardex_existencia_costo_total']
                        ]
                    ];

                    $condicion_kardex=[
                        "condicion_campo"=>"kardex_codigo",
                        "condicion_marcador"=>":Codigo",
                        "condicion_valor"=>$array_kardex['kardex_codigo']
                    ];

                    mainModel::actualizar_datos("kardex",$datos_kardex_rs,$condicion_kardex);
                }else{
                    $check_kardex_del=mainModel::ejecutar_consulta_simple("SELECT kardex_id FROM kardex WHERE kardex_codigo='".$array_kardex['kardex_codigo']."'");

                    if($check_kardex_del->rowCount()==1){
                        mainModel::eliminar_registro("kardex","kardex_codigo",$array_kardex['kardex_codigo']);
                    }
                    $check_kardex_del->closeCursor();
                    $check_kardex_del=mainModel::desconectar($check_kardex_del);
                }

                /*== Actualizando datos de detalle de venta ==*/
                $datos_detalle_venta_up=[
                    "venta_detalle_cantidad"=>[
                        "campo_marcador"=>":Cantidad",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_cantidad']
                    ],
                    "venta_detalle_precio_compra"=>[
                        "campo_marcador"=>":PrecioCompra",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_precio_compra']
                    ],
                    "venta_detalle_precio_regular"=>[
                        "campo_marcador"=>":PrecioRegular",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_precio_regular']
                    ],
                    "venta_detalle_precio_venta"=>[
                        "campo_marcador"=>":PrecioVenta",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_precio_venta']
                    ],
                    "venta_detalle_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_subtotal']
                    ],
                    "venta_detalle_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_impuestos']
                    ],
                    "venta_detalle_descuento_porcentaje"=>[
                        "campo_marcador"=>":DescPorcentaje",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_descuento_porcentaje']
                    ],
                    "venta_detalle_descuento_total"=>[
                        "campo_marcador"=>":DescTotal",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_descuento_total']
                    ],
                    "venta_detalle_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_total']
                    ],
                    "venta_detalle_costo"=>[
                        "campo_marcador"=>":Costo",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_costo']
                    ],
                    "venta_detalle_utilidad"=>[
                        "campo_marcador"=>":Utilidad",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_utilidad']
                    ],
                    "venta_detalle_descripcion"=>[
                        "campo_marcador"=>":Descripcion",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_descripcion']
                    ],
                    "venta_detalle_garantia"=>[
                        "campo_marcador"=>":Garantia",
                        "campo_valor"=>$datos_venta_detalle['venta_detalle_garantia']
                    ],
                    "venta_codigo"=>[
                        "campo_marcador"=>":VentaCodigo",
                        "campo_valor"=>$datos_venta_detalle['venta_codigo']
                    ],
                    "producto_id"=>[
                        "campo_marcador"=>":Producto",
                        "campo_valor"=>$datos_venta_detalle['producto_id']
                    ]
                ];
                if($operacion_venta_detalle=="Actualizar"){
                    $condicion_venta_detalle=[
                        "condicion_campo"=>"venta_detalle_id",
                        "condicion_marcador"=>":Codigo",
                        "condicion_valor"=>$datos_venta_detalle['venta_detalle_id']
                    ];
                    mainModel::actualizar_datos("venta_detalle",$datos_detalle_venta_up,$condicion_venta_detalle);
                }else{
                    mainModel::guardar_datos("venta_detalle",$datos_detalle_venta_up);
                }
                

                /*== Actualizando datos de la venta ==*/
                $datos_venta_up=[
                    "venta_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$datos_venta['venta_subtotal']
                    ],
                    "venta_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$datos_venta['venta_impuestos']
                    ],
                    "venta_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$datos_venta['venta_total']
                    ],
                    "venta_descuento_total"=>[
                        "campo_marcador"=>":DescTotal",
                        "campo_valor"=>$datos_venta['venta_descuento_total']
                    ],
                    "venta_total_final"=>[
                        "campo_marcador"=>":TotalFinal",
                        "campo_valor"=>$datos_venta['venta_total_final']
                    ],
                    "venta_pagado"=>[
                        "campo_marcador"=>":Pagado",
                        "campo_valor"=>$datos_venta['venta_pagado']
                    ],
                    "venta_costo"=>[
                        "campo_marcador"=>":Costos",
                        "campo_valor"=>$datos_venta['venta_costo']
                    ],
                    "venta_utilidad"=>[
                        "campo_marcador"=>":Utilidad",
                        "campo_valor"=>$datos_venta['venta_utilidad']
                    ]
                ];
                mainModel::actualizar_datos("venta",$datos_venta_up,$condicion_venta);

                /*== Actualizando stock de producto ==*/
                $datos_producto_up=[
                    "producto_stock_total"=>[
                        "campo_marcador"=>":Stock",
                        "campo_valor"=>$datos_producto['producto_stock_total']
                    ],
                    "producto_stock_vendido"=>[
                        "campo_marcador"=>":Vendido",
                        "campo_valor"=>$datos_producto['producto_stock_vendido']
                    ]
                ];
                mainModel::actualizar_datos("producto",$datos_producto_up,$condicion_producto);

                /*== Actualizando efectivo en caja ==*/
				$datos_caja_up=[
					"caja_efectivo"=>[
						"campo_marcador"=>":Efectivo",
						"campo_valor"=>$datos_caja['caja_efectivo']
					]
                ];
				mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

				/*== Eliminando movimiento ==*/
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 008",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }
            $agregar_kardex_detalle->closeCursor();
            $agregar_kardex_detalle=mainModel::desconectar($agregar_kardex_detalle);


            $alerta=[
                "Alerta"=>"recargar",
                "Titulo"=>"¡Devolución realizada!",
                "Texto"=>"La devolución del producto se ha realizado con éxito",
                "Tipo"=>"success"
            ];
            echo json_encode($alerta);
        } /*-- Fin controlador --*/


        /*---------- Controlador devolucion de compra ----------*/
        public function devolucion_compra_controlador(){

            /*== Recuperando el codigo de la compra ==*/
            $compra_codigo=mainModel::limpiar_cadena($_POST['codigo_compra']);

            /*== Comprobando compra ==*/
			$check_compra=mainModel::ejecutar_consulta_simple("SELECT * FROM compra WHERE compra_codigo='$compra_codigo'");
			if($check_compra->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos encontrado la compra en el sistema para realizar la devolución.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }else{
                $datos_compra=$check_compra->fetch();
            }
            $check_compra->closeCursor();
            $check_compra=mainModel::desconectar($check_compra);

            /*== Recuperando id de producto y cantidad ==*/
            $producto_id=mainModel::limpiar_cadena($_POST['id_producto']);
            $devolucion_cantidad=mainModel::limpiar_cadena($_POST['devolucion_cantidad']);

            /*== Comprobando compra detalle ==*/
			$check_compra_detalle=mainModel::ejecutar_consulta_simple("SELECT * FROM compra_detalle WHERE compra_codigo='$compra_codigo' AND producto_id='$producto_id'");
			if($check_compra_detalle->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos encontrado los detalles de la compra en el sistema para realizar la devolución.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }else{
                $datos_compra_detalle=$check_compra_detalle->fetch();
            }
            $check_compra_detalle->closeCursor();
            $check_compra_detalle=mainModel::desconectar($check_compra_detalle);

            /*== Comprobando campos vacios ==*/
            if($devolucion_cantidad=="" || $devolucion_cantidad<=0){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"Debe de introducir una cantidad a devolver mayor a 0.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Comprobando integridad de los datos ==*/
            if(mainModel::verificar_datos("[0-9]{1,9}",$devolucion_cantidad)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"La cantidad a devolver no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Comprobando que la cantidad sea menor o igual a la de compra detalle ==*/
            if($devolucion_cantidad>$datos_compra_detalle['compra_detalle_cantidad']){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"La cantidad que desea devolver es mayor a la registrada en la compra.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Comprobando producto ==*/
			$check_producto=mainModel::ejecutar_consulta_simple("SELECT * FROM producto WHERE producto_id='$producto_id'");
			if($check_producto->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos encontrado el producto en el sistema para realizar la devolución.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }else{
                $datos_producto=$check_producto->fetch();
            }
            $check_producto->closeCursor();
            $check_producto=mainModel::desconectar($check_producto);

            /*== Comprobando que la cantidad sea menor o igual a stock de producto ==*/
            if($devolucion_cantidad>$datos_producto['producto_stock_total']){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"La cantidad que desea devolver es mayor a la cantidad de productos disponibles en inventario. Actualmente hay disponibles: ".$datos_producto['producto_stock_total'],
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
            
            /*== Generando datos de devolucion ==*/
            $devolucion_fecha=date("Y-m-d");
			$devolucion_hora=date("h:i a");
			
			$correlativo=mainModel::ejecutar_consulta_simple("SELECT devolucion_id FROM devolucion");
			$correlativo=($correlativo->rowCount())+1;

			$devolucion_codigo=mainModel::generar_codigo_aleatorio(8,$correlativo);
			
			$devolucion_tipo="Devolución de compra";

			$devolucion_total=$devolucion_cantidad*$datos_compra_detalle['compra_detalle_precio'];
            $devolucion_total=number_format($devolucion_total,MONEDA_DECIMALES,'.','');
            
            /*== Preparando datos para agregar devolucion ==*/
            $datos_devolucion=[
                "devolucion_codigo"=>[
                    "campo_marcador"=>":Codigo",
                    "campo_valor"=>$devolucion_codigo
                ],
                "devolucion_fecha"=>[
                    "campo_marcador"=>":Fecha",
                    "campo_valor"=>$devolucion_fecha
                ],
                "devolucion_hora"=>[
                    "campo_marcador"=>":Hora",
                    "campo_valor"=>$devolucion_hora
                ],
                "devolucion_tipo"=>[
                    "campo_marcador"=>":Tipo",
                    "campo_valor"=>$devolucion_tipo
                ],
                "devolucion_descripcion"=>[
                    "campo_marcador"=>":Descripcion",
                    "campo_valor"=>$datos_compra_detalle['compra_detalle_descripcion']
                ],
                "devolucion_cantidad"=>[
                    "campo_marcador"=>":Cantidad",
                    "campo_valor"=>$devolucion_cantidad
                ],
                "devolucion_precio"=>[
                    "campo_marcador"=>":Precio",
                    "campo_valor"=>$datos_compra_detalle['compra_detalle_precio']
                ],
                "devolucion_total"=>[
                    "campo_marcador"=>":Total",
                    "campo_valor"=>$devolucion_total
                ],
                "compra_venta_codigo"=>[
                    "campo_marcador"=>":Compra",
                    "campo_valor"=>$compra_codigo
                ],
                "producto_id"=>[
                    "campo_marcador"=>":Producto",
                    "campo_valor"=>$producto_id
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
			
            $agregar_devolucion=mainModel::guardar_datos("devolucion",$datos_devolucion);
            
            if($agregar_devolucion->rowCount()<1){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 001",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }
            $agregar_devolucion->closeCursor();
            $agregar_devolucion=mainModel::desconectar($agregar_devolucion);
            
            /*== Generando datos de movimiento ==*/
			$correlativo=mainModel::ejecutar_consulta_simple("SELECT movimiento_id FROM movimiento");
			$correlativo=($correlativo->rowCount())+1;

			$codigo_movimiento=mainModel::generar_codigo_aleatorio(8,$correlativo);
			
			$total_caja=$datos_caja['caja_efectivo']+$devolucion_total;
            $total_caja=number_format($total_caja,MONEDA_DECIMALES,'.','');
            
            /*== Preparando datos para agregar movimiento ==*/
			$datos_movimiento=[
                "movimiento_codigo"=>[
                    "campo_marcador"=>":Codigo",
                    "campo_valor"=>$codigo_movimiento
                ],
                "movimiento_fecha"=>[
                    "campo_marcador"=>":Fecha",
                    "campo_valor"=>$devolucion_fecha
                ],
                "movimiento_hora"=>[
                    "campo_marcador"=>":Hora",
                    "campo_valor"=>$devolucion_hora
                ],
                "movimiento_tipo"=>[
                    "campo_marcador"=>":Tipo",
                    "campo_valor"=>"Entrada de efectivo"
                ],
                "movimiento_motivo"=>[
                    "campo_marcador"=>":Motivo",
                    "campo_valor"=>"Devolución de compra de producto"
                ],
                "movimiento_saldo_anterior"=>[
                    "campo_marcador"=>":Anterior",
                    "campo_valor"=>$datos_caja['caja_efectivo']
                ],
                "movimiento_cantidad"=>[
                    "campo_marcador"=>":Cantidad",
                    "campo_valor"=>$devolucion_total
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

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 002",
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

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 003",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }
            
            /*== Actualizando stock de producto ==*/
			$stock_total=$datos_producto['producto_stock_total']-$devolucion_cantidad;

            $datos_producto_up=[
                "producto_stock_total"=>[
                    "campo_marcador"=>":Stock",
                    "campo_valor"=>$stock_total
				]
            ];

            $condicion_producto=[
                "condicion_campo"=>"producto_id",
                "condicion_marcador"=>":ID",
                "condicion_valor"=>$producto_id
            ];
            
            /*== Reestableciendo DB debido a errores ==*/
            if(!mainModel::actualizar_datos("producto",$datos_producto_up,$condicion_producto)){

				/*== Actualizando efectivo en caja ==*/
				$datos_caja_up=[
					"caja_efectivo"=>[
						"campo_marcador"=>":Efectivo",
						"campo_valor"=>$datos_caja['caja_efectivo']
					]
                ];
				mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

				/*== Eliminando movimiento ==*/
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 004",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }
            
            /*== Actualizando datos de compra ==*/
            $compra_total=$datos_compra['compra_total']-($devolucion_cantidad*$datos_compra_detalle['compra_detalle_precio']);
            $compra_total=number_format($compra_total,MONEDA_DECIMALES,'.','');

            $compra_subtotal=$compra_total/(($datos_compra['compra_impuesto_porcentaje']/100)+1);
            $compra_subtotal=number_format($compra_subtotal,MONEDA_DECIMALES,'.','');

            $compra_impuestos=$compra_total-$compra_subtotal;
            $compra_impuestos=number_format($compra_impuestos,MONEDA_DECIMALES,'.','');

            $datos_compra_up=[
                "compra_subtotal"=>[
                    "campo_marcador"=>":Subtotal",
                    "campo_valor"=>$compra_subtotal
                ],
                "compra_impuestos"=>[
					"campo_marcador"=>":Impuestos",
					"campo_valor"=>$compra_impuestos
                ],
                "compra_total"=>[
					"campo_marcador"=>":Total",
					"campo_valor"=>$compra_total
                ]
            ];

            $condicion_compra=[
                "condicion_campo"=>"compra_codigo",
                "condicion_marcador"=>":Codigo",
                "condicion_valor"=>$compra_codigo
            ];
            
            /*== Reestableciendo DB debido a errores ==*/
            if(!mainModel::actualizar_datos("compra",$datos_compra_up,$condicion_compra)){

                /*== Actualizando stock de producto ==*/
                $datos_producto_up=[
                    "producto_stock_total"=>[
                        "campo_marcador"=>":Stock",
                        "campo_valor"=>$datos_producto['producto_stock_total']
                    ]
                ];
                mainModel::actualizar_datos("producto",$datos_producto_up,$condicion_producto);

                /*== Actualizando efectivo en caja ==*/
				$datos_caja_up=[
					"caja_efectivo"=>[
						"campo_marcador"=>":Efectivo",
						"campo_valor"=>$datos_caja['caja_efectivo']
					]
                ];
				mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

				/*== Eliminando movimiento ==*/
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 005",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Actualizando datos de detalle compra ==*/
            $errores_compra_detalle=0;
            if($devolucion_cantidad==$datos_compra_detalle['compra_detalle_cantidad']){
                $operacion_compra_detalle="Eliminar";

                $eliminar_detalle_compra=mainModel::eliminar_registro("compra_detalle","compra_detalle_id",$datos_compra_detalle['compra_detalle_id']);
                if($eliminar_detalle_compra->rowCount()<=0){
                    $errores_compra_detalle=1;
                }
                $eliminar_detalle_compra->closeCursor();
			    $eliminar_detalle_compra=mainModel::desconectar($eliminar_detalle_compra);
            }else{
                $operacion_compra_detalle="Actualizar";

                $compra_detalle_cantidad=$datos_compra_detalle['compra_detalle_cantidad']-$devolucion_cantidad;

                $compra_detalle_total=$compra_detalle_cantidad*$datos_compra_detalle['compra_detalle_precio'];
                $compra_detalle_total=number_format($compra_detalle_total,MONEDA_DECIMALES,'.','');

                $compra_detalle_subtotal=$compra_detalle_total/(($datos_compra['compra_impuesto_porcentaje']/100)+1);
                $compra_detalle_subtotal=number_format($compra_detalle_subtotal,MONEDA_DECIMALES,'.','');

                $compra_detalle_impuestos=$compra_detalle_total-$compra_detalle_subtotal;
                $compra_detalle_impuestos=number_format($compra_detalle_impuestos,MONEDA_DECIMALES,'.','');

                $datos_detalle_compra_up=[
                    "compra_detalle_cantidad"=>[
                        "campo_marcador"=>":Cantidad",
                        "campo_valor"=>$compra_detalle_cantidad
                    ],
                    "compra_detalle_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$compra_detalle_subtotal
                    ],
                    "compra_detalle_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$compra_detalle_impuestos
                    ],
                    "compra_detalle_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$compra_detalle_total
                    ]
                ];

                $condicion_compra_detalle=[
                    "condicion_campo"=>"compra_detalle_id",
                    "condicion_marcador"=>":Codigo",
                    "condicion_valor"=>$datos_compra_detalle['compra_detalle_id']
                ];

                if(!mainModel::actualizar_datos("compra_detalle",$datos_detalle_compra_up,$condicion_compra_detalle)){
                    $errores_compra_detalle=1;
                }
            }

            /*== Reestableciendo DB debido a errores ==*/
            if($errores_compra_detalle==1){
                /*== Actualizando datos de la compra ==*/
                $datos_compra_up=[
                    "compra_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$datos_compra['compra_subtotal']
                    ],
                    "compra_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$datos_compra['compra_impuestos']
                    ],
                    "compra_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$datos_compra['compra_total']
                    ]
                ];
                mainModel::actualizar_datos("compra",$datos_compra_up,$condicion_compra);

                /*== Actualizando stock de producto ==*/
                $datos_producto_up=[
                    "producto_stock_total"=>[
                        "campo_marcador"=>":Stock",
                        "campo_valor"=>$datos_producto['producto_stock_total']
                    ]
                ];
                mainModel::actualizar_datos("producto",$datos_producto_up,$condicion_producto);

                /*== Actualizando efectivo en caja ==*/
				$datos_caja_up=[
					"caja_efectivo"=>[
						"campo_marcador"=>":Efectivo",
						"campo_valor"=>$datos_caja['caja_efectivo']
					]
                ];
				mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

				/*== Eliminando movimiento ==*/
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 006",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Agregando kardex y detalle kardex ==*/
            $year=date("Y");
            $mes=date("m");
            $fecha=date("Y-m-d");
            $errores_kardex=0;

            /*== Obteniendo datos de kardex ==*/
            $check_kardex=mainModel::ejecutar_consulta_simple("SELECT * FROM kardex WHERE kardex_mes='$mes' AND kardex_year='$year' AND producto_id='$producto_id'");
            if($check_kardex->rowCount()<1){
                $operacion_kardex="Guardar";

                $correlativo=mainModel::ejecutar_consulta_simple("SELECT kardex_id FROM kardex");
                $correlativo=($correlativo->rowCount())+1;

                $kardex_codigo=mainModel::generar_codigo_aleatorio(10,$correlativo);
                $kardex_mes=$mes;
                $kardex_year=$year;

                $kardex_entrada_unidad=0;
                $kardex_entrada_costo_total=0.00;

                $kardex_salida_unidad=$devolucion_cantidad;
                $kardex_salida_costo_total=$devolucion_cantidad*$datos_compra_detalle['compra_detalle_precio'];
                $kardex_salida_costo_total=number_format($kardex_salida_costo_total,MONEDA_DECIMALES,'.','');

                $check_kardex_anterior=mainModel::ejecutar_consulta_simple("SELECT * FROM kardex WHERE producto_id='$producto_id' ORDER BY kardex_id DESC LIMIT 1");
                if($check_kardex_anterior->rowCount()==1){
                    $datos_ka=$check_kardex_anterior->fetch();
                    $kardex_existencia_costo_total_old=$datos_ka['kardex_existencia_costo_total'];
                }else{
                    $kardex_existencia_costo_total_old=$datos_producto['producto_stock_total']*$datos_producto['producto_precio_compra'];
                }
                $check_kardex_anterior->closeCursor();
                $check_kardex_anterior=mainModel::desconectar($check_kardex_anterior);

                $kardex_existencia_inicial=$datos_producto['producto_stock_total'];
                $kardex_existencia_unidad=$datos_producto['producto_stock_total']-$devolucion_cantidad;
                $kardex_existencia_costo_total=$kardex_existencia_costo_total_old-($devolucion_cantidad*$datos_compra_detalle['compra_detalle_precio']); 
                $kardex_existencia_costo_total=number_format($kardex_existencia_costo_total,MONEDA_DECIMALES,'.','');

                $array_kardex=[
                    "kardex_codigo"=>$kardex_codigo,
                    "total_unidades"=>$devolucion_cantidad,
                    "kardex_salida_unidad"=>$kardex_salida_unidad,
                    "kardex_salida_costo_total"=>$kardex_salida_costo_total,
                    "kardex_existencia_unidad"=>$kardex_existencia_unidad,
                    "kardex_existencia_costo_total"=>$kardex_existencia_costo_total
                ];

            }else{
                $operacion_kardex="Actualizar";

                $datos_kardex=$check_kardex->fetch();
				
                $kardex_codigo=$datos_kardex['kardex_codigo'];
                $kardex_mes=$datos_kardex['kardex_mes'];
                $kardex_year=$datos_kardex['kardex_year'];

                $kardex_entrada_unidad=$datos_kardex['kardex_entrada_unidad'];
                $kardex_entrada_costo_total=$datos_kardex['kardex_entrada_costo_total'];
                

                $kardex_salida_unidad=$datos_kardex['kardex_salida_unidad']+$devolucion_cantidad;
                $kardex_salida_costo_total=$datos_kardex['kardex_salida_costo_total']+($devolucion_cantidad*$datos_compra_detalle['compra_detalle_precio']);
                $kardex_salida_costo_total=number_format($kardex_salida_costo_total,MONEDA_DECIMALES,'.','');

                $kardex_existencia_inicial=$datos_kardex['kardex_existencia_inicial'];
                $kardex_existencia_unidad=$datos_kardex['kardex_existencia_unidad']-$devolucion_cantidad;
                $kardex_existencia_costo_total=$datos_kardex['kardex_existencia_costo_total']-($devolucion_cantidad*$datos_compra_detalle['compra_detalle_precio']);
                $kardex_existencia_costo_total=number_format($kardex_existencia_costo_total,MONEDA_DECIMALES,'.','');

                $array_kardex=[
                    "kardex_codigo"=>$kardex_codigo,
                    "total_unidades"=>$devolucion_cantidad,
                    "kardex_salida_unidad"=>$datos_kardex['kardex_salida_unidad'],
                    "kardex_salida_costo_total"=>$datos_kardex['kardex_salida_costo_total'],
                    "kardex_existencia_unidad"=>$datos_kardex['kardex_existencia_unidad'],
                    "kardex_existencia_costo_total"=>$datos_kardex['kardex_existencia_costo_total']
                ];
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
                    "campo_valor"=>$producto_id
                ]
            ];

            /*== Detectando la operacion a realizar ==*/
            if($operacion_kardex=="Guardar"){
                $agregar_kardex=mainModel::guardar_datos("kardex",$datos_kardex_reg);
                if($agregar_kardex->rowCount()!=1){
                    $errores_kardex=1;
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
                }
            }

            /*== Reestableciendo DB debido a errores ==*/
            if($errores_kardex==1){

                /*== Actualizando datos de detalle compra ==*/
                $datos_detalle_compra_up=[
                    "compra_detalle_cantidad"=>[
                        "campo_marcador"=>":Cantidad",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_cantidad']
                    ],
                    "compra_detalle_precio"=>[
                        "campo_marcador"=>":Precio",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_precio']
                    ],
                    "compra_detalle_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_subtotal']
                    ],
                    "compra_detalle_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_impuestos']
                    ],
                    "compra_detalle_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_total']
                    ],
                    "compra_detalle_descripcion"=>[
                        "campo_marcador"=>":Descripcion",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_descripcion']
                    ],
                    "compra_codigo"=>[
                        "campo_marcador"=>":Codigo",
                        "campo_valor"=>$datos_compra_detalle['compra_codigo']
                    ],
                    "producto_id"=>[
                        "campo_marcador"=>":Producto",
                        "campo_valor"=>$datos_compra_detalle['producto_id']
                    ]
                ];
                if($operacion_compra_detalle=="Actualizar"){
                    $condicion_compra_detalle=[
                        "condicion_campo"=>"compra_detalle_id",
                        "condicion_marcador"=>":Codigo",
                        "condicion_valor"=>$datos_compra_detalle['compra_detalle_id']
                    ];
                    mainModel::actualizar_datos("compra_detalle",$datos_detalle_compra_up,$condicion_compra_detalle);
                }else{
                    mainModel::guardar_datos("compra_detalle",$datos_detalle_compra_up);
                }
                

                /*== Actualizando datos de la compra ==*/
                $datos_compra_up=[
                    "compra_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$datos_compra['compra_subtotal']
                    ],
                    "compra_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$datos_compra['compra_impuestos']
                    ],
                    "compra_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$datos_compra['compra_total']
                    ]
                ];
                mainModel::actualizar_datos("compra",$datos_compra_up,$condicion_compra);

                /*== Actualizando stock de producto ==*/
                $datos_producto_up=[
                    "producto_stock_total"=>[
                        "campo_marcador"=>":Stock",
                        "campo_valor"=>$datos_producto['producto_stock_total']
                    ]
                ];
                mainModel::actualizar_datos("producto",$datos_producto_up,$condicion_producto);

                /*== Actualizando efectivo en caja ==*/
				$datos_caja_up=[
					"caja_efectivo"=>[
						"campo_marcador"=>":Efectivo",
						"campo_valor"=>$datos_caja['caja_efectivo']
					]
                ];
				mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

				/*== Eliminando movimiento ==*/
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 007",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Preparando datos para enviarlos al modelo ==*/
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
                    "campo_valor"=>"Devolución de producto a proveedor"
                ],
                "kardex_detalle_unidad"=>[
                    "campo_marcador"=>":Unidad",
                    "campo_valor"=>$devolucion_cantidad
                ],
                "kardex_detalle_costo_unidad"=>[
                    "campo_marcador"=>":Costo",
                    "campo_valor"=>$datos_compra_detalle['compra_detalle_precio']
                ],
                "kardex_detalle_costo_total"=>[
                    "campo_marcador"=>":Total",
                    "campo_valor"=>$devolucion_total
                ],
                "kardex_codigo"=>[
                    "campo_marcador"=>":Codigo",
                    "campo_valor"=>$kardex_codigo
                ],
                "producto_id"=>[
                    "campo_marcador"=>":Producto",
                    "campo_valor"=>$producto_id
                ],
                "usuario_id"=>[
                    "campo_marcador"=>":Usuario",
                    "campo_valor"=>$_SESSION['id_svi']
                ]
            ];

            $agregar_kardex_detalle=mainModel::guardar_datos("kardex_detalle",$datos_kardex_detalle);

            /*== Reestableciendo DB debido a errores ==*/
            if($agregar_kardex_detalle->rowCount()!=1){
                /*== Actualizando kardex ==*/
                if($operacion_kardex=="Actualizar"){

                    $datos_kardex_rs=[
                        "kardex_salida_unidad"=>[
                            "campo_marcador"=>":KardexSalU",
                            "campo_valor"=>$array_kardex['kardex_salida_unidad']
                        ],
                        "kardex_salida_costo_total"=>[
                            "campo_marcador"=>":KardexSalCT",
                            "campo_valor"=>$array_kardex['kardex_salida_costo_total']
                        ],
                        "kardex_existencia_unidad"=>[
                            "campo_marcador"=>":KardexExU",
                            "campo_valor"=>$array_kardex['kardex_existencia_unidad']
                        ],
                        "kardex_existencia_costo_total"=>[
                            "campo_marcador"=>":KardexExCT",
                            "campo_valor"=>$array_kardex['kardex_existencia_costo_total']
                        ]
                    ];

                    $condicion_kardex=[
                        "condicion_campo"=>"kardex_codigo",
                        "condicion_marcador"=>":Codigo",
                        "condicion_valor"=>$array_kardex['kardex_codigo']
                    ];

                    mainModel::actualizar_datos("kardex",$datos_kardex_rs,$condicion_kardex);
                }else{
                    $check_kardex_del=mainModel::ejecutar_consulta_simple("SELECT kardex_id FROM kardex WHERE kardex_codigo='".$array_kardex['kardex_codigo']."'");

                    if($check_kardex_del->rowCount()==1){
                        mainModel::eliminar_registro("kardex","kardex_codigo",$array_kardex['kardex_codigo']);
                    }
                    $check_kardex_del->closeCursor();
                    $check_kardex_del=mainModel::desconectar($check_kardex_del);
                }

                /*== Actualizando datos de detalle compra ==*/
                $datos_detalle_compra_up=[
                    "compra_detalle_cantidad"=>[
                        "campo_marcador"=>":Cantidad",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_cantidad']
                    ],
                    "compra_detalle_precio"=>[
                        "campo_marcador"=>":Precio",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_precio']
                    ],
                    "compra_detalle_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_subtotal']
                    ],
                    "compra_detalle_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_impuestos']
                    ],
                    "compra_detalle_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_total']
                    ],
                    "compra_detalle_descripcion"=>[
                        "campo_marcador"=>":Descripcion",
                        "campo_valor"=>$datos_compra_detalle['compra_detalle_descripcion']
                    ],
                    "compra_codigo"=>[
                        "campo_marcador"=>":Codigo",
                        "campo_valor"=>$datos_compra_detalle['compra_codigo']
                    ],
                    "producto_id"=>[
                        "campo_marcador"=>":Producto",
                        "campo_valor"=>$datos_compra_detalle['producto_id']
                    ]
                ];
                if($operacion_compra_detalle=="Actualizar"){
                    $condicion_compra_detalle=[
                        "condicion_campo"=>"compra_detalle_id",
                        "condicion_marcador"=>":Codigo",
                        "condicion_valor"=>$datos_compra_detalle['compra_detalle_id']
                    ];
                    mainModel::actualizar_datos("compra_detalle",$datos_detalle_compra_up,$condicion_compra_detalle);
                }else{
                    mainModel::guardar_datos("compra_detalle",$datos_detalle_compra_up);
                }

                /*== Actualizando datos de la compra ==*/
                $datos_compra_up=[
                    "compra_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$datos_compra['compra_subtotal']
                    ],
                    "compra_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$datos_compra['compra_impuestos']
                    ],
                    "compra_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$datos_compra['compra_total']
                    ]
                ];
                mainModel::actualizar_datos("compra",$datos_compra_up,$condicion_compra);

                /*== Actualizando stock de producto ==*/
                $datos_producto_up=[
                    "producto_stock_total"=>[
                        "campo_marcador"=>":Stock",
                        "campo_valor"=>$datos_producto['producto_stock_total']
                    ]
                ];
                mainModel::actualizar_datos("producto",$datos_producto_up,$condicion_producto);

                /*== Actualizando efectivo en caja ==*/
				$datos_caja_up=[
					"caja_efectivo"=>[
						"campo_marcador"=>":Efectivo",
						"campo_valor"=>$datos_caja['caja_efectivo']
					]
                ];
				mainModel::actualizar_datos("caja",$datos_caja_up,$condicion_caja);

				/*== Eliminando movimiento ==*/
                mainModel::eliminar_registro("movimiento","movimiento_codigo",$codigo_movimiento);

				/*== Eliminando devolucion ==*/
				mainModel::eliminar_registro("devolucion","devolucion_codigo",$devolucion_codigo);

                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido realizar la devolución, por favor intente nuevamente. COD: 007",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }
            $agregar_kardex_detalle->closeCursor();
            $agregar_kardex_detalle=mainModel::desconectar($agregar_kardex_detalle);


            $alerta=[
                "Alerta"=>"recargar",
                "Titulo"=>"¡Devolución realizada!",
                "Texto"=>"La devolución del producto se ha realizado con éxito",
                "Tipo"=>"success"
            ];
            echo json_encode($alerta);
        } /*-- Fin controlador --*/


        /*---------- Controlador paginador devolucion ----------*/
		public function paginador_devolucion_controlador($pagina,$registros,$url,$fecha_inicio,$fecha_final){
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
            
            if($tipo=="return-search"){
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

            $campos_tablas="devolucion.devolucion_id,devolucion.devolucion_codigo,devolucion.devolucion_fecha,devolucion.devolucion_hora,devolucion.devolucion_tipo,devolucion.devolucion_descripcion,devolucion.devolucion_cantidad,devolucion.devolucion_precio,devolucion.devolucion_total,devolucion.compra_venta_codigo,devolucion.usuario_id,devolucion.caja_id,usuario.usuario_id,usuario.usuario_nombre,usuario.usuario_apellido,caja.caja_id,caja.caja_numero,caja.caja_nombre";

			if($tipo=="return-search" && $fecha_inicio!="" && $fecha_final!=""){
				$consulta="SELECT SQL_CALC_FOUND_ROWS $campos_tablas FROM devolucion INNER JOIN caja ON devolucion.caja_id=caja.caja_id INNER JOIN usuario ON devolucion.usuario_id=usuario.usuario_id WHERE (devolucion.devolucion_fecha BETWEEN '$fecha_inicio' AND '$fecha_final') ORDER BY devolucion.devolucion_id DESC LIMIT $inicio,$registros";
			}else{
				$consulta="SELECT SQL_CALC_FOUND_ROWS $campos_tablas FROM devolucion INNER JOIN caja ON devolucion.caja_id=caja.caja_id INNER JOIN usuario ON devolucion.usuario_id=usuario.usuario_id ORDER BY devolucion.devolucion_id DESC LIMIT $inicio,$registros";
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
                            <th>TIPO</th>
                            <th>PRODUCTO</th>
                            <th>CANTIDAD</th>
                            <th>PRECIO</th>
                            <th>TOTAL</th>
                            <th>VENDEDOR</th>
                            <th>DETALLES</th>
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
                            <td>'.date("d-m-Y", strtotime($rows['devolucion_fecha'])).' '.$rows['devolucion_hora'].'</td>
                            <td>'.$rows['devolucion_tipo'].'</td>
                            <td>'.$rows['devolucion_descripcion'].'</td>
                            <td>'.$rows['devolucion_cantidad'].'</td>
                            <td>'.MONEDA_SIMBOLO.number_format($rows['devolucion_precio'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                            <td>'.MONEDA_SIMBOLO.number_format($rows['devolucion_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                            <td>'.$rows['usuario_nombre'].' '.$rows['usuario_apellido'].'</td>
                            <td>';
                            if($rows['devolucion_tipo']=="Devolución de venta" || $rows['devolucion_tipo']=="Devolucion de venta"){
                                $tabla.='<a href="'.SERVERURL.'sale-detail/'.$rows['compra_venta_codigo'].'/" class="btn btn-info" data-toggle="popover" data-trigger="hover" title="Ver detalles de venta" data-content="Venta código '.$rows['compra_venta_codigo'].'" >
									<i class="fas fa-cart-plus fa-fw"></i>
								</a>';
                            }else{
                                $tabla.='<a href="'.SERVERURL.'shop-detail/'.$rows['compra_venta_codigo'].'/" class="btn btn-info" data-toggle="popover" data-trigger="hover" title="Ver detalles de compra" data-content="Compra código '.$rows['compra_venta_codigo'].'" >
									<i class="fas fa-shopping-bag fa-fw"></i>
								</a>';
                            }
								
                    $tabla.='</td>
                        </tr>
                    ';
                    $contador++;
				}
				$pag_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='
						<tr class="text-center" >
							<td colspan="9">
								<a href="'.$url.'" class="btn btn-raised btn-primary btn-sm">
									Haga clic acá para recargar el listado
								</a>
							</td>
						</tr>
					';
				}else{
					$tabla.='
						<tr class="text-center" >
							<td colspan="9">
								No hay registros en el sistema
							</td>
						</tr>
					';
				}
			}

			$tabla.='</tbody></table></div>';

			if($total>0 && $pagina<=$Npaginas){
				$tabla.='<p class="text-right">Mostrando devoluciones <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';
			}

			### Paginacion ###
			if($total>=1 && $pagina<=$Npaginas){
				$tabla.=mainModel::paginador_tablas($pagina,$Npaginas,$url,7);
			}

			return $tabla;
        } /*-- Fin controlador --*/
    }