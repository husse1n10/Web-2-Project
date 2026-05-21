@props([
    'id',
    'search' => true,
    'searchPlaceholder' => 'Search records...',
    'filters' => []
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
        @if($search)
            <div class="input-group" style="max-width: 320px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-start-0" placeholder="{{ $searchPlaceholder }}" data-table-search="{{ $id }}">
            </div>
        @endif

        @if(count($filters))
            <div class="d-flex gap-2 align-items-center">
                @foreach($filters as $name => $options)
                    <select class="form-select" style="min-width:140px" data-table-filter="{{ $id }}" data-filter-name="{{ $name }}">
                        <option value="">All {{ ucfirst(str_replace('_', ' ', $name)) }}</option>
                        @foreach($options as $value => $label)
                            <option value="{{ strtolower($value) }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @endforeach
            </div>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0" id="{{ $id }}">
            {{ $slot }}
        </table>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const table = document.getElementById('{{ $id }}');
        if (!table) return;

        const rows = () => Array.from(table.querySelectorAll('tbody tr'));
        const searchInput = document.querySelector('[data-table-search="{{ $id }}"]');
        const filterInputs = Array.from(document.querySelectorAll('[data-table-filter="{{ $id }}"]'));

        function applyFilters() {
            const searchTerm = (searchInput?.value || '').toLowerCase().trim();
            const activeFilters = {};

            filterInputs.forEach((input) => {
                const key = input.getAttribute('data-filter-name');
                const value = input.value.toLowerCase().trim();
                if (value) activeFilters[key] = value;
            });

            rows().forEach((row) => {
                const text = row.textContent.toLowerCase();
                let visible = !searchTerm || text.includes(searchTerm);

                Object.keys(activeFilters).forEach((key) => {
                    const cellValue = (row.getAttribute('data-filter-' + key) || '').toLowerCase();
                    if (visible && cellValue !== activeFilters[key]) {
                        visible = false;
                    }
                });

                row.style.display = visible ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', applyFilters);
        filterInputs.forEach((input) => input.addEventListener('change', applyFilters));
    })();
</script>
@endpush
