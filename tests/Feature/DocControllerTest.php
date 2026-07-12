<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_docs_index_page_is_displayed(): void
    {
        $this->get('/docs')->assertOk()->assertSee('Documentation');
    }

    public function test_docs_index_lists_project_guides(): void
    {
        $this->get('/docs')->assertSee('Deployment Guide')->assertSee('Roadmap');
    }

    public function test_root_doc_page_is_displayed(): void
    {
        $this->get('/docs/ROADMAP.md')
            ->assertOk()
            ->assertSee('Back to Documentation');
    }

    public function test_feature_doc_page_is_displayed(): void
    {
        $this->get('/docs/features/auth/login')
            ->assertOk()
            ->assertSee('Login');
    }

    public function test_missing_doc_returns_404(): void
    {
        $this->get('/docs/nonexistent-page')->assertNotFound();
    }

    public function test_path_traversal_is_prevented(): void
    {
        $this->get('/docs/../../../etc/passwd')->assertNotFound();
    }

    public function test_docs_link_appears_in_marketing_nav(): void
    {
        $this->get('/')->assertSee('Docs');
    }
}
