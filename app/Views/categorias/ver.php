<h2>Detalle del registro</h2>
<dl>
    <dt>Nombre</dt>
    <dd><?= escapar($datos['nombre']) ?></dd>
    <dt>Descripción</dt>
    <dd><?= escapar($datos['descripcion']) ?></dd>
</dl>
<p><a href="<?= escapar(url($singular . '-editar', (int) $datos['id_categoria'])) ?>">Editar</a> | <a href="<?= escapar(url($modulo)) ?>">Volver</a></p>
