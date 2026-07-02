@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">{{ $method === 'POST' ? 'Add Staff' : 'Edit Staff' }}</h1>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card p-3">
    <form action="{{ $action }}" method="POST">
        @csrf
        @if ($method === 'PUT')
            @method('PUT')
        @endif

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ $user->name ?? old('name') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email ?? old('email') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password {{ $method === 'PUT' ? '(leave blank to keep current)' : '' }}</label>
            <input type="password" name="password" class="form-control" {{ $method === 'POST' ? 'required' : '' }}>
        </div>

        @if ($method === 'POST')
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
        @endif

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="cashier" {{ ($user->role ?? old('role')) === 'cashier' ? 'selected' : '' }}>Cashier</option>
                <option value="admin" {{ ($user->role ?? old('role')) === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Position</label>
            <input type="text" name="position" class="form-control" value="{{ $user->position ?? old('position') }}" placeholder="e.g., Manager, Waiter, Chef">
        </div>

        <button type="submit" class="btn btn-primary">{{ $method === 'POST' ? 'Create Staff' : 'Update Staff' }}</button>
    </form>
</div>
@endsection
