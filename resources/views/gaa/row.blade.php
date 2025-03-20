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
        @if (!Request::is('gaa*'))
            @if ($category['allocation'] > 0)
                {{ number_format($category['allocation'], 2) }}
            @endif
            @else
            
            {{-- select gaa_id and current project_id here then get the budget from gaa_project table --}}
        @endif
    </td>
    @if (!Request::is('gaa*'))
        <td>
            <ul>
                {{-- Loop through all expenses where realign_in is not empty --}}
                @foreach ($category['expenses'] as $expense)
                    @if ($expense['gaa_project_id'] == $project->id)
                        @if (!is_null($expense['realign_from']))
                            <li class="hover-text" data-id="{{ $expense['id'] }}" data-activity="{{ $expense['date'] }}"
                                data-remarks="{{ $expense['remarks'] }}">
                                {{ number_format($expense['amount'], 2) }}
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        </td>
        <td>
            <ul>
                {{-- Loop through all expenses where realign_out is not empty --}}
                @foreach ($category['expenses'] as $expense)
                    @if ($expense['gaa_project_id'] == $project->id)
                        @if (!is_null($expense['realign_to']))
                            <li class="hover-text" data-id="{{ $expense['id'] }}" data-activity="{{ $expense['date'] }}"
                                data-remarks="{{ $expense['remarks'] }}">
                                {{ number_format($expense['amount'], 2) }}
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        </td>
    @endif

    <td>
        <ul>
            {{-- Loop through all expenses where both realign_out and realign_in are empty --}}
            @foreach ($category['expenses'] as $expense)
                @if (Request::is('gaa*'))
                    @if (is_null($expense['realign_from']) && is_null($expense['realign_to']))
                        <li class="hover-text" data-id="{{ $expense['id'] }}" data-activity="{{ $expense['date'] }}"
                            data-remarks="{{ $expense['remarks'] }}">
                            {{ number_format($expense['amount'], 2) }}
                        </li>
                    @endif
                @else
                    @if ($expense['gaa_project_id'] == $project->id)
                        @if (is_null($expense['realign_from']) && is_null($expense['realign_to']))
                            <li class="hover-text" data-id="{{ $expense['id'] }}" data-activity="{{ $expense['date'] }}"
                                data-remarks="{{ $expense['remarks'] }}">
                                {{ number_format($expense['amount'], 2) }}
                            </li>
                        @endif
                    @endif
                @endif
            @endforeach
        </ul>
    </td>

    <td>{{ $category['remaining_balance'] == 0 ? null : number_format($category['remaining_balance'], 2) }}</td>
    <td class="d-flex justify-content-end">

        @if (Request::is('project*'))
            @if (empty($category['children']) || $category['allocation'] > 0)
                <button class="btn btn-sm btn-success" onclick="showTracking(`{{ $category['id'] }}`)">Tracking</button>&nbsp;|&nbsp;
            @endif
        @endif
        <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-secondary rounded-circle" id="btnGroupDrop1" data-bs-toggle="dropdown">
                &nbsp;<span class="fas fa-ellipsis-v"></span>&nbsp;
            </button>
            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                @if (Request::is('gaa*'))
                    <li>
                        <a class="dropdown-item bg-success"
                            onclick="addItemToProject(`{{ $category['id'] }}`,`{{ $category['item_of_expenditure'] }}`,`{{ $category['allocation'] }}`)">
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
                        <a class="dropdown-item bg-danger" onclick="_delete({{ $category['id'] }}, {{ Request::is('project*') ? 2 : 1 }})">
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
