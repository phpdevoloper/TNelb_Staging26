@props(['stepper' => []])

@if(empty($stepper))
    <p class="text-muted mb-0">No pending approval workflow.</p>
@else
    <div class="approval-stepper">
        @foreach($stepper as $step)
            <div class="approval-step {{ $step['state'] }}">
                <div class="step-num">Level {{ $step['level'] }}</div>
                <div class="font-weight-bold">{{ $step['label'] }}</div>
                <div class="mt-2">
                    @if($step['state'] === 'completed')
                        <span class="badge badge-success">Approved</span>
                    @elseif($step['state'] === 'current')
                        <span class="badge badge-warning">Awaiting Action</span>
                    @elseif($step['state'] === 'rejected')
                        <span class="badge badge-danger">Rejected</span>
                    @else
                        <span class="badge badge-secondary">Pending</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
