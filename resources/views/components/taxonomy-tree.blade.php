<div>
    <div class="columns-2 md:columns-3 lg:columns-5 gap-4 space-y-10 text-sm w-full">
        @foreach ($taxonomies as $taxonomy)
            <div class="break-inside-avoid">
                <h3 class="mb-2">
                    <x-link 
                        href="{{ route('catalogs.index', array_filter([$store?->slug, $taxonomy['slug']])) }}"
                        class="font-medium text-gray-900"
                    >
                        {{ $taxonomy['title'] }}
                    </x-link>
                </h3>
                <ul class="space-y-1">
                    @foreach ($taxonomy['children'] as $subtaxonomy)
                        <li>
                            <x-link 
                                href="{{ route('catalogs.index', array_filter([$store?->slug, $subtaxonomy['slug']])) }}"
                                class="text-gray-600"
                            >
                                {{ $subtaxonomy['title'] }}
                            </x-link>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>