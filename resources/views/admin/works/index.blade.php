@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Works Management</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.works.create') }}" class="btn btn-primary">Create New Work</a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Works</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>User</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($works as $work)
                        <tr>
                            <td>{{ $work->id }}</td>
                            <td>{{ $work->name }}</td>
                            <td>
                                @if($work->user)
                                    {{ $work->user->first_name }} {{ $work->user->last_name }}
                                @else
                                    <span class="text-muted">No user</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($work->date)->format('M d, Y') }}</td>
                            <td>{{ $work->workItems->count() }}</td>
                            <td>${{ number_format($work->total, 2) }}</td>
                            <td>
                                @if($work->is_active == '1')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.works.show', $work) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.works.edit', $work) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.works.destroy', $work) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this work?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No works found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $works->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
