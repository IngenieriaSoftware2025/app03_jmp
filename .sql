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