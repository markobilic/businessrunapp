@extends('invoice-template')
@section('header')
<table style="width: 100%">
    <tr>
        <td style="width: 50%">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/images/'.$logo))) }}" width="250">
        </td>
        <td style="width: 50%; text-align: right;">
            <p>{{$organizer->name}}</p>
            <p>{{$organizer->address}}</p>
            <p>{{$organizer->city}}</p>
            <p>PIB: {{$organizer->pin}}</p>
            <p>Matični br: {{$organizer->pin_other}}</p>
        </td>
    </tr>
</table>
@stop