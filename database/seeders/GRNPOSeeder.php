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

		// Ensure exactly 3 suppliers with real names (create if missing)
		$supplierSeeds = [
			[ 'reg' => 'REG-SEED-101', 'name' => 'Acme Supplies Ltd', 'trade' => 'Acme Supplies', 'email' => 'ap@acme.example', 'phone' => '0700123001' ],
			[ 'reg' => 'REG-SEED-102', 'name' => 'BlueSky Technologies Ltd', 'trade' => 'BlueSky Tech', 'email' => 'ap@bluesky.example', 'phone' => '0700123002' ],
			[ 'reg' => 'REG-SEED-103', 'name' => 'Nova Industrial Co.', 'trade' => 'Nova Industrial', 'email' => 'ap@nova.example', 'phone' => '0700123003' ],
		];
		$supplierIds = [];
		foreach ($supplierSeeds as $seed) {
			$thirdPartyId = DB::table('t_ThirdParties')->where('RegistrationNumber', $seed['reg'])->value('Id');
			if (!$thirdPartyId) {
				$thirdPartyId = DB::table('t_ThirdParties')->insertGetId([
					'ThirdPartyName' => $seed['name'],
					'TradingName' => $seed['trade'],
					'BusinessType' => 'Company',
					'RegistrationNumber' => $seed['reg'],
					'TaxPIN' => 'P'.substr($seed['reg'], -6),
					'VATNumber' => 'V'.substr($seed['reg'], -6),
					'Country' => 'KE',
					'CountryId' => 1,
					'PhysicalAddress' => 'Nairobi',
					'Email' => $seed['email'],
					'Phone' => $seed['phone'],
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

		$itemIds = DB::table('t_Items')->pluck('Id')->take(10)->toArray();
		if (empty($itemIds)) {
			$itemIds = [1, 2, 3, 4, 5];
		}

		$hasTotalAmount = Schema::hasColumn('t_Orders', 'TotalAmount');
		$hasOrdTotExcl = Schema::hasColumn('t_Orders', 'OrdTotExcl');

		$orders = [];
		$poCounter = 2001;
		foreach ($supplierIds as $sIdx => $supplierId) {
			for ($j = 1; $j <= 8; $j++) { // 5 matching + 3 mismatched
				$orderNo = sprintf('PO-%d%03d', ($sIdx + 1), $j); // deterministic by supplier
				$order = [
					'OrderNo' => $orderNo,
					'AccountID' => $supplierId,
				];
				if (Schema::hasColumn('t_Orders', 'OrderDate')) $order['OrderDate'] = $now->copy()->subDays(14 - ($sIdx * 2) - $j);
				if (Schema::hasColumn('t_Orders', 'Terms')) $order['Terms'] = 'PaymentTerm';
				if (Schema::hasColumn('t_Orders', 'Priority')) $order['Priority'] = 'Normal';
				if (Schema::hasColumn('t_Orders', 'ExtOrdNum')) $order['ExtOrdNum'] = 'RFQ-' . str_pad((string) ($sIdx*100 + $j), 4, '0', STR_PAD_LEFT);
				if (Schema::hasColumn('t_Orders', 'Status')) $order['Status'] = 'open';
				if (Schema::hasColumn('t_Orders', 'Notes')) $order['Notes'] = 'Seeded order S'.($sIdx+1).' #'.$j;
				if (Schema::hasColumn('t_Orders', 'Description')) $order['Description'] = 'Seeded order S'.($sIdx+1).' #'.$j;
				if (Schema::hasColumn('t_Orders', 'DeliveryTerms')) $order['DeliveryTerms'] = 'Within 7 days';
				if (Schema::hasColumn('t_Orders', 'BranchID')) $order['BranchID'] = 1;
				if (Schema::hasColumn('t_Orders', 'CreatedBy')) $order['CreatedBy'] = $actorId;
				if (Schema::hasColumn('t_Orders', 'CreatedOn')) $order['CreatedOn'] = $now;
				if (Schema::hasColumn('t_Orders', 'ModifiedBy')) $order['ModifiedBy'] = $actorId;
				if (Schema::hasColumn('t_Orders', 'ModifiedOn')) $order['ModifiedOn'] = $now;
				$orders[] = $order;
			}
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
			$total = (float) DB::table('t_OrderLines')->where('iOrderID', $row->Id)->sum('LineTotal');
			$update = [];
			if ($hasTotalAmount) $update['TotalAmount'] = $total;
			if ($hasOrdTotExcl) $update['OrdTotExcl'] = $total;
			if (!empty($update)) {
				DB::table('t_Orders')->where('Id', $row->Id)->update($update);
			}
		}
		// -----------------------------------------------------------------------

		// Build GRNs: 5 matching and 3 mismatched per supplier
		$ordersByNo = $insertedOrders->keyBy('OrderNo');
		foreach ($supplierIds as $sIdx => $supplierId) {
			for ($j = 1; $j <= 8; $j++) {
				$orderNo = sprintf('PO-%d%03d', ($sIdx + 1), $j);
				if (!isset($ordersByNo[$orderNo])) continue;
				$row = $ordersByNo[$orderNo];
				$grnId = 'GRN-'.substr($orderNo, -4).'-1';
				$lines = DB::table('t_OrderLines')->where('iOrderID', $row->Id)->get(['iStockCodeID','fQuantity']);
				$match = $j <= 5; // first 5 per supplier match, last 3 mismatch
				foreach ($lines as $li) {
					$poQty = (float)($li->fQuantity ?? 0);
					$recvQty = $match ? $poQty : max(0, $poQty - 1);
					$gr = [
						'GRNID' => $grnId,
						'POID' => $row->OrderNo,
						'SupplierId' => $row->AccountID,
						'ReceivedDate' => $now->copy()->subDays(3),
						'StoreID' => 'STORE-001',
						'ReceivedBy' => $actorId,
						'InspectionStatus' => 'p',
						'TransferStatus' => '1',
						'ItemNo' => $li->iStockCodeID,
						'POQTY' => $poQty,
						'ReceivedQTY' => $recvQty,
						'TransferTo' => '1',
						'TagRequired' => 0,
						'CreatedBy' => $actorId,
						'CreatedOn' => $now,
						'ModifiedBy' => $actorId,
						'ModifiedOn' => $now,
						'DeletedBy' => null,
						'DeletedOn' => null,
					];
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
	}
}
