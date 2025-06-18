import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

const form = document.getElementById("FormClientes");
const btnGuardar = document.getElementById("BtnGuardar");
const btnModificar = document.getElementById("BtnModificar");
const btnLimpiar = document.getElementById("BtnLimpiar");

let clientesData = [];

const tabla = new DataTable("#TableClientes", {
    language: lenguaje,
    data: [],
    pageLength: 25,
    columns: [
        { title: "No.", data: null, render: (data, type, row, meta) => meta.row + 1, width: "5%" },
        { title: "Nombres", data: "cliente_nombres", defaultContent: "", width: "15%" },
        { title: "Apellidos", data: "cliente_apellidos", defaultContent: "", width: "15%" },
        { 
            title: "NIT", data: "cliente_nit", defaultContent: "", width: "12%",
            render: (data) => data?.trim() ? data : '<em class="text-muted">Sin NIT</em>'
        },
        { 
            title: "Teléfono", data: "cliente_telefono", defaultContent: "", width: "10%",
            render: (data) => data ? `<code>${data}</code>` : ''
        },
        { 
            title: "Correo", data: "cliente_correo", defaultContent: "", width: "18%",
            render: (data) => {
                if (!data?.trim()) return '<em class="text-muted">Sin correo</em>';
                return data.length > 25 ? `<span title="${data}">${data.substring(0, 25)}...</span>` : data;
            }
        },
        { 
            title: "Dirección", data: "cliente_direccion", defaultContent: "", width: "15%",
            render: (data) => {
                if (!data?.trim()) return '<em class="text-muted">Sin dirección</em>';
                return data.length > 30 ? `<span title="${data}">${data.substring(0, 30)}...</span>` : data;
            }
        },
        {
            title: "Acciones", data: "cliente_id", orderable: false, width: "10%",
            render: (data, type, row, meta) => data ? `
                <button class="btn btn-warning btn-sm modificar" data-index="${meta.row}" title="Modificar">Modificar</button>
                <button class="btn btn-danger btn-sm eliminar ms-1" data-id="${data}" title="Eliminar">Eliminar</button>
            ` : ''
        }
    ]
});

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

function validarNit() {
    const nitInput = document.getElementById('cliente_nit');
    const nitValue = nitInput.value.trim();

    let nd, add = 0;

    if (nd = /^(\d+)-?([\dkK])$/.exec(nitValue)) {
        nd[2] = (nd[2].toLowerCase() === 'k') ? 10 : parseInt(nd[2], 10);

        for (let i = 0; i < nd[1].length; i++) {
            add += ((((i - nd[1].length) * -1) + 1) * parseInt(nd[1][i], 10));
        }
        return ((11 - (add % 11)) % 11) === nd[2];
    } else {
        return false;
    }
}

const validarNitInput = () => {
    const nitInput = document.getElementById('cliente_nit');
    
    if (!nitInput.value.trim()) {
        nitInput.classList.remove('is-valid', 'is-invalid');
        return;
    }
    
    if (validarNit()) {
        nitInput.classList.add('is-valid');
        nitInput.classList.remove('is-invalid');
    } else {
        nitInput.classList.remove('is-valid');
        nitInput.classList.add('is-invalid');
    }
};

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

const buscarClientes = async () => {
    console.log('🔍 Iniciando búsqueda de clientes...');
    
    try {
        const respuesta = await fetch('./clientes/buscarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        });
        
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1) {
            if (resultado.data && resultado.data.length > 0) {
                clientesData = resultado.data;
                tabla.clear().rows.add(resultado.data).draw();
                mostrarMensaje('success', 'Éxito', `Se encontraron ${resultado.data.length} clientes`);
            } else {
                clientesData = [];
                tabla.clear().draw();
                mostrarMensaje('info', 'Sin clientes', 'No hay clientes registrados. Agregue el primer cliente.');
            }
        } else {
            clientesData = [];
            tabla.clear().draw();
            mostrarMensaje('info', 'Información', resultado.mensaje || 'No hay clientes disponibles');
        }
    } catch (error) {
        console.error('❌ Error completo:', error);
        clientesData = [];
        mostrarMensaje('error', 'Error', `Problema de conexión: ${error.message}`);
        tabla.clear().draw();
    }
};

const guardarCliente = async (e) => {
    e.preventDefault();
    
    if (!validarFormulario(form, ["cliente_id"])) {
        mostrarMensaje('error', 'Error', 'Complete los campos obligatorios');
        return;
    }

    btnGuardar.disabled = true;
    
    try {
        const datos = new FormData(form);
        const respuesta = await fetch('./clientes/guardarAPI', {
            method: 'POST',
            body: datos
        });
        const resultado = await respuesta.json();

        if (resultado.codigo === 1) {
            mostrarMensaje('success', 'Éxito', 'Cliente guardado');
            limpiarFormulario();
            buscarClientes();
        } else {
            mostrarMensaje('error', 'Error', resultado.mensaje);
        }
    } catch (error) {
        mostrarMensaje('error', 'Error', 'Problema de conexión');
    }
    
    btnGuardar.disabled = false;
};

const modificarCliente = async (e) => {
    e.preventDefault();
    
    const clienteId = document.getElementById('cliente_id').value;
    
    if (!clienteId) {
        mostrarMensaje('error', 'Error', 'No se ha seleccionado un cliente para modificar');
        return;
    }
    
    if (!validarFormulario(form)) {
        mostrarMensaje('error', 'Error', 'Complete los campos obligatorios');
        return;
    }

    btnModificar.disabled = true;
    
    try {
        const datos = new URLSearchParams();
        datos.append('cliente_id', clienteId);
        datos.append('cliente_nombres', document.getElementById('cliente_nombres').value.trim());
        datos.append('cliente_apellidos', document.getElementById('cliente_apellidos').value.trim());
        datos.append('cliente_nit', document.getElementById('cliente_nit').value.trim());
        datos.append('cliente_telefono', document.getElementById('cliente_telefono').value.trim());
        datos.append('cliente_correo', document.getElementById('cliente_correo').value.trim());
        datos.append('cliente_direccion', document.getElementById('cliente_direccion').value.trim());
        datos.append('cliente_situacion', '1');
        
        const respuesta = await fetch('./clientes/modificarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: resultado.mensaje,
                timer: 3000,
                showConfirmButton: false
            });
            limpiarFormulario();
            buscarClientes();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: resultado.mensaje,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#f39c12'
            });
        }
    } catch (error) {
        console.error('Error técnico:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de sistema',
            text: 'Ocurrió un problema técnico. Intente nuevamente.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#e74c3c'
        });
    }
    
    btnModificar.disabled = false;
};

const eliminarCliente = async (e) => {
    const id = e.target.dataset.id;
    
    if (!id) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'ID de cliente no válido'
        });
        return;
    }
    
    const confirmacion = await Swal.fire({
        title: '¿Eliminar cliente?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d'
    });

    if (!confirmacion.isConfirmed) return;

    try {
        const datos = new URLSearchParams();
        datos.append('cliente_id', id);
        
        const respuesta = await fetch('./clientes/eliminarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1) {
            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: resultado.mensaje,
                timer: 3000,
                showConfirmButton: false
            });
            buscarClientes();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No se puede eliminar',
                text: resultado.mensaje,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#f39c12'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de sistema',
            text: 'Ocurrió un problema técnico. Intente nuevamente.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#e74c3c'
        });
    }
};

const llenarFormulario = (e) => {
    try {
        const index = parseInt(e.target.dataset.index);
        const cliente = clientesData[index];
        
        if (!cliente) {
            mostrarMensaje('error', 'Error', 'No se encontró el cliente');
            return;
        }
        
        ['cliente_id', 'cliente_nombres', 'cliente_apellidos', 'cliente_nit', 
         'cliente_telefono', 'cliente_correo', 'cliente_direccion', 'cliente_situacion']
        .forEach(campo => {
            const input = document.getElementById(campo);
            if (input) input.value = cliente[campo] || '';
        });

        btnGuardar.classList.add("d-none");
        btnModificar.classList.remove("d-none");
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
    } catch (error) {
        console.error('❌ Error al llenar formulario:', error);
        mostrarMensaje('error', 'Error', 'No se pudo cargar los datos del cliente');
    }
};

const limpiarFormulario = () => {
    form.reset();
    document.getElementById('cliente_id').value = '';
    document.getElementById('cliente_situacion').value = '1';
    
    btnGuardar.classList.remove("d-none");
    btnModificar.classList.add("d-none");
    
    form.querySelectorAll('.form-control').forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
    });
};

const buscarFiltrado = async () => {
    const busqueda = document.getElementById('buscarCliente').value.trim();
    
    if (!busqueda) {
        buscarClientes();
        return;
    }
    
    try {
        const datos = new URLSearchParams();
        datos.append('busqueda', busqueda);
        
        const respuesta = await fetch('./clientes/buscarFiltradoAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1) {
            clientesData = resultado.data || [];
            tabla.clear().rows.add(clientesData).draw();
            
            if (clientesData.length > 0) {
                mostrarMensaje('success', 'Búsqueda', `${clientesData.length} clientes encontrados`);
            } else {
                mostrarMensaje('info', 'Sin resultados', 'No se encontraron clientes con ese criterio');
            }
        } else {
            mostrarMensaje('warning', 'Búsqueda', resultado.mensaje);
        }
    } catch (error) {
        console.error('Error en búsqueda:', error);
        mostrarMensaje('error', 'Error', 'Error en la búsqueda');
    }
};

const mostrarEstadisticas = async () => {
    try {
        const respuesta = await fetch('./clientes/estadisticasAPI', {
            method: 'POST'
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1) {
            const stats = resultado.data;
            
            Swal.fire({
                title: '📊 Estadísticas de Clientes',
                html: `
                    <div class="text-start">
                        <p><strong>👥 Total clientes activos:</strong> ${stats.total_activos}</p>
                        <p><strong>🆔 Clientes con NIT:</strong> ${stats.con_nit}</p>
                        <p><strong>📧 Clientes con correo:</strong> ${stats.con_correo}</p>
                        <hr>
                        <small class="text-muted">Datos actualizados en tiempo real</small>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#007bff'
            });
        } else {
            mostrarMensaje('error', 'Error', 'No se pudieron obtener las estadísticas');
        }
    } catch (error) {
        console.error('Error en estadísticas:', error);
        mostrarMensaje('error', 'Error', 'Error al obtener estadísticas');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM cargado, iniciando aplicación de clientes...');
    
    buscarClientes();
    
    form.addEventListener("submit", guardarCliente);
    btnLimpiar.addEventListener("click", limpiarFormulario);
    btnModificar.addEventListener("click", modificarCliente);
    
    const btnActualizar = document.getElementById('btnActualizar');
    const btnEstadisticas = document.getElementById('btnEstadisticas');
    
    if (btnActualizar) {
        btnActualizar.addEventListener('click', buscarClientes);
    }
    
    if (btnEstadisticas) {
        btnEstadisticas.addEventListener('click', mostrarEstadisticas);
    }
    
    document.getElementById("cliente_nit").addEventListener('change', validarNitInput);
    document.getElementById("cliente_telefono").addEventListener("input", validarTelefono);
    
    const inputBuscar = document.getElementById('buscarCliente');
    if (inputBuscar) {
        let timeoutId;
        inputBuscar.addEventListener('input', () => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(buscarFiltrado, 500);
        });
    }
    
    tabla.on("click", ".modificar", llenarFormulario);
    tabla.on("click", ".eliminar", eliminarCliente);
});

window.buscarClientes = buscarClientes;
window.mostrarEstadisticas = mostrarEstadisticas;