<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use App\Models\System\User;
use App\Models\Workflow;
use App\Models\WorkflowDefinition;
use App\Permissions;

class EntityGroupController extends Controller
{
    public function index()
    {
        abort_unless(authUser()->hasPermission(Permissions::SA_ENTITY_GROUP), 403);

        return view('system.entityGroup', [
            'entityGroups' => EntityGroup::whereCategory(EntityGroupCategory::WORK_FLOW_RELATED)->paginate()
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(authUser()->hasPermission(Permissions::SA_ENTITY_GROUP), 403);

        $input = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'category' => 'bail|required|integer|exists:0_entity_group_categories,id'
        ]);

        $entityGroup = new EntityGroup($input);
        $entityGroup->save();
        
        return response()->json([
            'status' => 201,
            'data' => $entityGroup->fresh()
        ]);
    }

    public function destroy(Request $request, EntityGroup $entityGroup)
    {
        abort_unless(authUser()->hasPermission(Permissions::SA_ENTITY_GROUP), 403);
        abort_if($entityGroup->category == EntityGroupCategory::SYSTEM_RESERVED, 404);

        // $applicableGroup = Workflow::where('applicable_group_id', $entityGroup->id)->exists();
        $flowDefinition  = WorkflowDefinition::where('entity_type_id', Entity::GROUP)
                                ->where('entity_id', $entityGroup->id)->exists();
        $userFlowGroup   = User::where('flow_group_id', $entityGroup->id)->exists();

        abort_if(
            ($flowDefinition || $userFlowGroup),
            422,
            'This group is assigned to users or is contained in a workflow definition'
        );
        

        $entityGroup->delete();
        return response()->json(['status' => 204]);
    }
}
