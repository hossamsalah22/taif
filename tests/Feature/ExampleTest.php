<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The site has no public home page; visitors are sent to the admin login.
     */
    public function test_the_home_page_redirects_to_the_admin_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin/login');
    }
}
