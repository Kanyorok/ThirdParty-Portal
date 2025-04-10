<?php

namespace App\Http\Controllers\CRM\Marketing\Socials;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\NewSocialPostRequest;
use App\Models\CRMImage;
use App\Models\Social;
use App\Services\ImageService;
use App\Services\SocialMediaService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class SocialController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'create', 'show']);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Social::query();
            if ($request->get('filter') === 'scheduled') {
                $query->where('t_Socials.Scheduled_at', '>', now()->addMinutes(30))->whereNull('Published_at');
            } else {
                $query->whereNotNull('Published_at');
            }

            return Datatables::of($query->lock('WITH(NOLOCK)')->with(['creator', 'images' => fn($query) => $query->where('t_CRMImages.MIMEType', 'like', 'image/%')])->select('*'))->addIndexColumn()
                ->addColumn('image', function (Social $social) {
                    $image = $social->images->first();
                    $attr = 'class="img-thumbnail" style="width: 70px;"';
                    if ($image instanceof CRMImage) {
                        $service = new ImageService($image);
                        if ($service->isPrevieable()) {
                            return $service->preview($attr);
                        }
                    }
                    return '-';
                })->editColumn('Response', function ($social) {
                    return '';
                })->editColumn('Type', function (Social $social) {
                    return $social->Type->getIcon();
                })->editColumn('Content', function (Social $social) {
                    return Str::limit($social->Content, 150);
                })->editColumn('LikesCount', function (Social $social) {
                    return number_format($social->LikesCount);
                })->editColumn('Scheduled_at', function (Social $social) {
                    return $social->Scheduled_at?->format('Y-m-d h:i A');
                })->editColumn('Published_at', function (Social $social) {
                    return $social->Published_at?->format('Y-m-d h:i A');
                })->editColumn('ViewCount', function (Social $social) {
                    return number_format($social->ViewsCount);
                })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                    'dbl_click_url' => function (Social $social) {
                        return route('socials.show', $social->SocialID);
                    }
                ])->rawColumns(['image', 'Type'])->make();
        }

        return view('crm.marketing.socials.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('crm.marketing.socials.create');
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(NewSocialPostRequest $request): JsonResponse
    {
        $types = $request->getDestinations();
        $date = $request->getDate();
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($date, $types, $actor, $request) {
                $image = ImageService::createUpload($request->file('image'), Social::getPrimaryKey(), 0, $actor)->image;
                $id = 0;
                foreach ($types as $type) {
                    $service = SocialMediaService::create($type, $request->validated('Content'), $date, $actor);
                    $service->addImage($image, $actor);
                    if ($id === 0) {
                        $image->update(["ImageTypeID" => $service->social->Id]);
                        $id++;
                    }
                    activity()->causedBy($actor)->performedOn($service->social)->event('create')->log('Scheduled a post on ' . $type->name);
                }
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error scheduling post : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('posts scheduled successfully.', route: route('socials.index'));
    }

    /**
     * Display the specified resource. covermart brilliant white 2l.
     */
    public function show(Social $social):View
    {
        return view('crm.marketing.socials.show')
            ->with('images', $social->images()->where('t_CRMImages.MIMEType', 'like', 'image/%')->get())
            ->with('video', $social->images()->where('t_CRMImages.MIMEType', 'like', 'video/%')->first())
            ->with('social', $social);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Social $social)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Social $social)
    {
        //
    }
}
