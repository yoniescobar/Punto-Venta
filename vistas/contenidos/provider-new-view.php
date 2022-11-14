<?php
    include "./vistas/inc/admin_security.php";
?>
<div class="full-box page-header">
    <h3 class="text-left text-uppercase">
        <i class="fas fa-shipping-fast fa-fw"></i> &nbsp; Nuevo proveedor
    </h3>
    <?php include "./vistas/desc/desc_proveedor.php"; ?>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs text-uppercase">
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>provider-new/">
                <i class="fas fa-shipping-fast fa-fw"></i> &nbsp; Nuevo proveedor
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>provider-list/">
                <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de proveedores
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>provider-search/">
                <i class="fas fa-search fa-fw"></i> &nbsp; Buscar proveedor
            </a>
        </li>
    </ul>	
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/proveedorAjax.php" method="POST" data-form="save" autocomplete="off" >
        <input type="hidden" name="modulo_proveedor" value="registrar">
        <fieldset>
            <legend><i class="far fa-address-card"></i> &nbsp; Datos del proveedor</legend>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_tipo_documento" class="bmd-label-floating">Tipo de documento <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <select class="form-control" name="proveedor_tipo_documento_reg" id="proveedor_tipo_documento">
                                <option value="" selected="" >Seleccione una opción</option>
                                <?php
                                    echo $lc->generar_select(DOCUMENTOS_PROVEEDORES,"VACIO");
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_numero_documento" class="bmd-label-floating">Numero de documento <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <input type="text" pattern="[a-zA-Z0-9-]{7,30}" class="form-control" name="proveedor_numero_documento_reg" id="proveedor_numero_documento" maxlength="30">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_nombre" class="bmd-label-floating">Nombre <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <input type="text" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ., ]{4,75}" class="form-control" name="proveedor_nombre_reg" id="proveedor_nombre" maxlength="75">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_direccion" class="bmd-label-floating">Dirección</label>
                            <input type="text" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{4,97}" class="form-control" name="proveedor_direccion_reg" id="proveedor_direccion" maxlength="97">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_estado" class="bmd-label-floating">Estado <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <select class="form-control" name="proveedor_estado_reg" id="proveedor_estado">
                                <option value="Habilitado" selected="" >1 - Habilitado</option>
                                <option value="Deshabilitado">2 - Deshabilitado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        <br><br><br>
        <fieldset>
            <legend><i class="fas fa-phone-volume"></i> &nbsp; Información de contacto</legend>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_encargado" class="bmd-label-floating">Nombre del encargado</label>
                            <input type="text" pattern="[a-zA-Z ]{4,70}" class="form-control" name="proveedor_encargado_reg" id="proveedor_encargado" maxlength="70">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_telefono" class="bmd-label-floating">Teléfono</label>
                            <input type="text" pattern="[0-9()+]{8,20}" class="form-control" name="proveedor_telefono_reg" id="proveedor_telefono" maxlength="20">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_email" class="bmd-label-floating">Email</label>
                            <input type="email" class="form-control" name="proveedor_email_reg" id="proveedor_email" maxlength="50">
                        </div>
                    </div>               
                </div>
            </div>
        </fieldset>
        <p class="text-center" style="margin-top: 40px;">
            <button type="reset" class="btn btn-raised btn-secondary btn-sm"><i class="fas fa-paint-roller"></i> &nbsp; LIMPIAR</button>
            &nbsp; &nbsp;
            <button type="submit" class="btn btn-raised btn-info btn-sm"><i class="far fa-save"></i> &nbsp; GUARDAR</button>
        </p>
        <p class="text-center">
            <small>Los campos marcados con <?php echo CAMPO_OBLIGATORIO; ?> son obligatorios</small>
        </p>
    </form>
</div>