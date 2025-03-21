@extends('layouts.master')

@section('heading')
    {{ __('Reset Data') }}
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h4>{{ __('Database Reset Tool') }}</h4>
    </div>
    <div class="card-body">
        
        <!-- Messages de session -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="alert alert-danger">
            <strong>{{ __('Warning!') }}</strong> 
            {{ __('This action will delete all data from the system. This cannot be undone!') }}
        </div>
        
        <p>{{ __('This tool will delete all data in the following order:') }}</p>
        
        <ol>
            <li>{{ __('Temporary data (password resets, notifications)') }}</li>
            <li>{{ __('User relationships and permissions') }}</li>
            <li>{{ __('Client-related data (payments, invoices, documents)') }}</li>
            <li>{{ __('Projects, tasks, and leads') }}</li>
            <li>{{ __('Clients and contacts') }}</li>
            <li>{{ __('System settings and parameters') }}</li>
            <li>{{ __('Departments and access control data') }}</li>
        </ol>
        
        <div class="text-center mt-4">
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#resetDataModal">
                <i class="ion ion-md-nuclear"></i> {{ __('Reset All Data') }}
            </button>
        </div>
    </div>
</div>

<!-- Reset Data Confirmation Modal -->
<div class="modal fade" id="resetDataModal" tabindex="-1" role="dialog" aria-labelledby="resetDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="padding:35px 50px;">
                <h4 id="resetDataModalLabel">
                    <span class="ion ion-md-warning" style="margin-right: 1em;"></span> 
                    {{ __('Confirm Complete Data Reset') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding:40px 50px;">
                <form id="resetDataForm" method="POST" action="{{ route('data.process') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="confirmation">{{ __('Type "RESET" to confirm:') }}</label>
                        <input type="text" class="form-control" id="confirmation" placeholder="{{ __('RESET') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">{{ __('Enter your password:') }}</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" id="confirmResetButton" class="btn btn-danger" disabled>
                    <i class="ion ion-md-nuclear"></i> {{ __('Permanently Reset All Data') }}
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
    $(function() {
        $('#confirmation').on('input', function() {
            if ($(this).val() === 'RESET') {
                $('#confirmResetButton').prop('disabled', false);
            } else {
                $('#confirmResetButton').prop('disabled', true);
            }
        });

        $('#confirmResetButton').click(function() {
            if ($('#confirmation').val() === 'RESET' && $('#password').val().length > 0) {
                $('#resetDataForm').submit();
            }
        });
    });
</script>
@endpush
