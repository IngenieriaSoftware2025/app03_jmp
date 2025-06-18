CREATE DATABASE morataya_celulares;

-- Tabla clientes (nombres originales del código)
CREATE TABLE clientes (
    cliente_id SERIAL PRIMARY KEY NOT NULL,
    cliente_nombres VARCHAR(100) NOT NULL,
    cliente_apellidos VARCHAR(100) NOT NULL,
    cliente_nit VARCHAR(15),                   
    cliente_telefono VARCHAR(8) NOT NULL,      
    cliente_correo VARCHAR(100),
    cliente_direccion VARCHAR(200),            
    cliente_situacion SMALLINT DEFAULT 1       
);

-- Tabla de marcas
CREATE TABLE marcas (
    marca_id SERIAL PRIMARY KEY,
    marca_nombre VARCHAR(50) NOT NULL UNIQUE,
    marca_descripcion LVARCHAR(200),
    fecha_creacion DATE DEFAULT TODAY,
    situacion SMALLINT DEFAULT 1     
);

-- Tabla de productos (nombres originales)
CREATE TABLE productos (
    producto_id SERIAL PRIMARY KEY,
    marca_id INT,
    nombre_producto VARCHAR(150) NOT NULL,
    tipo_producto VARCHAR(20) NOT NULL CHECK (tipo_producto IN ('celular', 'repuesto', 'servicio')),
    modelo VARCHAR(100),
    precio_compra DECIMAL(10,2) DEFAULT 0 CHECK (precio_compra >= 0),
    precio_venta DECIMAL(10,2) NOT NULL CHECK (precio_venta > 0),
    stock_actual INT DEFAULT 0 CHECK (stock_actual >= 0),
    stock_minimo INT DEFAULT 0 CHECK (stock_minimo >= 0),
    descripcion LVARCHAR(300),
    fecha_creacion DATE DEFAULT TODAY,
    situacion SMALLINT DEFAULT 1,   
    FOREIGN KEY (marca_id) REFERENCES marcas(marca_id)
);

-- CORREGIDO: Tabla de ventas con sintaxis Informix correcta
CREATE TABLE ventas (
    venta_id SERIAL PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha_venta DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND,
    total DECIMAL(10,2) NOT NULL CHECK (total >= 0),
    tipo_venta VARCHAR(20) NOT NULL CHECK (tipo_venta IN ('venta', 'reparacion')),
    descripcion LVARCHAR(300),
    situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (cliente_id) REFERENCES clientes(cliente_id)
);

-- Tabla de detalle de ventas
CREATE TABLE detalle_ventas (
    detalle_id SERIAL PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    precio_unitario DECIMAL(10,2) NOT NULL CHECK (precio_unitario > 0),
    subtotal DECIMAL(10,2) NOT NULL CHECK (subtotal >= 0),
    FOREIGN KEY (venta_id) REFERENCES ventas(venta_id),
    FOREIGN KEY (producto_id) REFERENCES productos(producto_id)
);



-- Tabla de usuarios
CREATE TABLE usuario (
    usu_id SERIAL PRIMARY KEY,
    usu_nombre VARCHAR(50),
    usu_codigo INTEGER,
    usu_password VARCHAR(150),
    usu_situacion SMALLINT DEFAULT 1
);

-- Tabla de roles
CREATE TABLE rol (
    rol_id SERIAL PRIMARY KEY,
    rol_nombre VARCHAR(75),
    rol_nombre_ct VARCHAR(25),
    rol_situacion SMALLINT DEFAULT 1
);

-- Tabla de permisos
CREATE TABLE permiso (
    permiso_id SERIAL PRIMARY KEY,
    permiso_usuario INTEGER,
    permiso_rol INTEGER,
    permiso_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (permiso_usuario) REFERENCES usuario (usu_id),
    FOREIGN KEY (permiso_rol) REFERENCES rol (rol_id)
);

-- INSERT USUARIOS (password: 123456)
INSERT INTO usuario (usu_nombre, usu_codigo, usu_password) VALUES 
('ADMINISTRADOR MORATAYA', 12345678, '$2y$10$Nz6/ESQw7b7xW1Q2j.WEM.g5LQ/NSSmHnhZpfolFAH.ltD0GGRKGS'),
('EMPLEADO VENTAS', 12345679, '$2y$10$Nz6/ESQw7b7xW1Q2j.WEM.g5LQ/NSSmHnhZpfolFAH.ltD0GGRKGS'),
('EMPLEADO REPARACIONES', 12345670, '$2y$10$Nz6/ESQw7b7xW1Q2j.WEM.g5LQ/NSSmHnhZpfolFAH.ltD0GGRKGS');

-- ROLES
INSERT INTO rol (rol_nombre, rol_nombre_ct) VALUES 
('ADMINISTRADOR', 'ADMIN'),
('EMPLEADO', 'USER');

-- PERMISOS
INSERT INTO permiso (permiso_usuario, permiso_rol) VALUES 
(1, 1), (2, 2), (3, 2);