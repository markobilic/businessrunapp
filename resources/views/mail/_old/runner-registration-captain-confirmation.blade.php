<div>
    <h1>Business Run</h1>
    <h2>{{ $data['captain']->team_name }}</h2>
    <p>Trkač uspješno prijavljen: {{ $data['runner']->name }} {{ $data['runner']->last_name }}.</p>
    <p>Vaš kapetan: {{ $data['captain']->name }} {{ $data['captain']->last_name }}</p>
</div>