<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Login - Laundry Point of Sale</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="Halaman login sistem kasir untuk usaha laundry Anda." name="description" />
    <meta content="Laundry POS Team" name="author" />

    <link rel="icon" href="https://via.placeholder.com/32/007bff/ffffff?text=L" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .login-wrapper {
            display: flex;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 900px;
            max-width: 90%;
            z-index: 10;
            min-height: 550px; /* Memastikan tinggi cukup untuk layout */
        }

        /* --- SIDEBAR STYLES --- */
        .login-sidebar {
            flex: 1;
            background: linear-gradient(to right, #007bff, #0056b3);
            color: white;
            display: flex;
            flex-direction: column;
            /* Ubah layout agar bisa menaruh footer di bawah */
            justify-content: space-between;
            align-items: center;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
        }

        .sidebar-content {
            position: relative;
            z-index: 2;
            /* Agar konten tetap di tengah secara vertikal */
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        /* Style untuk Footer Sidebar (Teks Keamanan) */
        .sidebar-footer {
            position: relative;
            z-index: 2;
            font-size: 0.85rem;
            opacity: 0.8;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }

        .login-sidebar h1 {
            font-size: 2.2rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .login-sidebar p {
            font-size: 0.95rem;
            line-height: 1.6;
            opacity: 0.9;
        }

        .login-sidebar .icon-large {
            font-size: 4.5rem;
            margin-bottom: 20px;
            animation: bounceIn 1s ease-out;
        }

        /* --- FORM STYLES --- */
        .login-form-container {
            flex: 1;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            z-index: 10;
            background: white;
        }

        .login-form-container h2 {
            text-align: left; /* Sesuai referensi gambar */
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .login-subtext {
            text-align: left;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 500;
            color: #444;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        /* Wrapper untuk Input + Icon */
        .input-group-custom {
            position: relative;
        }

        /* Style Icon di dalam Input */
        .input-group-custom .input-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #adb5bd; /* Warna abu-abu default */
            font-size: 1rem;
            transition: color 0.3s ease;
            z-index: 5;
        }

        .form-control {
            border-radius: 10px;
            padding: 14px 20px 14px 45px; /* Padding kiri lebih besar untuk icon */
            border: 1px solid #e1e5eb; /* Border lebih halus */
            transition: all 0.3s ease;
            background-color: #f8f9fa; /* Background sedikit abu seperti di gambar */
        }

        .form-control:focus {
            background-color: #fff;
            border-color: #007bff;
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1); /* Shadow halus */
            outline: none;
        }

        /* Ubah warna icon saat input difokuskan */
        .input-group-custom:focus-within .input-icon {
            color: #007bff;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 10px;
            padding: 14px 20px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
            margin-top: 10px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            transform: translateY(-2px);
        }

        .form-check-label {
            font-size: 0.9rem;
            color: #666;
        }

        .forgot-password {
            font-size: 0.9rem;
            color: #007bff;
            text-decoration: none;
            float: right;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .alert-danger {
            background-color: #ffe6e6;
            color: #cc0000;
            border-color: #ffb3b3;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }

        .text-danger {
            font-size: 0.8rem;
            margin-top: 5px;
            margin-left: 5px;
            display: block;
        }

        @media (max-width: 768px) {
            body {
                align-items: flex-start;
                padding: 20px 0;
                min-height: 100vh;
            }

            .login-wrapper {
                flex-direction: column;
                width: 95%;
                height: auto;
            }

            .login-sidebar {
                padding: 30px;
                min-height: 250px;
            }

            .sidebar-footer {
                margin-top: 30px; /* Jarak extra di mobile */
            }

            .login-form-container {
                padding: 30px;
            }
        }

        @keyframes bounceIn {
            0% { transform: scale(0.1); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-sidebar">
            <div id="particles-js"></div>

            <div class="sidebar-content">
                <i class="fas fa-soap icon-large"></i>
                <h1>Selamat Datang di Laundry POS</h1>
                <p>Kelola bisnis laundry Anda lebih pintar, cepat, dan efisien dengan sistem kasir masa depan.</p>
            </div>

            <div class="sidebar-footer">
                <i class="fas fa-shield-alt"></i> Sistem Keamanan Terenkripsi
            </div>
        </div>

        <div class="login-form-container">
            <h2>Masuk Akun</h2>
            <p class="login-subtext">Silakan masukkan detail akun Anda untuk melanjutkan</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="email" class="form-label">Alamat Email</label>
                    <div class="input-group-custom">
                        <i class="fas fa-envelope input-icon"></i>
                        <input name="email" type="email" class="form-control" id="email"
                            placeholder="nama@email.com" required autocomplete="email" autofocus />
                    </div>
                    @error('email')
                        <div class="text-danger">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label mb-0">Kata Sandi</label>
                        {{-- <a href="#" class="forgot-password">Lupa Sandi?</a> --}}
                    </div>
                    <div class="input-group-custom">
                        <i class="fas fa-lock input-icon"></i>
                        <input name="password" type="password" class="form-control" id="password"
                            placeholder="••••••••" required autocomplete="current-password" />
                        </div>
                    @error('password')
                        <div class="text-danger">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" />
                    <label class="form-check-label" for="rememberMe">
                        Ingat saya di perangkat ini
                    </label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Masuk Akun</button>
                </div>

                {{-- <div class="text-center mt-4">
                    <span style="font-size: 0.9rem; color: #666;">Belum punya akun? <a href="#" style="text-decoration: none; font-weight: 600; color: #007bff;">Daftar sekarang</a></span>
                </div> --}}
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

    <script>
        particlesJS("particles-js", {
            "particles": {
                "number": { "value": 40, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": "#ffffff" },
                "shape": { "type": "circle", "stroke": { "width": 0, "color": "#000000" } },
                "opacity": { "value": 0.3, "random": true, "anim": { "enable": false } },
                "size": { "value": 15, "random": true, "anim": { "enable": false } },
                "line_linked": { "enable": false },
                "move": { "enable": true, "speed": 3, "direction": "top", "random": true, "straight": false, "out_mode": "out", "bounce": false }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": { "enable": true, "mode": "repulse" },
                    "onclick": { "enable": true, "mode": "push" },
                    "resize": true
                },
                "modes": {
                    "grab": { "distance": 400, "line_linked": { "opacity": 1 } },
                    "bubble": { "distance": 400, "size": 40, "duration": 2, "opacity": 8, "speed": 3 },
                    "repulse": { "distance": 100, "duration": 0.4 },
                    "push": { "particles_nb": 4 },
                    "remove": { "particles_nb": 2 }
                }
            },
            "retina_detect": true
        });
    </script>
</body>

</html>
