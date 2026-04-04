<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * @var array<int, array{0: string, 1: string}>
     */
    private const TAGS = [
        ['Tecnologia', 'tecnologia'],
        ['Sport', 'sport'],
        ['Musica', 'musica'],
        ['Cinema', 'cinema'],
        ['Serie TV', 'serie-tv'],
        ['Gaming', 'gaming'],
        ['Animali', 'animali'],
        ['Attualità', 'attualita'],
        ['Politica', 'politica'],
        ['Educazione', 'educazione'],
        ['Lavoro', 'lavoro'],
        ['Lifestyle', 'lifestyle'],
        ['Salute', 'salute'],
        ['Viaggi', 'viaggi'],
        ['Cibo', 'cibo'],
        ['Social Media', 'social-media'],
        ['Scienza', 'scienza'],
        ['Ambiente', 'ambiente'],
        ['Arte', 'arte'],
        ['Moda', 'moda'],
        ['Finanza', 'finanza'],
        ['Hobby', 'hobby'],
        ['Storia', 'storia'],
        ['Auto e motori', 'auto-e-motori'],
        ['Eventi', 'eventi'],
        ['Famiglia', 'famiglia'],
    ];

    public function run(): void
    {
        foreach (self::TAGS as [$nome, $slug]) {
            Tag::query()->firstOrCreate(
                ['slug' => $slug],
                ['nome' => $nome]
            );
        }
    }
}
