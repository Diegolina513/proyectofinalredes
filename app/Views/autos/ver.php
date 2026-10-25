<h2>Detalle del registro</h2>
<dl>
    <dt>Placa</dt>
    <dd><?= escapar($datos['placa']) ?></dd>
    <dt>Línea</dt>
    <dd><?= escapar($datos['linea']) ?></dd>
    <dt>Modelo</dt>
    <dd><?= escapar($datos['modelo']) ?></dd>
    <dt>Color</dt>
    <dd><?= escapar($datos['color']) ?></dd>
    <dt>Marca</dt>
    <dd><?= escapar($datos['marca']) ?></dd>
    <dt>Categoría</dt>
    <dd><?= escapar($datos['categoria']) ?></dd>
    <dt>Precio (Q)</dt>
    <dd><?= escapar(number_format((float) $datos['precio'], 2, '.', ',')) ?></dd>
    <dt>Estado</dt>
    <dd><?= escapar($datos['estado']) ?></dd>
</dl>
<p><a href="<?= escapar(url($singular . '-editar', (int) $datos['id_auto'])) ?>">Editar</a> | <a href="<?= escapar(url($modulo)) ?>">Volver</a></p>
