<?php

namespace Database\Seeders;

use App\Models\Legal\LegalDocument as LegalLegalDocument;
use Illuminate\Database\Seeder;
use App\Models\LegalDocument;
use Carbon\Carbon;

class LegalDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
            [
                'DocumentTitle'   => 'Procurement Contract - Supplier X',
                'DocumentType'    => 'Contract',
                'SourceModule'    => 'Procurement',
                'SourceID'        => 300000,
                'LinkedDMSDocID'  => 2001,
                'ReviewStatus'    => 'Approved',
                'ExecutionStatus' => 'Signed',
                'DispatchDate'    => Carbon::parse('2025-01-15'),
                'SignOffDate'     => Carbon::parse('2025-01-20'),
                'ReviewedBy'      => 1,
                'ReviewedOn'      => Carbon::parse('2025-01-18'),
                'Remarks'         => 'Executed successfully and filed.',
                'CreatedBy'       => 1,
                'CreatedOn'       => Carbon::now(),
                'ModifiedBy'      => 1,
                'ModifiedOn'      => Carbon::now(),
                'IsActive'        => 1,
            ],
            [
                'DocumentTitle'   => 'Office Lease Agreement - Building A',
                'DocumentType'    => 'Lease',
                'SourceModule'    => 'Property',
                'SourceID'        => 500000,
                'LinkedDMSDocID'  => 2002,
                'ReviewStatus'    => 'Draft',
                'ExecutionStatus' => 'Pending',
                'DispatchDate'    => Carbon::parse('2025-02-01'),
                'SignOffDate'     => null,
                'ReviewedBy'      => 1,
                'ReviewedOn'      => Carbon::parse('2025-01-29'),
                'Remarks'         => 'Pending landlord confirmation.',
                'CreatedBy'       => 2,
                'CreatedOn'       => Carbon::now(),
                'ModifiedBy'      => 1,
                'ModifiedOn'      => Carbon::now(),
                'IsActive'        => 1,
            ],
            [
                'DocumentTitle'   => 'Employee NDA - John Doe',
                'DocumentType'    => 'NDA',
                'SourceModule'    => 'HR',
                'SourceID'        => 1000000,
                'LinkedDMSDocID'  => 2003,
                'ReviewStatus'    => 'Approved',
                'ExecutionStatus' => 'Signed',
                'DispatchDate'    => Carbon::parse('2025-01-10'),
                'SignOffDate'     => Carbon::parse('2025-01-12'),
                'ReviewedBy'      => 1,
                'ReviewedOn'      => Carbon::parse('2025-01-11'),
                'Remarks'         => 'Signed and archived in HR records.',
                'CreatedBy'       => 2,
                'CreatedOn'       => Carbon::now(),
                'ModifiedBy'      => 1,
                'ModifiedOn'      => Carbon::now(),
                'IsActive'        => 1,
            ],
            [
                'DocumentTitle'   => 'Partnership MOU - Company Y',
                'DocumentType'    => 'MOU',
                'SourceModule'    => 'Legal',
                'SourceID'        => 800000,
                'LinkedDMSDocID'  => 2004,
                'ReviewStatus'    => 'In Review',
                'ExecutionStatus' => 'Pending',
                'DispatchDate'    => Carbon::parse('2025-03-01'),
                'SignOffDate'     => null,
                'ReviewedBy'      => 1,
                'ReviewedOn'      => Carbon::parse('2025-02-27'),
                'Remarks'         => 'Awaiting feedback from partner legal team.',
                'CreatedBy'       => 1,
                'CreatedOn'       => Carbon::now(),
                'ModifiedBy'      => 1,
                'ModifiedOn'      => Carbon::now(),
                'IsActive'        => 1,
            ],
            [
                'DocumentTitle'   => 'Service Agreement - IT Maintenance',
                'DocumentType'    => 'Contract',
                'SourceModule'    => 'Procurement',
                'SourceID'        => 300000,
                'LinkedDMSDocID'  => 2005,
                'ReviewStatus'    => 'Rejected',
                'ExecutionStatus' => 'Archived',
                'DispatchDate'    => Carbon::parse('2025-01-05'),
                'SignOffDate'     => null,
                'ReviewedBy'      => 1,
                'ReviewedOn'      => Carbon::parse('2025-01-06'),
                'Remarks'         => 'Rejected due to pricing disagreements.',
                'CreatedBy'       => 1,
                'CreatedOn'       => Carbon::now(),
                'ModifiedBy'      => 1,
                'ModifiedOn'      => Carbon::now(),
                'IsActive'        => 0,
            ],
        ];

        foreach ($documents as $doc) {
            LegalLegalDocument::create($doc);
        }
    }
}
