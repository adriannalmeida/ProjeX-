<div class="searchBox {{ $additionalClasses ?? '' }}">
    <form id="search-form" action="{{ $formAction }}" method="GET">
        <div class="search-container" style="position: relative;">
            <input type="text" id="search-input" class="search {{ empty($filters) ? '' : 'filter' }}" name="search"
                value="{{ $search ?? '' }}" placeholder="{{ $placeholder ?? 'Search...' }}" autocomplete="off">
            <aside id="search_buttons">
                <i class="fa fa-times-circle clear-icon focusable" id="clear-input"
                    style="display: {{ $search ? 'block' : 'none' }};">
                </i>
                {{-- Optional Filter Dropdown --}}
                @if (!empty($filters))
                    <div class="filter-dropdown">
                        @php
                            $currentFilter = $currentFilter ?? $filters[0]; // Default to the first filter in the list if no current filter is set
                            $options = $filters;
                        @endphp

                        <select id="filter-select" name="filter">
                            @foreach ($options as $option)
                                <option value="{{ $option === 'All Priorities' ? '' : $option }}"
                                    {{ $currentFilter === $option || ($option === 'All Priorities' && !$currentFilter) ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </aside>
        </div>
    </form>

    {{-- Results Container --}}

    <ol id="results-container" data-url="{{ $resultsUrl }}">
        @if ($search && $results->isEmpty())
            <li class="no-search-results">No results found for "{{ $search }}".</li>
        @elseif ($search)
            @foreach ($results as $result)
                <li class="search-result">
                    <a href="{{ $result['url'] }}" class="{{ $result['class'] ?? '' }}"
                        data-id="{{ $result['id'] ?? '' }}">
                        <p class="result-name">{{ $result['name'] }}</p>
                        <p class="result-description">{{ $result['description'] }}</p>
                    </a>
                </li>
            @endforeach
        @endif
    </ol>

    {{-- Display the search query if it is not empty --}}
    @if (!empty($search))
        <div class="search-query-display">
            <p>Showing results for: <strong>{{ $search }}</strong></p>
        </div>
    @endif
</div>
