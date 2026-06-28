<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an attempt is made to delete a TourPackage that still has
 * bookings attached. Deleting it would cascade-remove the bookings (and their
 * payments), destroying financial history — so it is blocked.
 */
class PackageHasBookingsException extends RuntimeException {}
