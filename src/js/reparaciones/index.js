import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Variables globales
const form = document.getElementById("FormReparaciones");
const btnGuardar = document.getElementById("BtnGuardar");
const btnLimpiar = document.getElementById("BtnLimpiar");
const inputTelefono = document.getElementById("cliente_telefono");
const btnBuscarCliente = document.getElementById("btnBuscarCliente");
const infoCliente = document.getElementById("infoCliente");

let clienteSeleccionado = null;
let reparacionesData = [];

// Tabla de reparaciones
const tabla = new DataTable("#TableReparaciones", {
    language: lenguaje,
    data: [],
    pageLength: 25,
    columns: [
        { title: "No.", data: null, render: (data, type, row, meta) => meta.row + 1, width: "5%" },
        { title: "Cliente", data: "cliente_nombres", defaultContent: "", width: "15%",
          render: (data, type, row) => `${data} ${row.cliente_apellidos || ''}`
        },
        { title: "Teléfono", data: "cliente_telefono", defaultContent: "", width: "10%" },
        { title: "Fecha", data: "fecha_venta", defaultContent: "", width: "12%",
          render: (data) => {
              if (!data) return '';
              const fecha = new Date(data);
              return fecha.toLocaleDateString('es-GT');
          }
        },
        { title: "Descripción", data: "descripcion", defaultContent: "", width: "30%",
          render: (data) => {
              if (!data) return '';
              return data.length > 80 ? `<span title="${data}">${data.substring(0, 80)}...</span>` : data;
          }
        },
        { title: "Costo", data: "total", width: "10%",
          render: (data) => `Q ${parseFloat(data || 0).toFixed(2)}`
        },
        { title: "Estado", data: "descripcion", width: "8%",
          render: (data) => {
              if (!data) return '<span class="badge bg-secondary">Nuevo</span>';
              if (data.includes('ENTREGADA')) return '<span class="badge bg-success">Entregada</span>';
              if (data.includes('FINALIZADA')) return '<span class="badge bg-info">Finalizada</span>';
              if (data.includes('Estado:')) return '<span class="badge bg-warning">En proceso</span>';
              return '<span class="badge bg-secondary">Recibida</span>';
          }
        },
        {
            title: "Acciones", data: "venta_id", orderable: false, width: "10%",
            render: (data, type, row, meta) => {
                const estado = row.descripcion || '';
                let botones = `
                    <button class="btn btn-info btn-sm ver-detalle" data-index="${meta.row}" title="Ver detalle">
                        <i class="bi bi-eye"></i>
                    </button>
                `;
                
                if (!estado.includes('ENTREGADA')) {
                    if (!estado.includes('FINALIZADA')) {
                        botones += `
                            <button class="btn btn-warning btn-sm actualizar-estado ms-1" data-id="${data}" title="Actualizar estado">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-success btn-sm finalizar ms-1" data-id="${data}" title="Finalizar">
                                <i class="bi bi-check-circle"></i>
                            </button>
                        `;
                    } else {
                        botones += `
                            <button class="btn btn-primary btn-sm entregar ms-1" data-id="${data}" title="Entregar">
                                <i class="bi bi-box-arrow-right"></i>
                            </button>
                        `;
                    }
                }
                
                return botones;
            }
        }
    ]
});

// Función para mostrar mensajes
const mostrarMensaje = (tipo, titulo, texto) => {
    const config = {
        icon: tipo,
        title: titulo,
        text: texto
    };
    
    if (tipo === 'success') {
        config.timer = 3000;
        config.showConfirmButton = false;
        config.toast = true;
        config.position = 'top-end';
    } else {
        config.confirmButtonText = 'OK';
        config.confirmButtonColor = tipo === 'error' ? '#e74c3c' : '#3498db';
    }
    
    Swal.fire(config);
};

// Buscar cliente por teléfono
const buscarCliente = async () => {
    const telefono = inputTelefono.value.trim();
    
    if (!telefono || telefono.length !== 8) {
        mostrarMensaje('warning', 'Atención', 'Ingrese un teléfono válido de 8 dígitos');
        return;
    }
    
    try {
        const datos = new URLSearchParams();
        datos.append('telefono', telefono);
        
        const respuesta = await fetch('./clientes/buscarPorTelefonoAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1) {
            clienteSeleccionado = resultado.data;
            mostrarInfoCliente(clienteSeleccionado);
        } else {
            mostrarMensaje('error', 'Cliente no encontrado', resultado.mensaje);
            limpiarInfoCliente();
        }
    } catch (error) {
        mostrarMensaje('error', 'Error', 'Error de conexión');
    }
};

// Mostrar información del cliente
const mostrarInfoCliente = (cliente) => {
    infoCliente.innerHTML = `
        <div class="alert alert-success">
            <h5><i class="bi bi-person-check"></i> Cliente: ${cliente.cliente_nombres} ${cliente.cliente_apellidos}</h5>
            <p class="mb-0"><strong>Teléfono:</strong> ${cliente.cliente_telefono}</p>
            ${cliente.cliente_correo ? `<p class="mb-0"><strong>Correo:</strong> ${cliente.cliente_correo}</p>` : ''}
            ${cliente.cliente_direccion ? `<p class="mb-0"><strong>Dirección:</strong> ${cliente.cliente_direccion}</p>` : ''}
        </div>
    `;
    infoCliente.style.display = 'block';
    document.getElementById('cliente_id').value = cliente.cliente_id;
};

// Limpiar información del cliente
const limpiarInfoCliente = () => {
    clienteSeleccionado = null;
    infoCliente.style.display = 'none';
    infoCliente.innerHTML = '';
    document.getElementById('cliente_id').value = '';
};

// Validar teléfono (solo números)
const validarTelefono = () => {
    const input = document.getElementById("cliente_telefono");
    const valor = input.value.trim();
    
    const soloNumeros = valor.replace(/\D/g, '');
    
    if (input.value !== soloNumeros) {
        input.value = soloNumeros;
    }
    
    if (soloNumeros.length === 8) {
        input.classList.add("is-valid");
        input.classList.remove("is-invalid");
    } else if (soloNumeros.length > 0) {
        input.classList.add("is-invalid");
        input.classList.remove("is-valid");
    } else {
        input.classList.remove("is-valid", "is-invalid");
    }
};

// Buscar reparaciones
const buscarReparaciones = async () => {
    console.log('🔍 Iniciando búsqueda de reparaciones...');
    
    try {
        const respuesta = await fetch('./reparaciones/buscarAPI', {
            method: 'POST'
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1) {
            reparacionesData = resultado.data || [];
            tabla.clear().rows.add(reparacionesData).draw();
            
            if (reparacionesData.length > 0) {
                mostrarMensaje('success', 'Reparaciones cargadas', `${reparacionesData.length} reparaciones encontradas`);
            } else {
                mostrarMensaje('info', 'Sin reparaciones', 'No hay reparaciones registradas');
            }
        } else {
            mostrarMensaje('error', 'Error', resultado.mensaje);
        }
    } catch (error) {
        mostrarMensaje('error', 'Error', 'Error de conexión');
    }
};

// Guardar reparación
const guardarReparacion = async (e) => {
    e.preventDefault();
    
    if (!clienteSeleccionado) {
        mostrarMensaje('error', 'Error', 'Debe seleccionar un cliente');
        return;
    }
    
    if (!validarFormulario(form, ["cliente_id"])) {
        mostrarMensaje('error', 'Error', 'Complete los campos obligatorios');
        return;
    }

    btnGuardar.disabled = true;
    
    try {
        const datos = new URLSearchParams();
        datos.append('cliente_id', clienteSeleccionado.cliente_id);
        datos.append('descripcion', document.getElementById('descripcion').value.trim());
        datos.append('equipo_marca', document.getElementById('equipo_marca').value.trim());
        datos.append('equipo_modelo', document.getElementById('equipo_modelo').value.trim());
        datos.append('costo_estimado', document.getElementById('costo_estimado').value || '0');
        
        const respuesta = await fetch('./reparaciones/guardarAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();

        if (resultado.codigo === 1) {
            mostrarMensaje('success', 'Éxito', 'Reparación registrada correctamente');
            limpiarFormulario();
            buscarReparaciones();
        } else {
            mostrarMensaje('warning', 'Atención', resultado.mensaje);
        }
    } catch (error) {
        mostrarMensaje('error', 'Error', 'Problema de conexión');
    }
    
    btnGuardar.disabled = false;
};

// Ver detalle de reparación
const verDetalle = (e) => {
    const index = parseInt(e.target.closest('.ver-detalle').dataset.index);
    const reparacion = reparacionesData[index];
    
    if (!reparacion) {
        mostrarMensaje('error', 'Error', 'No se encontró la reparación');
        return;
    }
    
    Swal.fire({
        title: 'Detalle de Reparación',
        html: `
            <div class="text-start">
                <p><strong>Cliente:</strong> ${reparacion.cliente_nombres} ${reparacion.cliente_apellidos}</p>
                <p><strong>Teléfono:</strong> ${reparacion.cliente_telefono}</p>
                <p><strong>Fecha:</strong> ${new Date(reparacion.fecha_venta).toLocaleDateString('es-GT')}</p>
                <p><strong>Descripción:</strong> ${reparacion.descripcion}</p>
                <p><strong>Costo:</strong> Q ${parseFloat(reparacion.total).toFixed(2)}</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Cerrar'
    });
};

// Actualizar estado de reparación
const actualizarEstado = async (e) => {
    const id = e.target.closest('.actualizar-estado').dataset.id;
    
    const { value: formValues } = await Swal.fire({
        title: 'Actualizar Estado',
        html: `
            <div class="mb-3">
                <label for="estado" class="form-label">Estado:</label>
                <select id="estado" class="form-select">
                    <option value="En revisión">En revisión</option>
                    <option value="Esperando repuestos">Esperando repuestos</option>
                    <option value="En reparación">En reparación</option>
                    <option value="Reparación completada">Reparación completada</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones:</label>
                <textarea id="observaciones" class="form-control" rows="3"></textarea>
            </div>
        `,
        focusConfirm: false,
        preConfirm: () => {
            return {
                estado: document.getElementById('estado').value,
                observaciones: document.getElementById('observaciones').value
            }
        },
        showCancelButton: true,
        confirmButtonText: 'Actualizar',
        cancelButtonText: 'Cancelar'
    });

    if (formValues) {
        try {
            const datos = new URLSearchParams();
            datos.append('reparacion_id', id);
            datos.append('estado', formValues.estado);
            datos.append('observaciones', formValues.observaciones);
            
            const respuesta = await fetch('./reparaciones/actualizarEstadoAPI', {
                method: 'POST',
                body: datos
            });
            
            const resultado = await respuesta.json();
            
            if (resultado.codigo === 1) {
                mostrarMensaje('success', 'Éxito', 'Estado actualizado correctamente');
                buscarReparaciones();
            } else {
                mostrarMensaje('error', 'Error', resultado.mensaje);
            }
        } catch (error) {
            mostrarMensaje('error', 'Error', 'Error de conexión');
        }
    }
};

// Finalizar reparación
const finalizarReparacion = async (e) => {
    const id = e.target.closest('.finalizar').dataset.id;
    
    const { value: formValues } = await Swal.fire({
        title: 'Finalizar Reparación',
        html: `
            <div class="mb-3">
                <label for="costo_final" class="form-label">Costo Final:</label>
                <input type="number" id="costo_final" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="mb-3">
                <label for="observaciones_final" class="form-label">Observaciones finales:</label>
                <textarea id="observaciones_final" class="form-control" rows="3"></textarea>
            </div>
        `,
        focusConfirm: false,
        preConfirm: () => {
            const costoFinal = document.getElementById('costo_final').value;
            if (!costoFinal || costoFinal <= 0) {
                Swal.showValidationMessage('Ingrese un costo final válido');
                return false;
            }
            return {
                costo_final: costoFinal,
                observaciones: document.getElementById('observaciones_final').value
            }
        },
        showCancelButton: true,
        confirmButtonText: 'Finalizar',
        cancelButtonText: 'Cancelar'
    });

    if (formValues) {
        try {
            const datos = new URLSearchParams();
            datos.append('reparacion_id', id);
            datos.append('costo_final', formValues.costo_final);
            datos.append('observaciones', formValues.observaciones);
            
            const respuesta = await fetch('./reparaciones/finalizarAPI', {
                method: 'POST',
                body: datos
            });
            
            const resultado = await respuesta.json();
            
            if (resultado.codigo === 1) {
                mostrarMensaje('success', 'Éxito', 'Reparación finalizada correctamente');
                buscarReparaciones();
            } else {
                mostrarMensaje('error', 'Error', resultado.mensaje);
            }
        } catch (error) {
            mostrarMensaje('error', 'Error', 'Error de conexión');
        }
    }
};

// Entregar reparación
const entregarReparacion = async (e) => {
    const id = e.target.closest('.entregar').dataset.id;
    
    const confirmacion = await Swal.fire({
        title: '¿Entregar reparación?',
        text: 'Marcar como entregada al cliente',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, entregar',
        cancelButtonText: 'Cancelar'
    });

    if (confirmacion.isConfirmed) {
        try {
            const datos = new URLSearchParams();
            datos.append('reparacion_id', id);
            
            const respuesta = await fetch('./reparaciones/entregarAPI', {
                method: 'POST',
                body: datos
            });
            
            const resultado = await respuesta.json();
            
            if (resultado.codigo === 1) {
                mostrarMensaje('success', 'Éxito', 'Reparación marcada como entregada');
                buscarReparaciones();
            } else {
                mostrarMensaje('error', 'Error', resultado.mensaje);
            }
        } catch (error) {
            mostrarMensaje('error', 'Error', 'Error de conexión');
        }
    }
};

// Limpiar formulario
const limpiarFormulario = () => {
    form.reset();
    limpiarInfoCliente();
    
    form.querySelectorAll('.form-control, .form-select').forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
    });
};

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM cargado, iniciando aplicación de reparaciones...');
    
    buscarReparaciones();
    
    form?.addEventListener("submit", guardarReparacion);
    btnLimpiar?.addEventListener("click", limpiarFormulario);
    btnBuscarCliente?.addEventListener('click', buscarCliente);
    
    inputTelefono?.addEventListener("input", validarTelefono);
    inputTelefono?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') buscarCliente();
    });
    
    // Event listeners para acciones de la tabla
    tabla.on("click", ".ver-detalle", verDetalle);
    tabla.on("click", ".actualizar-estado", actualizarEstado);
    tabla.on("click", ".finalizar", finalizarReparacion);
    tabla.on("click", ".entregar", entregarReparacion);
});

// Exportar funciones globales
window.buscarReparaciones = buscarReparaciones;
window.buscarCliente = buscarCliente;