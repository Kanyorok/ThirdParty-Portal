<?php

use App\Http\Controllers\CRM\ReportsController;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Support\Facades\Route;

Route::middleware(['module:200000'])->namespace('CRM')->prefix('crm')->group(function () {

    Route::namespace('Call')->group(function () {
        Route::get('start-call', 'CallController@index')->name('call.incoming.start');

        Route::post('unattached/contact/start', 'CallActionController@startWithContact')->name('contact.call.incoming.start');

        Route::post('schedule/{schedule}/unreachable', 'CallActionController@unreachable')->name('call.unreachable');
        Route::post('schedule/{schedule}/reschedule', 'CallActionController@reschedule')->name('call.reschedule');
    });

    Route::namespace('Contact')->group(function () {
        Route::prefix('contacts/{contact}')->group(function () {
            Route::resource('contacts-calls', 'ContactCallController')->except(['edit', 'show', 'destroy']);

            Route::put('attach-lead', 'ContactActionsController@attachLead')->name('contacts.attach.lead');
            Route::put('attach-client', 'ContactActionsController@attachClient')->name('contacts.attach.client');

            Route::resource('contacts-mail', 'ContactEmailController')->only(['index', 'store']);
        });
        Route::prefix('unattached')->name('unattached.')->group(function () {
            Route::resource('contacts', 'ContactController')->only(['show', 'index']);
        });

        Route::resource('contacts', 'ContactsController')->only(['show', 'update', 'destroy']);
    });

    Route::namespace('Tickets')->group(function () {
        Route::prefix('tickets/{ticket}')->group(function () {
            Route::resource('ticket-comment', 'TicketCommentController')->only(['index', 'store', 'destroy']);
            Route::resource('ticket-watchers', 'TicketPermissionController')/*->parameters(['ticket-watchers' => 'ticket'])*/ ->only(['index', 'store', 'destroy']);

            Route::get('workflows', 'TicketActionsController@workflow')->name('ticket.workflows');
            Route::get('activities', 'TicketActionsController@activity')->name('ticket.activities');
            Route::put('resolved', 'TicketActionsController@resolved')->name('tickets.resolved');
            Route::put('priority', 'TicketActionsController@priority')->name('tickets.priority');
            Route::put('assignee', 'TicketActionsController@assignee')->name('ticket.assignee');
            Route::post('documents', 'TicketActionsController@upload')->name('ticket.upload');
            Route::put('restore', 'TicketController@restore')->name('ticket.restore');
        });

        Route::get('tickets-user', 'UserTicketController')->name('tickets.user');
        Route::resource('approve-ticket', 'TicketReopenController')->parameters(['approve-ticket' => 'ticket'])->only(['update', 'destroy']);
        Route::resource('tickets', 'TicketController')->except(['create']);//edit used for summary
    });

    Route::namespace('Client')->group(function () {
        Route::prefix('clients/{client}')->group(function () {
            Route::get('summary', 'ClientController@summary')->name('clients.summary')->can('view', 'client');
            Route::get('activities', 'ClientActivityController')->name('clients.activities');
            Route::get('relations', 'RelationController')->name('clients.relations');
            Route::get('discussions', 'ClientDiscussionController')->name('clients.discussions');

            Route::resource('client-contacts', 'ClientContactController')->only(['index', 'create', 'store']);//static email conversation show
            Route::get('feedback', 'ClientFeedbackController')->name('client.feedbacks');

            Route::resource('client-calls', 'ClientCallController')->except(['edit', 'destroy']);
            Route::resource('client-meetings', 'ClientMeetingController')->except(['show', 'edit', 'destroy']);
            Route::resource('client-tickets', 'ClientTicketController')->only(['index', 'store']);
            Route::resource('client-tasks', 'ClientTaskController')->only(['index', 'store', 'update']);

            Route::resource('client-marketing-lists', 'ClientMarketingListController')->only(['index', 'store']);
            Route::post('schedule-meeting', 'ClientScheduleController@meeting')->name('client-schedule.meeting');
            Route::post('schedule-call', 'ClientScheduleController@call')->name('client-schedule.call');
            Route::resource('client-schedule', 'ClientScheduleController')->only(['index', 'destroy']);

            Route::resource('client-notes', 'ClientNotesController')->only(['index', 'store']);

            Route::resource('client-mail', 'ClientEmailController')->only(['index', 'store']);
            Route::resource('client-sms', 'ClientMessageController')->only(['index', 'store']);
        });

        Route::get('fetch-clients', 'ClientSelectController')->name('clients.select2');

        Route::resource('clients', 'ClientController')->only(['index', 'show']);//->middleware('can:view,client');
    });

    Route::namespace('Leads')->group(function () {
        Route::prefix('leads/{lead}')->group(function () {

            Route::put('onboarding', 'LeadActionController@onBoarding')->name('leads.onboarding');
            Route::put('status', 'LeadActionController@updateStatus')->name('leads.status');
            Route::get('summary', 'LeadController@summary')->name('leads.summary');
            Route::get('discussions', 'LeadDiscussionController')->name('leads.discussions');
            Route::get('activities', 'LeadActivityController')->name('leads.activities');

            Route::put('assignee', 'LeadUserController@reassign')->name('lead.reassign');
            Route::resource('lead-watchers', 'LeadUserController')->only(['index', 'store', 'destroy']);
            Route::post('schedule-meeting', 'LeadScheduleController@meeting')->name('lead-schedule.meeting');
            Route::post('schedule-call', 'LeadScheduleController@call')->name('lead-schedule.call');
            Route::resource('lead-schedule', 'LeadScheduleController')->only(['index', 'destroy']);

            Route::resource('lead-marketing-lists', 'LeadMarketingListController')->only(['index', 'store']);
            Route::resource('lead-meetings', 'LeadMeetingController')->except(['show', 'edit', 'destroy']);
            Route::resource('lead-calls', 'LeadCallController')->except(['edit', 'destroy']);
            Route::resource('lead-contacts', 'LeadContactController')->only(['index', 'create', 'store']);

            Route::resource('lead-mail', 'LeadEmailController')->only(['index', 'store', 'show']);
            Route::resource('lead-sms', 'LeadMessageController')->only(['index', 'store']);

            Route::resource('lead-tasks', 'LeadTaskController')->only(['index', 'store', 'update']);
            Route::resource('lead-tickets', 'LeadTicketController')->only(['index', 'store']);
            Route::resource('lead-notes', 'LeadNotesController')->only(['index', 'store']);
            Route::resource('lead-products', 'LeadProductController')->only(['index', 'store', 'show', 'destroy']);
        });

        Route::get('fetch-leads', 'LeadSelectController')->name('leads.select2');
        Route::get('leads/analytics', 'LeadActionController@analytics')->name('leads.analytics');
        Route::resource('leads', 'LeadController');
    });

    Route::namespace('Accounts')->group(function () {
        Route::get('accounts/{account}/summary', 'AccountSummaryController')->name('accounts.summary');
        Route::resource('accounts', 'AccountController')->only(['index', 'show']);
    });

    Route::namespace('Marketing')->prefix('marketing')->group(function () {
        Route::namespace('Competitor')->group(function () {
            Route::prefix('competitors/{competitor}')->group(function () {
                Route::resource('competitor-fetch-online', 'CompetitorLLMController')->only(['index', 'store']);

                Route::resource('competitor-descriptions', 'CompetitorItemsController')->only(['index', 'store', 'destroy']);
                Route::resource('competitor-products', 'CompetitorProductController')->except(['create', 'edit']);
            });

            Route::resource('competitors', 'CompetitorController')->except(['create', 'edit']);
        });

        Route::namespace('Campaign')->group(function () {
            Route::resource('approve-campaigns', 'CampaignApprovalController')->parameters(['approve-campaigns' => 'campaign'])->only(['update', 'destroy']);

            Route::get('campaigns/{campaign}/workflows', 'CampaignActionsController@workflow')->name('campaigns.workflow');
            Route::put('campaigns/{campaign}/submit', 'CampaignActionsController@submit')->name('campaigns.submit');
            Route::get('campaigns/{campaign}/contacts', 'CampaignActionsController@contacts')->name('campaigns.contacts');

            Route::get('campaigns/{campaign}/progress', 'CampaignActionsController@progress')->name('campaigns.progress');
            Route::resource('campaigns', 'CampaignController')->except(['create', 'edit']);
        });

        Route::namespace('Lists')->group(function () {
            Route::prefix('marketing-list/{list}')->group(function () {
                Route::match(['get', 'post'], 'leads', 'MarketingListsActionsController@leads')->name('marketing-list.leads');
                Route::match(['get', 'post'], 'clients', 'MarketingListsActionsController@clients')->name('marketing-list.clients');

                Route::resource('marketing-list-upload', 'MarketingListsUploadController')->only(['index', 'store']);
                Route::resource('marketing-list-filters', 'MarketingListFilterController')->only(['index', 'create', 'store', 'destroy']);
            });

            Route::resource('marketing-list', 'MarketingListController')->parameters(['marketing-list' => 'list'])->except('create');
        });

        Route::namespace('Planner')->group(function () {
            Route::resource('marketing-planner-branch', 'MarketingPlanBranchController')->parameters(['marketing-planner-branch' => 'planner'])->only(['update', 'destroy']);
            Route::resource('marketing-planner-manager', 'MarketingPlanManagerController')->parameters(['marketing-planner-manager' => 'planner'])->only(['update', 'destroy']);
            Route::resource('marketing-planner-ceo', 'MarketingPlanCeoController')->parameters(['marketing-planner-ceo' => 'planner'])->only(['update', 'destroy']);

            Route::get('marketing-planner/{planner}/planner-activities-calendar', 'MarketingPlannerCalenderController')->name('planner-activities.calendar');
            Route::resource('marketing-planner/{planner}/planner-activities', 'MarketingPlannerActivityController')->except(['create']);

            Route::get('marketing-planner/{planner}/workflows', 'MarketingPlanActionController@workflow')->name('marketing-planner.workflows');
            Route::put('marketing-planner/{planner}/submit', 'MarketingPlanActionController@submit')->name('marketing-planner.submit');

            Route::resource('master-planner', 'GlobalPlannerController')->parameters(['master-planner' => 'planner'])->only(['store', 'edit', 'update', 'destroy']);

            Route::get('marketing-planner/{planner}/document', 'PlannerDocumentController')->name('planner.document');
            Route::resource('marketing-planner', 'MarketingPlannerController')->parameters(['marketing-planner' => 'planner'])->except(['create']);
        });

        Route::namespace('Socials')->group(function () {
            Route::prefix('socials/{social}')->group(function () {
                Route::resource('social-comment', 'SocialCommentController')->only(['index', 'store', 'destroy']);
            });

            Route::resource('socials', 'SocialController')->except(['edit']);
        });
    });

    Route::namespace('Feedback')->group(function () {
        Route::resource('approve-surveys', 'SurveyApprovalController')->parameters(['approve-surveys' => 'survey'])->only(['update', 'destroy']);

        Route::get('surveys/{survey}workflows', 'SurveyActionController@workflow')->name('surveys.workflows');
        Route::put('surveys/{survey}/submit', 'SurveyActionController@submit')->name('surveys.submit');

        Route::post('survey-question/{question}/survey-question-answer/five', 'SurveyQuestionAnswerController@five')->name('survey-question-answer.five');
        Route::post('survey-question/{question}/survey-question-answer/boolean', 'SurveyQuestionAnswerController@boolean')->name('survey-question-answer.boolean');
        Route::resource('survey-question/{question}/survey-question-answer', 'SurveyQuestionAnswerController')->only(['index', 'store', 'destroy']);//index static in survey.edit
        Route::resource('surveys/{survey}/survey-question', 'SurveyQuestionController')->only(['store', 'update', 'destroy']);
        Route::resource('surveys', 'SurveyController')->except(['create']);

        Route::resource('reviews', 'ReviewController')->only(['index', 'show']);
    });

    Route::namespace('ProductDev')->group(function () {
        Route::prefix('product-development/{product_development}')->group(function () {
            Route::resource('product-development-comment', 'ProductDevelopmentCommentController')->only(['index', 'store', 'destroy']);
            Route::resource('product-feature', 'ProductDevelopmentFeatureController')->only(['store', 'update', 'destroy']);

            Route::put('submit', 'ProductDevelopmentActionsController@submit')->name('product-development.submit');
            Route::put('comments-enable', 'ProductDevelopmentActionsController@enableComment')->name('product-development.comments.enable');
            Route::put('comments-disable', 'ProductDevelopmentActionsController@disableComment')->name('product-development.comments.disable');
            Route::get('workflows', 'ProductDevelopmentActionsController@workflow')->name('product-development.workflows');
            Route::get('activities', 'ProductDevelopmentActionsController@activity')->name('product-development.activities');

            Route::post('documents', 'ProductDevelopmentActionsController@upload')->name('product-development.upload');
        });

        Route::resource('product-development', 'ProductDevelopmentController')->except(['create', 'edit']);
    });

    Route::namespace('Training')->prefix('training')->group(function () {
        Route::resource('categories', 'TrainingCategoryController')->names('crm.training.categories')->except(['show']);
        Route::resource('trainers', 'TrainingTrainerController')->names('crm.training.trainers')->except(['show']);
        Route::get('programs/{id}/participants', 'ClientTrainingProgramController@participants')->name('crm.training.programs.participants');
        Route::post('programs/{id}/participants', 'ClientTrainingProgramController@addParticipants')->name('crm.training.programs.participants.add');
        Route::post('programs/{id}/participants/from-marketing-list', 'ClientTrainingProgramController@addParticipantsFromMarketingList')->name('crm.training.programs.participants.add-list');
        Route::delete('programs/{id}/participants/{clientId}', 'ClientTrainingProgramController@removeParticipant')->name('crm.training.programs.participants.remove');
        Route::get('programs/{id}/certification', 'ClientTrainingProgramController@certification')->name('crm.training.programs.certification');
        Route::post('programs/{id}/certification/issue', 'ClientTrainingProgramController@issueProgramCertificates')->name('crm.training.programs.certification.issue');
        Route::resource('programs', 'ClientTrainingProgramController')->names('crm.training.programs');
        Route::get('sessions/program-clients', 'TrainingSessionController@programClients')->name('crm.training.sessions.program-clients');
        Route::get('sessions/{id}/participants/search', 'TrainingSessionController@sessionParticipantsSearch')->name('crm.training.sessions.participants.search');
        Route::resource('sessions', 'TrainingSessionController')->names('crm.training.sessions');
        Route::post('sessions/{id}/participants', 'TrainingSessionController@addParticipants')->name('crm.training.sessions.participants');
        Route::post('sessions/{id}/participants/add-all', 'TrainingSessionController@addAllParticipants')->name('crm.training.sessions.participants.add-all');
        Route::post('sessions/{id}/participants/bulk-attendance', 'TrainingSessionController@bulkAttendance')->name('crm.training.sessions.participants.bulk-attendance');
        Route::post('sessions/{id}/participants/bulk-certificate', 'TrainingSessionController@bulkIssueCertificates')->name('crm.training.sessions.participants.bulk-certificate');
        Route::get('sessions/{id}/participants/bulk-certificate/status', 'TrainingSessionController@bulkCertificateStatus')->name('crm.training.sessions.participants.bulk-certificate.status');
        Route::post('sessions/{id}/participants/bulk-certificate/cancel', 'TrainingSessionController@cancelBulkIssueCertificates')->name('crm.training.sessions.participants.bulk-certificate.cancel');
        Route::post('sessions/{id}/participants/{participantId}', 'TrainingSessionController@updateParticipant')->name('crm.training.sessions.participants.update');
        Route::post('sessions/{id}/participants/{participantId}/certificate', 'TrainingSessionController@issueCertificate')->name('crm.training.sessions.participants.certificate');
        Route::get('sessions/{id}/feedback', 'TrainingSessionController@feedbackForm')->name('crm.training.sessions.feedback.form');
        Route::post('sessions/{id}/feedback', 'TrainingSessionController@saveFeedback')->name('crm.training.sessions.feedback');
        Route::resource('certificate-templates', 'TrainingCertificateTemplateController')->names('crm.training.certificate-templates')->except(['show']);
        Route::get('certificates', 'TrainingCertificateController@index')->name('crm.training.certificates.index');
        Route::get('reports', 'TrainingReportController@index')->name('crm.training.reports.index');
    });

    Route::namespace('DebtCollection')->group(function () {
        Route::prefix('debt-collection/{debt_product}')->group(function () {
            Route::get('activities', 'LoanActivityController')->name('debt-collection.activities');

            Route::resource('loan-assignment', 'LoanAssignmentController')->only(['index', 'store']);
            Route::resource('debt-collection-tasks', 'LoanTaskController')->only(['index', 'store']);

            Route::post('schedule-meeting', 'LoanScheduleController@meeting')->name('debt-collection-schedule.meeting');
            Route::post('schedule-call', 'LoanScheduleController@call')->name('debt-collection-schedule.call');
            Route::resource('debt-collection-schedule', 'LoanScheduleController')->only(['index', 'destroy']);

            Route::get('collaterals', 'LoanCollateralController')->name('debt-collection.collaterals');

            Route::get('guarantors', 'LoanGuarantorController@index')->name('debt-collection.guarantors');
            Route::post('guarantors/sms', 'LoanGuarantorController@sms')->name('debt-collection.guarantors.sms');
            Route::post('guarantors/email', 'LoanGuarantorController@email')->name('debt-collection.guarantors.email');

            Route::resource('debt-sms', 'LoanMessageController')->only(['index', 'store']);
        });

        Route::namespace('Lists')->group(function () {
            Route::match(['get', 'put'], 'loans-list/{list}/accounts', 'LoanListActionsController')->name('loans-list.accounts');
            Route::match(['get', 'put'], 'loans-list/{list}/accounts', 'LoanListActionsController')->name('loans-list.accounts');

            Route::resource('loans-list/{list}/loans-campaigns', 'LoanListCampaignController')->parameters(['loans-campaigns' => 'campaign'])->only(['index', 'store', 'show', 'edit']);

            Route::resource('loans-list', 'LoanListController')->parameters(['loans-list' => 'list'])->except('create');
        });
        Route::resource('debt-collection', 'LoansController')->only(['index', 'show']);
        Route::get('debt-notification/{bulk_notification}/messages', 'LoanNotificationController@messages')->name('debt-notification.messages');
        Route::resource('debt-notification', 'LoanNotificationController')->parameters(['debt-notification' => 'bulk-notification'])->except(['update', 'destroy']);
    });

    Route::namespace('Base')->prefix('base')->group(function () {
        Route::get('fetch-products', 'ProductSelectController')->name('products.select2');
        Route::resource('documents', 'DocumentController')->parameters(['documents' => 'image'])->only(['show', 'edit', 'destroy']);//@deprecated
        Route::resource('tasks', 'TaskController')->except(['create', 'store', 'edit']);
        Route::resource('notes', 'NotesController')->only(['index', 'show']);
        Route::resource('discussions', 'DiscussionController')->only(['index', 'show']);

        Route::get('messaging/{sms}/summary', 'SMSController')->name('sms.summary');
    });

    Route::namespace('Email')->group(function () {
        Route::prefix('emails/{email}')->group(function () {
            Route::get('summary', 'CrmEmailController@summary')->name('emails.summary');

            Route::post('reply-draft', 'EmailDraftController@reply')->name('email.reply-draft');
            Route::get('draft-edit', 'EmailDraftController@edit')->name('emails.draft-edit');
            Route::post('attachment', 'EmailActionsController@attachment')->name('email.attachment');
            Route::post('send-draft', 'EmailActionsController@send')->name('emails.send-draft');
        });

        Route::prefix('email-conversations/{conversation}')->group(function () {
            Route::resource('conversation-watchers', 'EmailConversationUserController')->only(['index', 'store', 'destroy']);

            Route::get('summary', 'EmailConversationController@summary')->name('email-conversations.summary');
        });

        Route::resource('email-conversations', 'EmailConversationController')->only(['index', 'update', 'show']);
        Route::resource('emails', 'CrmEmailController')->only(['index', 'store', 'update', 'show', 'destroy']);
    });

    Route::namespace('User')->prefix('user')->group(function () {

        Route::resource('schedule', 'ScheduleController');//->only(['index', '''show']);
    });

    Route::namespace('Board')->group(function () {
        Route::post('board/notification', 'BoardNotificationController')->name('bulk-notification.board');

        Route::post('board-meetings/{meeting}/documents', 'BoardMeetingActionController@upload')->name('board-meetings.upload');
        Route::resource('board-meetings', 'BoardMeetingsController')->except(['edit', 'create']);

        Route::resource('board/{board}/board-sms', 'BoardMessageController')->only(['index', 'store']);

        Route::resource('committee', 'CommitteeController')->except(['edit']);
        Route::resource('board', 'BoardController')->except(['edit']);
    });

    Route::withoutMiddleware(TrimStrings::class)->name('crm-')->group(function () {
        Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('reports.export');
        Route::resource('reports', ReportsController::class)->only(['index', 'show']);
    });
});


