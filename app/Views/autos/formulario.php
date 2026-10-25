<h2><?= $id ? 'Editar registro' : 'Crear registro' ?></h2>
<form class="editor" method="post" action="<?= escapar(url($singular . ($id ? '-editar' : '-crear'))) ?>">
    <?php if ($id): ?><input type="hidden" name="id" value="<?= escapar($id) ?>"><?php endif; ?>
    <label for="placa">Placa</label>
    <input id="placa" name="placa" type="text" required maxlength="20" value="<?= escapar($datos['placa'] ?? '') ?>">
    <label for="linea">Línea</label>
    <input id="linea" name="linea" type="text" required maxlength="100" value="<?= escapar($datos['linea'] ?? '') ?>">
    <label for="modelo">Modelo (año)</label>
    <input id="modelo" name="modelo" type="number" required min="1886" max="<?= escapar((int) date('Y') + 1) ?>" step="1" value="<?= escapar($datos['modelo'] ?? '') ?>">
    <label for="color">Color</label>
    <input id="color" name="color" type="text" required maxlength="50" value="<?= escapar($datos['color'] ?? '') ?>">
    <label for="precio">Precio (Q)</label>
    <input id="precio" name="precio" type="number" required min="0" max="9999999999.99" step="0.01" value="<?= escapar($datos['precio'] ?? '') ?>">
    <label for="id_marca">Marca</label>
    <select id="id_marca" name="id_marca" required>
        <option value="">Seleccione</option>
        <?php foreach ($marcas as $opcion): ?>
            <option value="<?= escapar($opcion['id_marca']) ?>" <?= (string) ($datos['id_marca'] ?? '') === (string) $opcion['id_marca'] ? 'selected' : '' ?>><?= escapar($opcion['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <label for="id_categoria">Categoría</label>
    <select id="id_categoria" name="id_categoria" required>
        <option value="">Seleccione</option>
        <?php foreach ($categorias as $opcion): ?>
            <option value="<?= escapar($opcion['id_categoria']) ?>" <?= (string) ($datos['id_categoria'] ?? '') === (string) $opcion['id_categoria'] ? 'selected' : '' ?>><?= escapar($opcion['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <label for="estado">Estado</label>
    <select name="estado" id="estado" required>
        <?php foreach (['Disponible', 'Vendido'] as $estado): ?>
            <option value="<?= escapar($estado) ?>" <?= ($datos['estado'] ?? 'Disponible') === $estado ? 'selected' : '' ?>><?= escapar($estado) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (!$marcas || !$categorias): ?><p>Primero registre marcas y categorías.</p><?php endif; ?>
    <div class="acciones">
        <button type="submit">Guardar</button>
        <a href="<?= escapar(url($modulo)) ?>">Cancelar</a>
    </div>
</form>
