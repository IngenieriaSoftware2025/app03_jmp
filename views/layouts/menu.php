<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="build/js/app.js"></script>
    <link rel="shortcut icon" href="<?= asset('images/cit.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <title>Morataya Celulares</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="./inicio">
                <img src="<?= asset('./images/cit.png') ?>" width="35px'" alt="logo">
                Morataya Celulares
            </a>
            <div class="collapse navbar-collapse" id="navbarToggler">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    
                    <li class="nav-item">
                        <a class="nav-link" href="./inicio">
                            <i class="bi bi-house-fill me-2"></i>Inicio
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="./clientes">
                            <i class="bi bi-person-check-fill me-2"></i>Clientes
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="./productos">
                            <i class="bi bi-box-seam me-2"></i>Inventario
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="./ventas">
                            <i class="bi bi-cart-check-fill me-2"></i>Ventas
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="./reparaciones">
                            <i class="bi bi-tools me-2"></i>Reparaciones
                        </a>
                    </li>

                    <?php if (isset($_SESSION['ADMIN'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="./marcas">
                            <i class="bi bi-tags-fill me-2"></i>Marcas
                        </a>
                    </li>
                    
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-2"></i>Administración
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item nav-link text-white" href="./usuarios">
                                    <i class="bi bi-people me-2"></i>Usuarios
                                </a>
                            </li>
                        </ul>
                    </div>
                    <?php endif; ?>

                </ul>
                
                <div class="d-flex align-items-center">
                    <span class="navbar-text me-3">
                        <i class="bi bi-person-circle"></i> 
                        <?= $_SESSION['nombre'] ?? 'Usuario' ?> 
                        <small class="text-muted">(<?= isset($_SESSION['ADMIN']) ? 'Admin' : 'Empleado' ?>)</small>
                    </span>
                    <a href="./logout" class="btn btn-danger">
                        <i class="bi bi-arrow-bar-left"></i>SALIR
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="progress fixed-bottom" style="height: 6px;">
        <div class="progress-bar progress-bar-animated bg-danger" id="bar" role="progressbar"></div>
    </div>
    
    <div class="container-fluid pt-5 mb-4" style="min-height: 85vh">
        <?php echo $contenido; ?>
    </div>
    
    <div class="container-fluid">
        <div class="row justify-content-center text-center">
            <div class="col-12">
                <p style="font-size:xx-small; font-weight: bold;">
                    Morataya Celulares - Sistema de Control, <?= date('Y') ?> &copy;
                </p>
            </div>
        </div>
    </div>
</body>
</html>