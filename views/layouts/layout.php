<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= asset('images/cit.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <title>Morataya Celulares</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/app03_jmp">
                <img src="<?= asset('./images/cit.png') ?>" width="35px'" alt="logo">
                Morataya Celulares
            </a>
            <div class="navbar-nav">
                <a class="nav-link" href="/app03_jmp/clientes">
                    <i class="bi bi-person-check-fill"></i> Clientes
                </a>
                <a class="nav-link" href="/app03_jmp/marcas">
                    <i class="bi bi-tags-fill"></i> Marcas
                </a>
                <a class="nav-link" href="/app03_jmp/productos">
                    <i class="bi bi-box-seam"></i> Productos
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid pt-5 mb-4" style="min-height: 85vh">
        <?php echo $contenido; ?>
    </div>
    
    <script src="<?= asset('build/js/app.js') ?>"></script>
</body>
</html>