<form action="{{ route('login') }}" method="POST">
    @csrf
    <div class="col-lg-12">
        <div class="form-group mb-3">
            <input name="email" type="text" value="{{ old('email') }}" class="form-control" placeholder="Email*">
            @error('email')
                <span class="text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>

    <div class="col-lg-12">
        <div class="form-group mb-3">
            <input name="password" type="text" class="form-control" placeholder="Password*">
            @error('password')
                <span class="text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>

    <div class="col-lg-12">
        <div class="twm-forgot-wrap">
            <div class="form-group mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="Password4"
                        {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label rem-forgot" for="Password4">{{ __('Remember Me') }}</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="site-text-primary">{{ __('Forgot Password') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <button type="submit" class="site-button">Login</button>
        </div>
    </div>
</form>
