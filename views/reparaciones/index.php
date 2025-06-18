<div class="row justify-content-center p-3">
    <div class="col-lg-12">
        <div class="card custom-card shadow-lg" style="border-radius: 10px; border: 1px solid #fd7e14;">
            <div class="card-body p-3">
                <div class="row mb-3">
                    <h4 class="text-center mb-2" style="color: #fd7e14;">Sistema de Reparaciones - Morataya Celulares</h4>
                    <p class="text-center text-muted">Gestión completa de reparaciones de equipos</p>
                </div>

                <!-- Formulario de nueva reparación -->
                <div class="row justify-content-center p-4 shadow-lg mb-4">
                    <form id="FormReparaciones">
                        <input type="hidden" id="cliente_id" name="cliente_id">

                        <!-- Búsqueda de cliente -->
                        <div class="row mb-3">
                            <div class="col-lg-4">
                                <label for="cliente_telefono" class="form-label">Teléfono del Cliente *</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="cliente_telefono" 
                                           placeholder="Ej: 41234567" maxlength="8" required>
                                    <button class="btn btn-primary" type="button" id="btnBuscarCliente">
                                        <i class="bi bi-search"></i> Buscar
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Ingrese un teléfono válido de 8 dígitos.
                                </div>
                            </div>
                        </div>

                        <!-- Información del cliente -->
                        <div id="infoCliente" style="display: none;" class="row mb-3">
                            <!-- Se llena dinámicamente -->
                        </div>

                        <!-- Información del equipo -->
                        <div class="row mb-3">
                            <div class="col-lg-4">
                                <label for="equipo_marca" class="form-label">Marca del Equipo</label>
                                <input type="text" class="form-control" id="equipo_marca" name="equipo_marca"
                                       placeholder="Ej: Samsung, iPhone, Xiaomi">
                            </div>
                            <div class="col-lg-4">
                                <label for="equipo_modelo" class="form-label">Modelo del Equipo</label>
                                <input type="text" class="form-control" id="equipo_modelo" name="equipo_modelo"
                                       placeholder="Ej: Galaxy S21, iPhone 12 Pro">
                            </div>
                            <div class="col-lg-4">
                                <label for="costo_estimado" class="form-label">Costo Estimado</label>
                                <div class="input-group">
                                    <span class="input-group-text">Q</span>
                                    <input type="number" class="form-control" id="costo_estimado" name="costo_estimado"
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <!-- Descripción del problema -->
                        <div class="row mb-3">
                            <div class="col-lg-12">
                                <label for="descripcion" class="form-label">Descripción del Problema *</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" 
                                          rows="4" placeholder="Describa detalladamente el problema del equipo..." required></textarea>
                                <div class="invalid-feedback">
                                    La descripción del problema es obligatoria.
                                </div>
                                <div class="form-text">Sea específico: ¿pantalla rota?, ¿no enciende?, ¿problemas de batería?, etc.</div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="row justify-content-center mt-4">
                            <div class="col-auto">
                                <button class="btn btn-success" type="submit" id="BtnGuardar">
                                    <i class="bi bi-save"></i> Registrar Reparación
                                </button>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-secondary" type="reset" id="BtnLimpiar">
                                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de reparaciones -->
<div class="row justify-content-center p-3">
    <div class="col-lg-12">
        <div class="card custom-card shadow-lg" style="border-radius: 10px; border: 1px solid #fd7e14;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Reparaciones Registradas</h3>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" onclick="buscarReparaciones()">
                            <i class="bi bi-arrow-clockwise"></i> Actualizar
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-funnel"></i> Filtros
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="filtrarPorEstado('nuevo')">Nuevas</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filtrarPorEstado('proceso')">En Proceso</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filtrarPorEstado('finalizada')">Finalizadas</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filtrarPorEstado('entregada')">Entregadas</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="buscarReparaciones()">Mostrar Todas</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered w-100" id="TableReparaciones">
                        <thead class="table-dark">
                            <tr>
                                <th>No.</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th>Costo</th>
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

<script src="<?= asset('build/js/reparaciones/index.js') ?>"></script>