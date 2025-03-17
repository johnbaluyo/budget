<option value="{{ $category['id'] }}"
    @if ($category['level'] === 0) style="background-color: #d3d3d3; font-weight: bold;"
    @elseif ($category['level'] === 1)
        style="background-color: #f0f0f0; font-weight: 600;"
    @else
        style="background-color: transparent;" @endif>
    {!! str_repeat('&nbsp;&nbsp;&nbsp;', $category['level']) !!}
    @if ($category['level'] > 0)
        -
    @endif
    {{ $category['item_of_expenditure'] }}
</option>
@if (!empty($category['children']))
    @foreach ($category['children'] as $child)
        @include('gaa.dropdown', ['category' => $child, 'level' => $level + 1])
    @endforeach
@endif
