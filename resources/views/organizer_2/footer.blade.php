@extends('invoice-template')
@section('footer')
<table style="width: 100%">

<tr>
    <td style="text-align: center;">
        {{$organizer->name}} • {{$organizer->address}},{{$organizer->postcode}} {{$organizer->city}} • Matični broj: {{$organizer->pin_other}} • PIB: {{$organizer->pin}} • Broj računa: {{$organizer->giro_account}}
        • {{$organizer->website}} • {{$organizer->email }}
    </td>
    <td>

    </td>
</tr>
</table>
@stop