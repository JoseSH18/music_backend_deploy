@foreach ($artistasConInfo as $artista)

    <li>

        <h2>{{ $artista['nombre'] }}</h2>

        @if (isset($artista['imagen']))

            <img src="{{ $artista['imagen'] }}" alt="{{ $artista['nombre'] }}">

        @endif

        <p>{{ $artista['seguidores'] }} seguidores</p>

        <p>Géneros: {{ implode(", ", $artista['generos']) }}</p>

    </li>

@endforeach