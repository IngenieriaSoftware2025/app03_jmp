<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">
                                <i class="bi bi-people-fill me-2"></i>Gestión de Usuarios
                            </h3>
                            <small>Solo administradores pueden gestionar usuarios</small>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-light" type="button" id="btnModalUsuario">
                                <i class="bi bi-person-plus me-1"></i>Nuevo Usuario
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3 align-items-center">
                        <div class="col-lg-3">
                            <button type="button" id="btnBuscar" class="btn btn-danger">
                                <i class="bi bi-search me-1"></i>Buscar
                            </button>
                        </div>
                        <div class="col-lg-3">
                            <button type="button" id="btnEstadisticas" class="btn btn-info">
                                <i class="bi bi-bar-chart me-1"></i>Estadísticas
                            </button>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex gap-2">
                                <input type="text" class="form-control form-control-sm" 
                                       id="inputBusqueda" 
                                       placeholder="Buscar usuarios..." 
                                       style="width: 250px;">
                                <small class="text-muted align-self-center">Búsqueda en tiempo real</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive" id="contenedorTabla" style="display: none;">
                        <table id="tablaUsuarios" class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No.</th>
                                    <th>Nombre</th>
                                    <th>Código</th>
                                    <th>Roles</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="tituloModal">Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formUsuario" novalidate>
                    <input type="hidden" id="usu_id" name="usu_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="usu_nombre" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="usu_nombre" name="usu_nombre" 
                                   required maxlength="50" placeholder="Ingrese el nombre completo del usuario">
                            <div class="invalid-feedback">
                                El nombre es obligatorio
                            </div>
                            <div class="form-text">
                                <span id="contador-nombre">0</span>/50 caracteres
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="rol_id" class="form-label">Rol *</label>
                            <select class="form-select" id="rol_id" name="rol_id" required>
                                <option value="">Seleccionar rol...</option>
                                <option value="1">Administrador</option>
                                <option value="2">Empleado</option>
                            </select>
                            <div class="invalid-feedback">
                                Seleccione un rol
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="usu_codigo" class="form-label">Código de Usuario *</label>
                            <input type="text" class="form-control" id="usu_codigo" name="usu_codigo" 
                                   required maxlength="12" placeholder="Código numérico único">
                            <div class="invalid-feedback">
                                El código debe tener al menos 8 dígitos
                            </div>
                            <div class="form-text">Mínimo 8 dígitos, solo números</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="usu_password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="usu_password" name="usu_password" 
                                   placeholder="Contraseña del usuario">
                            <div class="invalid-feedback">
                                La contraseña debe tener al menos 6 caracteres
                            </div>
                            <div class="form-text" id="password-help">Mínimo 6 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   placeholder="Repetir contraseña">
                            <div class="invalid-feedback">
                                Las contraseñas no coinciden
                            </div>
                        </div>
                    </div>

                    <!-- Información de seguridad -->
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Información Importante:</h6>
                        <ul class="mb-0">
                            <li><strong>Administradores:</strong> Acceso completo al sistema</li>
                            <li><strong>Empleados:</strong> Acceso a ventas, clientes, productos y reparaciones</li>
                            <li><strong>Códigos:</strong> Deben ser únicos para cada usuario</li>
                            <li><strong>Seguridad:</strong> Las contraseñas se encriptan automáticamente</li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnGuardar">
                    <i class="bi bi-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/usuarios/index.js') ?>"></script>