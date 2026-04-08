@extends('invoice-template')
@section('content')
<h2 style="text-align: center;">Facture Numéro: {{$invoice['invoiceNumber']}} </h2>
<table style="width: 100%;">
    <tr>
        <td style="width: 50%;">
            <p><strong>{{$buyer['teamName']}}</strong></p>
            <p>Adresse: {{$buyer['teamAddress']}}, {{$buyer['teamPostcode']}}, {{$buyer['teamCity']}}</p>
            <p>{{$buyer['teamPin']}}</p>

        </td>
        <td style="width: 50%; text-align: right;">
            <p><strong>Facture Numéro: </strong>{{$invoice['invoiceNumber']}}</p>
            <p><strong>Date: </strong>{{$invoice['date']}}</p>
        </td>
    </tr>
</table>
<br>
<br>
<table style="width: 100%;">
    <thead>
        <tr style=" background-color: lightgray">
            <th style="width: 50%;height: 40px;">Description</th>
            <th style="width: 5%;height: 40px;">Qté</th>
            <th style="width: 22,5%;height: 40px;">Prix</th>
            <th style="width: 22,5%;height: 40px;">Total</th>
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
            <td style="width: 22,5%;height: 40px;text-align: right">Total Prix</td>
            <td style="width: 22,5%;height: 40px;text-align: right">{{$totalExcludingTax}}{{$currency}}</td>
        </tr>
        <tr>
            <td style="width: 50%;height: 40px;text-align: left"></td>
            <td style="width: 5%;height: 40px;text-align: center"></td>
            <td style="width: 22,5%;height: 40px;text-align: right">VAT ({{$vatPercent}}%)</td>
            <td style="width: 22,5%;height: 40px;text-align: right">{{$vatPrice}}{{$currency}}</td>
        </tr>
        <tr style=" background-color: lightgray">
            <td style="width: 50%;height: 40px;text-align: left"></td>
            <td style="width: 5%;height: 40px;text-align: center"></td>
            <td style="width: 22,5%;height: 40px;text-align: right"><strong>Total</strong></td>
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
        <th>Modalités de paiement :</th>
    </tr>
    <tr>
        <td>xxxxxxx</td>
    </tr>
</table>
<table>
    <tr>
        <th style="text-align:left;">Coordonnées Bancaires :</th>
    </tr>
    <tr>
        <td>RAWBANK, N° de Compte : 051000001201074721401-53, CODE SWIFT: RAWBCDKI
illicopay : Congo River Marathon 001021</td>
    </tr>
</table>
<br>
<br>
<br>
<br>
<br>
<br>
@stop