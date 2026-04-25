{{-- resources/views/reports/scholarship-impact.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
.report-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 24px;
    overflow: hidden;
}
.report-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    color: white;
    padding: 15px 20px;
}
.stat-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold">Scholarship & Discount Impact Report</h4>
            <p class="text-muted">Analysis of scholarship and discount impact on school revenue</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-primary mb-2"><i class="ri-graduation-cap-line fs-1"></i></div>
                <h3 class="mb-1" id="totalScholarships">0</h3>
                <small class="text-muted">Total Scholarships</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-success mb-2"><i class="ri-discount-line fs-1"></i></div>
                <h3 class="mb-1" id="totalDiscounts">0</h3>
                <small class="text-muted">Total Discounts</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-warning mb-2"><i class="ri-user-star-line fs-1"></i></div>
                <h3 class="mb-1" id="totalBeneficiaries">0</h3>
                <small class="text-muted">Beneficiaries</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-info mb-2"><i class="ri-money-saved-line fs-1"></i></div>
                <h3 class="mb-1" id="totalSavings">₦0</h3>
                <small class="text-muted">Total Savings</small>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-header">
                    <h5 class="mb-0"><i class="ri-pie-chart-line me-2"></i>Scholarship Distribution</h5>
                </div>
                <div class="report-body">
                    <canvas id="scholarshipChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-header">
                    <h5 class="mb-0"><i class="ri-pie-chart-line me-2"></i>Discount Distribution</h5>
                </div>
                <div class="report-body">
                    <canvas id="discountChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="report-card mt-3">
        <div class="report-header">
            <h5 class="mb-0"><i class="ri-table-line me-2"></i>Impact by Class</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="impactTable">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th>Scholarship Students</th>
                        <th class="text-end">Scholarship Value (₦)</th>
                        <th>Discount Students</th>
                        <th class="text-end">Discount Value (₦)</th>
                        <th class="text-end">Total Savings (₦)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="6" class="text-center py-4 text-muted">Loading data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    loadImpactData();
});

function loadImpactData() {
    $.ajax({
        url: '{{ route("reports.financial.scholarship-impact") }}',
        type: 'GET',
        data: { ajax: true },
        success: function(response) {
            if (response.success) {
                updateStats(response.data);
                updateCharts(response.data);
                updateTable(response.data);
            }
        },
        error: function() {
            $('#impactTable tbody').html('<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load data</td></tr>');
        }
    });
}

function updateStats(data) {
    $('#totalScholarships').text(data.total_scholarships || 0);
    $('#totalDiscounts').text(data.total_discounts || 0);
    $('#totalBeneficiaries').text(data.total_beneficiaries || 0);
    $('#totalSavings').text('₦' + (data.total_savings?.toLocaleString() || '0'));
}

function updateCharts(data) {
    // Scholarship Chart
    if (data.scholarship_by_type) {
        var ctx1 = document.getElementById('scholarshipChart').getContext('2d');
        new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: Object.keys(data.scholarship_by_type),
                datasets: [{
                    data: Object.values(data.scholarship_by_type),
                    backgroundColor: ['#2563eb', '#16a34a', '#d97706', '#dc2626', '#8b5cf6']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }

    // Discount Chart
    if (data.discount_by_type) {
        var ctx2 = document.getElementById('discountChart').getContext('2d');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: Object.keys(data.discount_by_type),
                datasets: [{
                    data: Object.values(data.discount_by_type),
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#06b6d4']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }
}

function updateTable(data) {
    if (data.impact_by_class && data.impact_by_class.length > 0) {
        var html = '';
        data.impact_by_class.forEach(function(item) {
            html += `<tr>
                        <td>${item.class}</td>
                        <td>${item.scholarship_students || 0}</td>
                        <td class="text-end">₦${(item.scholarship_value || 0).toLocaleString()}</td>
                        <td>${item.discount_students || 0}</td>
                        <td class="text-end">₦${(item.discount_value || 0).toLocaleString()}</td>
                        <td class="text-end text-success">₦${((item.scholarship_value || 0) + (item.discount_value || 0)).toLocaleString()}</td>
                    </tr>`;
        });
        $('#impactTable tbody').html(html);
    } else {
        $('#impactTable tbody').html('<tr><td colspan="6" class="text-center py-4 text-muted">No impact data available</td></tr>');
    }
}
</script>
@endsection
