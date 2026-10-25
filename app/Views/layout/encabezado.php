<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autos MVC | <?= escapar(ucfirst($modulo)) ?></title>
    <link rel="stylesheet" href="css/estilos.css?v=<?= escapar(filemtime(__DIR__ . '/../../../public/css/estilos.css')) ?>">
</head>
<body>
<header>
    <strong>Autos MVC</strong>
    <nav>
        <?php foreach (['autos' => 'Autos', 'marcas' => 'Marcas', 'categorias' => 'Categorías'] as $nombre => $etiqueta): ?>
            <a href="<?= escapar(url($nombre)) ?>" <?= $nombre === $modulo ? 'aria-current="page"' : '' ?>><?= escapar($etiqueta) ?></a>
        <?php endforeach; ?>
    </nav>
</header>
<main>
    <h1><?= escapar(['autos' => 'Autos', 'marcas' => 'Marcas', 'categorias' => 'Categorías'][$modulo]) ?></h1>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <p class="exito"><?= escapar($_SESSION['mensaje']) ?></p>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>
    <?php if (!empty($errores)): ?>
        <div class="error"><ul>
        <?php foreach ($errores as $error): ?>
            <li><?= escapar($error) ?></li>
        <?php endforeach; ?>
        </ul></div>
    <?php endif; ?>
