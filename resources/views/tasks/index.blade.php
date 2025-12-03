<x-layouts.app title="Tasks">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Tasks</h1>
        <span class="text-sm text-gray-500">{{ $tasks->total() }} total</span>
    </div>

    @if ($tasks->isEmpty())
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <p class="text-gray-500 mb-4">No tasks yet</p>
            <a href="{{ route('tasks.create') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                Create your first task
            </a>
        </div>
    @else
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outcome</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($tasks as $task)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900 font-mono">#{{ $task->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">
                                {{ Str::limit($task->outcome_description, 50) }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $colors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $colors[$task->status->value] }}">
                                    {{ $task->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $task->created_at->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $tasks->links() }}
        </div>
    @endif
</x-layouts.app>
