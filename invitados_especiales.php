<?php
require_once __DIR__ . '/db.php';

// Generar ID especial automático
$query = $pdo->query("SELECT COUNT(*) AS total FROM registros WHERE es_especial = TRUE");
$count = $query->fetch(PDO::FETCH_ASSOC)['total'] + 1;
$id_especial = "ESP-" . str_pad($count, 4, "0", STR_PAD_LEFT);
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitados Especiales</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f0ff;
        }

        .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .bg-purple {
            background: #7a3cff !important;
            color: white !important;
        }

        .badge-especial {
            background: #c29bff;
            color: #3a1670;
            padding: 5px 10px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="row justify-content-center">

            <div class="col-md-7">
                <div class="card shadow">

                    <div class="card-header bg-purple">
                        <h4 class="mb-0">Registro de Invitado Especial</h4>
                    </div>

                    <div class="card-body">

                        <div class="alert alert-info">
                            <b>ID Especial asignado:</b> 
                            <span class="badge-especial"><?= $id_especial ?></span>
                        </div>

                        <form action="guardar_invitado_especial.php" method="POST">

                            <input type="hidden" name="id_especial" value="<?= $id_especial ?>">

                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Apellidos *</label>
                                <input type="text" class="form-control" name="apellidos" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cédula</label>
                                <input type="text" class="form-control" name="cc">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Hora *</label>
                                <select class="form-select" name="hora" required>
                                    <option value="">Seleccione...</option>
                                    <option value="08:00">08:00 AM</option>
                                    <option value="10:30">10:30 AM</option>
                                    <option value="14:00">02:00 PM</option>
                                    <option value="16:30">04:30 PM</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Programa / Evento *</label>
                                <input type="text" class="form-control" name="programa" required>
                            </div>

                            <input type="hidden" name="es_especial" value="1">

                            <button class="btn bg-purple w-100">
                                Guardar Invitado Especial
                            </button>

                        </form>

                        <a href="index.php" class="btn btn-outline-secondary mt-3 w-100">Volver al inicio</a>

                    </div>

                </div>
            </div>

        </div>
    </div>

</body>

</html>
