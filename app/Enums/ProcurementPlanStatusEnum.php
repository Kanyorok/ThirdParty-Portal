<?php

namespace App\Enums;

enum ProcurementPlanStatusEnum: string
{
    case Draft = 'Dr';
    case Submitted = 'Su';
    case Approved = 'Ap';
    case Rejected = 'Re';
}
