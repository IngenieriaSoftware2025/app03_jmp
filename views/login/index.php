<div class="container-fluid vh-100" style="background: linear-gradient(135deg,rgb(21, 28, 62) 0%,rgb(11, 8, 13) 100%);">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-12 col-sm-8 col-md-6 col-lg-4 col-xl-3">
            <div class="card shadow-lg border-0" style="border-radius: 20px; backdrop-filter: blur(10px);">
                <!-- Header con gradiente -->
                <div class="card-header text-center border-0 pb-0" style="background: linear-gradient(135deg,rgb(84, 65, 104),rgb(141, 108, 108)); border-radius: 20px 20px 0 0;">
                    <div class="py-4">
                        <!-- Logo de tu empresa -->
                        <div class="mb-3">
                            <img src="<?= asset('images/logo.png') ?>" 
                                 alt="Logo Morataya" 
                                 class="rounded-circle border border-4 border-white shadow"
                                 style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                        <h2 class="text-white fw-bold mb-1">MORATAYA CELULARES</h2>
                        <p class="text-white-50 mb-0">Sistema de Gestión</p>
                    </div>
                </div>

                <!-- Formulario -->
                <div class="card-body p-4">
                    <form id="FormLogin">
                        <!-- Campo de código -->
                        <div class="mb-4">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;">
                                    <i class="bi bi-person-circle text-primary"></i>
                                </span>
                                <input type="number" 
                                       class="form-control border-start-0 ps-0" 
                                       id="usu_codigo" 
                                       name="usu_codigo"
                                       placeholder="Código de usuario"
                                       style="border-radius: 0 12px 12px 0;"
                                       required>
                            </div>
                        </div>

                        <!-- Campo de contraseña -->
                        <div class="mb-4">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;">
                                    <i class="bi bi-lock-fill text-primary"></i>
                                </span>
                                <input type="password" 
                                       class="form-control border-start-0 ps-0" 
                                       id="usu_password" 
                                       name="usu_password"
                                       placeholder="Contraseña"
                                       style="border-radius: 0 12px 12px 0;"
                                       required>
                            </div>
                        </div>

                        <!-- Botón de login -->
                        <div class="mb-4">
                            <button type="submit" 
                                    class="btn btn-lg w-100 text-white fw-bold py-3"
                                    id="BtnIniciar"
                                    style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; border: none;">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Iniciar Sesión
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Footer con credenciales de prueba -->
                <div class="card-footer border-0 bg-light" style="border-radius: 0 0 20px 20px;">
                    <div class="alert alert-info border-0 mb-0" style="border-radius: 12px;">
                        <h6 class="alert-heading mb-3">
                            <i class="bi bi-info-circle me-2"></i>Usuarios de Prueba
                        </h6>
                        
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="bg-white p-2 rounded border">
                                    <small class="text-muted d-block">Administrador</small>
                                    <code class="text-primary">12345678</code> / 
                                    <code class="text-success">123456</code>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="bg-white p-2 rounded border">
                                    <small class="text-muted d-block">Empleado</small>
                                    <code class="text-primary">12345679</code> / 
                                    <code class="text-success">123456</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="text-center mt-4">
                <p class="text-white-50 mb-1">
                    <i class="bi bi-shield-check me-1"></i>
                    Acceso Seguro
                </p>
                <small class="text-white-50">
                    © <?= date('Y') ?> Morataya Celulares - Todos los derechos reservados
                </small>
            </div>
        </div>
    </div>
</div>

<style>
#BtnIniciar:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4) !important;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
}

.card {
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card-header img:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

@media (max-width: 576px) {
    .card {
        margin: 1rem;
    }
    .card-header {
        padding: 2rem 1rem;
    }
    .card-header h2 {
        font-size: 1.5rem;
    }
}
</style>

<script src="<?= asset('build/js/login/index.js') ?>"></script>