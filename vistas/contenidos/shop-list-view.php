<?php
    include "./vistas/inc/admin_security.php";
?>
<div class="full-box page-header">
    <h3 class="text-left text-uppercase">
        <i class="fas fa-file-invoice-dollar fa-fw"></i> &nbsp; Compras realizadas
    </h3>
    <?php include "./vistas/desc/desc_compra.php"; ?>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs text-uppercase">
        <li>
			<a href="<?php echo SERVERURL; ?>shop-new/">
				<i class="fas fa-shopping-bag fa-fw"></i> &nbsp; Nueva compra
			</a>
		</li>
		<li>
			<a class="active" href="<?php echo SERVERURL; ?>shop-list/">
				<i class="fas fa-file-invoice-dollar fa-fw"></i> &nbsp; Compras realizadas
			</a>
		</li>
		<li>
			<a href="<?php echo SERVERURL; ?>shop-search/">
				<i class="fas fa-search-dollar fa-fw"></i> &nbsp; Buscar compra
			</a>
		</li>
    </ul>	
</div>

<div class="container-fluid">
    <?php
        require_once "./controladores/compraControlador.php";
        $ins_compra = new compraControlador();

        echo $ins_compra->paginador_compra_controlador($pagina[1],15,$pagina[0],"Listado","","");
    ?>
</div>