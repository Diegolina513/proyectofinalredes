<h2><?= $id ? 'Editar registro' : 'Crear registro' ?></h2>
<form class="editor" method="post" action="<?= escapar(url($singular . ($id ? '-editar' : '-crear'))) ?>">
    <?php if ($id): ?><input type="hidden" name="id" value="<?= escapar($id) ?>"><?php endif; ?>
    <label for="nombre">Nombre</label>
    <input id="nombre" name="nombre" type="text" required maxlength="100" value="<?= escapar($datos['nombre'] ?? '') ?>">
    <label for="pais_origen">País de origen</label>
    <input id="pais_origen" name="pais_origen" type="text" maxlength="100" value="<?= escapar($datos['pais_origen'] ?? '') ?>">
    <div class="acciones">
        <button type="submit">Guardar</button>
        <a href="<?= escapar(url($modulo)) ?>">Cancelar</a>
    </div>
</form>
