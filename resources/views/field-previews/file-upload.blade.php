@if (!empty($value))
    @if (is_array($value) && isset($value[0]))
        @php $file = $value[0]; @endphp
        <div class="text-sm text-gray-700">
            <div class="flex items-center">
                <svg class="w-4 h-4 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm3 2a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
                <span>{{ $file['name'] ?? 'File uploaded' }}</span>
            </div>
        </div>
    @elseif (is_string($value))
        <div class="text-sm text-gray-700">
            <div class="flex items-center">
                <svg class="w-4 h-4 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm3 2a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
                <span>{{ $value }}</span>
            </div>
        </div>
    @else
        <div class="text-sm text-gray-700">
            File uploaded
        </div>
    @endif
@else
    <div class="text-sm text-gray-400 italic">
        No file uploaded
    </div>
@endif 