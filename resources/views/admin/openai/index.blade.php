@extends('admin.partials.master')

@section('main-content')
<div class="container mt-4">
    <div class="row">
        <!-- Product Name Form -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Generate Product Data</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('openai.updateOrCreate') }}" method="GET">
                        <div class="form-group">
                            <label for="product_name">Enter Product Name</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" placeholder="e.g. Apple iPhone 15" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-magic"></i> Generate Data
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- JSON Paste Form -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Import Product Data in Json format  For productLanguage Descriptions and Seo </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('openai.processJson') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="json_data">Paste Generated JSON</label>
                            <textarea class="form-control" id="json_data" name="json_data" rows="8" 
                                      placeholder='{"records":[{"lang":"en","name":"Apple iPhone 15",...}]}' required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-database"></i> Update Language Records
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Display Area -->
    @if(session('success'))
        <div class="alert alert-success mt-4">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mt-4">
            <i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    textarea { font-family: monospace; font-size: 14px; }
    .card { box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); }
</style>
@endpush