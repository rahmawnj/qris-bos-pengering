<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>403 | Access Denied</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9; /* Warna abu-abu sangat muda, lebih nyaman di mata */
            font-family: 'Poppins', sans-serif;
            color: #343a40;
            overflow-x: hidden;
        }

        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-img {
            width: 100%;
            max-width: 350px;
            height: auto;
            /* Efek bayangan halus pada gambar */
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
        }

        .error-content {
            padding: 40px;
        }

        .error-code {
            font-size: 6rem; /* Diperkecil sedikit agar proporsional */
            font-weight: 800;
            line-height: 1;
            color: #dc3545; /* Merah soft untuk error */
            margin-bottom: 10px;
        }

        .error-message {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .error-desc {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 30px;
            font-weight: 300;
        }

        .btn-custom {
            background-color: #0d6efd;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.3);
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
        }

        .btn-custom:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px); /* Efek naik saat di-hover */
            box-shadow: 0 6px 8px rgba(13, 110, 253, 0.4);
            color: white;
        }
    </style>
</head>

<body>
    <div class="container error-container">
        <div class="row w-100 align-items-center">

            <div class="col-md-6 text-center mb-4 mb-md-0">
                <img src="{{ asset('assets/img/forbidden.png') }}" alt="Forbidden" class="error-img" />
            </div>

            <div class="col-md-6 error-content">
                <div class="error-code">403</div>
                <div class="error-message">Oops! Access Denied</div>
                <div class="error-desc">
                    {{ $exception->getMessage() ?: 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.' }}
                    <br>Silakan hubungi administrator jika ini kesalahan.
                </div>
                <div>
                    <a href="{{ route('home') }}" class="btn btn-custom">Kembali ke Home</a>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
