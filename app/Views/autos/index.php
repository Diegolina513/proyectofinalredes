<p><a class="boton" href="<?= escapar(url($singular . '-crear')) ?>">Crear registro</a></p>
<div class="tabla"><table>
    <thead><tr>
            <th>Placa</th>
            <th>Línea</th>
            <th>Modelo</th>
            <th>Color</th>
            <th>Marca</th>
            <th>Categoría</th>
            <th>Precio (Q)</th>
            <th>Estado</th>
        <th>Acciones</th>
    </tr></thead>
    <tbody>
    <?php foreach ($filas as $fila): $registroId = (int) $fila['id_auto']; ?>
        <tr>
                <td><?= escapar($fila['placa']) ?></td>
                <td><?= escapar($fila['linea']) ?></td>
                <td><?= escapar($fila['modelo']) ?></td>
                <td><?= escapar($fila['color']) ?></td>
                <td><?= escapar($fila['marca']) ?></td>
                <td><?= escapar($fila['categoria']) ?></td>
                <td><?= escapar(number_format((float) $fila['precio'], 2, '.', ',')) ?></td>
                <td><?= escapar($fila['estado']) ?></td>
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
    <?php if (!$filas): ?><tr><td colspan="9">No hay registros.</td></tr><?php endif; ?>
    </tbody>
</table></div>
