import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Variables principales
const form = document.getElementById("FormClientes");
const btnGuardar = document.getElementById("BtnGuardar");
const btnModificar = document.getElementById("BtnModificar");
const btnLimpiar = document.getElementById("BtnLimpiar");

// Validación simple de NIT
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
}

// Validación simple de teléfono
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

// SOLUCIÓN: Cambiar todos los nombres de campos a MAYÚSCULAS
const tabla = new DataTable("#TableClientes", {
    language: lenguaje,
    data: [],
    columns: [
        { title: "No.", data: null, render: (data, type, row, meta) => meta.row + 1 },
        { title: "Nombres", data: "CLIENTE_NOMBRES", defaultContent: "" },        // CAMBIADO
        { title: "Apellidos", data: "CLIENTE_APELLIDOS", defaultContent: "" },    // CAMBIADO
        { title: "NIT", data: "CLIENTE_NIT", defaultContent: "" },               // CAMBIADO
        { title: "Teléfono", data: "CLIENTE_TELEFONO", defaultContent: "" },     // CAMBIADO
        { title: "Correo", data: "CLIENTE_CORREO", defaultContent: "" },         // CAMBIADO
        {
            title: "Acciones",
            data: "CLIENTE_ID",                                                   // CAMBIADO
            orderable: false,
            render: (data, type, row) => {
                if (!data) return '';
                return `
                    <button class="btn btn-warning btn-sm modificar" 
                            data-cliente='${JSON.stringify(row)}'>
                        Modificar
                    </button>
                    <button class="btn btn-danger btn-sm eliminar ms-1" 
                            data-id="${data}">
                        Eliminar
                    </button>
                `;
            }
        }
    ]
});

// REEMPLAZAR la función mostrarMensaje por esta versión única
const mostrarMensaje = (tipo, titulo, texto, timer = null) => {
    // Configuración base
    const config = {
        icon: tipo,
        title: titulo,
        text: texto
    };
    
    // Si es success, usar timer automático
    if (tipo === 'success') {
        config.timer = timer || 3000;
        config.showConfirmButton = false;
        config.toast = true;
        config.position = 'top-end';
    } else {
        // Para error, warning, info - mostrar botón
        config.confirmButtonText = 'OK';
        config.confirmButtonColor = tipo === 'error' ? '#e74c3c' : '#3498db';
    }
    
    Swal.fire(config);
};

// Buscar clientes CORREGIDO con POST y mejor debugging
const buscarClientes = async () => {
    console.log('🔍 Iniciando búsqueda de clientes...');
    
    try {
        const respuesta = await fetch('/app03_jmp/clientes/buscarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        });
        
        console.log('📡 Respuesta del servidor:', respuesta);
        console.log('📊 Status:', respuesta.status);
        console.log('📋 Headers:', respuesta.headers);
        
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        const resultado = await respuesta.json();
        
        console.log('📦 Resultado completo:', resultado);
        console.log('🔢 Código:', resultado.codigo);
        console.log('💬 Mensaje:', resultado.mensaje);
        console.log('📄 Data:', resultado.data);
        
        if (resultado.codigo === 1) {
            console.log('✅ Datos encontrados:', resultado.data.length, 'clientes');
            console.log('👤 Primer cliente:', resultado.data[0]);
            
            tabla.clear().rows.add(resultado.data || []).draw();
            mostrarMensaje('success', 'Éxito', `Se encontraron ${resultado.data.length} clientes`);
        } else {
            console.log('⚠️ Sin datos:', resultado.mensaje);
            tabla.clear().draw();
            mostrarMensaje('info', 'Información', resultado.mensaje || 'No hay clientes disponibles');
        }
    } catch (error) {
        console.error('❌ Error completo:', error);
        console.error('📍 Stack trace:', error.stack);
        mostrarMensaje('error', 'Error', `Problema de conexión: ${error.message}`);
        tabla.clear().draw();
    }
};

// Guardar cliente
const guardarCliente = async (e) => {
    e.preventDefault();
    
    if (!validarFormulario(form, ["cliente_id"])) {
        mostrarMensaje('error', 'Error', 'Complete los campos obligatorios');
        return;
    }

    btnGuardar.disabled = true;
    
    try {
        const datos = new FormData(form);
        const respuesta = await fetch('/app03_jmp/clientes/guardarAPI', {
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

// Modificar cliente - VERSIÓN FINAL con mejor manejo de errores
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
        
        const respuesta = await fetch('/app03_jmp/clientes/modificarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        
        // Ahora siempre será 200, así que verificamos el contenido
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1) {
            // ÉXITO - Sweet Alert verde
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
            // ERROR DE NEGOCIO - Sweet Alert naranja/warning
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: resultado.mensaje,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#f39c12'
            });
        }
    } catch (error) {
        // ERROR TÉCNICO - Sweet Alert rojo
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

// Eliminar cliente FINAL - Solo Sweet Alert directo
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
        
        const respuesta = await fetch('/app03_jmp/clientes/eliminarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1) {
            // ÉXITO - UN SOLO Sweet Alert
            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: resultado.mensaje,
                timer: 3000,
                showConfirmButton: false
            });
            buscarClientes(); // Actualizar tabla
        } else {
            // ERROR DE NEGOCIO
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

// Llenar formulario COMPLETO - con todos los campos necesarios
const llenarFormulario = (e) => {
    const cliente = JSON.parse(e.target.dataset.cliente);
    
    console.log('Cliente recibido:', cliente); // DEBUG
    
    // Mapeo COMPLETO de campos de la BD (MAYÚSCULAS) a los inputs del formulario (minúsculas)
    const mapeoCliente = {
        'cliente_id': cliente.CLIENTE_ID || '',              // ¡IMPORTANTE!
        'cliente_nombres': cliente.CLIENTE_NOMBRES || '',
        'cliente_apellidos': cliente.CLIENTE_APELLIDOS || '',
        'cliente_nit': cliente.CLIENTE_NIT || '',
        'cliente_telefono': cliente.CLIENTE_TELEFONO || '',
        'cliente_correo': cliente.CLIENTE_CORREO || '',
        'cliente_direccion': cliente.CLIENTE_DIRECCION || '',
        'cliente_situacion': cliente.CLIENTE_SITUACION || '1'
    };
    
    console.log('Mapeo cliente:', mapeoCliente); // DEBUG
    
    // Llenar todos los campos del formulario
    Object.keys(mapeoCliente).forEach(key => {
        const input = document.getElementById(key);
        if (input) {
            input.value = mapeoCliente[key];
            console.log(`Campo ${key}: ${mapeoCliente[key]}`); // DEBUG
        } else {
            console.warn(`Input no encontrado: ${key}`); // DEBUG
        }
    });

    // Cambiar botones
    btnGuardar.classList.add("d-none");
    btnModificar.classList.remove("d-none");
    
    // Scroll hacia arriba
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    console.log('Cliente ID final:', document.getElementById('cliente_id').value); // DEBUG
};

// Limpiar formulario MEJORADO
const limpiarFormulario = () => {
    form.reset();
    
    // Asegurar que los campos ocultos se reseteen
    document.getElementById('cliente_id').value = '';
    document.getElementById('cliente_situacion').value = '1';
    
    // Cambiar botones
    btnGuardar.classList.remove("d-none");
    btnModificar.classList.add("d-none");
    
    // Quitar validaciones
    form.querySelectorAll('.form-control').forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
    });
    
    console.log('Formulario limpiado - Cliente ID:', document.getElementById('cliente_id').value); // DEBUG
};
// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM cargado, iniciando aplicación...');
    
    // Buscar clientes inmediatamente al cargar
    buscarClientes();
    
    form.addEventListener("submit", guardarCliente);
    btnLimpiar.addEventListener("click", limpiarFormulario);
    btnModificar.addEventListener("click", modificarCliente);
    
    document.getElementById("cliente_nit").addEventListener('change', validarNitInput);
    document.getElementById("cliente_telefono").addEventListener("input", validarTelefono);
    
    tabla.on("click", ".modificar", llenarFormulario);
    tabla.on("click", ".eliminar", eliminarCliente);
});

// Exponer función globalmente para el botón HTML
window.buscarClientes = buscarClientes;