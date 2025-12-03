<x-layouts.app title="New Task">
    <div class="max-w-2xl">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">New Task</h1>

        <form action="{{ route('tasks.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="reference_script" class="block text-sm font-medium text-gray-700 mb-2">
                    Reference Script
                </label>
                <textarea
                    id="reference_script"
                    name="reference_script"
                    rows="8"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    placeholder="Paste your original ad script here..."
                >{{ old('reference_script') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">The advertising script you want to improve</p>
            </div>

            <div>
                <label for="outcome_description" class="block text-sm font-medium text-gray-700 mb-2">
                    Desired Outcome
                </label>
                <textarea
                    id="outcome_description"
                    name="outcome_description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    placeholder="e.g., Make it more professional, target younger audience, add urgency..."
                >{{ old('outcome_description') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Describe how you want the script improved</p>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Create Task
                </button>
                <a href="{{ route('tasks.index') }}" class="text-gray-600 hover:text-gray-700 text-sm">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>
