@extends('layout.app')
@section('title', 'Manage Workflow')
@section('page')
    <div class="container">
        <form action="" id="workflow-form">
            <div class="card-header border">
                <div class="card">
                    <div class="card-title">
                        <h2>Manage Workflow</h2>
                    </div>
                </div>
                <div class="card-body p-xxl-15">
                    <div class="row">
                        <div class="col-lg-3">
                            <label for="applicable_group_id">Applicable Group</label>
                            <select required class="form-select" name="applicable_group_id" id="applicable_group_id">
                                <option value="">-- Select Group --</option>
                                @foreach ($flowGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label for="task_type">Task Type</label>
                            <select required class="form-select" name="task_type" id="task_type">
                                <option value="">-- Select Type --</option>
                                @foreach ($taskTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <hr class="my-7">
                    <div class="row">
                        <div class="col-12 ">
                            <table class="table g-3 table-bordered table-striped w-100 thead-strong align-middle">
                                <thead class="bg-dark text-light">
                                    <tr>
                                        <th class="w-25">State</th>
                                        <th class="w-25">Entity Types</th>
                                        <th style="width: 50%">Assigned To</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody id="workflow-definitions"></tbody>
                            </table>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center" id="actions">
                                <button type="reset" data-action="reset" class="btn btn-secondary mx-3">Cancel</button>
                                <button type="button" data-action="submit" class="btn btn-primary mx-3">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
<script>
    $(function() {
        route.push('workflow.update', '{{ rawRoute('workflow.update') }}')
        
        const ENTITY_USER = '{{ App\Models\Entity::USER }}';
        const ENTITY_EMPLOYEE = '{{ App\Models\Entity::EMPLOYEE }}';
        const ENTITY_GROUP = '{{ App\Models\Entity::GROUP }}';
        const ENTITY_SPECIAL_GROUP = '{{ App\Models\Entity::SPECIAL_GROUP }}';
        const ENTITY_ACCESS_ROLE = '{{ App\Models\Entity::ACCESS_ROLE }}'

        const storage = {
            entityTypes : @json($entityTypes),
            [ENTITY_GROUP]: [],
            [ENTITY_SPECIAL_GROUP]: @json($specialGroups),
            taskStates: @json($taskStates),
            [ENTITY_USER]: @json($users),
            [ENTITY_EMPLOYEE]: @json($employees),
            [ENTITY_ACCESS_ROLE]: @json($accessRoles),
            workflow: {
                id: null,
                task_type: '',
                entity_type_id: '',
                entity_id: '',
                definitions: []
            }
        };

        const definitionsTable = document.getElementById('workflow-definitions');

        // Clone the groups select element from ui
        (() => {
            const elem = document.getElementById('applicable_group_id').cloneNode(true);

            for (let i = 1; i < elem.options.length; i++) {
                const option = elem.options[i];
                storage[ENTITY_GROUP][storage[ENTITY_GROUP].length] = {
                    id: option.value,
                    name: option.text
                }
            }
        })();

        // Handle add button click
        $(definitionsTable).on('click', '[data-action="add"]', function() {
            const row = $(this).closest('tr');
            const stateId = row.find('[data-state]').val();
            const entityTypeId = row.find('[data-entity-type]').val();
            const assignedToId = row.find('[data-assigned-to]').val();
            const lastDefinition = storage.workflow.definitions[storage.workflow.definitions.length -1] || {};
            
            if (assignedToId == '') {
                toastr.error('Please select a assignee')
                return false;
            }

            storage.workflow.definitions.push({
                previous_state_id: lastDefinition.state_id || null,
                state_id: stateId,
                entity_type_id: entityTypeId,
                entity_id: assignedToId
            });
            render();
        })

        // Handle delete button click
        $(definitionsTable).on('click', '[data-action="delete"]', function() {
            const row = $(this).closest('tr');
            const stateId = row.find('[data-state]').data('state');
            storage.workflow.definitions = storage.workflow.definitions.filter(definition => definition.state_id != stateId);
            render();
        })

        // Handle form submit
        $('[data-action="submit"]').on('click', function (e) {
            const workflow = storage.workflow;

            if (!workflow.applicable_group_id) {
                return toastr.error('Please select the applicable group');
            }

            if (!workflow.task_type) {
                return toastr.error('Please select the task type');
            }

            if (!workflow.definitions.length) {
                return toastr.error('Please define atleast one assigned group');
            }

            ajaxRequest({
                url: workflow.id
                    ? route('workflow.update', {workflow: workflow.id})
                    : '{{ route('workflow.store') }}',
                method: workflow.id ? 'put' : 'post',
                data: workflow
            }).done((data, msg, xhr) => {
                switch (xhr.status) {
                    case 201:
                        return swal.fire('SUCCESS', 'Workflow saved successfully', 'success').then(() => $('#workflow-form')[0].reset())
                    case 204:
                        return swal.fire('SUCCESS', 'Workflow updated successfully', 'success').then(() => $('#workflow-form')[0].reset())
                    default:
                        return defaultErrorHandler()
                }
            }).fail((xhr) => {
                if (xhr.status == 422 && xhr.responseJSON && xhr.responseJSON.message) {
                    return toastr.error(xhr.responseJSON.message);
                }

                return defaultErrorHandler();
            })
        })

        // Handle the change
        $('#applicable_group_id, #task_type').on('change', function() {
            storage.workflow.applicable_group_id = $('#applicable_group_id').val();
            storage.workflow.task_type = $('#task_type').val();
            
            if (!storage.workflow.applicable_group_id || !storage.workflow.task_type) {
                return render();
            }

            ajaxRequest({
                url: '{{ route('workflow.find') }}',
                data: {
                    applicable_group_id: storage.workflow.applicable_group_id,
                    task_type: storage.workflow.task_type
                }
            }).done((data, msg, resp) => {
                if (resp.status == 200) {
                    const workflow = data.workflow || {};
                    storage.workflow.id = workflow.id || null;
                    storage.workflow.definitions = workflow.definitions || [];
                    render()
                } else {
                    defaultErrorHandler()
                }
            }).fail(defaultErrorHandler)
        })

        // Handle form reset
        $('#workflow-form').on('reset', function () {
            storage.workflow = {
                entity_type_id: '',
                entity_id: '',
                task_type: '',
                definitions: []
            }

            render();
        })

        render();

        function render() {
            empty(definitionsTable);

            storage.workflow.definitions.map(definition => {
                definitionsTable.appendChild(generateRow(definition));
            })
            if ((nextDefinitionRow = generateRow())) {
                definitionsTable.appendChild(nextDefinitionRow);
            }
        }

        function generateRow(definition = null) {
            const lastDefinition = storage.workflow.definitions[storage.workflow.definitions.length -1] || {};
            const tr = document.createElement('tr');
            const stateTd = document.createElement('td');
            const entityTypesTd = document.createElement('td');
            const assignedToTd = document.createElement('td');
            const actionsTd = document.createElement('td');

            tr.appendChild(stateTd);
            tr.appendChild(entityTypesTd);
            tr.appendChild(assignedToTd)
            tr.appendChild(actionsTd);

            if (definition === null) {
                if (!storage.workflow.applicable_group_id || !storage.workflow.task_type) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.colSpan = 4;
                    td.className = 'text-center'
                    td.textContent = "Please select the group and task type to continue";

                    tr.appendChild(td);
                    return tr;
                }

                lastDefinition.next_state_id = +(lastDefinition.state_id || 0) + 1;
                const nextState = storage.taskStates.find(state => state.id == lastDefinition.next_state_id);
                const entityTypes = storage.entityTypes;

                if (!nextState) {
                    return;
                }

                stateTd.appendChild(generateStateSelect(nextState));
                entityTypesTd.appendChild(generateTypesSelect(entityTypes));
                assignedToTd.appendChild(generateAssignToSelect());
                actionsTd.appendChild(addButtonControl());
            } else {
                const state = storage.taskStates.find(state => state.id == definition.state_id);
                const entityTypes = storage.entityTypes.find(entity => entity.id == definition.entity_type_id);
                const assignedTo = storage[definition.entity_type_id].find(entity => entity.id == definition.entity_id);

                stateTd.dataset.state = state.id;
                stateTd.textContent = state.name;
                entityTypesTd.textContent = entityTypes.name;
                assignedToTd.textContent = assignedTo.name;

                if (lastDefinition === definition) {
                    actionsTd.appendChild(deleteButtonControl());
                }
            }

            return tr;
        }

        function generateStateSelect(state) {
            const select = document.createElement('select');
            select.dataset.state = state.id;
            select.className = 'form-select form-select-sm';
            select.appendChild(new Option(state.name, state.id, true));
            return select;
        }

        function generateTypesSelect(entities) {
            const select = document.createElement('select');
            select.dataset.entityType = true;
            select.className = 'form-select form-select-sm';
            select.required = true;

            select.appendChild(new Option('-- Select Type --', ''));
            entities.map(entity => {
                select.appendChild(new Option(entity.name, entity.id));
            })
            return select;
        }

        function generateAssignToSelect() {
            const select = document.createElement('select');
            select.dataset.assignedTo = true;
            select.className = 'form-select form-select-sm';
            select.required = true;

            select.appendChild(new Option('-- Select --', ''));
            return select;
        }

        function addButtonControl() {
            const addButton = document.createElement('button');
            addButton.dataset.action = 'add';
            addButton.textContent = 'Add'
            addButton.className = 'btn btn-sm btn-success mx-3';

            return addButton
        }

        function deleteButtonControl() {
            const deleteButton = document.createElement('button');
            deleteButton.dataset.action = 'delete';
            deleteButton.textContent = 'Delete'
            deleteButton.className = 'btn btn-sm btn-danger mx-3';

            return deleteButton;
        }

        $(definitionsTable).on('change', '[data-entity-type="true"]', function() {
            const selectedValue = $(this).val();
            const assignedToSelect = $('[data-assigned-to="true"]');
            assignedToSelect.empty();
        
            var options = '<option value="">-- Select --</option>';    

            storage[selectedValue].filter(notInDefinitions).map(user => {
                options += '<option value="'+ user.id +'">'+ user.name +'</option>';
            });

            assignedToSelect.append(options);

            function notInDefinitions(entity) {
                return (
                    (selectedValue != ENTITY_GROUP || entity.id != storage.workflow.applicable_group_id)
                    && storage.workflow.definitions.findIndex(
                        definition => definition.entity_type_id == selectedValue && definition.entity_id == entity.id
                    ) == -1
                );
            }
        });

    })
</script>
@endpush
