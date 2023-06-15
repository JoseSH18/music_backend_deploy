<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;


class MusicController extends Controller
{
    
   public function buscarArtista(Request $request, $nombre){
       
    $query = $nombre;

    $client = new Client();

    $response = $client->request('GET', 'https://api.spotify.com/v1/search', [
        'query' => [
            'q' => $query,
            'type' => 'artist',
            'limit' => 10
        ],
        'headers' => [
            'Authorization' => 'Bearer ' . $this->obtenerToken(),
            'Accept' => 'application/json'
        ]
    ]);

    $body = json_decode($response->getBody());

    $artistas = $body->artists->items;

    $artistasConInfo = [];

    foreach ($artistas as $artista) {
        $response = $client->request('GET', 'https://api.spotify.com/v1/artists/' . $artista->id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->obtenerToken(),
                'Accept' => 'application/json'
            ]
        ]);

        $body = json_decode($response->getBody());

        $similitud = levenshtein(strtolower($query), strtolower($artista->name));

        $artistasConInfo[] = [
            'id' => $artista->id,
            'nombre' => $artista->name,
            'generos' => $artista->genres,
            'popularidad' => $artista->popularity,
            'imagen' => count($artista->images) > 0 ? $artista->images[0]->url : null,
            'url_spotify' => $artista->external_urls->spotify,
            'seguidores' => $body->followers->total,
            'similitud' => $similitud
        ];
    }

    // Ordenar el arreglo de artistas según la similitud con el nombre buscado
usort($artistasConInfo, function ($a, $b) {
    if ($b['similitud'] === $a['similitud']) {
        return $b['popularidad'] - $a['popularidad'];
    }
    });

    return compact('artistasConInfo');
}
public function detalleArtista(Request $request, $id){
       
    $client = new Client();

    $response = $client->request('GET', 'https://api.spotify.com/v1/artists/' . $id, [
        'headers' => [
            'Authorization' => 'Bearer ' . $this->obtenerToken(),
            'Accept' => 'application/json'
        ]
    ]);

    $body = json_decode($response->getBody());

    $albumsResponse = $client->request('GET', 'https://api.spotify.com/v1/artists/' . $id . '/albums?include_groups=album,single&market=ES', [
        'headers' => [
            'Authorization' => 'Bearer ' . $this->obtenerToken(),
            'Accept' => 'application/json'
        ]
    ]);

    $albumsBody = json_decode($albumsResponse->getBody());

    $topTracksResponse = $client->request('GET', 'https://api.spotify.com/v1/artists/' . $id . '/top-tracks?market=ES', [
        'headers' => [
            'Authorization' => 'Bearer ' . $this->obtenerToken(),
            'Accept' => 'application/json'
        ]
    ]);

    $topTracksBody = json_decode($topTracksResponse->getBody());

    $artist = [
        'id' => $body->id,
        'nombre' => $body->name,
        'generos' => $body->genres,
        'popularidad' => $body->popularity,
        'imagen' => count($body->images) > 0 ? $body->images[0]->url : null,
        'url_spotify' => $body->external_urls->spotify,
        'seguidores' => $body->followers->total,
        'albums' => $albumsBody->items,
        'top_tracks' => $topTracksBody->tracks
    ];

    return compact('artist');
}
public function obtenerTopAlbums() {

    $pais = 'US';
    $periodo = 'week';

    $client = new Client();

    // Obtener los nuevos lanzamientos de Spotify para el país y periodo especificados
    $response = $client->request('GET', 'https://api.spotify.com/v1/browse/new-releases', [
        'query' => [
            'country' => $pais,
            'limit' => 10,
            'offset' => 0,
            'time_range' => 'short_term'
        ],
        'headers' => [
            'Authorization' => 'Bearer ' . $this->obtenerToken(),
            'Accept' => 'application/json'
        ]
    ]);

    $body = json_decode($response->getBody());

    // Crear un arreglo de los álbumes obtenidos
    $albums = [];
    foreach ($body->albums->items as $album) {
        $albums[] = [
            'id' => $album->id,
            'nombre' => $album->name,
            'artista' => $album->artists[0]->name,
            'imagen' => count($album->images) > 0 ? $album->images[0]->url : null,
            'release_date' => $album->release_date,
            'url_spotify' => $album->external_urls->spotify
            
        ];
    }

    return compact('albums');
}

public function detalleAlbum(Request $request, $albumId)
{
    $client = new Client();

    $response = $client->request('GET', 'https://api.spotify.com/v1/albums/' . $albumId, [
        'headers' => [
            'Authorization' => 'Bearer ' . $this->obtenerToken(),
            'Accept' => 'application/json'
        ]
    ]);

    $album = json_decode($response->getBody());

    return compact('album');
}

    private function obtenerToken()
    {
        $clientId = env('SPOTIFY_CLIENT_ID');
        $clientSecret = env('SPOTIFY_CLIENT_SECRET');
        $client = new Client();
        $response = $client->request('POST', 'https://accounts.spotify.com/api/token', [
            'form_params' => [
                'grant_type' => 'client_credentials'
            ],
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($clientId . ':' . $clientSecret),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);
        $data = json_decode($response->getBody());
        return $data->access_token;
    }
}
