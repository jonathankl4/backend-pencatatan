@extends('layouts.authenticated', ['showBackButton' => true])

@section('title', 'Daftar Pengeluaran')

@push('appbar_actions')
    <button class="btn btn-link text-white text-decoration-none px-2" id="btnClearFilter" style="display: none;" title="Hapus Filter">
        <i class="bi bi-funnel-fill text-warning"></i>
    </button>
    <button class="btn btn-link text-white text-decoration-none px-2" id="btnFilterDate" title="Filter Tanggal">
        <i class="bi bi-calendar-range"></i>
    </button>
@endpush

@section('content')
<div class="container-fluid py-3" style="padding-bottom: 88px;">
    
    <!-- Active Filter Indicator -->
    <div id="filterIndicator" class="alert alert-danger py-2 mb-3 d-flex justify-content-between align-items-center" style="display: none !important;">
        <span class="fw-bold fs-7 text-danger" id="filterText"></span>
        <button type="button" class="btn btn-sm btn-link text-danger p-0" id="btnResetFilterIndicator">Reset</button>
    </div>

    <!-- Hidden inputs for dates -->
    <input type="hidden" id="startDate">
    <input type="hidden" id="endDate">
    
    <div class="mb-3">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
            <input type="text" class="form-control border-start-0 ps-0" id="searchExpense" placeholder="Cari pengeluaran...">
        </div>
    </div>
    
    <div id="expenseList">
        <div class="text-center py-5 text-secondary">
            <div class="spinner-border text-danger" role="status"></div>
            <div class="mt-3">Memuat data pengeluaran...</div>
        </div>
    </div>
    
    <!-- Floating Action Button -->
    <a href="/expenses/create" class="btn btn-danger rounded-circle shadow position-fixed" style="bottom: 24px; right: 24px; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; z-index: 1000;">
        <i class="bi bi-plus-lg fs-4"></i>
    </a>
</div>

<!-- Modal Date Range -->
<div class="modal fade" id="dateFilterModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Filter Tanggal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" class="form-control" id="modalStartDate">
        </div>
        <div class="mb-3">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" class="form-control" id="modalEndDate">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="btnApplyFilter">Terapkan</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    let allExpenses = [];

    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    };

    const formatDate = (dateString) => {
        if (!dateString) return '';
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    };

    const fetchExpenses = () => {
        let url = '/api/expenses';
        const start = $('#startDate').val();
        const end = $('#endDate').val();
        
        if (start && end) {
            url += `?start_date=${start}&end_date=${end}`;
            $('#filterText').text(`Filter: ${formatDate(start)} - ${formatDate(end)}`);
            $('#filterIndicator').attr('style', 'display: flex !important;');
            $('#btnClearFilter').show();
        } else {
            $('#filterIndicator').attr('style', 'display: none !important;');
            $('#btnClearFilter').hide();
        }

        $.ajax({
            url: url,
            type: 'GET',
            success: function(res) {
                allExpenses = res.data || res; // depends on pagination
                renderExpenses();
            },
            error: function() {
                $('#expenseList').html('<div class="text-center py-5 text-danger">Gagal memuat pengeluaran.</div>');
                showError('Gagal memuat data pengeluaran dari server.');
            }
        });
    };

    const renderExpenses = () => {
        const query = $('#searchExpense').val().toLowerCase();
        
        const filtered = allExpenses.filter(expense => {
            const name = expense.name ? expense.name.toLowerCase() : '';
            const category = expense.category ? expense.category.toLowerCase() : '';
            return name.includes(query) || category.includes(query);
        });

        if(filtered.length === 0) {
            $('#expenseList').html(`
                <div class="d-flex flex-column align-items-center justify-content-center text-secondary py-5 mt-5">
                    <i class="bi bi-wallet2" style="font-size: 64px; color: #ccc;"></i>
                    <h5 class="mt-3">Belum ada pengeluaran.</h5>
                </div>
            `);
            return;
        }

        let html = '';
        filtered.forEach(expense => {
            html += `
                <div class="card mb-3 shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="bi bi-arrow-down"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold fs-6">${expense.name}</h6>
                                <small class="text-muted">${formatDate(expense.expense_date)} • ${expense.category}</small>
                            </div>
                            <div class="text-end ms-2">
                                <span class="fw-bold text-danger fs-6">${formatRupiah(expense.amount)}</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-2 pt-2 border-top">
                            <a href="/expenses/${expense.id}/edit" class="btn btn-sm btn-link text-primary p-1 me-2" title="Edit Pengeluaran">
                                <i class="bi bi-pencil-square fs-5"></i>
                            </a>
                            <button class="btn btn-sm btn-link text-danger p-1 btn-delete" data-id="${expense.id}" data-name="${expense.name}" title="Hapus Pengeluaran">
                                <i class="bi bi-trash fs-5"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#expenseList').html(html);
    };

    $(document).ready(function() {
        // Initial fetch
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('startDate') && urlParams.has('endDate')) {
            $('#startDate').val(urlParams.get('startDate'));
            $('#endDate').val(urlParams.get('endDate'));
        }
        fetchExpenses();

        let searchTimeout;
        $('#searchExpense').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(renderExpenses, 300);
        });

        $('#btnFilterDate').click(function() {
            const today = new Date().toISOString().split('T')[0];
            $('#modalStartDate').val($('#startDate').val() || today);
            $('#modalEndDate').val($('#endDate').val() || today);
            
            const modal = new bootstrap.Modal(document.getElementById('dateFilterModal'));
            modal.show();
        });

        $('#btnApplyFilter').click(function() {
            const s = $('#modalStartDate').val();
            const e = $('#modalEndDate').val();
            if(s && e) {
                $('#startDate').val(s);
                $('#endDate').val(e);
                bootstrap.Modal.getInstance(document.getElementById('dateFilterModal')).hide();
                fetchExpenses();
            } else {
                showError('Pilih tanggal awal dan akhir.');
            }
        });

        const resetFilter = () => {
            $('#startDate').val('');
            $('#endDate').val('');
            // remove url params
            window.history.replaceState({}, document.title, window.location.pathname);
            fetchExpenses();
        };

        $('#btnClearFilter, #btnResetFilterIndicator').click(resetFilter);

        // Delete logic
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            Swal.fire({
                title: 'Hapus Pengeluaran',
                text: `Apakah Anda yakin ingin menghapus pengeluaran "${name}"? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#F44336',
                cancelButtonColor: '#757575',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    $.ajax({
                        url: `/api/expenses/${id}`,
                        type: 'DELETE',
                        success: function() {
                            hideLoading();
                            showSuccess(`Pengeluaran "${name}" berhasil dihapus`);
                            fetchExpenses();
                        },
                        error: function(xhr) {
                            hideLoading();
                            let msg = 'Gagal menghapus pengeluaran';
                            if(xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showError(msg);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
