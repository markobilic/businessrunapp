<div>
    <h1>Business Run</h1>
    <h2>{{ $data['captain']->team_name }}</h2>
    <p>Rezervacija broj <strong>#{{ $data['organizer']->id }}</strong> za utrku <strong>{{ $data['race']->name }}</strong> je unešena! </p>
    <p>Svu sreću u natjecanju vam želi organizator: <strong>{{ $data['organizer']->name }}</strong></p>
</div>