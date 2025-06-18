import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

const form = document.getElementById("FormProductos");
const btnGuardar = document.getElementById("BtnGuardar");
const btnModificar = document.getElementById("BtnModificar");
const btnLimpiar = document.getElementById("BtnLimpiar");
const btnStockBajo = document.getElementById("btnStockBajo");

let productosData = [];

const tabla = new DataTable("#TableProductos", {
    language: lenguaje,
    data: [],
    pageLength: 25,
    columns: [
        { title: "No.", data: null, render: (data, type, row, meta) => meta.row + 1, width: "4%" },
        { title: "Producto", data: "nombre_producto", defaultContent: "", width: "12%" },
        { title: "Marca", data: "marca_nombre", defaultContent: "", width: "8%" },
        { 
            title: "Tipo", data: "tipo_producto", defaultContent: "", width: "7%",
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
            title: "Descripción", data: "descripcion", defaultContent: "", width: "15%",
            render: (data) => {
                if (!data?.trim()) return '<em class="text-muted">Sin descripción</em>';
                return data.length > 50 ? `<span title="${data}">${data.substring(0, 50)}...</span>` : data;
            }
        },
        { 
            title: "P. Compra", data: "precio_compra", width: "7%",
            render: (data) => `Q ${parseFloat(data || 0).toFixed(2)}`
        },
        { 
            title: "P. Venta", data: "precio_venta", width: "7%",
            render: (data) => `Q ${parseFloat(data || 0).toFixed(2)}`
        },
        { 
            title: "Stock", data: "stock_actual", width: "5%",
            render: (data, type, row) => {
                if (row.tipo_producto === 'servicio') return '<span class="text-muted">N/A</span>';
                const stock = parseInt(data || 0);
                const minimo = parseInt(row.stock_minimo || 0);
                const clase = stock <= minimo ? 'text-danger fw-bold' : 'text-success';
                return `<span class="${clase}">${stock}</span>`;
            }
        },
        {
            title: "Estado", data: "stock_actual", width: "8%",
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
            title: "Acciones", data: "producto_id", orderable: false, width: "16%",
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

const manejarTipoProducto = () => {
    const tipo = document.getElementById('tipo_producto').value;
    const elementos = {
        modelo: document.getElementById('modelo'),
        precioCompra: document.getElementById('precio_compra'),
        stockActual: document.getElementById('stock_actual'),
        stockMinimo: document.getElementById('stock_minimo'),
        descripcion: document.getElementById('descripcion')
    };
    
    const contenedores = {
        modelo: elementos.modelo.closest('.col-lg-4'),
        precioCompra: elementos.precioCompra.closest('.col-lg-4'),
        stockActual: elementos.stockActual.closest('.col-lg-4'),
        stockMinimo: elementos.stockMinimo.closest('.col-lg-4')
    };
    
    Object.values(elementos).forEach(input => {
        input.classList.remove('is-invalid', 'is-valid');
        input.removeAttribute('required');
    });
    
    const configuraciones = {
        celular: {
            mostrar: ['modelo', 'precioCompra', 'stockActual', 'stockMinimo'],
            requeridos: ['modelo', 'precioCompra', 'stockActual', 'stockMinimo'],
            placeholders: {
                modelo: 'Ej: iPhone 15 Pro Max, Galaxy S24 Ultra',
                descripcion: 'Características del celular (color, memoria, estado, etc.)'
            }
        },
        repuesto: {
            mostrar: ['modelo', 'precioCompra', 'stockActual', 'stockMinimo'],
            requeridos: ['precioCompra', 'stockActual', 'stockMinimo', 'descripcion'],
            placeholders: {
                modelo: 'Ej: iPhone 14 Pro, Galaxy S22, Universal, Genérico',
                descripcion: 'OBLIGATORIO: Especifique tipo de repuesto y compatibilidad'
            }
        },
        servicio: {
            mostrar: [],
            requeridos: ['descripcion'],
            valores: { modelo: 'No aplica', precioCompra: '0', stockActual: '0', stockMinimo: '0' },
            placeholders: {
                descripcion: 'OBLIGATORIO: Detalle del servicio (reparación de pantalla, liberación, etc.)'
            }
        }
    };
    
    const config = configuraciones[tipo] || configuraciones.celular;
    
    Object.keys(contenedores).forEach(key => {
        contenedores[key].style.display = config.mostrar.includes(key) ? 'block' : 'none';
    });
    
    if (config.valores) {
        Object.entries(config.valores).forEach(([campo, valor]) => {
            elementos[campo].value = valor;
        });
    }
    
    config.requeridos.forEach(campo => elementos[campo]?.setAttribute('required', 'required'));
    Object.entries(config.placeholders || {}).forEach(([campo, placeholder]) => {
        if (elementos[campo]) elementos[campo].placeholder = placeholder;
    });
    
    calcularGanancia();
};

const cargarMarcas = async () => {
    try {
        // Usar ruta absoluta para la API
        const respuesta = await fetch('/app03_jmp/productos/buscarMarcasAPI', {
            method: 'POST'
        });
        
        if (!respuesta.ok) {
            throw new Error(`Error HTTP: ${respuesta.status}`);
        }
        
        const resultado = await respuesta.json();
        
        const selectMarca = document.getElementById('marca_id');
        selectMarca.innerHTML = '<option value="">Seleccione una marca...</option>';
        
        if (resultado.codigo === 1 && resultado.data) {
            resultado.data.forEach(marca => {
                selectMarca.insertAdjacentHTML('beforeend', 
                    `<option value="${marca.marca_id}">${marca.marca_nombre}</option>`
                );
            });
        } else {
            console.warn("No se encontraron marcas:", resultado.mensaje);
            mostrarMensaje('warning', 'Sin marcas', 'No hay marcas disponibles. Por favor, agregue marcas primero.');
        }
    } catch (error) {
        console.error('Error cargando marcas:', error);
        mostrarMensaje('error', 'Error', `No se pudieron cargar las marcas: ${error.message}`);
    }
};

const buscarProductos = async () => {
    console.log('🔍 Iniciando búsqueda de productos...');
    
    try {
        // Usar ruta absoluta para la API
        const respuesta = await fetch('/app03_jmp/productos/buscarAPI', {
            method: 'POST'
        });
        
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        const resultado = await respuesta.json();
        console.log('📦 Resultado:', resultado);
        
        if (resultado.codigo === 1) {
            productosData = resultado.data || [];
            if (productosData.length > 0) {
                tabla.clear().rows.add(productosData).draw();
                mostrarMensaje('success', 'Productos cargados', `${productosData.length} productos encontrados`);
            } else {
                tabla.clear().draw();
                mostrarMensaje('info', 'Sin productos', 'No hay productos registrados. Agregue el primer producto.');
            }
        } else {
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
        const estados = {
            critico: { clase: 'alert alert-danger', texto: 'Stock Bajo' },
            advertencia: { clase: 'alert alert-warning', texto: 'Advertencia' },
            normal: { clase: 'alert alert-success', texto: 'Normal' }
        };
        
        const estado = stockActual <= stockMinimo ? 'critico' : 
                      stockActual <= stockMinimo * 2 ? 'advertencia' : 'normal';
        
        alertaStock.className = estados[estado].clase;
        estadoStock.textContent = estados[estado].texto;
    }
    
    document.getElementById('indicadores').style.display = 'block';
};

const validarPrecios = () => {
    const tipo = document.getElementById('tipo_producto').value;
    const precioCompra = parseFloat(document.getElementById('precio_compra').value) || 0;
    const precioVenta = parseFloat(document.getElementById('precio_venta').value) || 0;
    const inputVenta = document.getElementById('precio_venta');
    
    const esValido = tipo === 'servicio' ? precioVenta > 0 : 
                     (precioVenta > 0 && precioCompra > 0 && precioVenta > precioCompra);
    
    inputVenta.classList.toggle('is-valid', esValido);
    inputVenta.classList.toggle('is-invalid', !esValido);
    
    calcularGanancia();
};

const garantizarValoresPorDefecto = () => {
    if (document.getElementById('tipo_producto').value === 'servicio') {
        ['modelo', 'precio_compra', 'stock_actual', 'stock_minimo'].forEach(campo => {
            const valores = { modelo: 'No aplica', precio_compra: '0', stock_actual: '0', stock_minimo: '0' };
            document.getElementById(campo).value = valores[campo];
        });
    }
};

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
        
        // Usar ruta absoluta para la API
        const respuesta = await fetch('/app03_jmp/productos/guardarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        
        if (!respuesta.ok) {
            throw new Error(`Error HTTP: ${respuesta.status}`);
        }
        
        const resultado = await respuesta.json();

        if (resultado.codigo === 1) {
            mostrarMensaje('success', 'Éxito', 'Producto guardado correctamente');
            limpiarFormulario();
            buscarProductos();
        } else {
            mostrarMensaje('warning', 'Atención', resultado.mensaje);
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('error', 'Error', `Problema de conexión: ${error.message}`);
    }
    
    btnGuardar.disabled = false;
};

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
        
        // Usar ruta absoluta para la API
        const respuesta = await fetch('/app03_jmp/productos/modificarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        
        if (!respuesta.ok) {
            throw new Error(`Error HTTP: ${respuesta.status}`);
        }
        
        const resultado = await respuesta.json();

        if (resultado.codigo === 1) {
            mostrarMensaje('success', 'Éxito', 'Producto actualizado correctamente');
            limpiarFormulario();
            buscarProductos();
        } else {
            mostrarMensaje('warning', 'Atención', resultado.mensaje);
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('error', 'Error', `Problema de conexión: ${error.message}`);
    }
    
    btnModificar.disabled = false;
};

const eliminarProducto = async (e) => {
    const id = e.target.dataset.id || e.target.closest('.eliminar')?.dataset.id;
    
    if (!id) {
        mostrarMensaje('error', 'Error', 'ID de producto no identificado');
        return;
    }
    
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
        
        // Usar ruta absoluta para la API
        const respuesta = await fetch('/app03_jmp/productos/eliminarAPI', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: datos
        });
        
        if (!respuesta.ok) {
            throw new Error(`Error HTTP: ${respuesta.status}`);
        }
        
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
            text: `Ocurrió un problema técnico: ${error.message}. Intente nuevamente.`,
            confirmButtonText: 'OK',
            confirmButtonColor: '#e74c3c'
        });
    }
};

const llenarFormulario = (e) => {
    try {
        const index = parseInt(e.target.closest('.modificar').dataset.index);
        const producto = productosData[index];
        
        if (!producto) {
            mostrarMensaje('error', 'Error', 'No se encontró el producto');
            return;
        }
        
        ['producto_id', 'nombre_producto', 'marca_id', 'tipo_producto', 'modelo', 
         'precio_compra', 'precio_venta', 'stock_actual', 'stock_minimo', 'descripcion']
        .forEach(campo => {
            const input = document.getElementById(campo);
            if (input) input.value = producto[campo] || '';
        });

        manejarTipoProducto();
        btnGuardar.classList.add("d-none");
        btnModificar.classList.remove("d-none");
        calcularGanancia();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
    } catch (error) {
        console.error('❌ Error al llenar formulario:', error);
        mostrarMensaje('error', 'Error', 'No se pudo cargar los datos del producto');
    }
};

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

const verStockBajo = async () => {
    try {
        // Usar ruta absoluta para la API
        const respuesta = await fetch('/app03_jmp/productos/stockBajoAPI', {
            method: 'POST'
        });
        
        if (!respuesta.ok) {
            throw new Error(`Error HTTP: ${respuesta.status}`);
        }
        
        const resultado = await respuesta.json();
        
        if (resultado.codigo === 1 && resultado.data.length > 0) {
            productosData = resultado.data;
            tabla.clear().rows.add(resultado.data).draw();
            mostrarMensaje('warning', 'Stock Bajo', `Se encontraron ${resultado.data.length} productos con stock bajo`);
        } else {
            mostrarMensaje('success', 'Excelente', 'No hay productos con stock bajo');
            buscarProductos();
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('error', 'Error', `Error al consultar stock bajo: ${error.message}`);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM cargado, iniciando aplicación de productos...');
    
    cargarMarcas();
    buscarProductos();
    
    form.addEventListener("submit", guardarProducto);
    btnLimpiar?.addEventListener("click", limpiarFormulario);
    btnModificar?.addEventListener("click", modificarProducto);
    btnStockBajo?.addEventListener("click", verStockBajo);
    
    document.getElementById("tipo_producto")?.addEventListener("change", manejarTipoProducto);
    document.getElementById("precio_compra")?.addEventListener("input", validarPrecios);
    document.getElementById("precio_venta")?.addEventListener("input", validarPrecios);
    document.getElementById("stock_actual")?.addEventListener("input", calcularGanancia);
    document.getElementById("stock_minimo")?.addEventListener("input", calcularGanancia);
    
    tabla.on("click", ".modificar", llenarFormulario);
    tabla.on("click", ".eliminar", eliminarProducto);
    
    // Inicializar otros componentes si es necesario
    const btnActualizar = document.getElementById('btnActualizar');
    if (btnActualizar) {
        btnActualizar.addEventListener('click', buscarProductos);
    }
});

window.buscarProductos = buscarProductos;
window.verStockBajo = verStockBajo;