<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Graduados</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 350px;
            margin: 20px auto;
            text-align: center;
        }
        .title {
            font-size: 22px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .number {
            font-size: 48px;
            font-weight: bold;
            color: #008000;
        }

        .table-card {
            width: 90%;
            max-width: 600px;
            background: white;
            padding: 20px;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        table tr td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .label {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- TOTAL GRADUADOS -->
    <div class="card">
        <div class="title">Total de Graduados Registrados</div>
        <div id="contadorGraduados" class="number">0</div>
    </div>

    <!-- GRADUADOS POR PROGRAMA -->
    <div class="table-card">
        <div class="title">Graduados por Programa</div>
        <table id="tablaProgramas"></table>
    </div>

    <!-- GRADUADOS POR HORA -->
    <div class="table-card">
        <div class="title">Graduados por Hora</div>
        <table id="tablaHoras"></table>
    </div>

    <script>
        function cargarContadorGraduados() {
            fetch("contador_graduados.php")
                .then(res => res.json())
                .then(data => {
                    document.getElementById("contadorGraduados").innerText = data.total;
                });
        }

        function cargarPorPrograma() {
            fetch("graduados_por_programa.php")
                .then(res => res.json())
                .then(data => {
                    let html = "";
                    data.data.forEach(row => {
                        html += `
                            <tr>
                                <td class="label">${row.programa}</td>
                                <td>${row.total}</td>
                            </tr>
                        `;
                    });
                    document.getElementById("tablaProgramas").innerHTML = html;
                });
        }

        function cargarPorHora() {
            fetch("graduados_por_hora.php")
                .then(res => res.json())
                .then(data => {
                    let html = "";
                    data.data.forEach(row => {
                        html += `
                            <tr>
                                <td class="label">${row.hora}</td>
                                <td>${row.total}</td>
                            </tr>
                        `;
                    });
                    document.getElementById("tablaHoras").innerHTML = html;
                });
        }

        // Auto refresco cada 5 segundos
        setInterval(() => {
            cargarContadorGraduados();
            cargarPorPrograma();
            cargarPorHora();
        }, 5000);

        // Cargar al inicio
        cargarContadorGraduados();
        cargarPorPrograma();
        cargarPorHora();
    </script>

</body>
</html>
