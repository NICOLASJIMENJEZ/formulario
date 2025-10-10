-- SQL to create the 'registros' table
CREATE DATABASE IF NOT EXISTS formulario_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE formulario_db;

CREATE TABLE IF NOT EXISTS registros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titular_nombre VARCHAR(100) NOT NULL,
  titular_apellidos VARCHAR(100) NOT NULL,
  titular_cc VARCHAR(20),
  titular_celular VARCHAR(20),
  titular_correo VARCHAR(100),
  invitado1_nombre VARCHAR(100),
  invitado1_apellidos VARCHAR(100),
  invitado1_cc VARCHAR(20),
  invitado2_nombre VARCHAR(100),
  invitado2_apellidos VARCHAR(100),
  invitado2_cc VARCHAR(20),
  invitado3_nombre VARCHAR(100),
  invitado3_apellidos VARCHAR(100),
  invitado3_cc VARCHAR(20),
  discapacidad VARCHAR(2) DEFAULT 'no',
  discapacidad_cual VARCHAR(255),
  fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
