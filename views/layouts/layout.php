<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= asset('images/cit.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <title>Morataya Celulares</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/app03_jmp">
                <img src="<?= asset('./images/cit.png') ?>" width="35px" alt="logo">
                Morataya Celulares
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Enlaces del menú -->
                <div class="navbar-nav me-auto">
                    <a class="nav-link" href="/app03_jmp/clientes">
                        <i class="bi bi-person-check-fill"></i> Clientes
                    </a>
                    <a class="nav-link" href="/app03_jmp/marcas">
                        <i class="bi bi-tags-fill"></i> Marcas
                    </a>
                    <a class="nav-link" href="/app03_jmp/productos">
                        <i class="bi bi-box-seam"></i> Productos
                    </a>
                    <a class="nav-link" href="/app03_jmp/ventas">
                        <i class="bi bi-cart-fill"></i> Ventas
                    </a>
                    <a class="nav-link" href="/app03_jmp/reparaciones">
                        <i class="bi bi-tools"></i> Reparaciones
                    </a>
                    <a class="nav-link" href="/app03_jmp/usuarios">
                        <i class="bi bi-person-gear"></i> Usuarios
                    </a>
                </div>
                
                <div class="navbar-nav">
                    <span class="navbar-text me-3">
                        <i class="bi bi-person-circle"></i> 
                        <?php 
                        echo $_SESSION['user'] ?? 'Usuario'; 
                        ?>
                    </span>
                    <button type="button" 
                            class="btn btn-outline-danger btn-sm" 
                            onclick="logout()"
                            title="Cerrar Sesión">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid pt-5 mb-4" style="min-height: 85vh">
        <?php echo $contenido; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    const logout = async () => {
        try {
            const confirmacion = await Swal.fire({
                title: '¿Cerrar sesión?',
                text: "¿Estás seguro que deseas cerrar la sesión?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cerrar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            });

            if (confirmacion.isConfirmed) {
                await Swal.fire({
                    title: 'Cerrando sesión',
                    text: 'Redirigiendo al login...',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1000,
                    timerProgressBar: true
                });

                location.href = '/app03_jmp/logout';
            }

        } catch (error) {
            console.log(error);
            Swal.fire({
                title: '¡Error!',
                text: 'Error al cerrar sesión',
                icon: 'error'
            });
        }
    }
    </script>
    
    <script src="<?= asset('build/js/app.js') ?>"></script>
</body>
</html>