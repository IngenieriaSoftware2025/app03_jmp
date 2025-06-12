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

let productosData = [];

// Tabla de productos optimizada
const tabla = new DataTable("#TableProductos", {
    language: lenguaje,
    data: [],
    pageLength: 25,
    columns: [
        { title: "No.", data: null, render: (data, type, row, meta) => meta.row + 1, width: "4%" },
        { title: "Producto", data: "nombre_producto", defaultContent: "", width: "12%" },
        { title: "Marca", data: "marca_nombre", defaultContent: "", width: "8%" },
        { 
            title: "Tipo", 
            data: "tipo_producto", 
            defaultContent: "",
            width: "7%",
            render: (data) => {
                const badges = {
                    'celular': '<span class="badge bg-primary">Celular</span>',
                    'repuesto': '<span class="badge bg-info">Repuesto</span>',
                    'servicio': '<span class="badge bg-secondary">Servicio</span>'
                };
                return badges[data] || data;
            }
        },
        { title: "Modelo", data: "modelo", defaultContent: "", width: "10%" },
        { 
            title: "Descripción", 
            data: "descripcion", 
            defaultContent: "", 
            width: "15%",
            render: (data) => {
                if (!data || data.trim() === '') return '<em class="text-muted">Sin descripción</em>';
                return data.length > 50 ? 
                    `<span title="${data}">${data.substring(0, 50)}...</span>` : 
                    data;
            }
        },
        { 
            title: "P. Compra", 
            data: "precio_compra", 
            width: "7%",
            render: (data) => `Q ${parseFloat(data || 0).toFixed(2)}`
        },
        { 
            title: "P. Venta", 
            data: "precio_venta", 
            width: "7%",
            render: (data) => `Q ${parseFloat(data || 0).toFixed(2)}`
        },
        { 
            title: "Stock", 
            data: "stock_actual",
            width: "5%",
            render: (data, type, row) => {
                if (row.tipo_producto === 'servicio') return '<span class="text-muted">N/A</span>';
                const stock = parseInt(data || 0);
                const minimo = parseInt(row.stock_minimo || 0);
                const clase = stock <= minimo ? 'text-danger fw-bold' : 'text-success';
                return `<span class="${clase}">${stock}</span>`;
            }
        },
        {
            title: "Estado",
            data: "stock_actual",
            width: "8%",
            render: (data, type, row) => {
                if (row.tipo_producto === 'servicio') return '<span class="badge bg-info">Servicio</span>';
                const stock = parseInt(data || 0);
                const minimo = parseInt(row.stock_minimo || 0);
                if (stock === 0) return '<span class="badge bg-danger">Agotado</span>';
                if (stock <= minimo) return '<span class="badge bg-danger">Stock Bajo</span>';
                if (stock <= minimo * 2) return '<span class="badge bg-warning">Advertencia</span>';
                return '<span class="badge bg-success">Normal</span>';
            }
        },
        {
            title: "Acciones",
            data: "producto_id",
            orderable: false,
            width: "16%",
            render: (data, type, row, meta) => {
                if (!data) return '';
                
                return `
                    <button class="btn btn-warning btn-sm modificar" 
                            data-index="${meta.row}" 
                            title="Modificar">
                        Modificar
                    </button>
                    <button class="btn btn-danger btn-sm eliminar ms-1" 
                            data-id="${data}" 
                            title="Eliminar">
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
            
            if (modelo.value === 'No aplica') modelo.value = '';
            if (precioCompra.value === '0') precioCompra.value = '';
            if (stockActual.value === '0') stockActual.value = '';
            if (stockMinimo.value === '0') stockMinimo.value = '';
            break;
            
        case 'repuesto':
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
            
            if (modelo.value === 'No aplica') modelo.value = '';
            if (precioCompra.value === '0') precioCompra.value = '';
            if (stockActual.value === '0') stockActual.value = '';
            if (stockMinimo.value === '0') stockMinimo.value = '';
            break;
            
        case 'servicio':
            contenedorModelo.style.display = 'none';
            contenedorPrecioCompra.style.display = 'none';
            contenedorStockActual.style.display = 'none';
            contenedorStockMinimo.style.display = 'none';
            
            modelo.value = 'No aplica';
            precioCompra.value = '0';
            stockActual.value = '0';
            stockMinimo.value = '0';
            
            descripcion.setAttribute('required', 'required');
            descripcion.placeholder = 'OBLIGATORIO: Detalle del servicio (reparación de pantalla, liberación, formateo, instalación, etc.)';
            
            console.log('🛠️ Servicio: Campos llenados automáticamente');
            break;
            
        default:
            contenedorModelo.style.display = 'block';
            contenedorPrecioCompra.style.display = 'block';
            contenedorStockActual.style.display = 'block';
            contenedorStockMinimo.style.display = 'block';
            labelModelo.textContent = 'Modelo';
            break;
    }
    
    calcularGanancia();
};

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
                

                productosData = resultado.data;
                
                tabla.clear().rows.add(resultado.data).draw();
                mostrarMensaje('success', 'Productos cargados', `${resultado.data.length} productos encontrados`);
            } else {
                console.log('📭 Sin productos:', resultado.mensaje);
                productosData = [];
                tabla.clear().draw();
                mostrarMensaje('info', 'Sin productos', 'No hay productos registrados. Agregue el primer producto.');
            }
        } else {
            console.log('⚠️ Error del sistema:', resultado.mensaje);
            productosData = [];
            tabla.clear().draw();
            mostrarMensaje('error', 'Error del sistema', resultado.mensaje);
        }
    } catch (error) {
        console.error('❌ Error de conexión:', error);
        productosData = [];
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
    
    const ganancia = tipo === 'servicio' ? precioVenta : (precioVenta - precioCompra);
    const porcentajeGanancia = tipo === 'servicio' ? 100 : (precioCompra > 0 ? ((ganancia / precioCompra) * 100) : 0);
    
    document.getElementById('ganancia').textContent = `Q ${ganancia.toFixed(2)}`;
    document.getElementById('porcentaje_ganancia').textContent = `${porcentajeGanancia.toFixed(1)}%`;
    
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
    
    document.getElementById('indicadores').style.display = 'block';
};

// Validar precios
const validarPrecios = () => {
    const tipo = document.getElementById('tipo_producto').value;
    const precioCompra = parseFloat(document.getElementById('precio_compra').value) || 0;
    const precioVenta = parseFloat(document.getElementById('precio_venta').value) || 0;
    const inputVenta = document.getElementById('precio_venta');
    
    if (tipo === 'servicio') {
        if (precioVenta > 0) {
            inputVenta.classList.remove('is-invalid');
            inputVenta.classList.add('is-valid');
        }
    } else {
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
        document.getElementById('modelo').value = 'No aplica';
        document.getElementById('precio_compra').value = '0';
        document.getElementById('stock_actual').value = '0';
        document.getElementById('stock_minimo').value = '0';
        console.log('🛠️ Valores garantizados para servicio antes de enviar');
    }
};

// Guardar producto
const guardarProducto = async (e) => {
    e.preventDefault();
    
    garantizarValoresPorDefecto();
    
    if (!validarFormulario(form, ["producto_id"])) {
        mostrarMensaje('error', 'Error', 'Complete los campos obligatorios');
        return;
    }

    btnGuardar.disabled = true;
    
    try {
        const datos = new URLSearchParams();
        
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
    
    garantizarValoresPorDefecto();
    
    if (!validarFormulario(form, ["producto_id"])) {
        mostrarMensaje('error', 'Error', 'Complete los campos obligatorios');
        return;
    }

    btnModificar.disabled = true;
    
    try {
        const datos = new URLSearchParams();
        
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


const llenarFormulario = (e) => {
    try {
        // OBTENER ÍNDICE DEL BOTÓN
        const index = parseInt(e.target.dataset.index);
        
        // OBTENER PRODUCTO DE LA VARIABLE GLOBAL
        const producto = productosData[index];
        
        if (!producto) {
            mostrarMensaje('error', 'Error', 'No se encontró el producto');
            return;
        }
        
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
        
    } catch (error) {
        console.error('❌ Error al llenar formulario:', error);
        mostrarMensaje('error', 'Error', 'No se pudo cargar los datos del producto');
    }
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
            // ACTUALIZAR TAMBIÉN LA VARIABLE GLOBAL
            productosData = resultado.data;
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
    
    cargarMarcas();
    buscarProductos();
    
    form.addEventListener("submit", guardarProducto);
    btnLimpiar.addEventListener("click", limpiarFormulario);
    btnModificar.addEventListener("click", modificarProducto);
    btnStockBajo.addEventListener("click", verStockBajo);
    
    document.getElementById("tipo_producto").addEventListener("change", manejarTipoProducto);
    
    document.getElementById("precio_compra").addEventListener("input", validarPrecios);
    document.getElementById("precio_venta").addEventListener("input", validarPrecios);
    document.getElementById("stock_actual").addEventListener("input", calcularGanancia);
    document.getElementById("stock_minimo").addEventListener("input", calcularGanancia);
    
    tabla.on("click", ".modificar", llenarFormulario);
    tabla.on("click", ".eliminar", eliminarProducto);
});

window.buscarProductos = buscarProductos;