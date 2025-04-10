<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ $title }}</title>
    <style>
        @page {
            size: auto;
            margin: 0mm;
        }

        table {
            width: 100%;
            margin-bottom: 20px;
        }

        #activities {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        #activities td, #customers th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #activities tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        #activities tr:hover {
            background-color: #ddd;
        }

        #activities th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #2d3091;
            color: white;
        }

        .form-container {
            margin: 20px;
        }

        .header-row h1, h3, p {
            text-align: center;
            padding: 0px;
            margin: 0px;
        }

        .headoffice-row p, h1 {
            text-align: start;
            padding: 0px;
            margin: 0px;
        }

        @media print {
            header, footer, .no-print {
                display: none;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .content {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page-break {
                page-break-before: always;
            }

        }
    </style>
</head>
<body>
<div>
    <div id="MainContainer" class="form-container">
        <table>
            <tr class="header-row">
                <td>
                    <h1>IMARISHA SAVINGS AND CREDIT</h1>
                    <p>CO-OPERATIVE SOCIETY LTD</p>
                    <p>(FORMER KIPSIGIS TEACHERS SACCO SOCIECTY LTD)</p>
                </td>

            </tr>
            <tr>
                <td style="text-align: center;">HEAD OFFICE: KERICHO/NAKURU ROAD OPPOSITE KOBIL PETROL STATION, PO BOX
                    682-20200, TEL 254 -052-30229, KERICHO
                </td>
            </tr>
        </table>
        <hr>
        <h2 style="text-align: center; margin:5px 0;">{{  $title  }}</h2>

        <table style="border: 1px" id="activities">
            <thead>
            <tr>
                <th>No</th>
                <th>Member No</th>
                <th>Name</th>
                <th>Phone No</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>

            @php
                $id = 0;
                    \App\Models\CrmSMS::query()->whereLike('SMSId','ussd-first-ever%')->with('party')->chunk(2000, function ($sms) use ($id){

                        foreach ($sms as $notification)
                        {
                            $id ++;
                           $data =  '<tr><td>'.number_format($id).'.</td><td>';
                           $data .= ($notification->party instanceof \App\Models\BR\Client)?$notification->party->ClientID:'?';
                           $data.='</td><td>';
                           $data.= ($notification->party instanceof \App\Models\BR\Client)?$notification->party->Name:'?';
                           $data.='</td><td>';
                           $data.=($notification->Phone)??'?';
                           $data.='</td><td>'.$notification->Status->name.'</td> <td>';
                           $data.=($notification->Dated)?$notification->Dated->format('M d, Y h:i a'): $notification->CreatedOn->format('M d, Y h:i a');
                           $data.='</td></tr>';
                            echo  $data;
                        }
                    });
            @endphp
            {{--@foreach(\App\Models\CrmSMS::query()->whereLike('SMSId','ussd-first-ever%')->with('party')->get() as $notification)
                <tr>
                    <td>{{ $loop->iteration }}.</td>
                    <td>{{ ($notification->party instanceof \App\Models\BR\Client)?$notification->party->ClientID:'?'  }}</td>
                    <td>{{ ($notification->party instanceof \App\Models\BR\Client)?$notification->party->Name:'?' }}</td>
                    <td>{{ ($notification->Phone)??'?' }}</td>
                    <td>{{ $notification->Status->name }}</td>
                    <td>{{ ($notification->Dated)?$notification->Dated->format('M d, Y h:i a'): $notification->CreatedOn->format('M d, Y h:i a') }}</td>
                </tr>
            @endforeach--}}
            </tbody>
        </table>

    </div>
    <p style="text-align: center">UNITY IS STRENGTH</p>
</div>
</body>
</html>
