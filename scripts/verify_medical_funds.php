<?php
// Quick verification script for Medical Funds tables & indexes (SQL Server)

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function out($label, $value) {
    echo str_pad($label . ':', 45) . (is_bool($value) ? ($value ? 'YES' : 'NO') : $value) . PHP_EOL;
}

$tables = [
    't_MedicalFunds',
    't_MedicalFundContributors',
    't_MedicalFundBeneficiaries',
    't_MedicalFundContributions',
    't_MedicalFundDisbursements',
    't_MedicalFundPackages',
    't_MedicalFundContributorPackages',
    't_Coverages',
    't_MedicalFundPackageCoverages',
    't_BeneficiaryRelationships',
];

echo "TABLES\n";
foreach ($tables as $t) {
    out($t, Schema::hasTable($t));
}

echo PHP_EOL . "COLUMNS\n";
$cols = [
    ['t_MedicalFundBeneficiaries','ContributorID'],
    ['t_MedicalFundContributions','ContributorID'],
    ['t_MedicalFundDisbursements','ContributorID'],
    ['t_MedicalFundDisbursements','CoverageID'],
    ['t_MedicalFundDisbursements','PackageID'],
    ['t_MedicalFundContributorPackages','IsPrimary'],
];
foreach ($cols as [$t,$c]) {
    $exists = Schema::hasTable($t) && Schema::hasColumn($t, $c);
    out($t.'.'.$c, $exists);
}

echo PHP_EOL . "INDEXES\n";
$indexNames = [
    'IX_MedFundContrib_Fund_ContributorNo',
    'IX_MedFundBeneficiaries_Contributor',
    'IX_MedFundContribs_Contributor',
    'IX_MFD_Contributor',
    'IX_MFD_Coverage',
    'IX_MFD_Package',
    'UX_PackageCoverage',
    'UX_Contrib_Primary',
];

$placeholders = implode(',', array_fill(0, count($indexNames), '?'));
$existing = DB::select("SELECT name FROM sys.indexes WHERE name IN ($placeholders)", $indexNames);
$existingNames = array_map(fn($r) => $r->name, $existing);
foreach ($indexNames as $name) {
    out($name, in_array($name, $existingNames, true));
}

