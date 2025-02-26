<div class="task-data-wrapper table-responsive">
    <table class="table g-3 text-nowrap">
        <tbody>
            @foreach($data as $key => $value)
            <tr>
                <td class="fw-bold">{{ $key }}:</td>
                <td class="mw-200px text-wrap">{{ $value }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>