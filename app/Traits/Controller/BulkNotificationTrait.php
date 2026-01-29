<?php

namespace App\Traits\Controller;

use App\Models\Communication\BulkNotification;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

trait BulkNotificationTrait
{
    /**
     * @throws Exception
     */
    public function notifications(string $Module): JsonResponse
    {
        return Datatables::of(BulkNotification::query()->where('t_BulkNotifications.Module', $Module)->lock('WITH(NOLOCK)')->with('creator')->select('*'))->addIndexColumn()
            ->editColumn('creator', function (BulkNotification $notification) {
                return $notification->creator?->UserID;
            })->editColumn('CreatedOn', function (BulkNotification $notification) {
                return $notification->CreatedOn->format('F d, Y h:i A');
            })->editColumn('CompleteOn', function (BulkNotification $notification) {
                return $notification->CompleteOn?->format('F d, Y h:i A');
            })->editColumn('Total', function ($notification) {
                return number_format($notification->Total);
            })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                                                                                                   'dbl_click_url' => function (BulkNotification $notification) {
                                                                                                       return route('debt-notification.show', $notification->BulkNotificationID);
                                                                                                   },
                                                                                                  ])->rawColumns(['creator'])->make();
    }
}
