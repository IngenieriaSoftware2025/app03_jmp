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

let marcasData = [];
let tablaMarcas;
let accion = 'guardar';

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
        const data = await fetch('./marcas/buscarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        }).then(response => response.json());
        
        console.log('📦 Resultado completo:', data);
        
        if (data.codigo == 1) {
            if (data.data?.length > 0) {
                console.log('✅ Marcas encontradas:', data.data.length);
                marcasData = data.data;
                mostrarTabla(data.data);
                mostrarMensaje('success', 'Éxito', `${data.data.length} marcas encontradas`);
            } else {
                marcasData = [];
                contenedorTabla.style.display = 'none';
                mostrarMensaje('info', 'Sin marcas', 'No hay marcas registradas. Agregue la primera marca.');
            }
        } else {
            marcasData = [];
            mostrarMensaje('info', 'Información', data.mensaje);
            contenedorTabla.style.display = 'none';
        }
    } catch (error) {
        console.error('❌ Error completo:', error);
        marcasData = [];
        mostrarMensaje('error', 'Error', 'Error en el sistema: ' + error.message);
    }
};

const mostrarTabla = (marcas) => {
    contenedorTabla.style.display = 'block';
    
    if (tablaMarcas) tablaMarcas.destroy();
    
    tablaMarcas = new DataTable('#tablaMarcas', {
        language: lenguaje,
        data: marcas,
        pageLength: 25,
        columns: [
            { title: "No.", data: null, render: (data, type, row, meta) => meta.row + 1, width: "8%" },
            { title: "Nombre", data: "marca_nombre", defaultContent: "", width: "25%" },
            { 
                title: "Descripción", data: "marca_descripcion", defaultContent: "", width: "35%",
                render: (data) => {
                    if (!data?.trim()) return '<em class="text-muted">Sin descripción</em>';
                    return data.length > 60 ? `<span title="${data}">${data.substring(0, 60)}...</span>` : data;
                }
            },
            { 
                title: "Fecha Creación", data: "fecha_creacion", defaultContent: "", width: "12%",
                render: (data) => data || '<em class="text-muted">N/A</em>'
            },
            { 
                title: "Estado", data: "situacion", width: "10%",
                render: (data) => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">
                    ${data == 1 ? 'Activo' : 'Inactivo'}
                </span>`
            },
            {
                title: "Acciones", data: "marca_id", orderable: false, width: "10%",
                render: (data, type, row, meta) => data ? `
                    <button class="btn btn-warning btn-sm modificar" data-index="${meta.row}" title="Modificar">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-danger btn-sm eliminar ms-1" data-id="${data}" title="Eliminar">
                        <i class="bi bi-trash"></i>
                    </button>
                ` : ''
            }
        ]
    });
    
    tablaMarcas.on("click", ".modificar", llenarFormulario);
    tablaMarcas.on("click", ".eliminar", eliminarMarca);
};

const guardar = async () => {
    console.log('💾 Iniciando guardado...', 'Acción:', accion);
    
    if (!validarFormulario(formMarca, ["marca_id"])) {
        mostrarMensaje('warning', 'Validación', 'Complete los campos obligatorios');
        return;
    }
    
    btnGuardar.disabled = true;
    
    try {
        const datos = new URLSearchParams();
        
        if (accion === 'modificar') {
            const marcaId = document.getElementById('marca_id').value;
            console.log('🔄 Modificando marca ID:', marcaId);
            datos.append('marca_id', marcaId);
        }
        datos.append('marca_nombre', document.getElementById('marca_nombre').value.trim());
        datos.append('marca_descripcion', document.getElementById('marca_descripcion').value.trim());
        
        const url = accion === 'guardar' ? './marcas/guardarAPI' : './marcas/modificarAPI';
        console.log('🌐 URL a llamar:', url);
        
        const respuesta = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        
        const data = await respuesta.json();
        console.log('📥 Respuesta del servidor:', data);
        
        if (data.codigo == 1) {
            mostrarMensaje('success', 'Éxito', data.mensaje);
            modalMarca.hide();
            limpiarModal();
            buscar();
        } else {
            mostrarMensaje('warning', 'Atención', data.mensaje);
        }
    } catch (error) {
        console.error('❌ Error en guardar:', error);
        mostrarMensaje('error', 'Error', 'Error en el sistema: ' + error.message);
    }
    
    btnGuardar.disabled = false;
};

const llenarFormulario = (e) => {
    try {
        e.preventDefault();
        e.stopPropagation();
        
        const boton = e.target.closest('.modificar');
        const index = parseInt(boton.dataset.index);
        const marca = marcasData[index];
        
        if (!marca) {
            mostrarMensaje('error', 'Error', 'No se encontró la marca');
            return;
        }
        
        console.log('🏷️ Marca recibida:', marca);
        
        formMarca.reset();
        formMarca.querySelectorAll('.form-control').forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
        
        accion = 'modificar';
        tituloModal.textContent = 'Modificar Marca';
        
        ['marca_id', 'marca_nombre', 'marca_descripcion'].forEach(campo => {
            const input = document.getElementById(campo);
            if (input) input.value = marca[campo] || '';
        });
        
        console.log('📝 Formulario llenado - ID:', marca.marca_id, 'Acción:', accion);
        modalMarca.show();
        
    } catch (error) {
        console.error('❌ Error al llenar formulario:', error);
        mostrarMensaje('error', 'Error', 'No se pudo cargar los datos de la marca');
    }
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
        
        const respuesta = await fetch('./marcas/eliminarAPI', {
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
    formMarca.querySelectorAll('.form-control').forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
    });
    
    document.getElementById('marca_id').value = '';
    accion = 'guardar';
    tituloModal.textContent = 'Nueva Marca';
    
    console.log('🧹 Modal limpiado - Acción:', accion);
};

const mostrarEstadisticas = async () => {
    try {
        const respuesta = await fetch('./marcas/estadisticasAPI', {
            method: 'POST'
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1) {
            const stats = resultado.data;
            
            Swal.fire({
                title: '📊 Estadísticas de Marcas',
                html: `
                    <div class="text-start">
                        <p><strong>🏷️ Total marcas activas:</strong> ${stats.total_activas}</p>
                        <p><strong>📦 Marcas con productos:</strong> ${stats.con_productos}</p>
                        <p><strong>📭 Marcas sin productos:</strong> ${stats.sin_productos}</p>
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
    console.log('🚀 DOM cargado, iniciando aplicación de marcas...');
    
    buscar();
    
    btnBuscar?.addEventListener('click', buscar);
    btnModalMarca?.addEventListener('click', () => {
        limpiarModal();
        modalMarca.show();
    });
    btnGuardar?.addEventListener('click', guardar);
    document.getElementById('btnEstadisticas')?.addEventListener('click', mostrarEstadisticas);
});

window.buscarMarcas = buscar;
window.mostrarEstadisticas = mostrarEstadisticas;