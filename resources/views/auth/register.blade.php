<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Register - Top Up</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="Register page for Member" name="description" />
    <meta content="Member Team" name="author" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap" rel="stylesheet" />
    <!-- Icon (Baju Biru) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <!-- Custom CSS for Styling -->
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f4f7fa;
        }

        .register-container {
            max-width: 400px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .register-container h2 {
            text-align: center;
            color: #007bff;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .register-container .form-control {
            border-radius: 10px;
            padding: 15px;
        }

        .register-container button {
            background-color: #007bff;
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            width: 100%;
        }

        .register-container button:hover {
            background-color: #0056b3;
        }

        .register-container .form-check-label {
            font-size: 14px;
        }

        .register-container .form-check {
            margin-bottom: 20px;
        }

        .register-container .text-danger {
            font-size: 12px;
        }

        .register-container .icon-container {
            text-align: center;
            margin-bottom: 10px;
        }

        .register-container .icon-container i {
            font-size: 35px;
            color: #007bff;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <!-- Header: Ikon dan Register Topup berdampingan -->
        <div class="d-flex align-items-center">
            <!-- Icon -->
            <div class="icon-container me-3">
                <i class="fas fa-coffee fa-3x"></i>
            </div>
            <!-- Teks Register Topup -->
            <h2 class="fw-bold mb-0">REGISTER TOPUP</h2>
        </div>

        <hr class="my-4">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
       <form action="{{ route('register') }}" method="POST">
    @csrf

    <!-- Name Field -->
    <div class="mb-3">
        <label for="name" class="form-label">Nama Lengkap</label>
        <input name="name" type="text" value="{{ old('name') }}" class="form-control" id="name"
            placeholder="Masukkan nama lengkap" />
        @error('name')
            <div class="text-danger">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
    </div>

    <!-- Email Field -->
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input name="email" type="email" value="{{ old('email') }}" class="form-control" id="email"
            placeholder="Enter your email" />
        @error('email')
            <div class="text-danger">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
    </div>

   <div class="row">
    <!-- Password Field -->
    <div class="mb-3 col-md-6">
        <label for="password" class="form-label">Password</label>
        <input name="password" type="password" class="form-control" id="password"
            placeholder="Enter your password" />
        @error('password')
            <div class="text-danger">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
    </div>

    <!-- Password Confirmation -->
    <div class="mb-3 col-md-6">
        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
        <input name="password_confirmation" type="password" class="form-control" id="password_confirmation"
            placeholder="Ulangi password" />
    </div>
</div>

    <!-- Remember Me Checkbox -->
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="remember" value="1"
            id="rememberMe" {{ old('remember') ? 'checked' : '' }} />
        <label class="form-check-label" for="rememberMe">
            Remember Me
        </label>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary btn-lg">Sign Up</button>
</form>

    </div>


    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>

</html>
