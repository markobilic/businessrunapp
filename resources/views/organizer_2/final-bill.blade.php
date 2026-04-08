@extends('invoice-template')
@section('content')
<h2 style="text-align: center;">Račun br: {{$invoice['invoiceNumber']}}</h2>
<table style="width: 100%;">
    <tr>
        <td style="width: 50%;">
            <p><strong>{{$buyer['teamName']}}</strong></p>
            <p>Adresa: {{$buyer['teamAddress']}}, {{$buyer['teamPostcode']}}, {{$buyer['teamCity']}}</p>
            <p>PIB: {{$buyer['teamPin']}}</p>
            <p>Matični br: {{$buyer['teamIdentificationNumber']}}</p>

        </td>
        <td style="width: 50%; text-align: right;">
            <p><strong>Datum izdavanja: </strong>{{$raceFinished}}</p>
            <p><strong>Mesto izdavanja: </strong>Beograd</p>
            <p><strong>Datum prometa: </strong>{{$raceDate}}</p>

        </td>
    </tr>
</table>
<br>
<br>
<table style="width: 100%;">
    <thead>
        <tr style=" background-color: lightgray">
            <th style="width: 50%;height: 40px;">Usluga</th>
            <th style="width: 5%;height: 40px;">Količina</th>
            <th style="width: 22,5%;height: 40px;">Cena</th>
            <th style="width: 22,5%;height: 40px;">Međusuma</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)

        <tr>
            <td style="width: 50%;height: 40px;text-align: left">{{$product['name']}}</td>
            <td style="width: 5%;height: 40px;text-align: center">{{$product['reservationAmount']}}</td>
            <td style="width: 22,5%;height: 40px;text-align: right"> {{number_format($product['price'], 2)}}{{$currency}}</td>
            <td style="width: 22,5%;height: 40px;text-align: right"> {{number_format($product['total'], 2)}}{{$currency}}</td>
        </tr>
        @endforeach
   

        <tr>
            <td style="width: 50%;height: 40px;text-align: left"></td>
            <td style="width: 5%;height: 40px;text-align: center"></td>
            <td style="width: 22,5%;height: 40px;text-align: right">Ukupan iznos</td>
            <td style="width: 22,5%;height: 40px;text-align: right">{{$totalExcludingTax}}{{$currency}}</td>
        </tr>
        <tr>
            <td style="width: 50%;height: 40px;text-align: left"></td>
            <td style="width: 5%;height: 40px;text-align: center"></td>
            <td style="width: 22,5%;height: 40px;text-align: right">PDV ({{$vatPercent}}%)</td>
            <td style="width: 22,5%;height: 40px;text-align: right">{{$vatPrice}}{{$currency}}</td>
        </tr>
        <tr style=" background-color: lightgray">
            <td style="width: 50%;height: 40px;text-align: left"></td>
            <td style="width: 5%;height: 40px;text-align: center"></td>
            <td style="width: 22,5%;height: 40px;text-align: right"><strong>Ukupno</strong></td>
            <td style="width: 22,5%;height: 40px;text-align: right">{{$totalIncludingTax}}{{$currency}} </td>
        </tr>
    </tbody>
</table>
<table>
    @if (!is_null($orderNumber))
        <tr>
            <td><strong>Broj porudžbenice: </strong>{{$orderNumber}}</td>
        </tr>
    @endif
    @if ($isPaid === 0)
        <tr>
            <td>Rok za plaćanje 30 dana.</td>
        </tr>
    @else
        @if($avansneFakture)
            <tr>
                <td>
                    <b>Avansne fakture</b> <br/>
                </td>
            </tr>   
            @foreach ($avansneFakture as $af)
                <tr>
                    <td>
                        Broj izabrane avansne fakture: {{$af['BrojAvansneFakture']}} <br/>
                        Datum izdavanja izabrane avansne fakture: {{ \Carbon\Carbon::parse($af['DatumIzdavanja'])->format('d.m.Y.') }} <br/>
                        Iskorišćena osnovica po avansu - stopa 20% : {{$af['Osnovica20']}} <br>    
                        PDV po avansu - stopa 20% : {{$af['Osnovica20']*0.2}} <br>    
                        Ukupan iskorišćeni iznos po avansu : {{$af['Osnovica20']*1.2}} <br>    
                    </td>
                </tr>
            @endforeach
            <tr>
                <td>
                    Preostalo za uplatu: {{$totalIncludingTaxNumeric - $paidAmount}}{{$currency}}.
                </td>
            </tr>            
        @else
            <tr>
                <td>
                    Preostalo za uplatu: {{$totalIncludingTaxNumeric - $paidAmount}}{{$currency}}.
                </td>
            </tr>  
        @endif
    @endif
</table>
<table style="margin-top: 30px">
    <tr>
        <td><strong>Ovaj račun važi bez pečata i potpisa.</strong></td>
    </tr>
    <tr>
        <td>Fakturisala: Marina Vignjević</td>
    </tr>
</table>
<br>
<br>
<br>
<br>
<br>
<br>
@stop