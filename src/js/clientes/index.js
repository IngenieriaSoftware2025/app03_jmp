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

// Tabla simple con nombres de campos correctos
const tabla = new DataTable("#TableClientes", {
    language: lenguaje,
    data: [],
    columns: [
        { title: "No.", data: null, render: (data, type, row, meta) => meta.row + 1 },
        { title: "Nombres", data: "cliente_nombres", defaultContent: "" },
        { title: "Apellidos", data: "cliente_apellidos", defaultContent: "" },
        { title: "NIT", data: "cliente_nit", defaultContent: "" },
        { title: "Teléfono", data: "cliente_telefono", defaultContent: "" },
        { title: "Correo", data: "cliente_correo", defaultContent: "" },
        {
            title: "Acciones",
            data: "cliente_id",
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

// Mostrar mensaje simple
const mostrarMensaje = (tipo, titulo, texto) => {
    Swal.fire({ icon: tipo, title: titulo, text: texto, timer: 4000 });
};

// Buscar clientes con debug
const buscarClientes = async () => {
    try {
        const respuesta = await fetch('/app03_jmp/clientes/buscarAPI');
        const resultado = await respuesta.json();
        
        console.log('Respuesta completa:', resultado); // DEBUG
        
        if (resultado.codigo === 1) {
            console.log('Datos recibidos:', resultado.data); // DEBUG
            console.log('Primer cliente:', resultado.data[0]); // DEBUG
            
            tabla.clear().rows.add(resultado.data || []).draw();
        } else {
            console.log('No hay datos:', resultado.mensaje); // DEBUG
            tabla.clear().draw();
        }
    } catch (error) {
        console.error('Error:', error); // DEBUG
        mostrarMensaje('error', 'Error', 'Problema de conexión');
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

// Modificar cliente
const modificarCliente = async (e) => {
    e.preventDefault();
    
    if (!validarFormulario(form)) {
        mostrarMensaje('error', 'Error', 'Complete los campos obligatorios');
        return;
    }

    btnModificar.disabled = true;
    
    try {
        const datos = new FormData(form);
        const respuesta = await fetch('/app03_jmp/clientes/modificarAPI', {
            method: 'POST',
            body: datos
        });
        const resultado = await respuesta.json();

        if (resultado.codigo === 1) {
            mostrarMensaje('success', 'Éxito', 'Cliente actualizado');
            limpiarFormulario();
            buscarClientes();
        } else {
            mostrarMensaje('error', 'Error', resultado.mensaje);
        }
    } catch (error) {
        mostrarMensaje('error', 'Error', 'Problema de conexión');
    }
    
    btnModificar.disabled = false;
};

// Eliminar cliente
const eliminarCliente = async (e) => {
    const id = e.target.dataset.id;
    
    const confirmacion = await Swal.fire({
        title: '¿Eliminar cliente?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!confirmacion.isConfirmed) return;

    try {
        const datos = new URLSearchParams();
        datos.append('cliente_id', id);
        
        const respuesta = await fetch('/app03_jmp/clientes/eliminarAPI', {
            method: 'POST',
            body: datos
        });
        const resultado = await respuesta.json();

        if (resultado.codigo === 1) {
            mostrarMensaje('success', 'Éxito', 'Cliente eliminado');
            buscarClientes();
        } else {
            mostrarMensaje('error', 'Error', resultado.mensaje);
        }
    } catch (error) {
        mostrarMensaje('error', 'Error', 'Problema de conexión');
    }
};

// Llenar formulario
const llenarFormulario = (e) => {
    const cliente = JSON.parse(e.target.dataset.cliente);
    
    Object.keys(cliente).forEach(key => {
        const input = document.getElementById(key);
        if (input) input.value = cliente[key] || '';
    });

    btnGuardar.classList.add("d-none");
    btnModificar.classList.remove("d-none");
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar formulario
const limpiarFormulario = () => {
    form.reset();
    btnGuardar.classList.remove("d-none");
    btnModificar.classList.add("d-none");
    
    form.querySelectorAll('.form-control').forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
    });
};

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
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