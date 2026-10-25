<h2>Detalle del registro</h2>
<dl>
    <dt>Nombre</dt>
    <dd><?= escapar($datos['nombre']) ?></dd>
    <dt>País de origen</dt>
    <dd><?= escapar($datos['pais_origen']) ?></dd>
</dl>
<p><a href="<?= escapar(url($singular . '-editar', (int) $datos['id_marca'])) ?>">Editar</a> | <a href="<?= escapar(url($modulo)) ?>">Volver</a></p>
