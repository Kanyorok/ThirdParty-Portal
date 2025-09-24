<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GRNPOSeeder extends Seeder
{
	public function run(): void
	{
		$now = Carbon::now();
		$actorId = (int) (DB::table('t_Users')->min('Id') ?? 1);

		// Prefer existing suppliers; ensure at least 3 suppliers by creating minimal ones as needed
		$supplierIds = DB::table('t_Suppliers')->pluck('Id')->take(5)->toArray();

		$ensureCount = 3;
		if (count($supplierIds) < $ensureCount) {
			for ($k = count($supplierIds) + 1; $k <= $ensureCount; $k++) {
				$reg = sprintf('REG-SEED-%03d', $k);
				$thirdPartyId = DB::table('t_ThirdParties')->where('RegistrationNumber', $reg)->value('Id');
				if (!$thirdPartyId) {
					$thirdPartyId = DB::table('t_ThirdParties')->insertGetId([
						'ThirdPartyName' => "Seed Supplier Co. {$k}",
						'TradingName' => "Seed Supplier {$k}",
						'BusinessType' => 'Company',
						'RegistrationNumber' => $reg,
						'TaxPIN' => 'P00000000' . $k,
						'VATNumber' => 'V00000000' . $k,
						'Country' => 'KE',
						'CountryId' => 1,
						'PhysicalAddress' => 'Nairobi',
						'Email' => "seed{$k}@supplier.local",
						'Phone' => '07000000' . str_pad((string)$k, 2, '0', STR_PAD_LEFT),
						'Website' => null,
						'Status' => 'Active',
						'IsPrequalified' => 0,
						'ApprovalStatus' => 'Approved',
						'CreatedBy' => null,
						'CreatedOn' => $now,
						'ModifiedBy' => null,
						'ModifiedOn' => $now,
					]);
				}
				$supplierId = DB::table('t_Suppliers')->where('ThirdPartyID', $thirdPartyId)->value('Id');
				if (!$supplierId) {
					$supplierId = DB::table('t_Suppliers')->insertGetId([
						'ThirdPartyID' => $thirdPartyId,
						'Active_Status' => 1,
						'CreatedBy' => $actorId,
						'CreatedOn' => $now,
						'ModifiedBy' => $actorId,
						'ModifiedOn' => $now,
					]);
				}
				$supplierIds[] = $supplierId;
			}
		}

		$itemIds = DB::table('t_Items')->pluck('Id')->take(10)->toArray();
		if (empty($itemIds)) {
			$itemIds = [1, 2, 3, 4, 5];
		}

		$hasTotalAmount = Schema::hasColumn('t_Orders', 'TotalAmount');
		$hasOrdTotExcl = Schema::hasColumn('t_Orders', 'OrdTotExcl');

		$orders = [];
		for ($i = 1; $i <= 5; $i++) {
			$orderNo = sprintf('PO-1%03d', $i); // e.g. PO-1001
			$supplierId = $supplierIds[($i - 1) % count($supplierIds)];
			$order = [
				'OrderNo' => $orderNo,
				'AccountID' => $supplierId,
			];

			// Conditionally include columns based on schema presence
			if (Schema::hasColumn('t_Orders', 'OrderDate')) $order['OrderDate'] = $now->copy()->subDays(10 - $i);
			if (Schema::hasColumn('t_Orders', 'Terms')) $order['Terms'] = 'PaymentTerm';
			if (Schema::hasColumn('t_Orders', 'Priority')) $order['Priority'] = 'Normal';
			if (Schema::hasColumn('t_Orders', 'ExtOrdNum')) $order['ExtOrdNum'] = 'RFQ-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
			if (Schema::hasColumn('t_Orders', 'Status')) $order['Status'] = 'open';
			if (Schema::hasColumn('t_Orders', 'Notes')) $order['Notes'] = 'Seeded order ' . $i;
			if (Schema::hasColumn('t_Orders', 'Description')) $order['Description'] = 'Seeded order ' . $i;
			if (Schema::hasColumn('t_Orders', 'DeliveryTerms')) $order['DeliveryTerms'] = 'Within 7 days';
			if (Schema::hasColumn('t_Orders', 'BranchID')) $order['BranchID'] = 1;
			if (Schema::hasColumn('t_Orders', 'CreatedBy')) $order['CreatedBy'] = $actorId;
			if (Schema::hasColumn('t_Orders', 'CreatedOn')) $order['CreatedOn'] = $now;
			if (Schema::hasColumn('t_Orders', 'ModifiedBy')) $order['ModifiedBy'] = $actorId;
			if (Schema::hasColumn('t_Orders', 'ModifiedOn')) $order['ModifiedOn'] = $now;

			$orders[] = $order;
		}

		foreach ($orders as $order) {
			$exists = DB::table('t_Orders')->where('OrderNo', $order['OrderNo'])->exists();
			if (! $exists) {
				DB::table('t_Orders')->insert($order);
			}
		}

		$insertedOrders = DB::table('t_Orders')
			->whereIn('OrderNo', array_column($orders, 'OrderNo'))
			->get(['Id', 'OrderNo', 'AccountID']);

		// ------------ Seed order lines per order and backfill totals ------------
		$lineIndex = 0;
		foreach ($insertedOrders as $row) {
			$lineIndex++;
			$itemA = $itemIds[($lineIndex - 1) % count($itemIds)];
			$itemB = $itemIds[($lineIndex) % count($itemIds)];

			$qtyA = 4 + (($lineIndex - 1) % 3); // 4..6
			$qtyB = 5 + (($lineIndex) % 3);     // 5..7
			$priceA = 1500 + ($lineIndex * 250);
			$priceB = 1200 + ($lineIndex * 300);

			$lineA = [
				'iOrderID' => $row->Id,
				'iStockCodeID' => $itemA,
				'fQuantity' => $qtyA,
				'fUnitPriceExcl' => $priceA,
				'LineTotal' => $qtyA * $priceA,
				'CreatedBy' => $actorId,
				'CreatedOn' => $now,
				'ModifiedBy' => $actorId,
				'ModifiedOn' => $now,
			];

			$lineB = [
				'iOrderID' => $row->Id,
				'iStockCodeID' => $itemB,
				'fQuantity' => $qtyB,
				'fUnitPriceExcl' => $priceB,
				'LineTotal' => $qtyB * $priceB,
				'CreatedBy' => $actorId,
				'CreatedOn' => $now,
				'ModifiedBy' => $actorId,
				'ModifiedOn' => $now,
			];

			DB::table('t_OrderLines')->updateOrInsert([
				'iOrderID' => $row->Id,
				'iStockCodeID' => $itemA,
			], $lineA);

			DB::table('t_OrderLines')->updateOrInsert([
				'iOrderID' => $row->Id,
				'iStockCodeID' => $itemB,
			], $lineB);

			// Recompute order total from lines
			$total = (float) DB::table('t_OrderLines')->where('iOrderID', $row->Id)->sum('LineTotal');
			$update = [];
			if ($hasTotalAmount) $update['TotalAmount'] = $total;
			if ($hasOrdTotExcl) $update['OrdTotExcl'] = $total;
			if (!empty($update)) {
				DB::table('t_Orders')->where('Id', $row->Id)->update($update);
			}
		}
		// -----------------------------------------------------------------------

		$grns = [];
		$idx = 0;
		foreach ($insertedOrders as $row) {
			$idx++;
			$useItem = DB::table('t_OrderLines')->where('iOrderID', $row->Id)->value('iStockCodeID') ?? $itemIds[($idx - 1) % count($itemIds)];
			$grns[] = [
				'GRNID' => 'GRN-' . substr($row->OrderNo, -4) . '-1',
				'POID' => $row->OrderNo,
				'SupplierId' => $row->AccountID,
				'ReceivedDate' => $now->copy()->subDays(3),
				'StoreID' => 'STORE-001',
				'ReceivedBy' => $actorId,
				'InspectionStatus' => 'p', // Posted
				'TransferStatus' => '1',
				'ItemNo' => $useItem,
				'POQTY' => 5,
				'ReceivedQTY' => 5,
				'TransferTo' => '1',
				'TagRequired' => 0,
				'CreatedBy' => $actorId,
				'CreatedOn' => $now,
				'ModifiedBy' => $actorId,
				'ModifiedOn' => $now,
				'DeletedBy' => null,
				'DeletedOn' => null,
			];
		}

		// Upsert GRNs so reruns correct prior seeded records
		foreach ($grns as $gr) {
			DB::table('t_GoodsReceipts')->updateOrInsert(
				[
					'GRNID' => $gr['GRNID'],
					'POID' => $gr['POID'],
					'ItemNo' => $gr['ItemNo'],
				],
				$gr
			);
		}
	}
}
