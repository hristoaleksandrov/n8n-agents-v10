<div>
    <div class="mb-6">
        <a href="{{ route('tasks.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            &larr; Back to tasks
        </a>
    </div>

    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Task #{{ $task->id }}</h1>
            <p class="text-sm text-gray-500 mt-1">Created {{ $task->created_at->format('M j, Y \a\t g:i A') }}</p>
        </div>
        @php
            $colors = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'processing' => 'bg-blue-100 text-blue-800',
                'completed' => 'bg-green-100 text-green-800',
                'failed' => 'bg-red-100 text-red-800',
            ];
        @endphp
        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $colors[$task->status->value] }}">
            {{ $task->status->label() }}
        </span>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Desired Outcome</h2>
            <p class="text-gray-900">{{ $task->outcome_description }}</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Original Script</h2>
            <pre class="text-sm text-gray-700 whitespace-pre-wrap font-mono bg-gray-50 p-4 rounded">{{ $task->reference_script }}</pre>
        </div>

        @if ($task->new_script)
            <div class="bg-white rounded-lg border border-green-200 p-6">
                <h2 class="text-sm font-medium text-green-600 uppercase tracking-wide mb-3">Generated Script</h2>
                <pre class="text-sm text-gray-700 whitespace-pre-wrap font-mono bg-green-50 p-4 rounded">{{ $task->new_script }}</pre>
            </div>
        @endif

        @if ($task->analysis)
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Analysis</h2>
                <p class="text-gray-700">{{ $task->analysis }}</p>
            </div>
        @endif

        @if ($task->error_message)
            <div class="bg-red-50 rounded-lg border border-red-200 p-6">
                <h2 class="text-sm font-medium text-red-600 uppercase tracking-wide mb-3">Error</h2>
                <p class="text-red-700">{{ $task->error_message }}</p>
            </div>
        @endif

        @if ($task->status->value === 'pending' || $task->status->value === 'processing')
            <div class="bg-blue-50 rounded-lg border border-blue-200 p-4 text-center">
                <div class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-blue-700 text-sm">Processing...</p>
                </div>
            </div>
        @endif
    </div>
</div>
