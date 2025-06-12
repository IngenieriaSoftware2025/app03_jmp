<div class="row justify-content-center p-3">
    <div class="col-lg-12">
        <div class="card custom-card shadow-lg" style="border-radius: 10px; border: 1px solid #007bff;">
            <div class="card-body p-3">
                <div class="row mb-3">
                    <h5 class="text-center mb-2">¡Bienvenido a la Aplicación para el registro, modificación y eliminación de productos!</h5>
                    <h4 class="text-center mb-2 text-primary">Gestión de Productos - Morataya Celulares</h4>
                </div>

                <div class="row justify-content-center p-4 shadow-lg">
                    <form id="FormProductos">
                        <input type="hidden" id="producto_id" name="producto_id">
                        <input type="hidden" id="situacion" name="situacion" value="1">

                        <div class="row mb-3">
                            <div class="col-lg-4">
                                <label for="nombre_producto" class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" 
                                       placeholder="Ingrese el nombre del producto" required>
                                <div class="invalid-feedback">
                                    Por favor ingrese el nombre del producto.
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="marca_id" class="form-label">Marca *</label>
                                <select class="form-select" id="marca_id" name="marca_id" required>
                                    <option value="">Seleccione una marca...</option>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor seleccione una marca.
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="tipo_producto" class="form-label">Tipo de Producto *</label>
                                <select class="form-select" id="tipo_producto" name="tipo_producto" required>
                                    <option value="">Seleccione un tipo...</option>
                                    <option value="celular">Celular</option>
                                    <option value="repuesto">Repuesto</option>
                                    <option value="servicio">Servicio</option>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor seleccione un tipo de producto.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-4">
                                <label for="modelo" class="form-label">Modelo</label>
                                <input type="text" class="form-control" id="modelo" name="modelo" 
                                       placeholder="Ej: iPhone 15 Pro Max">
                                <div class="form-text">Opcional para servicios</div>
                            </div>
                            <div class="col-lg-4">
                                <label for="precio_compra" class="form-label">Precio de Compra *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Q</span>
                                    <input type="number" class="form-control" id="precio_compra" name="precio_compra" 
                                           step="0.01" min="0" placeholder="0.00" required>
                                </div>
                                <div class="invalid-feedback">
                                    Ingrese un precio de compra válido.
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="precio_venta" class="form-label">Precio de Venta *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Q</span>
                                    <input type="number" class="form-control" id="precio_venta" name="precio_venta" 
                                           step="0.01" min="0.01" placeholder="0.00" required>
                                </div>
                                <div class="invalid-feedback">
                                    El precio de venta debe ser mayor al precio de compra.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-4">
                                <label for="stock_actual" class="form-label">Stock Actual *</label>
                                <input type="number" class="form-control" id="stock_actual" name="stock_actual" 
                                       min="0" placeholder="0" required>
                                <div class="invalid-feedback">
                                    Ingrese un stock válido.
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="stock_minimo" class="form-label">Stock Mínimo *</label>
                                <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" 
                                       min="0" placeholder="0" required>
                                <div class="invalid-feedback">
                                    Ingrese un stock mínimo válido.
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" 
                                          rows="1" placeholder="Descripción opcional del producto"></textarea>
                            </div>
                        </div>

                        <!-- Indicadores de ganancia y stock -->
                        <div class="row mb-3" id="indicadores" style="display: none;">
                            <div class="col-lg-4">
                                <div class="alert alert-info">
                                    <strong>Ganancia:</strong> <span id="ganancia">Q 0.00</span>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="alert alert-success">
                                    <strong>% Ganancia:</strong> <span id="porcentaje_ganancia">0%</span>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="alert" id="alerta_stock">
                                    <strong>Estado Stock:</strong> <span id="estado_stock">Normal</span>
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-center mt-4">
                            <div class="col-auto">
                                <button class="btn btn-success" type="submit" id="BtnGuardar">
                                    <i class="bi bi-save"></i> Guardar Producto
                                </button>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-warning d-none" type="button" id="BtnModificar">
                                    <i class="bi bi-pencil"></i> Actualizar Producto
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

<div class="row justify-content-center p-3">
    <div class="col-lg-12">
        <div class="card custom-card shadow-lg" style="border-radius: 10px; border: 1px solid #007bff;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Productos Registrados</h3>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" id="btnActualizar" onclick="buscarProductos()">
                            <i class="bi bi-arrow-clockwise"></i> Actualizar
                        </button>
                        <button class="btn btn-warning" id="btnStockBajo">
                            <i class="bi bi-exclamation-triangle"></i> Stock Bajo
                        </button>
                        <input type="text" class="form-control form-control-sm" 
                               id="buscarProducto" name="buscarProducto"
                               placeholder="Buscar producto..." style="width: 200px;">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered w-100" id="TableProductos">
                        <thead class="table-dark">
                            <tr>
                                <th>No.</th>
                                <th>Producto</th>
                                <th>Marca</th>
                                <th>Tipo</th>
                                <th>Modelo</th>
                                <th>P. Compra</th>
                                <th>P. Venta</th>
                                <th>Stock</th>
                                <th>Stock Min</th>
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

<script src="<?=asset('build/js/productos/index.js')?>"></script>