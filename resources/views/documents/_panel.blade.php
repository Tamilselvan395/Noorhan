<x-card>
    <x-slot:header><h3 class="font-semibold">Documents</h3></x-slot:header>

    <livewire:documents.document-uploader :entity="$entity" wire:key="uploader-{{ class_basename($entity) }}-{{ $entity->id }}" />

    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse ($entity->documents()->with('uploader')->latest()->get() as $document)
            <li class="flex items-center justify-between p-4">
                <div class="min-w-0">
                    <a href="{{ route('documents.download', $document) }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline truncate">{{ $document->name }}</a>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $document->category()->badge() }}">{{ $document->category()->label() }}</span>
                        {{ $document->humanSize() }} · {{ $document->uploader?->name ?? 'System' }}
                        @if ($document->isExpired())
                            <span class="text-red-500 font-semibold">· EXPIRED {{ $document->expires_at->format('M d, Y') }}</span>
                        @elseif ($document->isExpiringSoon())
                            <span class="text-amber-500 font-semibold">· expires {{ $document->expires_at->format('M d, Y') }}</span>
                        @endif
                    </p>
                </div>
                @can('delete', $document)
                    <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Delete document and physical file?')">
                        @csrf @method('DELETE')
                        <button class="text-xs font-medium text-red-600 dark:text-red-400 hover:underline">Delete</button>
                    </form>
                @endcan
            </li>
        @empty
            <li class="p-6 text-center text-sm text-gray-400">No documents yet.</li>
        @endforelse
    </ul>
</x-card>