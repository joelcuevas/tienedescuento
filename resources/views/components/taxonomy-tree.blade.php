<div>
    <div class="columns-2 md:columns-3 lg:columns-5 gap-4 space-y-10">
        @foreach ($taxonomies as $taxonomy)
            <div class="break-inside-avoid">
                <h3 class="mb-2">
                    <x-link 
                        href="{{ $taxonomy->link() }}"
                        class="font-medium text-gray-900"
                    >
                        {{ $taxonomy->title }}
                    </x-link>
                </h3>
                <ul class="space-y-1">
                    @foreach ($taxonomy->subtaxonomies as $subtaxonomy)
                        <li>
                            <x-link 
                                href="{{ $subtaxonomy->link() }}"
                                class="text-gray-600"
                            >
                                {{ $subtaxonomy->title }}
                            </x-link>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>