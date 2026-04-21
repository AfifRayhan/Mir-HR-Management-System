<?php

namespace Tests\Unit;

use App\Models\RosterTime;
use PHPUnit\Framework\TestCase;

class RosterTimeOvernightFlagTest extends TestCase
{
    public function test_roster_time_has_is_overnight_in_fillable()
    {
        $model = new RosterTime();
        $this->assertContains('is_overnight', $model->getFillable());
    }

    public function test_is_overnight_casts_to_boolean()
    {
        $model = new RosterTime();
        $this->assertArrayHasKey('is_overnight', $model->getCasts());
    }
}
