<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title> -- | Not Found</title>
  <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
  <meta content="" name="description" />
  <meta content="" name="author" />

  <!-- ================== BEGIN core-css ================== -->
  <link href="{{ asset('assets/css/vendor.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/facebook/app.min.css') }}" rel="stylesheet" />
  <!-- ================== END core-css ================== -->

  <!-- Custom CSS -->
  <style>
    .error-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
    }
    .error-img {
      width: 80%;
      max-width: 400px;
      height: auto;
    }
    .error-content {
      padding: 20px;
    }
    .error-code {
      font-size: 8rem;
      font-weight: bold;
    }
    .error-message {
      font-size: 2.5rem;
      font-weight: 600;
    }
    .error-desc {
      font-size: 1.2rem;
    }
  </style>
</head>

<body style="background-color: white">
  <div class="container error-container" >
    <div class="row w-100">
      <!-- Kolom Kiri untuk Gambar -->
      <div class="col-md-4 text-center">
        <img src="{{ asset('assets/img/boss.gif') }}" alt="Logo" class="error-img" />
      </div>
      <!-- Kolom Kanan untuk Teks dan Tombol -->
      <div class="col-md-8 error-content">
        <div class="error-code">404</div>
        <div class="error-message">We couldn't find it...</div>
        <div class="error-desc mb-4">
          The page you're looking for doesn't exist. <br />
          Perhaps, these pages will help find what you're looking for.
        </div>
        <div>
          <a href="{{ route('home') }}" class="btn btn-primary px-3">Go Home</a>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
