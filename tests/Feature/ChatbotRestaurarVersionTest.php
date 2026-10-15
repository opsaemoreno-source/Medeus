<?php

namespace Tests\Feature;

use App\Models\ChatbotTopic;
use App\Models\ChatbotTopicVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotRestaurarVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurar_version_sincroniza_con_la_api(): void
    {
        config([
            'services.chatbot.url' => 'https://chatbot.test',
            'services.chatbot.key' => 'clave-test',
        ]);

        Http::fake(['chatbot.test/*' => Http::response(['success' => true])]);

        $topic = ChatbotTopic::create([
            'name' => 'Ventas',
            'slug' => 'ventas',
            'active' => true,
            'config_json' => ['token' => 'a'],
            'analysis_prompt' => 'prompt actual',
        ]);

        $version = ChatbotTopicVersion::create([
            'chatbot_topic_id' => $topic->id,
            'config_json' => ['token' => 'b'],
            'analysis_prompt' => 'prompt anterior',
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('chatbot.restore-version', [$topic, $version]))
            ->assertRedirect(route('chatbot.edit', $topic));

        $topic->refresh();
        $this->assertSame('prompt anterior', $topic->analysis_prompt);
        $this->assertSame('synced', $topic->sync_status);
        $this->assertNull($topic->sync_error);
        $this->assertNotNull($topic->synced_at);

        Http::assertSent(fn (Request $request) =>
            $request->url() === 'https://chatbot.test/admin/sync-topic'
            && $request->header('X-API-KEY') === ['clave-test']
            && $request['slug'] === 'ventas'
            && $request['analysis_prompt'] === 'prompt anterior'
        );
    }

    public function test_un_error_de_la_api_queda_registrado_en_el_tema(): void
    {
        config(['services.chatbot.url' => 'https://chatbot.test']);

        Http::fake(['chatbot.test/*' => Http::response(['success' => false, 'error' => 'tabla no permitida'], 422)]);

        $topic = ChatbotTopic::create(['name' => 'Ventas', 'slug' => 'ventas', 'active' => true]);

        $this->actingAs(User::factory()->create())
            ->post(route('chatbot.activate', $topic));

        $topic->refresh();
        $this->assertSame('error', $topic->sync_status);
        $this->assertStringContainsString('tabla no permitida', $topic->sync_error);
    }
}
