<?php
require_once __DIR__ . '/auth.php';

// If already logged, redirect to requested destination or registros list
if (isset($_SESSION['logged']) && $_SESSION['logged'] === true) {
    $goto = isset($_GET['goto']) ? $_GET['goto'] : '/formulario/registros/registros.php';
    header('Location: ' . $goto);
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = isset($_POST['password']) ? (string)$_POST['password'] : '';
    // static password as requested
    if ($pass === '903135') {
        do_login();
        // redirect to requested page or registros by default
        $goto = isset($_GET['goto']) ? $_GET['goto'] : (isset($_POST['goto']) ? $_POST['goto'] : '/formulario/registros/registros.php');
        header('Location: ' . $goto);
        exit;
    } else {
        $msg = 'Contraseña incorrecta';
    }
}

?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso - Registros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Acceso restringido</h5>
                        <?php if(isset($_GET['timeout'])): ?>
                            <div class="alert alert-warning">Sesión expirada por inactividad. Por favor ingresa de nuevo.</div>
                        <?php endif; ?>
                        <?php if($msg): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($msg); ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <input type="hidden" name="goto" value="<?php echo isset($_GET['goto']) ? htmlspecialchars($_GET['goto']) : ''; ?>">
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input name="password" type="password" class="form-control" required autofocus>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="/formulario/registros/registros.php" class="btn btn-outline-secondary btn-sm">Cancelar</a>
                                <button class="btn btn-primary btn-sm">Entrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
