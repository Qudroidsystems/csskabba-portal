{{-- resources/views/reports/balance-sheet.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold">Balance Sheet</h4>
            <p class="text-muted">As at {{ $asAtDate->format('d F, Y') }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Assets</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        @foreach($assets as $asset)
                        <tr><td>{{ $asset['account_name'] }}</td><td class="text-end">₦{{ number_format($asset['balance'], 2) }}</td></tr>
                        @endforeach
                        <tr class="table-active"><th>Total Assets</th><th class="text-end">₦{{ number_format($totalAssets, 2) }}</th></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Liabilities & Equity</h5>
                </div>
                <div class="card-body">
                    <h6>Liabilities</h6>
                    <table class="table table-sm">
                        @foreach($liabilities as $liability)
                        <tr><td>{{ $liability['account_name'] }}</td><td class="text-end">₦{{ number_format($liability['balance'], 2) }}</td></tr>
                        @endforeach
                        <tr><th>Total Liabilities</th><th class="text-end">₦{{ number_format($totalLiabilities, 2) }}</th></tr>
                    </table>
                    <h6>Equity</h6>
                    <table class="table table-sm">
                        @foreach($equity as $eq)
                        <tr><td>{{ $eq['account_name'] }}</td><td class="text-end">₦{{ number_format($eq['balance'], 2) }}</td></tr>
                        @endforeach
                        <tr><th>Total Equity</th><th class="text-end">₦{{ number_format($totalEquity, 2) }}</th></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>
@endsection
