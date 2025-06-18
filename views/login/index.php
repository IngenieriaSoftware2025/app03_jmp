<div class="row justify-content-center">
    <form class="col-lg-4 border rounded shadow p-4 bg-light" id="FormLogin">
        <h3 class="text-center mb-4"><b>MORATAYA CELULARES</b></h3>
        <div class="text-center mb-4">
            <img src="<?= asset('./images/login.jpg') ?>" alt="Logo" width="200px" class="img-fluid rounded-circle">
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="usu_codigo" class="form-label">Ingrese su Código</label>
                <input type="number" name="usu_codigo" id="usu_codigo" class="form-control" placeholder="Ingresa tu código">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="usu_password" class="form-label">Contraseña</label>
                <input type="password" name="usu_password" id="usu_password" class="form-control" placeholder="Ingresa tu contraseña">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <button type="submit" class="btn btn-primary w-100 btn-lg" id="BtnIniciar">
                    Iniciar sesión
                </button>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <small class="text-muted">
                <strong>Usuarios de prueba:</strong><br>
                Administrador: <code>12345678</code> / <code>123456</code><br>
                Empleado: <code>12345679</code> / <code>123456</code>
            </small>
        </div>
    </form>
</div>

<script src="<?= asset('build/js/login/login.js') ?>"></script>