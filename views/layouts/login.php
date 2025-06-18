<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= asset('images/cit.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <title>Login - Morataya Celulares</title>
</head>
<body>
    <div class="container-fluid pt-5 mb-5" style="min-height: 85vh">
        <?php echo $contenido; ?>
    </div>
    <div class="container-fluid">
        <div class="row justify-content-center text-center">
            <div class="col-12">
                <p style="font-size:xx-small; font-weight: bold;">
                    Morataya Celulares - Sistema de Control, <?= date('Y') ?> &copy;
                </p>
            </div>
        </div>
    </div>
</body>
</html>