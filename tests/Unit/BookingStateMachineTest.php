<?php

namespace Tests\Unit;

use App\Models\Booking;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pure-logic tests for the Booking status state machine. No DB needed.
 */
class BookingStateMachineTest extends TestCase
{
    public static function allowedTransitions(): array
    {
        return [
            'pending → paid'        => ['pending', 'paid', true],
            'pending → cancelled'   => ['pending', 'cancelled', true],
            'pending → expired'     => ['pending', 'expired', true],
            'paid → confirmed'      => ['paid', 'confirmed', true],
            'paid → completed'      => ['paid', 'completed', true],
            'paid → cancelled'      => ['paid', 'cancelled', true],
            'confirmed → completed' => ['confirmed', 'completed', true],
            'confirmed → cancelled' => ['confirmed', 'cancelled', true],
            'same state pending'    => ['pending', 'pending', true],
            'same state completed'  => ['completed', 'completed', true],
            'null → pending (init)' => [null, 'pending', true],
        ];
    }

    public static function disallowedTransitions(): array
    {
        return [
            'completed → pending'   => ['completed', 'pending'],
            'completed → cancelled' => ['completed', 'cancelled'],
            'cancelled → paid'      => ['cancelled', 'paid'],
            'cancelled → pending'   => ['cancelled', 'pending'],
            'expired → paid'        => ['expired', 'paid'],
            'expired → pending'     => ['expired', 'pending'],
            'paid → pending'        => ['paid', 'pending'],
            'confirmed → paid'      => ['confirmed', 'paid'],
            'confirmed → pending'   => ['confirmed', 'pending'],
        ];
    }

    #[DataProvider('allowedTransitions')]
    public function test_allowed_transitions_return_true(?string $from, string $to, bool $expected): void
    {
        $this->assertSame($expected, Booking::canTransitionTo($from, $to));
    }

    #[DataProvider('disallowedTransitions')]
    public function test_disallowed_transitions_return_false(string $from, string $to): void
    {
        $this->assertFalse(
            Booking::canTransitionTo($from, $to),
            "Transition '{$from}' → '{$to}' should be rejected"
        );
    }

    public function test_terminal_statuses_have_no_outgoing_transitions(): void
    {
        $terminals = ['completed', 'cancelled', 'expired'];

        foreach ($terminals as $terminal) {
            $this->assertSame(
                [],
                Booking::ALLOWED_TRANSITIONS[$terminal],
                "Terminal status '{$terminal}' must have no outgoing transitions"
            );
        }
    }
}
