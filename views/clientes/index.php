<div class="row justify-content-center p-3">
    <div class="col-lg-12">
        <div class="card custom-card shadow-lg" style="border-radius: 10px; border: 1px solid #007bff;">
            <div class="card-body p-3">
                <div class="row mb-3">
                    <h5 class="text-center mb-2">¡Bienvenido a la Aplicación para el registro, modificación y eliminación de clientes!</h5>
                    <h4 class="text-center mb-2 text-primary">Gestión de Clientes - Morataya Celulares</h4>
                </div>

                <div class="row justify-content-center p-4 shadow-lg">
                    <form id="FormClientes">
                        <input type="hidden" id="cliente_id" name="cliente_id">

                        <div class="row mb-3">
                            <div class="col-lg-4">
                                <label for="cliente_nombres" class="form-label">Nombres *</label>
                                <input type="text" class="form-control" id="cliente_nombres" name="cliente_nombres" 
                                       placeholder="Ingrese los nombres del cliente" required>
                                <div class="invalid-feedback">
                                    Por favor ingrese los nombres del cliente.
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="cliente_apellidos" class="form-label">Apellidos *</label>
                                <input type="text" class="form-control" id="cliente_apellidos" name="cliente_apellidos" 
                                       placeholder="Ingrese los apellidos del cliente" required>
                                <div class="invalid-feedback">
                                    Por favor ingrese los apellidos del cliente.
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="cliente_nit" class="form-label">NIT</label>
                                <input type="text" class="form-control" id="cliente_nit" name="cliente_nit" 
                                       placeholder="Ej: 12345678-9">
                                <div class="invalid-feedback">
                                    El NIT ingresado no es válido.
                                </div>
                                <div class="form-text">Formato: 12345678-9 o 12345678-K</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-4">
                                <label for="cliente_telefono" class="form-label">Teléfono *</label>
                                <input type="text" class="form-control" id="cliente_telefono" name="cliente_telefono" 
                                       placeholder="Ej: 41234567" maxlength="8" required>
                                <div class="invalid-feedback">
                                    Ingrese un teléfono válido de 8 dígitos.
                                </div>
                                <div class="form-text">8 dígitos sin código de país</div>
                            </div>
                            <div class="col-lg-4">
                                <label for="cliente_correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="cliente_correo" name="cliente_correo" 
                                       placeholder="ejemplo@correo.com">
                                <div class="invalid-feedback">
                                    Ingrese un correo electrónico válido.
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="cliente_direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="cliente_direccion" name="cliente_direccion" 
                                       placeholder="Ingrese la dirección del cliente">
                            </div>
                        </div>

                        <div class="row justify-content-center mt-4">
                            <div class="col-auto">
                                <button class="btn btn-success" type="submit" id="BtnGuardar">
                                    <i class="bi bi-save"></i> Guardar Cliente
                                </button>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-warning d-none" type="button" id="BtnModificar">
                                    <i class="bi bi-pencil"></i> Actualizar Cliente
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
                    <h3 class="mb-0">Clientes Registrados</h3>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" id="btnActualizar">Actualizar</button>
                            <i class="bi bi-arrow-clockwise"></i> Actualizar
                        </button>
                        <input type="text" class="form-control form-control-sm" id="buscarCliente" 
                               placeholder="Buscar cliente..." style="width: 200px;">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered w-100" id="TableClientes">
                        <thead class="table-dark">
                            <tr>
                                <th>No.</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>NIT</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Dirección</th>
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

<script src="<?=asset('build/js/clientes/index.js')?>"></script>