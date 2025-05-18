@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Create New Work</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.works.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Work Details</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.works.store') }}" method="POST" id="workForm">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">User</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Work Items</h5>
                        <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                            <i class="bi bi-plus-circle"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Diamond</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    <!-- Items will be added here dynamically -->
                                </tbody>
                            </table>
                        </div>
                        <div id="noItemsMessage" class="alert alert-info">
                            No items added yet. Click "Add Item" to add work items.
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Create Work</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsContainer = document.getElementById('itemsContainer');
        const addItemBtn = document.getElementById('addItemBtn');
        const noItemsMessage = document.getElementById('noItemsMessage');
        let itemCount = 0;

        // Function to add a new item row
        function addItemRow() {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="text" class="form-control" name="items[${itemCount}][type]" required>
                </td>
                <td>
                    <input type="number" class="form-control" name="items[${itemCount}][diamond]" min="1" value="1" required>
                </td>
                <td>
                    <input type="number" class="form-control" name="items[${itemCount}][price]" min="0" step="0.01" value="0.00" required>
                </td>
                <td>
                    <select class="form-select" name="items[${itemCount}][is_active]">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
                </td>
            `;
            itemsContainer.appendChild(row);
            itemCount++;
            updateNoItemsMessage();

            // Add event listener to the remove button
            row.querySelector('.remove-item').addEventListener('click', function() {
                row.remove();
                updateNoItemsMessage();
            });
        }

        // Function to update the "no items" message visibility
        function updateNoItemsMessage() {
            if (itemsContainer.children.length > 0) {
                noItemsMessage.style.display = 'none';
            } else {
                noItemsMessage.style.display = 'block';
            }
        }

        // Add item button click handler
        addItemBtn.addEventListener('click', addItemRow);

        // Initialize with one item
        addItemRow();
    });
</script>
@endpush
