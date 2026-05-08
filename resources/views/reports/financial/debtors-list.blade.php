@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
:root {
    --report-primary: #1e3a5f;
    --report-accent: #2563eb;
    --report-success: #16a34a;
    --report-warning: #d97706;
    --report-border: #e2e8f0;
    --report-radius: 12px;
}

.report-hero {
    background: linear-gradient(135deg, var(--report-primary) 0%, var(--report-accent) 60%, #4f46e5 100%);
    border-radius: var(--report-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.report-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
}
.report-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
}

.stat-card {
    background: #fff;
    border: 1px solid var(--report-border);
    border-radius: var(--report-radius);
    padding: 18px 20px;
    transition: transform .15s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--report-primary);
}
.stat-card .stat-label {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

.filter-bar {
    background: #f8fafc;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 24px;
}
.filter-label {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 8px;
    color: var(--report-primary);
}

.report-table th {
    background: var(--report-primary);
    color: #fff;
    padding: 12px 16px;
    font-size: 13px;
}
.report-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--report-border);
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="report-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="ri-file-list-3-line me-2"></i>Student Debtors Report</h1>
                <p>Students with outstanding fee balances</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-light btn-sm" onclick="exportReport('pdf')"><i class="ri-file-pdf-line"></i> PDF</button>
                <button class="btn btn-light btn-sm" onclick="exportReport('excel')"><i class="ri-file-excel-line"></i> Excel</button>
                <button class="btn btn-light btn-sm" onclick="window.print()"><i class="ri-printer-line"></i> Print</button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stat-card"><div class="stat-value" id="totalDebtors">—</div><div class="stat-label">Total Debtors</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-value" id="totalOutstanding">₦—</div><div class="stat-label">Total Outstanding</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-value" id="totalCollected">₦—</div><div class="stat-label">Total Collected</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-value" id="totalSavings">₦—</div><div class="stat-label">Total Savings</div></div></div>
    </div>

    <div class="filter-bar">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="filter-label">Class</label>
                <select class="form-select" id="classFilter">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="filter-label">Term</label>
                <select class="form-select" id="termFilter">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="filter-label">Session</label>
                <select class="form-select" id="sessionFilter">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100" id="applyFilters"><i class="ri-search-line me-1"></i> Search</button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold"><i class="ri-table-line me-2"></i>Debtors List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table report-table w-100" id="debtorsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Bill</th>
                            <th>Term/Session</th>
                            <th class="text-end">Original</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Outstanding</th>
                            <th class="text-end">Savings</th>
                            <th>Rate</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>

<div class="modal fade image-zoom-modal" id="imageZoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <button class="btn-close-zoom" data-bs-dismiss="modal" style="position:absolute; top:20px; right:30px; background:rgba(0,0,0,.7); border:none; border-radius:50%; width:38px; height:38px; color:white;">×</button>
            <div class="modal-body text-center">
                <img id="zoomedImage" src="" style="max-width:90vw; max-height:75vh; border-radius:16px; border:4px solid white;">
                <div class="zoomed-image-name" id="zoomedImageName" style="color:white; margin-top:18px;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
function formatNumber(n) { return parseFloat(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 2 }); }
function naira(n) { return '₦' + formatNumber(n); }

$(document).ready(function() {
    const table = $('#debtorsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("reports.financial.debtors") }}',
            data: function(d) {
                d.class_id = $('#classFilter').val();
                d.term_id = $('#termFilter').val();
                d.session_id = $('#sessionFilter').val();
                d.min_outstanding = $('#minOutstanding').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false },
            { data: 'student_avatar', orderable: false },
            { data: 'student_name' },
            { data: 'class_name' },
            { data: 'bill_title' },
            { data: 'term_name' },
            { data: 'original_amount', className: 'text-end' },
            { data: 'amount_paid', className: 'text-end' },
            { data: 'outstanding', className: 'text-end' },
            { data: 'savings', className: 'text-end' },
            { data: 'collection_rate' },
            { data: 'action', orderable: false }
        ],
        drawCallback: function() {
            const data = table.ajax.json();
            if (data && data.data) {
                let o=0, p=0, s=0;
                data.data.forEach(r => {
                    o += parseFloat(r.outstanding) || 0;
                    p += parseFloat(r.amount_paid) || 0;
                    s += parseFloat(r.savings) || 0;
                });
                $('#totalDebtors').text(data.recordsTotal || 0);
                $('#totalOutstanding').text(naira(o));
                $('#totalCollected').text(naira(p));
                $('#totalSavings').text(naira(s));
            }
        }
    });

    $('#applyFilters').on('click', () => table.ajax.reload());
    $('#classFilter, #termFilter, #sessionFilter, #minOutstanding').on('change', () => table.ajax.reload());
});

function exportReport(format) {
    var classId = $('#classFilter').val() || '';
    var termId = $('#termFilter').val() || '';
    var sessionId = $('#sessionFilter').val() || '';
    var minOutstanding = $('#minOutstanding').val() || '';

    var url = '{{ url("reports/financial/export") }}/debtors/' + format +
              '?class_id=' + classId +
              '&term_id=' + termId +
              '&session_id=' + sessionId +
              '&min_outstanding=' + minOutstanding;
    window.open(url, '_blank');
}
</script>
@endsection
