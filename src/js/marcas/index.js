import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { Modal } from "bootstrap";
import Swal from "sweetalert2";
import { lenguaje } from "../lenguaje";

const formMarca = document.getElementById('formMarca');
const btnBuscar = document.getElementById('btnBuscar');
const btnModalMarca = document.getElementById('btnModalMarca');
const btnGuardar = document.getElementById('btnGuardar');
const modalMarca = new Modal(document.getElementById('modalMarca'));
const tituloModal = document.getElementById('tituloModal');
const contenedorTabla = document.getElementById('contenedorTabla');

let tablaMarcas;
let accion = 'guardar';

// Función para mostrar mensajes con Sweet Alert
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

const buscar = async () => {
    console.log('🔍 Iniciando búsqueda de marcas...');
    
    try {
        const respuesta = await fetch('/app03_jmp/marcas/buscarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        });
        
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        const data = await respuesta.json();
        console.log('📦 Resultado completo:', data);
        
        if (data.codigo == 1) {
            console.log('✅ Marcas encontradas:', data.data.length);
            mostrarTabla(data.data);
            mostrarMensaje('success', 'Éxito', data.mensaje);
        } else {
            console.log('⚠️ Sin datos:', data.mensaje);
            mostrarMensaje('info', 'Información', data.mensaje);
            contenedorTabla.style.display = 'none';
        }
    } catch (error) {
        console.error('❌ Error completo:', error);
        mostrarMensaje('error', 'Error', 'Error en el sistema: ' + error.message);
    }
};

const mostrarTabla = (marcas) => {
    contenedorTabla.style.display = 'block';
    
    if (tablaMarcas) {
        tablaMarcas.destroy();
    }
    
    // TABLA CON NOMBRES EN MINÚSCULAS (como vienen del servidor)
    tablaMarcas = new DataTable('#tablaMarcas', {
        language: lenguaje,
        data: marcas,
        columns: [
            { title: "No.", data: null, render: (data, type, row, meta) => meta.row + 1 },
            { title: "Nombre", data: "marca_nombre", defaultContent: "" },             // minúsculas ✅
            { title: "Descripción", data: "marca_descripcion", defaultContent: "" },   // minúsculas ✅
            { title: "Fecha Creación", data: "fecha_creacion", defaultContent: "" },   // minúsculas ✅
            { 
                title: "Estado", 
                data: "situacion",                                                     // minúsculas ✅
                render: (data) => {
                    return `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">
                        ${data == 1 ? 'Activo' : 'Inactivo'}
                    </span>`;
                }
            },
            {
                title: "Acciones",
                data: "marca_id",                                                      // minúsculas ✅
                orderable: false,
                render: (data, type, row) => {
                    if (!data) return '';
                    return `
                        <button class="btn btn-warning btn-sm modificar" 
                                data-marca='${JSON.stringify(row)}'>
                            <i class="bi bi-pencil"></i> Modificar
                        </button>
                        <button class="btn btn-danger btn-sm eliminar ms-1" 
                                data-id="${data}">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    `;
                }
            }
        ]
    });
    
    // Event listeners para los botones de la tabla
    tablaMarcas.on("click", ".modificar", llenarFormulario);
    tablaMarcas.on("click", ".eliminar", eliminarMarca);
};

const guardar = async () => {
    if (!validarFormulario(formMarca, ["marca_id"])) {
        mostrarMensaje('warning', 'Validación', 'Complete los campos obligatorios');
        return;
    }
    
    btnGuardar.disabled = true;
    
    try {
        const datos = new URLSearchParams();
        
        // Agregar campos manualmente
        if (accion === 'modificar') {
            datos.append('marca_id', document.getElementById('marca_id').value);
        }
        datos.append('marca_nombre', document.getElementById('marca_nombre').value.trim());
        datos.append('marca_descripcion', document.getElementById('marca_descripcion').value.trim());
        
        const url = accion === 'guardar' ? 
            '/app03_jmp/marcas/guardarAPI' : 
            '/app03_jmp/marcas/modificarAPI';
        
        const respuesta = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        
        const data = await respuesta.json();
        
        if (data.codigo == 1) {
            mostrarMensaje('success', 'Éxito', data.mensaje);
            modalMarca.hide();
            limpiarModal();
            buscar();
        } else {
            mostrarMensaje('warning', 'Atención', data.mensaje);
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('error', 'Error', 'Error en el sistema: ' + error.message);
    }
    
    btnGuardar.disabled = false;
};

const llenarFormulario = (e) => {
    const marca = JSON.parse(e.target.dataset.marca);
    console.log('🏷️ Marca recibida:', marca);
    
    limpiarModal();
    accion = 'modificar';
    tituloModal.textContent = 'Modificar Marca';
    
    // CAMPOS VIENEN EN MINÚSCULAS del servidor ✅
    document.getElementById('marca_id').value = marca.marca_id || '';
    document.getElementById('marca_nombre').value = marca.marca_nombre || '';
    document.getElementById('marca_descripcion').value = marca.marca_descripcion || '';
    
    console.log('📝 Formulario llenado correctamente');
    
    modalMarca.show();
};

const eliminarMarca = async (e) => {
    const id = e.target.dataset.id;
    
    if (!id) {
        mostrarMensaje('error', 'Error', 'ID de marca no válido');
        return;
    }
    
    const confirmacion = await Swal.fire({
        title: '¿Eliminar marca?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
    
    if (!confirmacion.isConfirmed) return;
    
    try {
        const datos = new URLSearchParams();
        datos.append('marca_id', id);
        
        const respuesta = await fetch('/app03_jmp/marcas/eliminarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        
        const data = await respuesta.json();
        
        if (data.codigo == 1) {
            Swal.fire({
                icon: 'success',
                title: '¡Eliminada!',
                text: data.mensaje,
                timer: 3000,
                showConfirmButton: false
            });
            buscar();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No se puede eliminar',
                text: data.mensaje,
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

const limpiarModal = () => {
    formMarca.reset();
    
    // Limpiar validaciones
    formMarca.querySelectorAll('.form-control').forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
    });
    
    document.getElementById('marca_id').value = '';
    accion = 'guardar';
    tituloModal.textContent = 'Nueva Marca';
};

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM cargado, iniciando aplicación de marcas...');
    
    // Buscar marcas al cargar
    buscar();
    
    btnBuscar.addEventListener('click', buscar);
    btnModalMarca.addEventListener('click', () => {
        limpiarModal();
        modalMarca.show();
    });
    btnGuardar.addEventListener('click', guardar);
});

// Exponer función globalmente si es necesario
window.buscarMarcas = buscar;