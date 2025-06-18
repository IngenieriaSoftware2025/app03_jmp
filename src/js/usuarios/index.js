import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { Modal } from "bootstrap";
import { lenguaje } from "../lenguaje";

// Variables globales
const formUsuario = document.getElementById('formUsuario');
const btnBuscar = document.getElementById('btnBuscar');
const btnModalUsuario = document.getElementById('btnModalUsuario');
const btnGuardar = document.getElementById('btnGuardar');
const modalUsuario = new Modal(document.getElementById('modalUsuario'));
const tituloModal = document.getElementById('tituloModal');
const contenedorTabla = document.getElementById('contenedorTabla');

let usuariosData = [];
let tablaUsuarios;
let accion = 'guardar';

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

// Buscar usuarios
const buscar = async () => {
    console.log('🔍 Iniciando búsqueda de usuarios...');
    
    try {
        const respuesta = await fetch('./usuarios/buscarAPI', {
            method: 'POST'
        });
        
        const data = await respuesta.json();
        console.log('📦 Resultado completo:', data);
        
        if (data.codigo == 1) {
            if (data.data?.length > 0) {
                console.log('✅ Usuarios encontrados:', data.data.length);
                usuariosData = data.data;
                mostrarTabla(data.data);
                mostrarMensaje('success', 'Éxito', `${data.data.length} usuarios encontrados`);
            } else {
                usuariosData = [];
                contenedorTabla.style.display = 'none';
                mostrarMensaje('info', 'Sin usuarios', 'No hay usuarios registrados. Agregue el primer usuario.');
            }
        } else {
            usuariosData = [];
            mostrarMensaje('info', 'Información', data.mensaje);
            contenedorTabla.style.display = 'none';
        }
    } catch (error) {
        console.error('❌ Error completo:', error);
        usuariosData = [];
        mostrarMensaje('error', 'Error', 'Error en el sistema: ' + error.message);
    }
};

// Mostrar tabla de usuarios
const mostrarTabla = (usuarios) => {
    contenedorTabla.style.display = 'block';
    
    if (tablaUsuarios) tablaUsuarios.destroy();
    
    tablaUsuarios = new DataTable('#tablaUsuarios', {
        language: lenguaje,
        data: usuarios,
        pageLength: 25,
        columns: [
            { title: "No.", data: null, render: (data, type, row, meta) => meta.row + 1, width: "8%" },
            { title: "Nombre", data: "usu_nombre", defaultContent: "", width: "25%" },
            { title: "Código", data: "usu_codigo", defaultContent: "", width: "15%" },
            { 
                title: "Roles", data: "roles", defaultContent: "", width: "20%",
                render: (data) => {
                    if (!data) return '<span class="badge bg-secondary">Sin rol</span>';
                    const roles = data.split(',');
                    return roles.map(rol => `<span class="badge bg-primary me-1">${rol.trim()}</span>`).join('');
                }
            },
            { 
                title: "Estado", data: "usu_situacion", width: "12%",
                render: (data) => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">
                    ${data == 1 ? 'Activo' : 'Inactivo'}
                </span>`
            },
            {
                title: "Acciones", data: "usu_id", orderable: false, width: "20%",
                render: (data, type, row, meta) => data ? `
                    <button class="btn btn-warning btn-sm modificar" data-index="${meta.row}" title="Modificar">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <button class="btn btn-danger btn-sm eliminar ms-1" data-id="${data}" title="Eliminar" 
                            ${row.usu_codigo == '12345678' ? 'disabled' : ''}>
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                ` : ''
            }
        ]
    });
    
    // Event listeners para acciones
    tablaUsuarios.on("click", ".modificar", llenarFormulario);
    tablaUsuarios.on("click", ".eliminar", eliminarUsuario);
};

// Validar código de usuario
const validarCodigo = () => {
    const input = document.getElementById("usu_codigo");
    const valor = input.value.trim();
    
    const soloNumeros = valor.replace(/\D/g, '');
    
    if (input.value !== soloNumeros) {
        input.value = soloNumeros;
    }
    
    if (soloNumeros.length >= 8) {
        input.classList.add("is-valid");
        input.classList.remove("is-invalid");
    } else if (soloNumeros.length > 0) {
        input.classList.add("is-invalid");
        input.classList.remove("is-valid");
    } else {
        input.classList.remove("is-valid", "is-invalid");
    }
};

// Validar contraseña
const validarPassword = () => {
    const input = document.getElementById("usu_password");
    const valor = input.value;
    
    if (accion === 'modificar' && valor === '') {
        // En modificación, la contraseña es opcional
        input.classList.remove("is-valid", "is-invalid");
        return;
    }
    
    if (valor.length >= 6) {
        input.classList.add("is-valid");
        input.classList.remove("is-invalid");
    } else if (valor.length > 0) {
        input.classList.add("is-invalid");
        input.classList.remove("is-valid");
    } else {
        input.classList.remove("is-valid", "is-invalid");
    }
};

// Validar confirmación de contraseña
const validarConfirmPassword = () => {
    const password = document.getElementById("usu_password").value;
    const confirmPassword = document.getElementById("confirm_password");
    const valor = confirmPassword.value;
    
    if (accion === 'modificar' && password === '' && valor === '') {
        confirmPassword.classList.remove("is-valid", "is-invalid");
        return;
    }
    
    if (valor === password && valor !== '') {
        confirmPassword.classList.add("is-valid");
        confirmPassword.classList.remove("is-invalid");
    } else if (valor !== '') {
        confirmPassword.classList.add("is-invalid");
        confirmPassword.classList.remove("is-valid");
    } else {
        confirmPassword.classList.remove("is-valid", "is-invalid");
    }
};

// Guardar usuario
const guardar = async () => {
    console.log('💾 Iniciando guardado...', 'Acción:', accion);
    
    // Validaciones específicas
    const password = document.getElementById('usu_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (accion === 'guardar' && (!password || password.length < 6)) {
        mostrarMensaje('warning', 'Validación', 'La contraseña debe tener al menos 6 caracteres');
        return;
    }
    
    if (accion === 'guardar' && password !== confirmPassword) {
        mostrarMensaje('warning', 'Validación', 'Las contraseñas no coinciden');
        return;
    }
    
    if (accion === 'modificar' && password && password !== confirmPassword) {
        mostrarMensaje('warning', 'Validación', 'Las contraseñas no coinciden');
        return;
    }
    
    if (!validarFormulario(formUsuario, ["usu_id", "confirm_password", accion === 'modificar' ? "usu_password" : ""])) {
        mostrarMensaje('warning', 'Validación', 'Complete los campos obligatorios');
        return;
    }
    
    btnGuardar.disabled = true;
    
    try {
        const datos = new URLSearchParams();
        
        if (accion === 'modificar') {
            const usuarioId = document.getElementById('usu_id').value;
            console.log('🔄 Modificando usuario ID:', usuarioId);
            datos.append('usu_id', usuarioId);
        }
        
        datos.append('usu_nombre', document.getElementById('usu_nombre').value.trim());
        datos.append('usu_codigo', document.getElementById('usu_codigo').value.trim());
        datos.append('rol_id', document.getElementById('rol_id').value);
        
        // Solo agregar contraseña si se proporciona
        if (password) {
            datos.append('usu_password', password);
        }
        
        const url = accion === 'guardar' ? './usuarios/guardarAPI' : './usuarios/modificarAPI';
        console.log('🌐 URL a llamar:', url);
        
        const respuesta = await fetch(url, {
            method: 'POST',
            body: datos
        });
        
        const data = await respuesta.json();
        console.log('📥 Respuesta del servidor:', data);
        
        if (data.codigo == 1) {
            mostrarMensaje('success', 'Éxito', data.mensaje);
            modalUsuario.hide();
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

// Llenar formulario para modificación
const llenarFormulario = (e) => {
    try {
        e.preventDefault();
        e.stopPropagation();
        
        const boton = e.target.closest('.modificar');
        const index = parseInt(boton.dataset.index);
        const usuario = usuariosData[index];
        
        if (!usuario) {
            mostrarMensaje('error', 'Error', 'No se encontró el usuario');
            return;
        }
        
        console.log('👤 Usuario recibido:', usuario);
        
        // Limpiar formulario
        formUsuario.reset();
        formUsuario.querySelectorAll('.form-control, .form-select').forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
        
        accion = 'modificar';
        tituloModal.textContent = 'Modificar Usuario';
        
        // Llenar campos
        document.getElementById('usu_id').value = usuario.usu_id || '';
        document.getElementById('usu_nombre').value = usuario.usu_nombre || '';
        document.getElementById('usu_codigo').value = usuario.usu_codigo || '';
        
        // La contraseña se deja vacía en modificación
        document.getElementById('usu_password').value = '';
        document.getElementById('confirm_password').value = '';
        
        // Mostrar nota sobre contraseña
        const passwordHelp = document.getElementById('password-help');
        if (passwordHelp) {
            passwordHelp.textContent = 'Dejar vacío para mantener la contraseña actual';
            passwordHelp.style.display = 'block';
        }
        
        console.log('📝 Formulario llenado - ID:', usuario.usu_id, 'Acción:', accion);
        modalUsuario.show();
        
    } catch (error) {
        console.error('❌ Error al llenar formulario:', error);
        mostrarMensaje('error', 'Error', 'No se pudo cargar los datos del usuario');
    }
};

// Eliminar usuario
const eliminarUsuario = async (e) => {
    const id = e.target.closest('.eliminar').dataset.id;
    const usuario = usuariosData.find(u => u.usu_id == id);
    
    if (!id) {
        mostrarMensaje('error', 'Error', 'ID de usuario no válido');
        return;
    }
    
    // Proteger al administrador principal
    if (usuario && usuario.usu_codigo == '12345678') {
        mostrarMensaje('warning', 'Acción no permitida', 'No se puede eliminar el usuario administrador principal');
        return;
    }
    
    const confirmacion = await Swal.fire({
        title: '¿Eliminar usuario?',
        text: `¿Está seguro de eliminar al usuario: ${usuario ? usuario.usu_nombre : 'Usuario'}?`,
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
        datos.append('usu_id', id);
        
        const respuesta = await fetch('./usuarios/eliminarAPI', {
            method: 'POST',
            body: datos
        });
        
        const data = await respuesta.json();
        
        if (data.codigo == 1) {
            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
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

// Limpiar modal
const limpiarModal = () => {
    formUsuario.reset();
    formUsuario.querySelectorAll('.form-control, .form-select').forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
    });
    
    document.getElementById('usu_id').value = '';
    accion = 'guardar';
    tituloModal.textContent = 'Nuevo Usuario';
    
    // Ocultar nota de contraseña
    const passwordHelp = document.getElementById('password-help');
    if (passwordHelp) {
        passwordHelp.textContent = 'Mínimo 6 caracteres';
        passwordHelp.style.display = 'block';
    }
    
    console.log('🧹 Modal limpiado - Acción:', accion);
};

// Mostrar estadísticas de usuarios
const mostrarEstadisticas = async () => {
    try {
        // Calcular estadísticas básicas de los datos actuales
        const totalUsuarios = usuariosData.length;
        const usuariosActivos = usuariosData.filter(u => u.usu_situacion == 1).length;
        const administradores = usuariosData.filter(u => u.roles && u.roles.includes('ADMINISTRADOR')).length;
        const empleados = usuariosData.filter(u => u.roles && u.roles.includes('EMPLEADO')).length;
        
        Swal.fire({
            title: '📊 Estadísticas de Usuarios',
            html: `
                <div class="text-start">
                    <p><strong>👥 Total usuarios:</strong> ${totalUsuarios}</p>
                    <p><strong>✅ Usuarios activos:</strong> ${usuariosActivos}</p>
                    <p><strong>🔐 Administradores:</strong> ${administradores}</p>
                    <p><strong>👤 Empleados:</strong> ${empleados}</p>
                    <hr>
                    <small class="text-muted">Datos actualizados en tiempo real</small>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#007bff'
        });
    } catch (error) {
        console.error('Error en estadísticas:', error);
        mostrarMensaje('error', 'Error', 'Error al obtener estadísticas');
    }
};

// Event listeners principales
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM cargado, iniciando aplicación de usuarios...');
    
    buscar();
    
    // Event listeners básicos
    btnBuscar?.addEventListener('click', buscar);
    btnModalUsuario?.addEventListener('click', () => {
        limpiarModal();
        modalUsuario.show();
    });
    btnGuardar?.addEventListener('click', guardar);
    
    // Event listeners para estadísticas
    document.getElementById('btnEstadisticas')?.addEventListener('click', mostrarEstadisticas);
    
    // Validaciones en tiempo real
    document.getElementById('usu_codigo')?.addEventListener('input', validarCodigo);
    document.getElementById('usu_password')?.addEventListener('input', () => {
        validarPassword();
        validarConfirmPassword();
    });
    document.getElementById('confirm_password')?.addEventListener('input', validarConfirmPassword);
    
    // Contador de caracteres para nombre
    const inputNombre = document.getElementById('usu_nombre');
    const contadorNombre = document.getElementById('contador-nombre');
    
    if (inputNombre && contadorNombre) {
        inputNombre.addEventListener('input', () => {
            const longitud = inputNombre.value.length;
            contadorNombre.textContent = longitud;
            
            if (longitud > 45) {
                contadorNombre.className = 'text-danger fw-bold';
            } else if (longitud > 35) {
                contadorNombre.className = 'text-warning';
            } else {
                contadorNombre.className = 'text-muted';
            }
        });
    }
});

// Exportar funciones globales
window.buscarUsuarios = buscar;
window.mostrarEstadisticas = mostrarEstadisticas;