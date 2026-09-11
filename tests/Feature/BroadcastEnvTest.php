<?php

namespace Tests\Feature;

use Tests\TestCase;

class BroadcastEnvTest extends TestCase
{
    public function test_broadcast_config_in_test_env(): void
    {
        $this->assertSame('reverb', config('broadcasting.default'));
        $this->assertNotNull(config('broadcasting.connections.reverb.key'));
    }
}
