@extends('admin.layouts.app')

@section('content')
<div class="login-container">
    <h2>Admin Login</h2>
    <form action="{{ route('admin.login.submit') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
@endsection
