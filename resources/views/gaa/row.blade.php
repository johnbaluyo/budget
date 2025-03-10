{{-- <tr @if ($category['project']['project_name'] != 'PSRTI' && Request::is('gaa*')) style="background-color: #cef5d7;" @endif> --}}
<tr>
    <td
        style="padding-left: calc(20px * {{ $level }}); font-weight: 
            @if ($level === 0) bold 
            @elseif ($level === 1) 600 
            @else normal @endif;">
        @if ($level === 2)
            •
        @elseif ($level >= 3)
            -
        @endif
        &nbsp;{{ $category['item_of_expenditure'] }}

    </td>
    <td>
        @if ($category['allocation'] > 0)
            {{ number_format($category['allocation'], 2) }}
        @endif
    </td>
    @if (!Request::is('gaa*'))
        <td>
            <ul>
                @foreach ($category['realign_in'] as $in)
                    <li class="hover-text" data-id="{{ $in['id'] }}" data-activity="{{ $in['activity_date'] }}"
                        data-remarks="{{ $in['remarks'] }}">
                        {{ number_format($in['amount'], 2) }}
                    </li>
                @endforeach
            </ul>
        </td>
        <td>
            <ul>
                @foreach ($category['realign_out'] as $out)
                    <li class="hover-text" data-id="{{ $out['id'] }}" data-activity="{{ $out['activity_date'] }}"
                        data-remarks="{{ $out['remarks'] }}">
                        {{ number_format($out['amount'], 2) }}
                    </li>
                @endforeach
            </ul>
        </td>
    @endif
    <td>
        <ul>
            @foreach ($category['expenses'] as $expenses)
                <li class="hover-text" data-id="{{ $gaa['id'] }}" data-activity="{{ $gaa['activity_date'] }}"
                    data-remarks="{{ $gaa['remarks'] }}">
                    {{ number_format($gaa['amount'], 2) }}</li>
            @endforeach
        </ul>
    </td>
    <td>{{ $category['remaining_balance'] == 0 ? null : number_format($category['remaining_balance'], 2) }}</td>
    <td class="d-flex justify-content-end">
        @if (empty($category['children']) || $category['allocation'] > 0)
            <button class="btn btn-sm btn-success"
                onclick="showTracking(`{{ $category['id'] }}`,`{{ $category['item_of_expenditure'] }}`)">Tracking</button>&nbsp;|&nbsp;
        @endif
        <div class="btn-group" role="group">
            <button class="btn btn-sm btn-ssi" id="btnGroupDrop1" data-bs-toggle="dropdown">
                Options <span class="fa fa-chevron-right"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                @if (Request::is('gaa*'))
                    <li>
                        <a class="dropdown-item bg-success"
                            onclick="addItemToProject(`{{ $category['id'] }}`,`{{ $category['item_of_expenditure'] }}`)">
                            Add item to project
                        </a>
                    </li>
                @endif
                <li>
                    <a class="dropdown-item" onclick="editGaaItem({{ $category['id'] }})">
                        Edit details
                    </a>
                </li>
                <li>
                    <a class="dropdown-item"
                        onclick="addSubItem(`{{ $category['id'] }}`,`{{ $category['item_of_expenditure'] }}`,`{{ $category['object_type'] }}`)">
                        Add sub-item
                    </a>
                </li>
                @if (Request::is('project*') || Request::is('gaa*'))
                    <li>
                        <a class="dropdown-item bg-danger"
                            onclick="_delete({{ $category['id'] }}, {{ Request::is('project*') ? 2 : 1 }})">
                            {{ Request::is('project*') ? 'Remove item from Project' : 'Delete item from GAA' }}
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </td>
</tr>

@if (!empty($category['children']))
    @foreach ($category['children'] as $child)
        @include('gaa.row', ['category' => $child, 'level' => $level + 1])
    @endforeach
@endif
