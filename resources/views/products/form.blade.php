@extends('layouts.authenticated', ['showBackButton' => true, 'backUrl' => url()->previous() !== url()->current() ? url()->previous() : '/products'])

@section('title', 'Tambah Produk')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form id="productForm">
                <div class="mb-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="name" placeholder="Nama Produk" required>
                        <label for="name">Nama Produk</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="cost_price" placeholder="Harga Modal (Rp)" required min="0" step="1">
                        <label for="cost_price">Harga Modal (Rp)</label>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="sell_price" placeholder="Harga Jual (Rp)" required min="0" step="1">
                        <label for="sell_price">Harga Jual (Rp)</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mt-2">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#productForm').submit(function(e) {
            e.preventDefault();
            
            const payload = {
                name: $('#name').val(),
                cost_price: parseFloat($('#cost_price').val()),
                sell_price: parseFloat($('#sell_price').val()),
                is_active: 1
            };
            
            showLoading();
            
            $.ajax({
                url: '/api/products',
                type: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                success: function(res) {
                    hideLoading();
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan',
                        text: 'Produk berhasil ditambahkan',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '/products';
                    });
                },
                error: function(xhr) {
                    hideLoading();
                    let msg = 'Gagal menyimpan produk';
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
