<?php

namespace App\Http\Controllers\DebtCollection;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MailToRequest;
use App\Http\Requests\DebtCollection\GuarantorEmailRequest;
use App\Http\Requests\DebtCollection\GuarantorMessageRequest;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\BR\Guarantor;
use App\Services\BR\ClientService;
use App\Services\BR\LoanService;
use App\Services\PartyService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class LoanGuarantorController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(string $product_id): JsonResponse
    {
        $product = DebtProduct::query()->where('AccountID', $product_id)->oldest('processDate')->first();
        if (!$product instanceof DebtProduct) {
            throw new \RuntimeException('Product not found, maybe closed.', 404);
        }
        $this->authorize('view', $product);

        return Datatables::of($product->guarantors()->with('client')->select('*'))->addIndexColumn()
            ->addColumn('action', function (Guarantor $guarantor) {
                if (!$guarantor->client instanceof Client) {
                    return '..';
                }
                $service = new ClientService($guarantor->client);
                $btn = (is_string($service->phoneNo()))
                    ? '<button class="btn btn-sm btn-primary mx-1 send-message-to-action" title="send sms" data-info="' . route('debt-collection.guarantors.sms', [$guarantor->AccountID]) . '~' . $service->client->Name . '~' . $service->phoneNo() . '"><i class="fas fa-message"></i></button>'
                    : '<button class="btn btn-sm btn-primary mx-1" title="send sms" disabled><i class="fas fa-message"></i></button>';

                $btn .= (is_string($service->getEmail()))
                    ? '<button class="btn btn-sm btn-primary mx-1 send-mail-to-action" data-info="' . route('debt-collection.guarantors.email', [$guarantor->AccountID]) . '~' . $service->client->Name . '~' . $service->getEmail() . '" title="send email"><i class="fas fa-envelope"></i></button>'
                    : '<button class="btn btn-sm btn-primary mx-1" disabled title="send email"><i class="fas fa-envelope"></i></button>';
                return $btn;
            })->editColumn('client', function (Guarantor $guarantor) {
                return (new PartyService($guarantor->client))->getDTRow();
                //  return '<a href="javascript:void(0)" data-click_url="' . route('clients.summary', $guarantor->GuarantorID) . '" data-summary_title="member summary" class="click-summary-data">' . $guarantor->client?->Name . '</a>';
            })->editColumn('GuaranteeAmount', function (Guarantor $guarantor) {
                return number_format($guarantor->GuaranteeAmount, 2);
            })->editColumn('CreatedOn', function (Guarantor $guarantor) {
                return $guarantor->CreatedOn?->format('d M, Y');
            })->rawColumns(['client', 'action'])->make();
    }

    public function sms(GuarantorMessageRequest $request, string $product_id): JsonResponse
    {
        $product = DebtProduct::query()->where('AccountID', $product_id)->oldest('processDate')->first();
        if (!$product instanceof DebtProduct) {
            return $this->errored('Product not found, maybe closed.');
        }
        $this->authorize('view', $product);

        if ($request->has('guarantor_message_content')) {
            $guarantors = collect();
            foreach ($product->guarantors()->with('client')->get() as $guarantor) {
                if (($guarantor?->client instanceof Client) && is_string((new ClientService($guarantor->client))->phoneNo())) {
                    $guarantors->add($guarantor);
                }
            }
            if ($guarantors->count() === 0) {
                return $this->errored('no contacts to send message.');
            }

            $message = $request->validated('guarantor_message_content');
        } elseif ($request->has('message_content')) {
            $message = $request->validated('message_content');
            $guarantor = $product->guarantors()->whereHas('client', function (Builder $query) use ($request) {
                return ClientService::search($query, $request->validated('message_to'));
            })->with('client')->first();
            if (!$guarantor?->client instanceof Client) {
                throw ValidationException::withMessages([
                    'message_to' => 'phone number maybe invalid'
                ]);
            }

            $guarantors = collect()->add($guarantor);
        } else {
            return $this->errored('unexpected error occurred');
        }

        try {
            $activities = (new LoanService($product))->guarantorMessage($guarantors, $message, $request->user());
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error sending sms to guarantor ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('sending message(s)', data: ['activities' => $activities]);
    }

    public function emailAll(GuarantorEmailRequest $request, string $product_id): JsonResponse
    {
        $product = DebtProduct::query()->where('AccountID', $product_id)->oldest('processDate')->first();
        if (!$product instanceof DebtProduct) {
            return $this->errored('Product not found, maybe closed.');
        }
        $this->authorize('view', $product);

        return $this->succeeded('wip');
    }

    public function email(MailToRequest $request, string $product_id): JsonResponse
    {
        $product = DebtProduct::query()->where('AccountID', $product_id)->oldest('processDate')->first();
        if (!$product instanceof DebtProduct) {
            return $this->errored('Product not found, maybe closed.');
        }
        $this->authorize('view', $product);

        $guarantor = $product->guarantors()->whereHas('client', function (Builder $query) use ($request) {
            $query->where('Email', $request->validated('mail_to'));
        })->with('client')->first();
        if (!$guarantor?->client instanceof Client) {
            throw ValidationException::withMessages([
                'mail_to' => 'email maybe invalid'
            ]);
        }

        try {
            $activities = (new LoanService($product))
                ->guarantorMail(collect()->add($guarantor), $request->validated('mail_subject'), $request->validated('mail_content'),
                    $request->user(), $request->getCarbonCopyEmails());
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error sending email to guarantor ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }
        return $this->succeeded('sending email(s)', data: ['activities' => $activities]);

    }
}
