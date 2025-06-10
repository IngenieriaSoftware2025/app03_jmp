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
                    <div class="row mb-3">
                        <div class="col-lg-4">
                            <button type="button" id="btnBuscar" class="btn btn-success">
                                <i class="bi bi-search me-1"></i>Buscar
                            </button>
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

<!-- Modal para Marca -->
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
                    
                    <div class="mb-3">
                        <label for="marca_nombre" class="form-label">Nombre de la Marca</label>
                        <input type="text" class="form-control" id="marca_nombre" name="marca_nombre" required maxlength="50">
                        <div class="invalid-feedback">
                            El nombre de la marca es obligatorio
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="marca_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="marca_descripcion" name="marca_descripcion" rows="3" maxlength="200"></textarea>
                        <div class="form-text">Opcional - Describe las características de la marca</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardar">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('./build/js/marcas/index.js') ?>"></script>