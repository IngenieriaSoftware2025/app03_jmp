<div class="container-fluid vh-100" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 50%, #2d3436 100%);">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-12 col-sm-8 col-md-6 col-lg-4 col-xl-3">
            <div class="card shadow-lg border-0" style="border-radius: 20px; border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 25px 45px rgba(0,0,0,0.1); backdrop-filter: blur(10px);">
                
                <!-- Header con gradiente -->
                <div class="card-header text-center text-white border-0" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); border-radius: 20px 20px 0 0; padding: 40px 20px 30px;">
                    <div class="mb-3">
                        <div style="width: 70px; height: 70px; background: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-phone-fill" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">MORATAYA CELULARES</h2>
                    <p class="mb-0" style="opacity: 0.9;">Sistema de Gestión de Celulares</p>
                </div>

                <!-- Formulario -->
                <div class="card-body p-4" style="background: #f8f9fa;">
                    <form id="FormLogin">
                        
                        <!-- Campo de código -->
                        <div class="mb-4">
                            <div class="form-floating">
                                <input type="number" 
                                       name="usu_codigo" 
                                       id="usu_codigo" 
                                       class="form-control" 
                                       placeholder="Ingrese su código"
                                       style="border-radius: 12px; height: 55px; border: 2px solid #e1e5e9;"
                                       required>
                                <label for="usu_codigo">Ingrese su código</label>
                            </div>
                        </div>

                        <!-- Campo de contraseña -->
                        <div class="mb-4">
                            <div class="form-floating">
                                <input type="password" 
                                       name="usu_password" 
                                       id="usu_password" 
                                       class="form-control" 
                                       placeholder="Ingrese su contraseña"
                                       style="border-radius: 12px; height: 55px; border: 2px solid #e1e5e9;"
                                       required>
                                <label for="usu_password">Ingrese su contraseña</label>
                            </div>
                        </div>

                        <!-- Botón de login -->
                        <div class="d-grid mb-3">
                            <button type="submit" 
                                    id="BtnIniciar" 
                                    class="btn btn-primary btn-lg" 
                                    style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); border: none; border-radius: 12px; padding: 15px; font-weight: 600;">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                            </button>
                        </div>

                        <!-- Información de usuarios de prueba -->
                        <div class="text-center">
                            <small class="text-muted">Usuarios de prueba disponibles</small>
                        </div>

                    </form>
                </div>

                <!-- Footer con credenciales -->
                <div class="card-footer border-0 bg-light" style="border-radius: 0 0 20px 20px;">
                    <div class="alert alert-info border-0 mb-0" style="border-radius: 12px;">
                        <h6 class="alert-heading mb-3">
                            <i class="bi bi-info-circle me-2"></i>Credenciales de Prueba
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

            <!-- Copyright -->
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

<script src="<?= asset('build/js/login/index.js') ?>"></script>

<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }
    
    .form-control:focus {
        border-color: #74b9ff;
        box-shadow: 0 0 0 0.2rem rgba(116, 185, 255, 0.25);
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #0984e3 0%, #0057b3 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(116, 185, 255, 0.4);
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
</style>