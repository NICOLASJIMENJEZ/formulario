<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graduados Registrados</title>

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
            width: 280px;
            margin: auto;
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
    </style>
</head>
<body>

    <div class="card">
        <div class="title">Graduados Registrados</div>
        <div id="contadorGraduados" class="number">0</div>
    </div>

    <script>
        function cargarContadorGraduados() {
            fetch("contador_graduados.php")
                .then(response => response.json())
                .then(data => {
                    document.getElementById("contadorGraduados").innerText = data.total;
                })
                .catch(err => console.error("Error:", err));
        }

        // Cargar cada 5 segundos
        setInterval(cargarContadorGraduados, 5000);
        cargarContadorGraduados();
    </script>

</body>
</html>
