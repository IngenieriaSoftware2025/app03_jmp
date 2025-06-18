<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">
                                <i class="bi bi-tags-fill me-2"></i>Gestión de Marcas
                            </h3>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-light" type="button" id="btnModalMarca">
                                <i class="bi bi-plus-circle me-1"></i>Nueva Marca
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3 align-items-center">
                        <div class="col-lg-3">
                            <button type="button" id="btnBuscar" class="btn btn-success">
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
                                       placeholder="Buscar marcas..." 
                                       style="width: 250px;">
                                <small class="text-muted align-self-center">Búsqueda en tiempo real</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive" id="contenedorTabla" style="display: none;">
                        <table id="tablaMarcas" class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No.</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Fecha Creación</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Marca OPTIMIZADO -->
<div class="modal fade" id="modalMarca" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="tituloModal">Nueva Marca</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formMarca" novalidate>
                    <input type="hidden" id="marca_id" name="marca_id">
                    <input type="hidden" id="situacion" name="situacion" value="1">
                    
                    <div class="mb-3">
                        <label for="marca_nombre" class="form-label">Nombre de la Marca *</label>
                        <input type="text" class="form-control" id="marca_nombre" name="marca_nombre" 
                               required maxlength="50" placeholder="Ingrese el nombre de la marca">
                        <div class="invalid-feedback">
                            El nombre de la marca es obligatorio
                        </div>
                        <div class="form-text">Mínimo 2 caracteres, máximo 50</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="marca_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="marca_descripcion" name="marca_descripcion" 
                                  rows="3" maxlength="200" placeholder="Descripción opcional de la marca"></textarea>
                        <div class="form-text">Opcional - Describe las características de la marca (máx. 200 caracteres)</div>
                    </div>

                    <!-- INDICADOR DE CARACTERES -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">
                                <span id="contador-nombre">0</span>/50 caracteres (nombre)
                            </small>
                            <small class="text-muted">
                                <span id="contador-descripcion">0</span>/200 caracteres (descripción)
                            </small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnGuardar">
                    <i class="bi bi-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Contador de caracteres para nombre
    const inputNombre = document.getElementById('marca_nombre');
    const contadorNombre = document.getElementById('contador-nombre');
    
    if (inputNombre && contadorNombre) {
        inputNombre.addEventListener('input', () => {
            const longitud = inputNombre.value.length;
            contadorNombre.textContent = longitud;
            
            // Cambiar color según la longitud
            if (longitud > 45) {
                contadorNombre.className = 'text-danger fw-bold';
            } else if (longitud > 35) {
                contadorNombre.className = 'text-warning';
            } else {
                contadorNombre.className = 'text-muted';
            }
        });
    }
    
    // Contador de caracteres para descripción
    const inputDescripcion = document.getElementById('marca_descripcion');
    const contadorDescripcion = document.getElementById('contador-descripcion');
    
    if (inputDescripcion && contadorDescripcion) {
        inputDescripcion.addEventListener('input', () => {
            const longitud = inputDescripcion.value.length;
            contadorDescripcion.textContent = longitud;
            
            // Cambiar color según la longitud
            if (longitud > 180) {
                contadorDescripcion.className = 'text-danger fw-bold';
            } else if (longitud > 150) {
                contadorDescripcion.className = 'text-warning';
            } else {
                contadorDescripcion.className = 'text-muted';
            }
        });
    }
});
</script>

<script src="<?= asset('build/js/marcas/index.js') ?>"></script>