<div>
    <p>{{$data['respect']}}</p><br>

    <p>{{$data['likeCaptain']}} <strong>{{ $data['captain']->company_name }}</strong> {{$data['notice']}} {{$data['reservation']->reserved_places}} {{$data['spots']}} {{$data['forRace']}}<strong>{{ $data['race']->name }}</strong></p>
    <p>{{$data['reservationNumber']}} <strong>{{ $data['reservation']->id }}</strong></p>
    <p>{{$data['downloadBill']}}</p><br>

    <p>{{$data['goodLuck']}}</p><br>

    <p>{{$data['sbr']}}</p>
</div>