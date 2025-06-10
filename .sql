CREATE DATABASE morataya_celulares;

CREATE TABLE clientes (
    cliente_id SERIAL PRIMARY KEY NOT NULL,
    nombres VARCHAR(100),
    apellidos VARCHAR(100),
    nit VARCHAR(10),
    telefono VARCHAR(10),
    correo VARCHAR(100),
    situacion SMALLINT DEFAULT  1
)

-- Tabla de marcas de celulares
CREATE TABLE marcas (
    marca_id SERIAL PRIMARY KEY,
    marca_nombre VARCHAR(50) NOT NULL UNIQUE,
    marca_descripcion LVARCHAR(200),
    fecha_creacion DATE DEFAULT TODAY,
    situacion SMALLINT DEFAULT 1
);

-- Tabla de productos (celulares, repuestos, servicios - TODO EN UNA)
CREATE TABLE productos (
    producto_id SERIAL PRIMARY KEY,
    marca_id INT,
    nombre_producto VARCHAR(150) NOT NULL,
    tipo_producto VARCHAR(20) NOT NULL, -- 'celular', 'repuesto', 'servicio'
    modelo VARCHAR(100),
    precio_compra DECIMAL(10,2) DEFAULT 0,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock_actual INT DEFAULT 0,
    stock_minimo INT DEFAULT 0,
    descripcion LVARCHAR(300),
    fecha_creacion DATE DEFAULT TODAY,
    situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (marca_id) REFERENCES marcas(marca_id)
);

-- Tabla de ventas (incluye ventas y reparaciones)
CREATE TABLE ventas (
    venta_id SERIAL PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha_venta DATETIME YEAR TO SECOND DEFAULT CURRENT,
    total DECIMAL(10,2) NOT NULL,
    tipo_venta VARCHAR(20) NOT NULL, -- 'venta' o 'reparacion'
    descripcion LVARCHAR(300), -- Para reparaciones: motivo, estado, etc.
    situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (cliente_id) REFERENCES clientes(cliente_id)
);

-- Tabla de detalle de ventas (productos/servicios vendidos)
CREATE TABLE detalle_ventas (
    detalle_id SERIAL PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(venta_id),
    FOREIGN KEY (producto_id) REFERENCES productos(producto_id)
);