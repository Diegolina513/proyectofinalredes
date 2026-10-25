<p><a class="boton" href="<?= escapar(url($singular . '-crear')) ?>">Crear registro</a></p>
<div class="tabla"><table>
    <thead><tr>
            <th>Nombre</th>
            <th>Descripción</th>
        <th>Acciones</th>
    </tr></thead>
    <tbody>
    <?php foreach ($filas as $fila): $registroId = (int) $fila['id_categoria']; ?>
        <tr>
                <td><?= escapar($fila['nombre']) ?></td>
                <td><?= escapar($fila['descripcion']) ?></td>
            <td><div class="acciones">
                <a href="<?= escapar(url($singular . '-ver', $registroId)) ?>">Ver</a>
                <a href="<?= escapar(url($singular . '-editar', $registroId)) ?>">Editar</a>
                <form method="post" action="<?= escapar(url($singular . '-eliminar')) ?>">
                    <input type="hidden" name="id" value="<?= escapar($registroId) ?>">
                    <button class="peligro" type="submit">Eliminar</button>
                </form>
            </div></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$filas): ?><tr><td colspan="3">No hay registros.</td></tr><?php endif; ?>
    </tbody>
</table></div>
