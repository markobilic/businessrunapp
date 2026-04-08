@extends('invoice-template')
@section('header')
<table style="width: 100%">
    <tr>
        <td style="width: 100%" align="center">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/images/'.$logo))) }}" width="250">
        </td>
    </tr>
    <tr>
        <td style="width: 100%; text-align: center;">
            <p><b>Endurance Sports Industry sarl</b>
            <br>Ave du Plateau #6
            <br>C/ Gombe, Kinshasa
            <br>RCCM : N° CD-KNG/RCCM/21-B-00761
            <br>ID, NAT : 01-R9000-N78470X - N° IMPOT A2160411N</p>
        </td>
    </tr>
</table>
<br><br>
@stop