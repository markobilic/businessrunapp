@extends('invoice-template')
@section('content')
<h2 style="text-align: center;">Avansni račun br: {{$invoice['invoiceNumber']}} </h2>
<table style="width: 100%;">
    <tr>
        <td style="width: 50%;">
            <p><strong>{{$buyer['teamName']}}</strong></p>
            <p>Adresa: {{$buyer['teamAddress']}}, {{$buyer['teamPostcode']}}, {{$buyer['teamCity']}}</p>
            <p>PIB: {{$buyer['teamPin']}}</p>
            <p>Matični br: {{$buyer['teamIdentificationNumber']}}</p>

        </td>
        <td style="width: 50%; text-align: right;">
            <p><strong>Br. računa: </strong>{{$invoice['invoiceNumber']}}</p>
            <p><strong>Datum uplate: </strong>{{$invoice['date']}}</p>
            <p><strong>Datum izdavanja: </strong>{{$invoice['dateCreated']}}</p>
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
            <td style="width: 22,5%;height: 40px;text-align: right"> {{$product['price']}}{{$currency}}</td>
            <td style="width: 22,5%;height: 40px;text-align: right"> {{$product['total']}}{{$currency}}</td>
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
<br>
<br>
<br>
<br>
<table>
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