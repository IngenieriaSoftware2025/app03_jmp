import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Variables principales
const form = document.getElementById("FormProductos");
const btnGuardar = document.getElementById("BtnGuardar");
const btnModificar = document.getElementById("BtnModificar");
const btnLimpiar = document.getElementById("BtnLimpiar");
const btnStockBajo = document.getElementById("btnStockBajo");

// Tabla de productos
const tabla = new DataTable("#TableProductos", {
    language: lenguaje,
    data: [],
    columns: [
        { title: "No.", data: null, render: (data, type, row, meta) => meta.row + 1 },
        { title: "Producto", data: "nombre_producto", defaultContent: "" },
        { title: "Marca", data: "marca_nombre", defaultContent: "" },
        { 
            title: "Tipo", 
            data: "tipo_producto", 
            defaultContent: "",
            render: (data) => {
                const badges = {
                    'celular': '<span class="badge bg-primary">Celular</span>',
                    'repuesto': '<span class="badge bg-info">Repuesto</span>',
                    'servicio': '<span class="badge bg-secondary">Servicio</span>'
                };
                return badges[data] || data;
            }
        },
        { title: "Modelo", data: "modelo", defaultContent: "" },
        { 
            title: "P. Compra", 
            data: "precio_compra", 
            render: (data) => `Q ${parseFloat(data || 0).toFixed(2)}`
        },
        { 
            title: "P. Venta", 
            data: "precio_venta", 
            render: (data) => `Q ${parseFloat(data || 0).toFixed(2)}`
        },
        { 
            title: "Stock", 
            data: "stock_actual",
            render: (data, type, row) => {
                const stock = parseInt(data || 0);
                const minimo = parseInt(row.stock_minimo || 0);
                const clase = stock <= minimo ? 'text-danger fw-bold' : 'text-success';
                return `<span class="${clase}">${stock}</span>`;
            }
        },
        { title: "Stock Min", data: "stock_minimo", defaultContent: "0" },
        {
            title: "Estado",
            data: "stock_actual",
            render: (data, type, row) => {
                const stock = parseInt(data || 0);
                const minimo = parseInt(row.stock_minimo || 0);
                if (stock <= minimo) {
                    return '<span class="badge bg-danger">Stock Bajo</span>';
                } else if (stock <= minimo * 2) {
                    return '<span class="badge bg-warning">Advertencia</span>';
                } else {
                    return '<span class="badge bg-success">Normal</span>';
                }
            }
        },
        {
            title: "Acciones",
            data: "producto_id",
            orderable: false,
            render: (data, type, row) => {
                if (!data) return '';
                return `
                    <button class="btn btn-warning btn-sm modificar" 
                            data-producto='${JSON.stringify(row)}'>
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

const manejarTipoProducto = () => {
    const tipo = document.getElementById('tipo_producto').value;
    
    // Elementos que cambian según el tipo
    const modelo = document.getElementById('modelo');
    const precioCompra = document.getElementById('precio_compra');
    const stockActual = document.getElementById('stock_actual');
    const stockMinimo = document.getElementById('stock_minimo');
    const descripcion = document.getElementById('descripcion');
    
    // Contenedores de los campos
    const contenedorModelo = modelo.closest('.col-lg-4');
    const contenedorPrecioCompra = precioCompra.closest('.col-lg-4');
    const contenedorStockActual = stockActual.closest('.col-lg-4');
    const contenedorStockMinimo = stockMinimo.closest('.col-lg-4');
    
    // Labels que pueden cambiar
    const labelModelo = document.querySelector('label[for="modelo"]');
    
    // Resetear validaciones
    [modelo, precioCompra, stockActual, stockMinimo, descripcion].forEach(input => {
        input.classList.remove('is-invalid', 'is-valid');
        input.removeAttribute('required');
    });
    
    switch(tipo) {
        case 'celular':
            // CELULAR: Todos los campos visibles y requeridos
            contenedorModelo.style.display = 'block';
            contenedorPrecioCompra.style.display = 'block';
            contenedorStockActual.style.display = 'block';
            contenedorStockMinimo.style.display = 'block';
            
            labelModelo.textContent = 'Modelo';
            modelo.setAttribute('required', 'required');
            modelo.placeholder = 'Ej: iPhone 15 Pro Max, Galaxy S24 Ultra';
            precioCompra.setAttribute('required', 'required');
            stockActual.setAttribute('required', 'required');
            stockMinimo.setAttribute('required', 'required');
            descripcion.placeholder = 'Características del celular (color, memoria, estado, etc.)';
            
            // Limpiar valores automáticos si vienen de servicio
            if (modelo.value === 'No aplica') modelo.value = '';
            if (precioCompra.value === '0') precioCompra.value = '';
            if (stockActual.value === '0') stockActual.value = '';
            if (stockMinimo.value === '0') stockMinimo.value = '';
            break;
            
        case 'repuesto':
            // REPUESTO: Todos los campos visibles, modelo opcional
            contenedorModelo.style.display = 'block';
            contenedorPrecioCompra.style.display = 'block';
            contenedorStockActual.style.display = 'block';
            contenedorStockMinimo.style.display = 'block';
            
            labelModelo.textContent = 'Compatibilidad';
            modelo.placeholder = 'Ej: iPhone 14 Pro, Galaxy S22, Universal, Genérico';
            precioCompra.setAttribute('required', 'required');
            stockActual.setAttribute('required', 'required');
            stockMinimo.setAttribute('required', 'required');
            descripcion.setAttribute('required', 'required');
            descripcion.placeholder = 'OBLIGATORIO: Especifique tipo de repuesto y compatibilidad (ej: Pantalla OLED original, Batería 3000mAh, Cable USB-C trenzado)';
            
            // Limpiar valores automáticos si vienen de servicio
            if (modelo.value === 'No aplica') modelo.value = '';
            if (precioCompra.value === '0') precioCompra.value = '';
            if (stockActual.value === '0') stockActual.value = '';
            if (stockMinimo.value === '0') stockMinimo.value = '';
            break;
            
        case 'servicio':
            // SERVICIO: Ocultar campos y llenar automáticamente
            contenedorModelo.style.display = 'none';
            contenedorPrecioCompra.style.display = 'none';
            contenedorStockActual.style.display = 'none';
            contenedorStockMinimo.style.display = 'none';
            
            // LLENAR AUTOMÁTICAMENTE con valores por defecto
            modelo.value = 'No aplica';
            precioCompra.value = '0';
            stockActual.value = '0';
            stockMinimo.value = '0';
            
            descripcion.setAttribute('required', 'required');
            descripcion.placeholder = 'OBLIGATORIO: Detalle del servicio (reparación de pantalla, liberación, formateo, instalación, etc.)';
            
            console.log('🛠️ Servicio: Campos llenados automáticamente');
            break;
            
        default:
            // Mostrar todos por defecto
            contenedorModelo.style.display = 'block';
            contenedorPrecioCompra.style.display = 'block';
            contenedorStockActual.style.display = 'block';
            contenedorStockMinimo.style.display = 'block';
            labelModelo.textContent = 'Modelo';
            break;
    }
    
    // Recalcular indicadores
    calcularGanancia();
};

// ========================================
// FUNCIONES PRINCIPALES
// ========================================

// Cargar marcas en el select
const cargarMarcas = async () => {
    try {
        const respuesta = await fetch('/app03_jmp/productos/buscarMarcasAPI', {
            method: 'POST'
        });
        const resultado = await respuesta.json();
        
        const selectMarca = document.getElementById('marca_id');
        selectMarca.innerHTML = '<option value="">Seleccione una marca...</option>';
        
        if (resultado.codigo === 1 && resultado.data) {
            resultado.data.forEach(marca => {
                const option = document.createElement('option');
                option.value = marca.marca_id;
                option.textContent = marca.marca_nombre;
                selectMarca.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando marcas:', error);
    }
};

// Buscar productos
const buscarProductos = async () => {
    console.log('🔍 Iniciando búsqueda de productos...');
    
    try {
        const respuesta = await fetch('/app03_jmp/productos/buscarAPI', {
            method: 'POST'
        });
        
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        const resultado = await respuesta.json();
        console.log('📦 Resultado:', resultado);
        
        if (resultado.codigo === 1) {
            if (resultado.data && resultado.data.length > 0) {
                console.log('✅ Productos encontrados:', resultado.data.length);
                tabla.clear().rows.add(resultado.data).draw();
                mostrarMensaje('success', 'Productos cargados', `${resultado.data.length} productos encontrados`);
            } else {
                console.log('📭 Sin productos:', resultado.mensaje);
                tabla.clear().draw();
                mostrarMensaje('info', 'Sin productos', 'No hay productos registrados. Agregue el primer producto.');
            }
        } else {
            console.log('⚠️ Error del sistema:', resultado.mensaje);
            tabla.clear().draw();
            mostrarMensaje('error', 'Error del sistema', resultado.mensaje);
        }
    } catch (error) {
        console.error('❌ Error de conexión:', error);
        tabla.clear().draw();
        mostrarMensaje('error', 'Error de conexión', 'No se pudo conectar con el servidor. Verifique su conexión.');
    }
};

// Calcular ganancia con lógica para servicios
const calcularGanancia = () => {
    const tipo = document.getElementById('tipo_producto').value;
    const precioCompra = parseFloat(document.getElementById('precio_compra').value) || 0;
    const precioVenta = parseFloat(document.getElementById('precio_venta').value) || 0;
    const stockActual = parseInt(document.getElementById('stock_actual').value) || 0;
    const stockMinimo = parseInt(document.getElementById('stock_minimo').value) || 0;
    
    // Para servicios, la ganancia es el 100% del precio
    const ganancia = tipo === 'servicio' ? precioVenta : (precioVenta - precioCompra);
    const porcentajeGanancia = tipo === 'servicio' ? 100 : (precioCompra > 0 ? ((ganancia / precioCompra) * 100) : 0);
    
    // Mostrar indicadores
    document.getElementById('ganancia').textContent = `Q ${ganancia.toFixed(2)}`;
    document.getElementById('porcentaje_ganancia').textContent = `${porcentajeGanancia.toFixed(1)}%`;
    
    // Estado del stock (no aplica para servicios)
    const alertaStock = document.getElementById('alerta_stock');
    const estadoStock = document.getElementById('estado_stock');
    
    if (tipo === 'servicio') {
        alertaStock.className = 'alert alert-info';
        estadoStock.textContent = 'No Aplica';
    } else {
        if (stockActual <= stockMinimo) {
            alertaStock.className = 'alert alert-danger';
            estadoStock.textContent = 'Stock Bajo';
        } else if (stockActual <= stockMinimo * 2) {
            alertaStock.className = 'alert alert-warning';
            estadoStock.textContent = 'Advertencia';
        } else {
            alertaStock.className = 'alert alert-success';
            estadoStock.textContent = 'Normal';
        }
    }
    
    // Mostrar indicadores
    document.getElementById('indicadores').style.display = 'block';
};

// Validar precios
const validarPrecios = () => {
    const tipo = document.getElementById('tipo_producto').value;
    const precioCompra = parseFloat(document.getElementById('precio_compra').value) || 0;
    const precioVenta = parseFloat(document.getElementById('precio_venta').value) || 0;
    const inputVenta = document.getElementById('precio_venta');
    
    if (tipo === 'servicio') {
        // Para servicios, solo validar que el precio de venta sea mayor a 0
        if (precioVenta > 0) {
            inputVenta.classList.remove('is-invalid');
            inputVenta.classList.add('is-valid');
        }
    } else {
        // Para celulares y repuestos, validar que precio venta > precio compra
        if (precioVenta > 0 && precioCompra > 0) {
            if (precioVenta <= precioCompra) {
                inputVenta.classList.add('is-invalid');
                inputVenta.classList.remove('is-valid');
            } else {
                inputVenta.classList.remove('is-invalid');
                inputVenta.classList.add('is-valid');
            }
        }
    }
    
    calcularGanancia();
};

// Función para garantizar valores por defecto antes de enviar
const garantizarValoresPorDefecto = () => {
    const tipo = document.getElementById('tipo_producto').value;
    
    if (tipo === 'servicio') {
        // Forzar valores para servicios antes de enviar
        document.getElementById('modelo').value = 'No aplica';
        document.getElementById('precio_compra').value = '0';
        document.getElementById('stock_actual').value = '0';
        document.getElementById('stock_minimo').value = '0';
        console.log('🛠️ Valores garantizados para servicio antes de enviar');
    }
};

// ========================================
// FUNCIONES CRUD CON SOLUCIÓN HÍBRIDA
// ========================================

// Guardar producto
const guardarProducto = async (e) => {
    e.preventDefault();
    
    // GARANTIZAR valores por defecto para servicios
    garantizarValoresPorDefecto();
    
    // Usar validación original (ahora todos los campos tienen valores)
    if (!validarFormulario(form, ["producto_id"])) {
        mostrarMensaje('error', 'Error', 'Complete los campos obligatorios');
        return;
    }

    btnGuardar.disabled = true;
    
    try {
        const datos = new URLSearchParams();
        
        // Agregar todos los campos
        datos.append('nombre_producto', document.getElementById('nombre_producto').value.trim());
        datos.append('marca_id', document.getElementById('marca_id').value);
        datos.append('tipo_producto', document.getElementById('tipo_producto').value);
        datos.append('modelo', document.getElementById('modelo').value.trim());
        datos.append('precio_compra', document.getElementById('precio_compra').value);
        datos.append('precio_venta', document.getElementById('precio_venta').value);
        datos.append('stock_actual', document.getElementById('stock_actual').value);
        datos.append('stock_minimo', document.getElementById('stock_minimo').value);
        datos.append('descripcion', document.getElementById('descripcion').value.trim());
        
        const respuesta = await fetch('/app03_jmp/productos/guardarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        const resultado = await respuesta.json();

        if (resultado.codigo === 1) {
            mostrarMensaje('success', 'Éxito', 'Producto guardado correctamente');
            limpiarFormulario();
            buscarProductos();
        } else {
            mostrarMensaje('warning', 'Atención', resultado.mensaje);
        }
    } catch (error) {
        mostrarMensaje('error', 'Error', 'Problema de conexión');
    }
    
    btnGuardar.disabled = false;
};

// Modificar producto
const modificarProducto = async (e) => {
    e.preventDefault();
    
    const productoId = document.getElementById('producto_id').value;
    
    if (!productoId) {
        mostrarMensaje('error', 'Error', 'No se ha seleccionado un producto para modificar');
        return;
    }
    
    // GARANTIZAR valores por defecto para servicios
    garantizarValoresPorDefecto();
    
    // Usar validación original (ahora todos los campos tienen valores)
    if (!validarFormulario(form, ["producto_id"])) {
        mostrarMensaje('error', 'Error', 'Complete los campos obligatorios');
        return;
    }

    btnModificar.disabled = true;
    
    try {
        const datos = new URLSearchParams();
        
        // Agregar todos los campos incluyendo ID
        datos.append('producto_id', productoId);
        datos.append('nombre_producto', document.getElementById('nombre_producto').value.trim());
        datos.append('marca_id', document.getElementById('marca_id').value);
        datos.append('tipo_producto', document.getElementById('tipo_producto').value);
        datos.append('modelo', document.getElementById('modelo').value.trim());
        datos.append('precio_compra', document.getElementById('precio_compra').value);
        datos.append('precio_venta', document.getElementById('precio_venta').value);
        datos.append('stock_actual', document.getElementById('stock_actual').value);
        datos.append('stock_minimo', document.getElementById('stock_minimo').value);
        datos.append('descripcion', document.getElementById('descripcion').value.trim());
        
        const respuesta = await fetch('/app03_jmp/productos/modificarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        const resultado = await respuesta.json();

        if (resultado.codigo === 1) {
            mostrarMensaje('success', 'Éxito', 'Producto actualizado correctamente');
            limpiarFormulario();
            buscarProductos();
        } else {
            mostrarMensaje('warning', 'Atención', resultado.mensaje);
        }
    } catch (error) {
        mostrarMensaje('error', 'Error', 'Problema de conexión');
    }
    
    btnModificar.disabled = false;
};

// Eliminar producto
const eliminarProducto = async (e) => {
    const id = e.target.dataset.id;
    
    const confirmacion = await Swal.fire({
        title: '¿Eliminar producto?',
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
        datos.append('producto_id', id);
        
        const respuesta = await fetch('/app03_jmp/productos/eliminarAPI', {
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
            buscarProductos();
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

// Llenar formulario para modificar
const llenarFormulario = (e) => {
    const producto = JSON.parse(e.target.dataset.producto);
    
    console.log('📦 Producto recibido:', producto);
    
    // Llenar todos los campos
    document.getElementById('producto_id').value = producto.producto_id || '';
    document.getElementById('nombre_producto').value = producto.nombre_producto || '';
    document.getElementById('marca_id').value = producto.marca_id || '';
    document.getElementById('tipo_producto').value = producto.tipo_producto || '';
    document.getElementById('modelo').value = producto.modelo || '';
    document.getElementById('precio_compra').value = producto.precio_compra || '';
    document.getElementById('precio_venta').value = producto.precio_venta || '';
    document.getElementById('stock_actual').value = producto.stock_actual || '';
    document.getElementById('stock_minimo').value = producto.stock_minimo || '';
    document.getElementById('descripcion').value = producto.descripcion || '';

    // Aplicar lógica según el tipo de producto
    manejarTipoProducto();

    btnGuardar.classList.add("d-none");
    btnModificar.classList.remove("d-none");
    
    // Calcular indicadores
    calcularGanancia();
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar formulario
const limpiarFormulario = () => {
    form.reset();
    document.getElementById('producto_id').value = '';
    
    btnGuardar.classList.remove("d-none");
    btnModificar.classList.add("d-none");
    
    form.querySelectorAll('.form-control, .form-select').forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
    });
    
    document.getElementById('indicadores').style.display = 'none';
    
    // Resetear la lógica de tipos
    manejarTipoProducto();
};

// Ver productos con stock bajo
const verStockBajo = async () => {
    try {
        const respuesta = await fetch('/app03_jmp/productos/stockBajoAPI', {
            method: 'POST'
        });
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1 && resultado.data.length > 0) {
            tabla.clear().rows.add(resultado.data).draw();
            mostrarMensaje('warning', 'Stock Bajo', `Se encontraron ${resultado.data.length} productos con stock bajo`);
        } else {
            mostrarMensaje('success', 'Excelente', 'No hay productos con stock bajo');
            buscarProductos();
        }
    } catch (error) {
        mostrarMensaje('error', 'Error', 'Error al consultar stock bajo');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM cargado, iniciando aplicación de productos...');
    
    // Cargar datos iniciales
    cargarMarcas();
    buscarProductos();
    
    // Formulario
    form.addEventListener("submit", guardarProducto);
    btnLimpiar.addEventListener("click", limpiarFormulario);
    btnModificar.addEventListener("click", modificarProducto);
    btnStockBajo.addEventListener("click", verStockBajo);
    
    // Event listener para tipo de producto
    document.getElementById("tipo_producto").addEventListener("change", manejarTipoProducto);
    
    // Validaciones en tiempo real
    document.getElementById("precio_compra").addEventListener("input", validarPrecios);
    document.getElementById("precio_venta").addEventListener("input", validarPrecios);
    document.getElementById("stock_actual").addEventListener("input", calcularGanancia);
    document.getElementById("stock_minimo").addEventListener("input", calcularGanancia);
    
    // Eventos de la tabla
    tabla.on("click", ".modificar", llenarFormulario);
    tabla.on("click", ".eliminar", eliminarProducto);
});

// Exponer función globalmente
window.buscarProductos = buscarProductos;