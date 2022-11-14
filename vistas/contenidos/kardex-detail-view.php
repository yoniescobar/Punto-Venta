<?php
    include "./vistas/inc/admin_security.php";
?>
<div class="full-box page-header">
    <h3 class="text-left text-uppercase">
        <i class="fas fa-pallet fa-fw"></i> &nbsp; Detalles de kardex
    </h3>
    <?php include "./vistas/desc/desc_kardex.php"; ?>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs text-uppercase">
        <li>
            <a href="<?php echo SERVERURL; ?>kardex/">
                <i class="fas fa-pallet fa-fw"></i> &nbsp; Kardex general
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>kardex-search/">
                <i class="fas fa-search fa-fw"></i> &nbsp; Buscar kardex
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>kardex-product/">
                <i class="fas fa-luggage-cart fa-fw"></i> &nbsp; Kardex por producto
            </a>
        </li>
    </ul>	
</div>

<div class="container-fluid">
    <?php
        include "./vistas/inc/btn_go_back.php";

        $datos_kardex=$lc->datos_tabla("Unico","kardex","kardex_codigo",$pagina[1]);

        if($datos_kardex->rowCount()==1){
            $campos=$datos_kardex->fetch();

            $datos_producto=$lc->datos_tabla("Normal","producto WHERE producto_id='".$campos['producto_id']."'","*",0);
            $datos_producto=$datos_producto->fetch();
    ?>
    <div class="container-fluid" id="invoice-kardex">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <p class="text-uppercase font-weight-bold text-center">kardex <?php echo $lc->obtener_nombre_mes($campos['kardex_mes'])." de ".$campos['kardex_year']." (".$datos_producto["producto_nombre"]; ?>)</p>
                </div>
                <div class="col-12 col-lg-4">
                    <p class="text-center text-uppercase font-weight-bold bg-dark" style="color: #FFF;" >Entradas</p>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Entrada de unidades
                            <span><?php echo $campos['kardex_entrada_unidad']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Costo de unidades
                            <span><?php echo MONEDA_SIMBOLO.number_format($campos['kardex_entrada_costo_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE; ?></span>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-lg-4">
                    <p class="text-center text-uppercase font-weight-bold bg-dark" style="color: #FFF;" >Salidas</p>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Salida de unidades
                            <span><?php echo $campos['kardex_salida_unidad']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Costo de unidades
                            <span><?php echo MONEDA_SIMBOLO.number_format($campos['kardex_salida_costo_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE; ?></span>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-lg-4">
                    <p class="text-center text-uppercase font-weight-bold bg-dark" style="color: #FFF;" >Existencias</p>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Inventario inicial
                            <span><?php echo $campos['kardex_existencia_inicial']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Inventario actual
                            <span><?php echo $campos['kardex_existencia_unidad']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Costo inventario actual
                            <span><?php echo MONEDA_SIMBOLO.number_format($campos['kardex_existencia_costo_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <p class="text-uppercase font-weight-bold text-center">Detalles de kardex</p>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="bg-info">
                        <tr class="text-center text-uppercase">
                            <th scope="col">#</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Unidades</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $detalle_kardex=$lc->datos_tabla("Normal","kardex_detalle WHERE kardex_codigo='".$campos['kardex_codigo']."'","*",0);
                            if($detalle_kardex->rowCount()>=1){
                                $detalle_kardex=$detalle_kardex->fetchAll();
                                $cc=1;
                                foreach($detalle_kardex as $detalle){
                                    echo '
                                    <tr class="text-center">
                                        <th scope="row">'.$cc.'</th>
                                        <td>'.date("d-m-Y", strtotime($detalle['kardex_detalle_fecha'])).'</td>
                                        <td>'.$detalle['kardex_detalle_tipo'].'</td>
                                        <td>'.$detalle['kardex_detalle_descripcion'].'</td>
                                        <td>'.$detalle['kardex_detalle_unidad'].'</td>
                                        <td>'.MONEDA_SIMBOLO.number_format($detalle['kardex_detalle_costo_unidad'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                                        <td>'.MONEDA_SIMBOLO.number_format($detalle['kardex_detalle_costo_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                                    </tr>
                                    ';
                                    $cc++;
                                }
                            }else{
                                echo '<tr class="text-center text-uppercase"><td colspan="7">No hay datos para mostrar</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="text-center">
        <a href="#" class="btn btn-outline-info print-barcode" data-id="#invoice-kardex"><i class="fas fa-print"></i> &nbsp; Imprimir</a>
    </p>
    <br><br>
    <?php 
        }else{
            include "./vistas/inc/error_alert.php";
        } 
    ?>
</div>