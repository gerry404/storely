<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AIProductService
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';

    /**
     * Generate product copy from an uploaded image.
     *
     * @param  string  $imagePath  Absolute path to the uploaded image on local disk.
     * @param  string|null  $hint   Optional merchant hint ("sac en cuir", category, etc).
     * @return array{name: string, description: string, tags: array<string>}
     */
    public function generateFromImage(string $imagePath, ?string $hint = null): array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            throw new RuntimeException('AI non configurée. Contactez le support.');
        }

        if (!is_file($imagePath)) {
            throw new RuntimeException("Image introuvable.");
        }

        $mime = mime_content_type($imagePath) ?: 'image/jpeg';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            throw new RuntimeException('Format image non supporté.');
        }

        $base64 = base64_encode(file_get_contents($imagePath));

        $systemPrompt = <<<TXT
Tu es un assistant rédactionnel pour des marchands africains (Cameroun en priorité) qui vendent en ligne sur la plateforme Storely. Tu reçois une photo de produit. Tu dois générer une fiche produit vendeuse en FRANÇAIS, adaptée au marché local (FCFA, ton chaleureux, vocabulaire camerounais si pertinent).

Réponds UNIQUEMENT avec un objet JSON strict, sans texte autour, sans backticks, sans ```json. Le JSON a EXACTEMENT ces clés :
- name: string court et accrocheur (max 60 caractères, Title Case)
- description: string vendeur (2-4 phrases, ~60-120 mots, met en avant les bénéfices concrets)
- tags: array de 4 à 6 strings en minuscules, mots-clés SEO utiles (ex: "sac cuir", "fait main", "livraison douala")

Si la photo n'est pas un produit vendable (personne, paysage, document), renvoie quand même un JSON avec name="", description="", tags=[].
TXT;

        $userText = $hint
            ? "Voici une photo du produit. Indice du vendeur : {$hint}. Génère la fiche."
            : "Voici une photo du produit. Génère la fiche.";

        $payload = [
            'model' => config('services.anthropic.model'),
            'max_tokens' => 700,
            'system' => $systemPrompt,
            'messages' => [[
                'role' => 'user',
                'content' => [
                    ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $mime, 'data' => $base64]],
                    ['type' => 'text', 'text' => $userText],
                ],
            ]],
        ];

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => self::API_VERSION,
            'content-type' => 'application/json',
        ])->timeout(40)->post(self::ENDPOINT, $payload);

        if (!$response->successful()) {
            Log::warning('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('IA indisponible. Réessayez dans un instant.');
        }

        $text = $response->json('content.0.text');
        if (!$text) {
            throw new RuntimeException('Réponse IA vide.');
        }

        $cleaned = trim($text);
        if (str_starts_with($cleaned, '```')) {
            $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $cleaned);
        }

        $parsed = json_decode($cleaned, true);
        if (!is_array($parsed)) {
            Log::warning('AI non-JSON response', ['text' => $text]);
            throw new RuntimeException("L'IA n'a pas pu analyser cette image. Réessayez avec une photo plus claire.");
        }

        $tags = $parsed['tags'] ?? [];
        if (!is_array($tags)) $tags = [];

        $name = trim((string) ($parsed['name'] ?? ''));
        $description = trim((string) ($parsed['description'] ?? ''));

        if ($name === '' && $description === '') {
            throw new RuntimeException("L'IA n'a pas reconnu un produit sur cette photo. Essayez une autre image.");
        }

        return [
            'name' => mb_substr($name, 0, 120),
            'description' => $description,
            'tags' => array_values(array_slice(array_map(fn ($t) => mb_strtolower(trim((string) $t)), $tags), 0, 6)),
        ];
    }
}
