<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use App\Models\EntityGroupMember;
use App\Models\Hr\Employee;
use App\Models\SpecialEntities;
use App\Models\System\AccessRole;
use App\Permissions;
use DB;
use Illuminate\Http\Request;

class EntityGroupMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        abort_unless(authUser()->hasPermission(Permissions::SA_MANAGE_GROUP_MEMBERS), 403);

        $flowGroups     = EntityGroup::whereCategory(EntityGroupCategory::SYSTEM_RESERVED)->get();
        $entityTypes    = Entity::whereIn('id', [Entity::USER, Entity::GROUP, Entity::SPECIAL_GROUP, Entity::ACCESS_ROLE])->get();
        $workflowGroups = EntityGroup::whereCategory(EntityGroupCategory::WORK_FLOW_RELATED)->get();
        $specialGroups  = SpecialEntities::all();
        $users =  DB::table('0_users')
            ->select(
                'id',
                DB::raw("concat(user_id, if(real_name = '', '', concat(' - ', `real_name`))) as name")
            )
            ->where('inactive', 0)
            ->orderBy('real_name')
            ->get();
        $accessRoles = AccessRole::active()->select('id', 'role', 'role as name')->get();
        $entities = [
            Entity::USER => $users,
            Entity::GROUP => $workflowGroups,
            Entity::SPECIAL_GROUP => $specialGroups,
            Entity::ACCESS_ROLE => $accessRoles,
        ];

        $groupMembers = EntityGroupMember::get()
            ->mapToGroups(function ($item) {
                return [$item->group_id => $item];
            })
            ->map(function ($items) {
                return $items->groupBy('entity_type')
                    ->map(function ($groupedItems) {
                        return $groupedItems->pluck('entity_id');
                    });
            })
            ->toArray();
                             
        return view('system/entityGroupMember', 
                    compact(
                        'flowGroups', 
                        'entityTypes',
                        'entities',
                        'groupMembers'
                    )
                );
    }
    
    /**
     * saveGroupMembers
     *
     * @param  mixed $request
     * @return void
     */
    public function saveGroupMembers(Request $request)
    {
        abort_unless(authUser()->hasPermission(Permissions::SA_MANAGE_GROUP_MEMBERS), 403);

        $groupData = json_decode($request->input('groupData'), true);

        $result = DB::transaction(function () use ($groupData) {
            
            foreach ($groupData as $data) {

                $groupId       = $data['groupId'];
                $entityTypeId  = $data['entityTypeId'];
                $groupMembers  = $data['groupMembers'];

                if($groupId && $entityTypeId) {

                    // Clear existing group members
                    EntityGroupMember::where('group_id', $groupId)
                        ->where('entity_type', $entityTypeId)
                        ->delete();

                    // Insert new group members
                    foreach ($groupMembers as $entityId) {
                        EntityGroupMember::create([
                            'group_id'    => $groupId,
                            'entity_type' => $entityTypeId,
                            'entity_id'   => $entityId,
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Group members updated successfully'], 200);

        });

        return $result;
    }

}
