<?php

namespace App\Traits\Controller;

use App\Models\Core\SpecialPermission;
use App\Services\PartyService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

trait SpecialPermissionTrait
{
    private function permissions(MorphMany $query, bool $canDelete, array $with = ['party']): JsonResponse
    {
        $query->lock('WITH(NOLOCK)')->with(in_array('model', $with, true) ? $with : array_merge($with, ['model']));


        return Datatables::of($query->select('*'))->addIndexColumn()
            ->addColumn('action', function (SpecialPermission $permission) use ($canDelete) {
                if ($canDelete) {
                    return '<button type="button" data-click_url="' . $this->_trashRoute($permission) . '" data-info="' . (new PartyService($permission->party))->getName() . '"
                        class="btn btn-danger btn-sm share-permission-trash"><i class="fas fa-trash"></i></button>';
                }
                return '...';
            })->editColumn('party', function (SpecialPermission $permission) {
                return (new PartyService($permission->party))->getDTRow();
            })->editColumn('CreatedOn', function (SpecialPermission $permission) {
                return $permission->CreatedOn?->format('d M, Y H:i');
            })->editColumn('Role', function (SpecialPermission $permission) {
                return $permission->Permission->name;
            })->rawColumns(['action', 'party'])->make();
    }

    abstract protected function _trashRoute(SpecialPermission $permission): string;
}
