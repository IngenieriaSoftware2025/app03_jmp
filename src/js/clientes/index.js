import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Variables principales
const form = document.getElementById("FormClientes");
const btnGuardar = document.getElementById("BtnGuardar");
const btnModificar = document.getElementById("BtnModificar");
const btnLimpiar = document.getElementById("BtnLimpiar");

// Validar teléfono (8 dígitos)
const validarTelefono = () => {
  const input = document.getElementById("cliente_telefono");
  const valor = input.value;
  
  if (valor.length === 8) {
    input.classList.add("is-valid");
    input.classList.remove("is-invalid");
  } else if (valor.length > 0) {
    input.classList.add("is-invalid");
    input.classList.remove("is-valid");
  } else {
    input.classList.remove("is-valid", "is-invalid");
  }
};

// Validar NIT guatemalteco
const validarNIT = () => {
  const input = document.getElementById("cliente_nit");
  const nit = input.value.trim();
  
  if (!nit) {
    input.classList.remove("is-valid", "is-invalid");
    return;
  }

  const esValido = /^(\d+)-?([\dkK])$/.test(nit);
  
  if (esValido) {
    input.classList.add("is-valid");
    input.classList.remove("is-invalid");
  } else {
    input.classList.add("is-invalid");
    input.classList.remove("is-valid");
  }
};

// Inicializar tabla
const tabla = new DataTable("#TableClientes", {
  language: lenguaje,
  data: [],
  columns: [
    { title: "No.", data: null, render: (data, type, row, meta) => meta.row + 1 },
    { title: "Nombres", data: "cliente_nombres" },
    { title: "Apellidos", data: "cliente_apellidos" },
    { title: "NIT", data: "cliente_nit" },
    { title: "Teléfono", data: "cliente_telefono" },
    { title: "Correo", data: "cliente_correo" },
    {
      title: "Acciones",
      data: "cliente_id",
      orderable: false,
      render: (data, type, row) => `
        <button class="btn btn-warning btn-sm modificar" 
                data-cliente='${JSON.stringify(row)}'>
          Modificar
        </button>
        <button class="btn btn-danger btn-sm eliminar ms-1" 
                data-id="${data}">
          Eliminar
        </button>
      `
    }
  ]
});

// Mostrar mensaje
const mostrarMensaje = (tipo, titulo, texto) => {
  Swal.fire({ icon: tipo, title: titulo, text: texto, timer: 4000 });
};

// Petición fetch simplificada
const peticion = async (url, metodo = 'GET', datos = null) => {
  try {
    const config = { method: metodo };
    if (datos) config.body = datos;

    const respuesta = await fetch(url, config);
    return await respuesta.json();
  } catch (error) {
    mostrarMensaje('error', 'Error', 'Problema de conexión');
    return { codigo: 0 };
  }
};

// Buscar clientes
const buscarClientes = async () => {
  const resultado = await peticion('/app03_jmp/clientes/buscarAPI');
  
  if (resultado.codigo === 1) {
    tabla.clear().rows.add(resultado.data || []).draw();
  } else {
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
  const datos = new FormData(form);
  const resultado = await peticion('/app03_jmp/clientes/guardarAPI', 'POST', datos);

  if (resultado.codigo === 1) {
    mostrarMensaje('success', 'Éxito', 'Cliente guardado');
    limpiarFormulario();
    buscarClientes();
  } else {
    mostrarMensaje('error', 'Error', resultado.mensaje);
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
  const datos = new FormData(form);
  const resultado = await peticion('/app03_jmp/clientes/modificarAPI', 'POST', datos);

  if (resultado.codigo === 1) {
    mostrarMensaje('success', 'Éxito', 'Cliente actualizado');
    limpiarFormulario();
    buscarClientes();
  } else {
    mostrarMensaje('error', 'Error', resultado.mensaje);
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

  const datos = new URLSearchParams();
  datos.append('cliente_id', id);
  
  const resultado = await peticion('/app03_jmp/clientes/eliminarAPI', 'POST', datos);

  if (resultado.codigo === 1) {
    mostrarMensaje('success', 'Éxito', 'Cliente eliminado');
    buscarClientes();
  } else {
    mostrarMensaje('error', 'Error', resultado.mensaje);
  }
};

// Llenar formulario para modificar
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
  
  // Limpiar validaciones
  form.querySelectorAll('.form-control').forEach(input => {
    input.classList.remove('is-valid', 'is-invalid');
  });
};

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
  buscarClientes();
  
  // Formulario
  form.addEventListener("submit", guardarCliente);
  btnLimpiar.addEventListener("click", limpiarFormulario);
  btnModificar.addEventListener("click", modificarCliente);
  
  // Validaciones
  document.getElementById("cliente_telefono").addEventListener("input", validarTelefono);
  document.getElementById("cliente_nit").addEventListener("input", validarNIT);
  
  // Tabla
  tabla.on("click", ".modificar", llenarFormulario);
  tabla.on("click", ".eliminar", eliminarCliente);
});