{{-- resources/views/finance/payroll/periods.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold">Payroll Periods</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPeriodModal">
                    <i class="ri-add-line me-1"></i>Create Period
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="payrollTable">
                    <thead class="table-light">
                        <tr><th>Period</th><th>Start Date</th><th>End Date</th><th>Payment Date</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($periods ?? [] as $period)
                        <tr>
                            <td>{{ $period->period_name }}</td>
                            <td>{{ $period->start_date->format('d M Y') }}</td>
                            <td>{{ $period->end_date->format('d M Y') }}</td>
                            <td>{{ $period->payment_date->format('d M Y') }}</td>
                            <td><span class="badge bg-{{ $period->status == 'approved' ? 'success' : 'warning' }}">{{ ucfirst($period->status) }}</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">View</button>
                                <button class="btn btn-sm btn-primary">Process</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>
@endsection
