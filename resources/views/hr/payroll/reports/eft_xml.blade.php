<?xml version="1.0" encoding="UTF-8"?>
@php
    $txCount = count($transactions);
    $ctrlSum = collect($transactions)->sum(function ($row) {
        return (float)($row['Amount'] ?? 0);
    });
    $createdAt = now()->utc()->format('Y-m-d\TH:i:s\Z');
    $companyBic = $debtorAccount->bank?->SwiftCode ?? $debtorAccount->bank?->BankCode ?? $debtorAccount->bank?->ClearingCode ?? '';
    $companyBranchCode = $debtorAccount->branch?->BranchCode ?? '';
    $companyBranchName = $debtorAccount->branch?->BranchName ?? $debtorAccount->bank?->BankName ?? '';
    $debtorName = $debtorAccount->AccountName ?? ($company?->BankName ?? 'Organization');
@endphp
<Document xmlns="urn:iso:std:iso:20022:tech:xsd:pacs.008.001.10">
    <FIToFICstmrCdtTrf>
        <GrpHdr>
            <MsgId>{{ $msgId }}</MsgId>
            <CreDtTm>{{ $createdAt }}</CreDtTm>
            <NbOfTxs>{{ $txCount }}</NbOfTxs>
            <SttlmInf>
                <SttlmMtd>CLRG</SttlmMtd>
            </SttlmInf>
            <InstgAgt>
                <FinInstnId>
                    <BICFI>{{ $companyBic }}</BICFI>
                </FinInstnId>
            </InstgAgt>
        </GrpHdr>
        @foreach($transactions as $tx)
            <CdtTrfTxInf>
                <PmtId>
                    <EndToEndId>{{ $tx['EndToEndId'] }}</EndToEndId>
                    <TxId>{{ $tx['TxId'] }}</TxId>
                </PmtId>
                <PmtTpInf>
                    <LclInstrm>
                        <Cd>59</Cd>
                    </LclInstrm>
                </PmtTpInf>
                <IntrBkSttlmAmt Ccy="KES">{{ $tx['Amount'] }}</IntrBkSttlmAmt>
                <InstgAgt>
                    <BrnchId>
                        <Id>{{ $companyBranchCode }}</Id>
                        <Nm>{{ $companyBranchName }}</Nm>
                    </BrnchId>
                </InstgAgt>
                <InstdAgt>
                    <FinInstnId>
                        <BICFI>{{ $tx['BankCode'] }}</BICFI>
                    </FinInstnId>
                    @if(!empty($tx['BranchCode']) || !empty($tx['BranchName']))
                        <BrnchId>
                            <Id>{{ $tx['BranchCode'] }}</Id>
                            <Nm>{{ $tx['BranchName'] }}</Nm>
                        </BrnchId>
                    @endif
                </InstdAgt>
                <Dbtr>
                    <Nm>{{ $debtorName }}</Nm>
                </Dbtr>
                <DbtrAcct>
                    <Id>
                        <Othr>{{ $debtorAccount->AccountNumber }}</Othr>
                    </Id>
                    <Tp>
                        <Cd>1</Cd>
                    </Tp>
                </DbtrAcct>
                <DbtrAgt>
                    <FinInstnId>
                        <BICFI>{{ $companyBic }}</BICFI>
                    </FinInstnId>
                    <BrnchId>
                        <Id>{{ $companyBranchCode }}</Id>
                        <Nm>{{ $companyBranchName }}</Nm>
                    </BrnchId>
                </DbtrAgt>
                <CdtrAgt>
                    <FinInstnId>
                        <BICFI>{{ $tx['BankCode'] }}</BICFI>
                    </FinInstnId>
                    @if(!empty($tx['BranchCode']) || !empty($tx['BranchName']))
                        <BrnchId>
                            <Id>{{ $tx['BranchCode'] }}</Id>
                            <Nm>{{ $tx['BranchName'] }}</Nm>
                        </BrnchId>
                    @endif
                </CdtrAgt>
                <Cdtr>
                    <Nm>{{ $tx['EmployeeName'] }}</Nm>
                </Cdtr>
                <CdtrAcct>
                    <Id>
                        <Othr>{{ $tx['AccountNumber'] }}</Othr>
                    </Id>
                    <Tp>
                        <Cd>1</Cd>
                    </Tp>
                </CdtrAcct>
                <RmtInf>
                    <Ustrd>{{ str_pad($tx['EmployeeName'], 30) }}</Ustrd>
                    <Ustrd>SALARY {{ $tx['EmployeeName'] }} {{ $tx['EmployeeNo'] }}</Ustrd>
                    <Ustrd>{{ str_pad($debtorName, 30) }}</Ustrd>
                    <Ustrd>0000</Ustrd>
                </RmtInf>
            </CdtTrfTxInf>
        @endforeach
    </FIToFICstmrCdtTrf>
</Document>
