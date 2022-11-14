<?php
    include "./vistas/inc/admin_security.php";
?>
<div class="full-box page-header">
    <h3 class="text-left text-uppercase">
		<i class="fas fa-search fa-fw"></i> &nbsp; Buscar kardex
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
            <a class="active" href="<?php echo SERVERURL; ?>kardex-search/">
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
<?php
    if(!isset($_SESSION['fecha_inicio_kardex']) && empty($_SESSION['fecha_inicio_kardex']) && !isset($_SESSION['fecha_final_kardex']) && empty($_SESSION['fecha_final_kardex'])){
?>
<div class="container-fluid">
	<form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/buscadorAjax.php" data-form="default" method="POST" autocomplete="off" >
        <input type="hidden" name="modulo" value="kardex">
		<div class="container-fluid">
			<div class="row justify-content-md-center">
				<div class="col-12 col-md-4">
					<div class="form-group">
						<label for="fecha_inicio" >Mes</label>
                        <select class="form-control" name="fecha_inicio" id="fecha_inicio">
                            <option value="01" selected="">Enero</option>
                            <option value="02">Febrero</option>
                            <option value="03">Marzo</option>
                            <option value="04">Abril</option>
                            <option value="05">Mayo</option>
                            <option value="06">Junio</option>
                            <option value="07">Julio</option>
                            <option value="08">Agosto</option>
                            <option value="09">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
					</div>
				</div>
				<div class="col-12 col-md-4">
					<div class="form-group">
						<label for="fecha_final" >Año</label>
						<input type="text" pattern="[0-9]{4,4}" class="form-control" name="fecha_final" id="fecha_final" maxlength="4" value="<?php echo date("Y"); ?>">
					</div>
				</div>
				<div class="col-12">
					<p class="text-center" style="margin-top: 40px;">
						<button type="submit" class="btn btn-raised btn-info"><i class="fas fa-search"></i> &nbsp; BUSCAR</button>
					</p>
				</div>
			</div>
		</div>
	</form>
</div>
<?php }else{ ?>
<div class="container-fluid">
	<form class="FormularioAjax" action="<?php echo SERVERURL; ?>ajax/buscadorAjax.php" data-form="search" method="POST" autocomplete="off" >
        <input type="hidden" name="modulo" value="kardex">
        <input type="hidden" name="eliminar_busqueda" value="eliminar">
		<div class="container-fluid">
			<div class="row justify-content-md-center">
				<div class="col-12 col-md-6">
					<p class="text-center" style="font-size: 20px;">
						Fecha de busqueda: <strong><?php echo $lc->obtener_nombre_mes($_SESSION['fecha_inicio_kardex'])." de ".$_SESSION['fecha_final_kardex']; ?></strong>
					</p>
				</div>
				<div class="col-12">
					<p class="text-center" style="margin-top: 20px;">
						<button type="submit" class="btn btn-raised btn-danger"><i class="far fa-trash-alt"></i> &nbsp; ELIMINAR BÚSQUEDA</button>
					</p>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="container-fluid">
    <?php
        require_once "./controladores/kardexControlador.php";
        $ins_kardex = new kardexControlador();

        echo $ins_kardex->paginador_kardex_controlador($pagina[1],15,$pagina[0],$_SESSION['fecha_inicio_kardex'],$_SESSION['fecha_final_kardex']);
    ?>
</div>
<?php } ?>