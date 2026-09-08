<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Login - Top Up</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="Login page for Top Up" name="description" />
    <meta content="Top Up Team" name="author" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap" rel="stylesheet" />
    <!-- Icon (Font Awesome) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f4f7fa;
        }

        .login-container {
            max-width: 400px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .login-container h2 {
            text-align: center;
            color: #007bff;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .login-container .form-control {
            border-radius: 10px;
            padding: 15px;
        }

        .login-container button {
            background-color: #007bff;
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            width: 100%;
        }

        .login-container button:hover {
            background-color: #0056b3;
        }

        .login-container .form-check-label {
            font-size: 14px;
        }

        .login-container .form-check {
            margin-bottom: 20px;
        }

        .login-container .text-danger {
            font-size: 12px;
        }

        /* Container untuk PIN */
        .pin-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .pin-container input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 24px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Header -->
        <div class="d-flex align-items-center">
            <div class="icon-container me-3">
                <i class="fas fa-coffee fa-3x"></i>
            </div>
            <h2 class="fw-bold mb-0">LOGIN TOPUP</h2>
            {{-- @dd(Auth::guard('outlet')->user()) --}}
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

        <form action="{{ route('outlet.login.submit') }}" method="POST">
            @csrf
            <!-- Field PIN (6 input terpisah) -->
            <div class="mb-3">
                <label for="pin" class="form-label">PIN</label>
                <div class="pin-container">
                    <input type="text" name="pin[]" id="pin-1" maxlength="1" inputmode="numeric"
                        pattern="\d*" class="form-control" autocomplete="off" />
                    <input type="text" name="pin[]" id="pin-2" maxlength="1" inputmode="numeric"
                        pattern="\d*" class="form-control" autocomplete="off" />
                    <input type="text" name="pin[]" id="pin-3" maxlength="1" inputmode="numeric"
                        pattern="\d*" class="form-control" autocomplete="off" />
                    <input type="text" name="pin[]" id="pin-4" maxlength="1" inputmode="numeric"
                        pattern="\d*" class="form-control" autocomplete="off" />
                    <input type="text" name="pin[]" id="pin-5" maxlength="1" inputmode="numeric"
                        pattern="\d*" class="form-control" autocomplete="off" />
                    <input type="text" name="pin[]" id="pin-6" maxlength="1" inputmode="numeric"
                        pattern="\d*" class="form-control" autocomplete="off" />
                </div>
            </div>

            <!-- Field Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input name="password" type="password" class="form-control" id="password"
                    placeholder="Enter your password" />
                @error('password')
                    <div class="text-danger">
                        <strong>{{ $message }}</strong>
                    </div>
                @enderror
            </div>

            <!-- Checkbox Remember Me -->
            <!--<div class="form-check mb-3">-->
            <!--    <input class="form-check-input" type="checkbox" name="remember" value="1" id="rememberMe"-->
            <!--        {{ old('remember') ? 'checked' : '' }} />-->
            <!--    <label class="form-check-label" for="rememberMe">-->
            <!--        Remember Me-->
            <!--    </label>-->
            <!--</div>-->

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
        </form>
    </div>

    <!-- Script JavaScript untuk auto tab pada PIN fields -->
    <script>
        const pinInputs = document.querySelectorAll('.pin-container input');
        const passwordField = document.getElementById('password');

        pinInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                // Hanya izinkan angka
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length >= this.maxLength) {
                    // Jika bukan input terakhir, pindah ke input berikutnya
                    if (index < pinInputs.length - 1) {
                        pinInputs[index + 1].focus();
                    } else {
                        // Jika input terakhir sudah diisi, pindah ke field password
                        passwordField.focus();
                    }
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    pinInputs[index - 1].focus();
                }
            });
        });
    </script>

    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>

</html>
