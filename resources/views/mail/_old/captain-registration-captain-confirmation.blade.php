<div>
    <h1>Business Run</h1>
    <h2>{{ $data['captain']->team_name }}</h2>
    <p>Uspješno ste registrovali kapetana {{ $data['captain']->name }} {{ $data['captain']->last_name }}.</p>
    <p><strong>Email za login:</strong> {{ $data['captain']->email }}.</p>
        @if($data['password'] != null) 
            <p><strong>Šifra:</strong> {{ $data['password']}}</p>
    <p>Svu sreću u natjecanju vam želi organizator: <strong>{{ $data['organizer']->name }}</strong></p>
</div>