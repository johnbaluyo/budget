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
        @php $row_total = 0; @endphp

    </td>

    @if (Request::is('gaa*'))
        {{-- gaa --}}
        <td>
            {{-- // budget --}}

            @php $row_total += $category['allocation']; @endphp
            @if ($category['allocation'] > 0)
                {{ number_format($category['allocation'], 2) }}
            @endif
        </td>
        <td>
            {{-- expenses --}}
            @foreach ($category['expenses'] as $expense)
                @if (is_null($expense['realign_from']) && is_null($expense['realign_to']))
                    <li class="hover-text" data-id="{{ $expense['id'] }}" data-activity="{{ $expense['date'] }}" data-remarks="{{ $expense['remarks'] }}"
                        data-type="{{ $expense['type'] }}">
                        {{ $expense['type'] == 'IN' ? '+' : '-' }} {{ number_format($expense['amount'], 2) }}
                        @php
                            $row_total += $expense['type'] == 'IN' ? $expense['amount'] : -$expense['amount'];
                        @endphp
                    </li>
                @endif
            @endforeach
        </td>
    @else
        {{-- projects --}}
        <td>
            {{-- budget --}}
            @php $gaa_project_budget = App\GaaProject::where('gaa_id', $category['gaa_id'])->where('project_id', $project->id)->pluck('budget')->first(); @endphp
            @php $row_total += $gaa_project_budget; @endphp
            @if ($gaa_project_budget > 0)
                {{ number_format($gaa_project_budget, 2) }}
            @endif
        </td>
        <td>
            {{-- realign_in --}}
            <ul>
                {{-- Loop through all expenses where realign_in is not empty --}}
                @foreach ($category['expenses'] as $expense)
                    @if ($expense['gaa_project_id'] == $project->id)
                        @if (!is_null($expense['realign_from']))
                            <li class="hover-text" data-id="{{ $expense['id'] }}" data-activity="{{ $expense['date'] }}"
                                data-remarks="{{ $expense['remarks'] }}">
                                {{ number_format($expense['amount'], 2) }}
                                @php $row_total += $expense['amount']; @endphp
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        </td>
        <td>
            {{-- realign_out --}}
            <ul>
                {{-- Loop through all expenses where realign_out is not empty --}}
                @foreach ($category['expenses'] as $expense)
                    @if ($expense['gaa_project_id'] == $project->id)
                        @if (!is_null($expense['realign_to']))
                            <li class="hover-text" data-id="{{ $expense['id'] }}" data-activity="{{ $expense['date'] }}"
                                data-remarks="{{ $expense['remarks'] }}">
                                {{ number_format($expense['amount'], 2) }}
                                @php $row_total -= $expense['amount']; @endphp
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        </td>
        <td>
            {{-- expenses --}}
            <ul>
                {{-- Loop through all expenses where both realign_out and realign_in are empty --}}
                @foreach ($category['expenses'] as $expense)
                    @if ($expense['gaa_project_id'] == $project->id)
                        @if (is_null($expense['realign_from']) && is_null($expense['realign_to']))
                            <li class="hover-text" data-id="{{ $expense['id'] }}" data-activity="{{ $expense['date'] }}"
                                data-remarks="{{ $expense['remarks'] }}">
                                {{ $expense['type'] == 'IN' ? '+' : '-' }} {{ number_format($expense['amount'], 2) }}
                                @php $row_total -= $expense['amount']; @endphp
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        </td>
    @endif

    <td>{{ number_format($row_total, 2) }}</td>
    <td class="d-flex justify-content-end">

        @if (Request::is('project*'))
            @if (empty($category['children']) || $category['allocation'] > 0)
                <button class="btn btn-sm btn-success" onclick="showTracking(`{{ $category['gaa_id'] }}`)">Tracking</button>&nbsp;|&nbsp;
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
                            onclick="addItemToProject(`{{ $category['gaa_id'] }}`,`{{ $category['item_of_expenditure'] }}`,`{{ $category['allocation'] }}`)">
                            Add item to project
                        </a>
                    </li>
                @endif
                <li>
                    <a class="dropdown-item" onclick="editGaaItem({{ $category['gaa_id'] }})">
                        Edit details
                    </a>
                </li>
                <li>
                    <a class="dropdown-item"
                        onclick="addSubItem(`{{ $category['gaa_id'] }}`,`{{ $category['item_of_expenditure'] }}`,`{{ $category['object_type'] }}`)">
                        Add sub-item
                    </a>
                </li>
                @if (Request::is('project*') || Request::is('gaa*'))
                    <li>
                        <a class="dropdown-item bg-danger" onclick="_delete({{ $category['gaa_id'] }}, {{ Request::is('project*') ? 2 : 1 }})">
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
