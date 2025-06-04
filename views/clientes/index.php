<div class="row justify-content-center p-3">
    <div class="col-lg-10">
        <div class="card custom-card shadow-lg" style="border-radius: 10px; border: 1px solid #007bff;">
            <div class="card-body p-3">
                <div class="row mb-3">
                    <h5 class="text-center mb-2">¡Bienvenido a la Aplicación para el registro, modificación y eliminación de cliente!</h5>
                    <h4 class="text-center mb-2 text-primary">Manipulacion de clientes</h4>
                </div>

                <div class="row justify-content-center p-5 shadow-lg">

                    <form id="FormClientes">
                        <input type="hidden" id="cliente_id" name="cliente_id">

                        <div class="row mb-3 justify-content-center">
                            <div class="col-lg-6">
                                <label for="nombres" class="form-label">Nombres</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Ingrese aca sus nombres">
                            </div>
                            <div class="col-lg-6">
                                <label for="apellidos" class="form-label">Apellidos</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Ingrese aca sus apellidos">
                            </div>
                        </div>

                        <div class="row mb-3 justify-content-center">
                            <div class="col-lg-6">
                                <label for="nit" class="form-label">NIT</label>
                                <input type="number" class="form-control" id="nit" name="nit" placeholder="Ingrese aca su nit">
                            </div>
                            <div class="col-lg-6">
                                <label for="telefono" class="form-label">Telefono</label>
                                <input type="number" class="form-control" id="telefono" name="telefono" placeholder="Ingrese aca su numero de telefono sin el +502">
                            </div>
                        </div>

                        <div class="row mb-3 justify-content-center mb-3">
                            <div class="col-lg-6">
                                <label for="correo" class="form-label">Correo</label>
                                <input type="email" class="form-control" id="correo" name="correo" placeholder="Ingrese aca su correo ejemplo@ejemplo.com">
                            </div>
                        </div>


                        <div class="row justify-content-center mt-5">
                            <div class="col-auto">
                                <button class="btn btn-success" type="submit" id="BtnGuardar"><i class="bi bi-floppy"></i>
                                     Guardar
                                </button>
                            </div>

                            <div class="col-auto ">
                                <button class="btn btn-warning d-none" type="button" id="BtnModificar"> <i class="bi bi-vector-pen"></i>
                                     Modificar
                                </button>
                            </div>

                            <div class="col-auto">
                                <button class="btn btn-secondary" type="reset" id="BtnLimpiar"> <i class="bi bi-x-octagon-fill"></i>
                                     Limpiar
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
    <div class="col-lg-10">
        <div class="card custom-card shadow-lg" style="border-radius: 10px; border: 1px solid #007bff;">
            <div class="card-body p-3">
                <h3 class="text-center">clientes existentes</h3>

                <div class="table-responsive p-2">
                    <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TableClientes">
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
</div>




<script src="<?= asset('build/js/clientes/index.js') ?>"></script>