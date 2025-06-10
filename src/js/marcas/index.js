import DataTable from "datatables.net-bs5";
import { Toast } from "../funciones";
import { Modal } from "bootstrap";
import Swal from "sweetalert2";

const formMarca = document.getElementById('formMarca');
const btnBuscar = document.getElementById('btnBuscar');
const btnModalMarca = document.getElementById('btnModalMarca');
const btnGuardar = document.getElementById('btnGuardar');
const modalMarca = new Modal(document.getElementById('modalMarca'));
const tituloModal = document.getElementById('tituloModal');
const contenedorTabla = document.getElementById('contenedorTabla');

let tablaMarcas;
let accion = 'guardar';

const buscar = async () => {
    try {
        const url = '/app03_jmp/marcas/buscar';
        const config = {
            method: 'POST'
        };
        
        const respuesta = await fetch(url, config);
        const data = await respuesta.json();
        
        if(data.codigo == 1) {
            mostrarTabla(data.data);
            Toast.fire({
                icon: 'success',
                title: data.mensaje
            });
        } else {
            Toast.fire({
                icon: 'error',
                title: data.mensaje
            });
        }
    } catch (error) {
        console.log(error);
        Toast.fire({
            icon: 'error',
            title: 'Error en el sistema'
        });
    }
};

const mostrarTabla = (marcas) => {
    contenedorTabla.style.display = 'block';
    
    if(tablaMarcas) {
        tablaMarcas.destroy();
    }
    
    const tbody = document.querySelector('#tablaMarcas tbody');
    tbody.innerHTML = '';
    
    marcas.forEach((marca, index) => {
        const fila = `
            <tr>
                <td>${index + 1}</td>
                <td>${marca.marca_nombre || ''}</td>
                <td>${marca.marca_descripcion || ''}</td>
                <td>${marca.fecha_creacion || ''}</td>
                <td>
                    <span class="badge ${marca.situacion == 1 ? 'bg-success' : 'bg-danger'}">
                        ${marca.situacion == 1 ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="modificar(${marca.marca_id}, '${marca.marca_nombre}', '${marca.marca_descripcion}')">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="eliminar(${marca.marca_id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.innerHTML += fila;
    });
    
    tablaMarcas = new DataTable('#tablaMarcas', {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        }
    });
};

const guardar = async () => {
    // Validación simple solo para el nombre (que es obligatorio)
    const nombre = document.getElementById('marca_nombre').value.trim();
    
    if(nombre === '') {
        Toast.fire({
            icon: 'warning',
            title: 'El nombre de la marca es obligatorio'
        });
        return;
    }
    
    try {
        const body = new FormData(formMarca);
        
        const url = accion === 'guardar' ? 
            '/app03_jmp/marcas/guardar' : 
            '/app03_jmp/marcas/modificar';
        
        const config = {
            method: 'POST',
            body
        };
        
        const respuesta = await fetch(url, config);
        const data = await respuesta.json();
        
        if(data.codigo == 1) {
            Toast.fire({
                icon: 'success',
                title: data.mensaje
            });
            
            modalMarca.hide();
            limpiarModal();
            buscar();
        } else {
            Toast.fire({
                icon: 'error',
                title: data.mensaje
            });
        }
    } catch (error) {
        console.log(error);
        Toast.fire({
            icon: 'error',
            title: 'Error en el sistema'
        });
    }
};

const limpiarModal = () => {
    formMarca.reset();
    // Limpiar validaciones manualmente
    const elements = formMarca.querySelectorAll('.is-invalid');
    elements.forEach(element => {
        element.classList.remove('is-invalid');
    });
    
    document.getElementById('marca_id').value = '';
    accion = 'guardar';
    tituloModal.textContent = 'Nueva Marca';
};

window.modificar = (id, nombre, descripcion) => {
    limpiarModal();
    accion = 'modificar';
    tituloModal.textContent = 'Modificar Marca';
    document.getElementById('marca_id').value = id;
    document.getElementById('marca_nombre').value = nombre;
    document.getElementById('marca_descripcion').value = descripcion;
    modalMarca.show();
};

window.eliminar = async (id) => {
    const confirmacion = await Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción eliminará la marca',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
    
    if(confirmacion.isConfirmed) {
        try {
            const body = new FormData();
            body.append('marca_id', id);
            
            const url = '/app03_jmp/marcas/eliminar';
            const config = {
                method: 'POST',
                body
            };
            
            const respuesta = await fetch(url, config);
            const data = await respuesta.json();
            
            if(data.codigo == 1) {
                Toast.fire({
                    icon: 'success',
                    title: data.mensaje
                });
                buscar();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: data.mensaje
                });
            }
        } catch (error) {
            console.log(error);
        }
    }
};

btnBuscar.addEventListener('click', buscar);
btnModalMarca.addEventListener('click', () => {
    limpiarModal();
    modalMarca.show();
});
btnGuardar.addEventListener('click', guardar);