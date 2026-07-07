@extends('layouts.authenticated', ['showBackButton' => true])

@section('title', isset($id) ? 'Edit Pengeluaran' : 'Tambah Pengeluaran')

@push('styles')
<style>
    .navbar-custom {
        background-color: var(--error) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form id="expenseForm">
                <input type="hidden" id="expense_id" value="{{ $id ?? '' }}">
                
                <div class="mb-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="name" placeholder="Nama Pengeluaran" required>
                        <label for="name">Nama Pengeluaran</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="amount" placeholder="Jumlah (Rp)" required min="0" step="1">
                        <label for="amount">Jumlah (Rp)</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="category" placeholder="Kategori" required value="Lainnya">
                        <label for="category">Kategori</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-floating">
                        <input type="date" class="form-control" id="expense_date" placeholder="Tanggal Pengeluaran" required>
                        <label for="expense_date">Tanggal Pengeluaran</label>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="form-floating">
                        <textarea class="form-control" id="notes" placeholder="Catatan (Opsional)" style="height: 100px"></textarea>
                        <label for="notes">Catatan (Opsional)</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-danger w-100 py-2 fs-6 fw-medium" id="btnSave">
                    {{ isset($id) ? 'Simpan Perubahan' : 'Simpan Pengeluaran' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const expenseId = $('#expense_id').val();
        
        // Initialize date to today
        if(!expenseId) {
            const today = new Date().toISOString().split('T')[0];
            $('#expense_date').val(today);
        } else {
            // Load existing data
            showLoading();
            $.ajax({
                url: \`/api/expenses/\${expenseId}\`,
                type: 'GET',
                success: function(res) {
                    hideLoading();
                    const expense = res.data || res;
                    $('#name').val(expense.name);
                    $('#amount').val(expense.amount);
                    $('#category').val(expense.category);
                    $('#expense_date').val(expense.expense_date.split('T')[0]); // Ensure YYYY-MM-DD
                    $('#notes').val(expense.notes);
                },
                error: function() {
                    hideLoading();
                    showError('Gagal memuat data pengeluaran');
                }
            });
        }

        $('#expenseForm').submit(function(e) {
            e.preventDefault();
            
            const payload = {
                name: $('#name').val(),
                amount: parseFloat($('#amount').val()),
                category: $('#category').val(),
                expense_date: $('#expense_date').val(),
                notes: $('#notes').val()
            };
            
            showLoading();
            
            const url = expenseId ? \`/api/expenses/\${expenseId}\` : '/api/expenses';
            const method = expenseId ? 'PUT' : 'POST';
            
            $.ajax({
                url: url,
                type: method,
                data: JSON.stringify(payload),
                contentType: 'application/json',
                success: function(res) {
                    hideLoading();
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan',
                        text: expenseId ? 'Perubahan berhasil disimpan' : 'Pengeluaran berhasil ditambahkan',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '/expenses';
                    });
                },
                error: function(xhr) {
                    hideLoading();
                    let msg = 'Gagal menyimpan pengeluaran';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    showError(msg);
                }
            });
        });
    });
</script>
@endpush
