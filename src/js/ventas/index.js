import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Variables globales
let clienteSeleccionado = null;
let productosCarrito = [];
let totalVenta = 0;
let tablaProductos, tablaHistorial;

// Elementos del DOM
const inputTelefono = document.getElementById('cliente_telefono');
const btnBuscarCliente = document.getElementById('btnBuscarCliente');
const infoCliente = document.getElementById('infoCliente');
const seccionProductos = document.getElementById('seccionProductos');
const carritoVenta = document.getElementById('carritoVenta');
const totalElement = document.getElementById('totalVenta');
const btnProcesarVenta = document.getElementById('btnProcesarVenta');

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

// Validar teléfono (solo números)
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

// Buscar cliente por teléfono
const buscarCliente = async () => {
    const telefono = inputTelefono.value.trim();

    if (!telefono || telefono.length !== 8) {
        mostrarMensaje('warning', 'Atención', 'Ingrese un teléfono válido de 8 dígitos');
        return;
    }

    try {
        console.log('Buscando cliente con teléfono:', telefono);
        const datos = new URLSearchParams();
        datos.append('telefono', telefono);

        // Usar ruta absoluta para la API
        const respuesta = await fetch('/app03_jmp/ventas/buscarClienteAPI', {
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
        console.log('Resultado búsqueda cliente:', resultado);

        if (resultado.codigo === 1) {
            clienteSeleccionado = resultado.data;
            mostrarInfoCliente(clienteSeleccionado);
            cargarProductos();
        } else {
            mostrarMensaje('error', 'Cliente no encontrado', resultado.mensaje);
            limpiarInfoCliente();
        }
    } catch (error) {
        console.error('Error completo:', error);
        mostrarMensaje('error', 'Error', `Error de conexión: ${error.message}`);
    }
};

// Mostrar información del cliente
const mostrarInfoCliente = (cliente) => {
    infoCliente.innerHTML = `
        <div class="alert alert-success">
            <h5><i class="bi bi-person-check"></i> Cliente: ${cliente.cliente_nombres} ${cliente.cliente_apellidos}</h5>
            <p class="mb-0"><strong>Teléfono:</strong> ${cliente.cliente_telefono}</p>
            ${cliente.cliente_correo ? `<p class="mb-0"><strong>Correo:</strong> ${cliente.cliente_correo}</p>` : ''}
            ${cliente.cliente_direccion ? `<p class="mb-0"><strong>Dirección:</strong> ${cliente.cliente_direccion}</p>` : ''}
        </div>
    `;
    infoCliente.style.display = 'block';
};

// Limpiar información del cliente
const limpiarInfoCliente = () => {
    clienteSeleccionado = null;
    infoCliente.style.display = 'none';
    infoCliente.innerHTML = '';
    limpiarCarrito();
    if (seccionProductos) seccionProductos.style.display = 'none';
};

// Cargar productos disponibles
const cargarProductos = async () => {
    try {
        console.log('Cargando productos disponibles...');
        // Usar ruta absoluta para la API
        const respuesta = await fetch('/app03_jmp/ventas/buscarProductosAPI', {
            method: 'POST'
        });

        if (!respuesta.ok) {
            throw new Error(`Error HTTP: ${respuesta.status}`);
        }

        const resultado = await respuesta.json();
        console.log('Resultado productos:', resultado);

        if (resultado.codigo === 1) {
            mostrarTablaProductos(resultado.data);
        } else {
            mostrarMensaje('warning', 'Sin productos', resultado.mensaje || 'No hay productos disponibles');
        }
    } catch (error) {
        console.error('Error completo:', error);
        mostrarMensaje('error', 'Error', `Error al cargar productos: ${error.message}`);
    }
};

// Mostrar tabla de productos
const mostrarTablaProductos = (productos) => {
    if (tablaProductos) {
        tablaProductos.destroy();
    }

    console.log('Mostrando tabla de productos:', productos.length);

    tablaProductos = new DataTable('#tablaProductosVenta', {
        language: lenguaje,
        data: productos,
        pageLength: 10,
        columns: [
            { title: "Producto", data: "nombre_producto", width: "25%" },
            { title: "Marca", data: "marca_nombre", width: "15%" },
            {
                title: "Tipo", data: "tipo_producto", width: "12%",
                render: (data) => {
                    const badges = {
                        'celular': '<span class="badge bg-primary">Celular</span>',
                        'repuesto': '<span class="badge bg-info">Repuesto</span>',
                        'servicio': '<span class="badge bg-secondary">Servicio</span>'
                    };
                    return badges[data] || data;
                }
            },
            {
                title: "Precio", data: "precio_venta", width: "12%",
                render: data => `Q ${parseFloat(data).toFixed(2)}`
            },
            {
                title: "Stock", data: "stock_actual", width: "8%",
                render: (data, type, row) => {
                    if (row.tipo_producto === 'servicio') return '<span class="text-muted">N/A</span>';
                    return `<span class="badge ${data > 0 ? 'bg-success' : 'bg-danger'}">${data}</span>`;
                }
            },
            {
                title: "Acciones", data: null, orderable: false, width: "18%",
                render: (data, type, row) => `
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control cantidad-input" 
                               data-producto-id="${row.producto_id}" 
                               min="1" max="${row.tipo_producto === 'servicio' ? '99' : row.stock_actual}" 
                               value="1" style="width: 60px;">
                        <button class="btn btn-primary agregar-producto" 
                                data-producto='${JSON.stringify(row)}'>
                            <i class="bi bi-cart-plus"></i> Agregar
                        </button>
                    </div>
                `
            }
        ]
    });

    // Event listener para agregar productos
    document.querySelectorAll('.agregar-producto').forEach(btn => {
        btn.addEventListener('click', function () {
            try {
                const producto = JSON.parse(this.dataset.producto);
                const cantidadInput = this.parentElement.querySelector('.cantidad-input');
                const cantidad = parseInt(cantidadInput.value) || 1;
                agregarAlCarrito(producto, cantidad);
            } catch (error) {
                console.error('Error al agregar producto:', error);
                mostrarMensaje('error', 'Error', 'No se pudo agregar el producto');
            }
        });
    });

    if (seccionProductos) seccionProductos.style.display = 'block';
};

// Agregar producto al carrito
const agregarAlCarrito = (producto, cantidad = 1) => {
    // Verificar stock disponible
    if (producto.tipo_producto !== 'servicio' && cantidad > producto.stock_actual) {
        mostrarMensaje('warning', 'Stock insuficiente', `Solo hay ${producto.stock_actual} unidades disponibles`);
        return;
    }

    // Verificar si ya está en el carrito
    const existente = productosCarrito.find(p => p.producto_id === producto.producto_id);

    if (existente) {
        const nuevaCantidad = existente.cantidad + cantidad;
        if (producto.tipo_producto !== 'servicio' && nuevaCantidad > producto.stock_actual) {
            mostrarMensaje('warning', 'Stock insuficiente', `Solo puedes agregar ${producto.stock_actual - existente.cantidad} unidades más`);
            return;
        }
        existente.cantidad = nuevaCantidad;
        existente.subtotal = existente.cantidad * existente.precio_unitario;
    } else {
        productosCarrito.push({
            producto_id: producto.producto_id,
            nombre_producto: producto.nombre_producto,
            precio_unitario: parseFloat(producto.precio_venta),
            cantidad: cantidad,
            subtotal: cantidad * parseFloat(producto.precio_venta),
            tipo_producto: producto.tipo_producto,
            stock_disponible: producto.stock_actual
        });
    }

    actualizarCarrito();
    mostrarMensaje('success', 'Producto agregado', `${cantidad} ${producto.nombre_producto} agregado al carrito`);
};

// Actualizar carrito
const actualizarCarrito = () => {
    if (productosCarrito.length === 0) {
        carritoVenta.innerHTML = '<p class="text-muted text-center">Carrito vacío</p>';
        totalVenta = 0;
    } else {
        let html = '<div class="table-responsive"><table class="table table-sm table-striped">';
        html += '<thead class="table-dark"><tr><th>Producto</th><th>Precio</th><th>Cant.</th><th>Subtotal</th><th>Acciones</th></tr></thead><tbody>';

        totalVenta = 0;
        productosCarrito.forEach((producto, index) => {
            totalVenta += producto.subtotal;
            html += `
                <tr>
                    <td>${producto.nombre_producto}</td>
                    <td>Q ${producto.precio_unitario.toFixed(2)}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm cantidad-producto" 
                               data-index="${index}" value="${producto.cantidad}" 
                               min="1" max="${producto.tipo_producto === 'servicio' ? '99' : producto.stock_disponible}" 
                               style="width: 70px;">
                    </td>
                    <td class="fw-bold">Q ${producto.subtotal.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-danger btn-sm eliminar-producto" data-index="${index}" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table></div>';
        carritoVenta.innerHTML = html;

        // Event listeners para cantidad y eliminar
        document.querySelectorAll('.cantidad-producto').forEach(input => {
            input.addEventListener('change', cambiarCantidad);
        });

        document.querySelectorAll('.eliminar-producto').forEach(btn => {
            btn.addEventListener('click', eliminarDelCarrito);
        });
    }

    if (totalElement) totalElement.textContent = `Q ${totalVenta.toFixed(2)}`;
    if (btnProcesarVenta) btnProcesarVenta.disabled = productosCarrito.length === 0;
};

// Cambiar cantidad de producto
const cambiarCantidad = (e) => {
    const index = parseInt(e.target.dataset.index);
    const nuevaCantidad = parseInt(e.target.value);

    if (nuevaCantidad > 0 && nuevaCantidad <= (productosCarrito[index].stock_disponible || 99)) {
        productosCarrito[index].cantidad = nuevaCantidad;
        productosCarrito[index].subtotal = nuevaCantidad * productosCarrito[index].precio_unitario;
        actualizarCarrito();
    } else {
        mostrarMensaje('warning', 'Cantidad inválida', 'Verifique la cantidad disponible');
        e.target.value = productosCarrito[index].cantidad;
    }
};

// Eliminar producto del carrito
const eliminarDelCarrito = (e) => {
    const index = parseInt(e.target.closest('.eliminar-producto').dataset.index);
    productosCarrito.splice(index, 1);
    actualizarCarrito();
    mostrarMensaje('info', 'Producto eliminado', 'Producto removido del carrito');
};

// Limpiar carrito
const limpiarCarrito = () => {
    productosCarrito = [];
    actualizarCarrito();
    if (seccionProductos) seccionProductos.style.display = 'none';
};

// Procesar venta
const procesarVenta = async () => {

    // Procesar venta
    const procesarVenta = async () => {
        if (!clienteSeleccionado || productosCarrito.length === 0) {
            mostrarMensaje('warning', 'Datos incompletos', 'Seleccione un cliente y agregue productos');
            return;
        }

        // Mostrar resumen de la venta
        let resumenHtml = '<div class="text-start"><h6>Resumen de la venta:</h6><ul>';
        productosCarrito.forEach(producto => {
            resumenHtml += `<li>${producto.cantidad}x ${producto.nombre_producto} - Q ${producto.subtotal.toFixed(2)}</li>`;
        });
        resumenHtml += '</ul></div>';

        const confirmacion = await Swal.fire({
            title: '¿Procesar venta?',
            html: `
            <div class="text-center mb-3">
                <p><strong>Cliente:</strong> ${clienteSeleccionado.cliente_nombres} ${clienteSeleccionado.cliente_apellidos}</p>
                <p><strong>Total:</strong> <span class="fs-4 text-success">Q ${totalVenta.toFixed(2)}</span></p>
            </div>
            ${resumenHtml}
        `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, procesar venta',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        });

        if (!confirmacion.isConfirmed) return;

        btnProcesarVenta.disabled = true;

        try {
            console.log('Procesando venta...');
            const datos = new URLSearchParams();
            datos.append('cliente_id', clienteSeleccionado.cliente_id);
            datos.append('productos', JSON.stringify(productosCarrito));
            datos.append('total', totalVenta);
            datos.append('tipo_venta', 'venta');
            datos.append('descripcion', `Venta de ${productosCarrito.length} producto(s)`);

            // Usar ruta absoluta para la API
            const respuesta = await fetch('/app03_jmp/ventas/procesarVentaAPI', {
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
            console.log('Resultado procesarVenta:', resultado);

            if (resultado.codigo === 1) {
                await Swal.fire({
                    icon: 'success',
                    title: '¡Venta procesada!',
                    html: `
                    <p>La venta se procesó exitosamente</p>
                    <p><strong>ID de Venta:</strong> ${resultado.data?.venta_id || 'N/A'}</p>
                    <p><strong>Total:</strong> Q ${totalVenta.toFixed(2)}</p>
                `,
                    confirmButtonText: 'Continuar',
                    confirmButtonColor: '#28a745'
                });
                limpiarFormulario();
                cargarHistorial();
            } else {
                mostrarMensaje('error', 'Error', resultado.mensaje || 'Error al procesar la venta');
            }
        } catch (error) {
            console.error('Error completo:', error);
            mostrarMensaje('error', 'Error', `Error al procesar la venta: ${error.message}`);
        }

        btnProcesarVenta.disabled = false;
    };

    // Limpiar formulario
    const limpiarFormulario = () => {
        if (inputTelefono) inputTelefono.value = '';
        limpiarInfoCliente();
        if (inputTelefono) {
            inputTelefono.classList.remove('is-valid', 'is-invalid');
        }
    };

    // Cargar historial de ventas
    const cargarHistorial = async () => {
        try {
            console.log('Cargando historial de ventas...');
            // Usar ruta absoluta para la API
            const respuesta = await fetch('/app03_jmp/ventas/historialAPI', {
                method: 'POST'
            });

            if (!respuesta.ok) {
                throw new Error(`Error HTTP: ${respuesta.status}`);
            }

            const resultado = await respuesta.json();
            console.log('Resultado historial:', resultado);

            if (resultado.codigo === 1 && resultado.data) {
                mostrarHistorial(resultado.data);
            } else {
                // Si no hay ventas, mostrar tabla vacía
                mostrarHistorial([]);
            }
        } catch (error) {
            console.error('Error cargando historial:', error);
            mostrarMensaje('error', 'Error', `No se pudo cargar el historial: ${error.message}`);
        }
    };

    // Mostrar historial
    const mostrarHistorial = (ventas) => {
        if (tablaHistorial) {
            tablaHistorial.destroy();
        }

        tablaHistorial = new DataTable('#tablaHistorial', {
            language: lenguaje,
            data: ventas,
            pageLength: 10,
            order: [[0, 'desc']],
            columns: [
                {
                    title: "Fecha", data: "fecha_venta", width: "15%",
                    render: (data) => {
                        if (!data) return '';
                        const fecha = new Date(data);
                        return fecha.toLocaleDateString('es-GT') + ' ' + fecha.toLocaleTimeString('es-GT', { hour: '2-digit', minute: '2-digit' });
                    }
                },
                {
                    title: "Cliente", data: "cliente_nombres", width: "25%",
                    render: (data, type, row) => `${data} ${row.cliente_apellidos || ''}`
                },
                {
                    title: "Total", data: "total", width: "15%",
                    render: data => `<strong>Q ${parseFloat(data).toFixed(2)}</strong>`
                },
                {
                    title: "Tipo", data: "tipo_venta", width: "15%",
                    render: (data) => {
                        const badges = {
                            'venta': '<span class="badge bg-success">Venta</span>',
                            'reparacion': '<span class="badge bg-warning">Reparación</span>'
                        };
                        return badges[data] || data;
                    }
                },
                {
                    title: "Estado", data: "situacion", width: "15%",
                    render: (data) => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-secondary'}">
                    ${data == 1 ? 'Completada' : 'Anulada'}
                </span>`
                },
                {
                    title: "Acciones", data: "venta_id", width: "15%",
                    render: (data, type, row) => `
                    <button class="btn btn-info btn-sm ver-detalle" data-id="${data}" title="Ver detalle">
                        <i class="bi bi-eye"></i>
                    </button>
                    ${row.situacion == 1 && isAdmin() ? `
                        <button class="btn btn-danger btn-sm anular-venta ms-1" data-id="${data}" title="Anular">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    ` : ''}
                `
                }
            ]
        });

        // Event listeners para los botones de la tabla
        document.querySelectorAll('.ver-detalle').forEach(btn => {
            btn.addEventListener('click', verDetalleVenta);
        });

        document.querySelectorAll('.anular-venta').forEach(btn => {
            btn.addEventListener('click', anularVenta);
        });
    };

    // Verificar si el usuario es administrador
    const isAdmin = () => {
        // Esta función debería verificar si el usuario tiene rol de administrador
        // Por ahora, devolvemos true para simplificar
        return true;
    };

    // Ver detalle de venta
    const verDetalleVenta = async (e) => {
        const ventaId = e.target.closest('.ver-detalle').dataset.id;

        try {
            const datos = new URLSearchParams();
            datos.append('venta_id', ventaId);

            // Usar ruta absoluta para la API
            const respuesta = await fetch('/app03_jmp/ventas/obtenerDetalleVentaAPI', {
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
                const venta = resultado.data.venta;
                const detalles = resultado.data.detalles;

                let detallesHtml = `
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

                detalles.forEach(detalle => {
                    detallesHtml += `
                    <tr>
                        <td>${detalle.nombre_producto}</td>
                        <td>Q ${parseFloat(detalle.precio_unitario).toFixed(2)}</td>
                        <td>${detalle.cantidad}</td>
                        <td>Q ${parseFloat(detalle.subtotal).toFixed(2)}</td>
                    </tr>
                `;
                });

                detallesHtml += `
                        </tbody>
                    </table>
                </div>
            `;

                Swal.fire({
                    title: 'Detalle de Venta',
                    html: `
                    <div class="text-start mb-3">
                        <p><strong>Cliente:</strong> ${venta.cliente_nombres} ${venta.cliente_apellidos}</p>
                        <p><strong>Teléfono:</strong> ${venta.cliente_telefono}</p>
                        <p><strong>Fecha:</strong> ${new Date(venta.fecha_venta).toLocaleString('es-GT')}</p>
                        <p><strong>Total:</strong> <span class="text-success fw-bold">Q ${parseFloat(venta.total).toFixed(2)}</span></p>
                    </div>
                    ${detallesHtml}
                `,
                    width: '800px',
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#3085d6'
                });
            } else {
                mostrarMensaje('error', 'Error', resultado.mensaje || 'No se pudo obtener el detalle de la venta');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarMensaje('error', 'Error', `Error al obtener detalle: ${error.message}`);
        }
    };

    // Anular venta
    const anularVenta = async (e) => {
        const ventaId = e.target.closest('.anular-venta').dataset.id;

        const confirmacion = await Swal.fire({
            title: '¿Anular esta venta?',
            text: 'Esta acción no se puede deshacer y revertirá el stock',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        });

        if (!confirmacion.isConfirmed) return;

        try {
            const datos = new URLSearchParams();
            datos.append('venta_id', ventaId);

            // Usar ruta absoluta para la API
            const respuesta = await fetch('/app03_jmp/ventas/anularVentaAPI', {
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
                mostrarMensaje('success', 'Éxito', 'Venta anulada correctamente');
                cargarHistorial();
            } else {
                mostrarMensaje('error', 'Error', resultado.mensaje || 'No se pudo anular la venta');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarMensaje('error', 'Error', `Error al anular venta: ${error.message}`);
        }
    };

    // Inicialización al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
        console.log('🚀 DOM cargado, iniciando aplicación de ventas...');

        // Cargar historial inicial
        cargarHistorial();

        // Event listeners para botones y campos
        btnBuscarCliente?.addEventListener('click', buscarCliente);

        inputTelefono?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarCliente();
            }
        });

        inputTelefono?.addEventListener('input', validarTelefono);

        btnProcesarVenta?.addEventListener('click', procesarVenta);

        // Event listener para el botón "Nueva Venta"
        const btnNuevaVenta = document.querySelector('button[onclick="location.reload()"]');
        if (btnNuevaVenta) {
            btnNuevaVenta.removeAttribute('onclick');
            btnNuevaVenta.addEventListener('click', () => {
                if (productosCarrito.length > 0) {
                    Swal.fire({
                        title: '¿Nueva venta?',
                        text: 'Se perderán los productos en el carrito actual',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, limpiar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            limpiarFormulario();
                        }
                    });
                } else {
                    limpiarFormulario();
                }
            });
        }

    });
}
window.buscarCliente = buscarCliente;
window.procesarVenta = procesarVenta;
window.cargarHistorial = cargarHistorial;