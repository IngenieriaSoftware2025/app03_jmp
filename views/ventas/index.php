<div class="row justify-content-center p-3">
    <div class="col-lg-12">
        <div class="card custom-card shadow-lg" style="border-radius: 10px; border: 1px solid #28a745;">
            <div class="card-body p-3">
                <div class="row mb-3">
                    <h5 class="text-center mb-2">¡Bienvenido al Sistema de Ventas!</h5>
                    <h4 class="text-center mb-2 text-success">Gestión de Ventas - Morataya Celulares</h4>
                </div>

                <!-- Formulario para buscar cliente -->
                <div class="row justify-content-center p-4 shadow-lg mb-4">
                    <form id="FormBuscarCliente">
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
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sección de productos -->
<div id="seccionProductos" style="display: none;" class="row justify-content-center p-3">
    <div class="col-lg-12">
        <div class="card custom-card shadow-lg" style="border-radius: 10px; border: 1px solid #28a745;">
            <div class="card-body p-3">
                <h5 class="mb-3">Productos Disponibles</h5>
                
                <div class="table-responsive">
                    <table id="tablaProductosVenta" class="table table-striped table-hover table-bordered w-100">
                        <thead class="table-dark">
                            <tr>
                                <th>Producto</th>
                                <th>Marca</th>
                                <th>Tipo</th>
                                <th>Precio</th>
                                <th>Stock</th>
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

<!-- Carrito de ventas -->
<div class="row justify-content-center p-3">
    <div class="col-lg-12">
        <div class="card custom-card shadow-lg" style="border-radius: 10px; border: 1px solid #28a745;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Carrito de Venta</h5>
                    <h4>Total: <span id="totalVenta" class="text-success">Q 0.00</span></h4>
                </div>
                
                <div id="carritoVenta" class="mb-3">
                    <p class="text-muted text-center">Carrito vacío</p>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button class="btn btn-secondary" type="button" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Nueva Venta
                    </button>
                    <button class="btn btn-success" type="button" id="btnProcesarVenta" disabled>
                        <i class="bi bi-cart-check"></i> Procesar Venta
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historial de ventas -->
<div class="row justify-content-center p-3">
    <div class="col-lg-12">
        <div class="card custom-card shadow-lg" style="border-radius: 10px; border: 1px solid #28a745;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Historial de Ventas</h5>
                    <button class="btn btn-primary" type="button" onclick="cargarHistorial()">
                        <i class="bi bi-arrow-repeat"></i> Actualizar
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table id="tablaHistorial" class="table table-striped table-hover table-bordered w-100">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/ventas/index.js') ?>"></script>